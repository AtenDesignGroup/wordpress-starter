<?php

class ameRoleUtils {
	/**
	 * Retrieve a list of all known, non-meta capabilities of all roles.
	 *
	 * @param bool $include_multisite_caps
	 * @return array Associative array with capability names as keys
	 */
	public static function get_all_capabilities($include_multisite_caps = null) {
		if ( $include_multisite_caps === null ) {
			$include_multisite_caps = is_multisite();
		}

		//Cache the results.
		static $regular_cache = null, $multisite_cache = null;
		if ( $include_multisite_caps ) {
			if ( isset($multisite_cache) ) {
				return $multisite_cache;
			}
		} else if ( isset($regular_cache) ) {
			return $regular_cache;
		}

		$wp_roles = self::get_roles();
		$capabilities = [];

		//Iterate over all known roles and collect their capabilities
		foreach ($wp_roles->roles as $role) {
			if ( !empty($role['capabilities']) && is_array($role['capabilities']) ) { //Being defensive here
				//We use the "+" operator instead of array_merge() to combine arrays because we don't want
				//integer keys to be renumbered. Technically, capabilities should be strings and not integers,
				//but in practice some plugins do create integer capabilities.
				$capabilities = $capabilities + $role['capabilities'];
			}
		}
		$regular_cache = $capabilities;

		//Add multisite-specific capabilities (not listed in any roles in WP 3.0)
		if ( $include_multisite_caps ) {
			$multisite_caps = [
				'manage_sites'           => 1,
				'manage_network'         => 1,
				'manage_network_users'   => 1,
				'manage_network_themes'  => 1,
				'manage_network_options' => 1,
				'manage_network_plugins' => 1,
			];
			$capabilities = $capabilities + $multisite_caps;
			$multisite_cache = $capabilities;
		}

		return $capabilities;
	}

	/**
	 * Retrieve a list of all known roles and their names.
	 *
	 * @return array Associative array with role IDs as keys and role display names as values
	 */
	public static function get_role_names() {
		$wp_roles = self::get_roles();
		$roles = [];

		foreach ($wp_roles->roles as $role_id => $role) {
			$roles[$role_id] = $role['name'];
		}

		return $roles;
	}

	/**
	 * Get the global WP_Roles instance.
	 *
	 * @return WP_Roles
	 * @global WP_Roles $wp_roles
	 */
	public static function get_roles() {
		//Requires WP 4.3.0.
		return wp_roles();
	}
}

class ameActorAccessCleaner {
	/**
	 * @var array<string, bool>
	 */
	private $userExistsCache = [];

	/**
	 * Remove non-existent actors from a dictionary where the keys are actor IDs.
	 *
	 * @param array|mixed $actorDictionary
	 * @return array|mixed
	 */
	public function cleanUpDictionary($actorDictionary) {
		if ( !is_array($actorDictionary) ) {
			return $actorDictionary;
		}
		if ( empty($actorDictionary) ) {
			return $actorDictionary;
		}

		$cleanedDictionary = [];
		foreach ($actorDictionary as $actorId => $value) {
			//Keep the entry if the actor exists. To reduce the risk of future bugs causing data
			//loss, avoid removing data associated with unknown/unsupported actor IDs.
			if ( $this->tryActorExists($actorId, true) ) {
				$cleanedDictionary[$actorId] = $value;
			}
		}
		return $cleanedDictionary;
	}

	/**
	 * @param string $actorId
	 * @return bool
	 * @throws \ameUnsupportedActorIdException
	 */
	public function actorExists($actorId) {
		$parts = explode(':', $actorId, 2);
		if ( count($parts) !== 2 ) {
			throw new ameUnsupportedActorIdException('Actor ID must contain a colon character.');
		}

		switch ($parts[0]) {
			case 'user':
				return $this->userExists($parts[1]);
			case 'role':
				return $this->roleExists($parts[1]);
			case 'special':
				return $parts[1] == 'super_admin';
		}

		throw new ameUnsupportedActorIdException('Unsupported actor ID prefix "' . $parts[0] . '".');
	}

	/**
	 * Like actorExists(), but returns a default value if it can't parse the actor ID
	 * instead of throwing an exception.
	 *
	 * @param string $actorId
	 * @param bool $defaultResult
	 * @return bool
	 */
	public function tryActorExists($actorId, $defaultResult) {
		try {
			return $this->actorExists($actorId);
		} catch (ameUnsupportedActorIdException $e) {
			return $defaultResult;
		}
	}

	/**
	 * Check if a role exists on the current site.
	 *
	 * @param string $roleId
	 * @return bool
	 */
	public function roleExists($roleId) {
		$role = get_role($roleId);
		return !empty($role);
	}

	/**
	 * Check if a user exists in the database.
	 *
	 * @param $userLogin
	 * @return bool
	 */
	public function userExists($userLogin) {
		//The admin menu (or other configuration) can contain multiple references to the same user,
		//so we cache the results to avoid redundant trips the WP cache or, worse, the database.
		if ( !isset($this->userExistsCache[$userLogin]) ) {
			$user = get_user_by('login', $userLogin);
			$this->userExistsCache[$userLogin] = !empty($user);
		}
		return $this->userExistsCache[$userLogin];
	}
}

class ameUnsupportedActorIdException extends Exception {
}

abstract class ameAccessEvaluatorConfigFields {
	/**
	 * @var \WPMenuEditor
	 */
	protected $menuEditor = null;

	/**
	 * @var bool|null
	 */
	protected $superAdminDefaultAccess = null;

	/**
	 * @var bool|null
	 */
	protected $roleDefaultAccess = null;

	/**
	 * @var array<string, bool>
	 */
	protected $perRoleDefaultAccess = [];

	/**
	 * @var bool
	 */
	protected $defaultEvaluationResult = false;
}

class ameAccessEvaluatorBuilder extends ameAccessEvaluatorConfigFields {
	public function __construct($menuEditor) {
		$this->menuEditor = $menuEditor;
	}

	public static function create($menuEditor) {
		return new ameAccessEvaluatorBuilder($menuEditor);
	}

	public function superAdminDefault($defaultValue) {
		$this->superAdminDefaultAccess = $defaultValue;
		return $this;
	}

	public function roleDefault($defaultValue) {
		$this->roleDefaultAccess = $defaultValue;
		return $this;
	}

	public function roleDefaultFor($roleId, $defaultValue) {
		$this->perRoleDefaultAccess[$roleId] = $defaultValue;
		return $this;
	}

	public function defaultResult($result) {
		$this->defaultEvaluationResult = $result;
		return $this;
	}

	/**
	 * @return \ameActorAccessEvaluator
	 */
	public function build() {
		return new ameActorAccessEvaluator($this);
	}

	/**
	 * @param WP_User $user
	 * @return \ameAccessEvaluatorWithUser
	 */
	public function buildForUser($user) {
		return new ameAccessEvaluatorWithUser($this, $user);
	}
}

abstract class ameBaseActorAccessEvaluator extends ameAccessEvaluatorConfigFields {
	/**
	 * @param \ameAccessEvaluatorBuilder $builder
	 */
	public function __construct($builder) {
		$this->superAdminDefaultAccess = $builder->superAdminDefaultAccess;
		$this->roleDefaultAccess = $builder->roleDefaultAccess;
		$this->perRoleDefaultAccess = $builder->perRoleDefaultAccess;
		$this->defaultEvaluationResult = $builder->defaultEvaluationResult;
		$this->menuEditor = $builder->menuEditor;
	}

	/**
	 * @param array<string,bool> $enabledForActor
	 * @param string $userActorId
	 * @param bool $isSuperAdmin
	 * @param array<string,string> $roleActors Role ID => Actor ID
	 * @return bool
	 */
	protected function evaluate($enabledForActor, $userActorId, $isSuperAdmin, $roleActors) {
		//User-specific settings have the highest priority.
		if ( isset($enabledForActor[$userActorId]) ) {
			return $enabledForActor[$userActorId];
		}

		//Super Admin is next.
		if ( $isSuperAdmin ) {
			if ( isset($enabledForActor['special:super_admin']) ) {
				return $enabledForActor['special:super_admin'];
			} else if ( $this->superAdminDefaultAccess !== null ) {
				return $this->superAdminDefaultAccess;
			}
		}

		//Finally, allow access if at least one role has access.
		$hasAccess = null;
		foreach ($roleActors as $roleId => $roleActorId) {
			$roleHasAccess = null;

			if ( isset($enabledForActor[$roleActorId]) ) {
				$roleHasAccess = $enabledForActor[$roleActorId];
			} else if ( $this->roleDefaultAccess !== null ) {
				$roleHasAccess = $this->roleDefaultAccess;
			} else if ( isset($this->perRoleDefaultAccess[$roleId]) ) {
				$roleHasAccess = $this->perRoleDefaultAccess[$roleId];
			}

			if ( $roleHasAccess !== null ) {
				if ( $hasAccess === null ) {
					$hasAccess = $roleHasAccess;
				} else {
					$hasAccess = $hasAccess || $roleHasAccess;
				}
			}
		}

		if ( $hasAccess !== null ) {
			return $hasAccess;
		}
		return $this->defaultEvaluationResult;
	}

	/**
	 * @param WP_User $user
	 * @return array<string,string> Role ID => Actor ID
	 */
	protected function getUserRoleActors($user) {
		$roles = $this->menuEditor->get_user_roles($user);
		$roleActors = [];
		foreach ($roles as $roleId) {
			$roleActors[$roleId] = 'role:' . $roleId;
		}
		return $roleActors;
	}
}

class ameActorAccessEvaluator extends ameBaseActorAccessEvaluator {
	/**
	 * @param \WP_User $user
	 * @param array<string,bool> $enabledForActor
	 * @return bool
	 */
	public function isEnabledForUser($user, $enabledForActor) {
		return $this->evaluate(
			$enabledForActor,
			'user:' . $user->user_login,
			is_multisite() && is_super_admin($user->ID),
			$this->getUserRoleActors($user)
		);
	}

	/**
	 * Alias for isEnabledForUser().
	 *
	 * @param \WP_User $user
	 * @param array<string,bool> $enabledForActor
	 * @return bool
	 */
	public function userHasAccess($user, $enabledForActor) {
		return $this->isEnabledForUser($user, $enabledForActor);
	}
}

class ameAccessEvaluatorWithUser extends ameBaseActorAccessEvaluator {
	/**
	 * @var WP_User
	 */
	private $user;

	private $userActorId;
	private $isSuperAdmin;
	private $roleActors;

	/**
	 * @param \ameAccessEvaluatorBuilder $builder
	 * @param \WP_User $user
	 */
	public function __construct($builder, $user) {
		parent::__construct($builder);
		$this->user = $user;

		$this->userActorId = 'user:' . $user->user_login;
		$this->isSuperAdmin = is_multisite() && is_super_admin($user->ID);
		$this->roleActors = $this->getUserRoleActors($user);
	}

	/**
	 * @param array<string,bool> $enabledForActor
	 * @return bool
	 */
	public function isEnabled($enabledForActor) {
		return $this->evaluate(
			$enabledForActor,
			$this->userActorId,
			$this->isSuperAdmin,
			$this->roleActors
		);
	}

	/**
	 * Alias for isEnabled().
	 *
	 * @param array<string,bool> $enabledForActor
	 * @return bool
	 */
	public function userHasAccess($enabledForActor) {
		return $this->isEnabled($enabledForActor);
	}

	/**
	 * Get the user for which this evaluator was created.
	 *
	 * @return WP_User
	 */
	public function getUser() {
		return $this->user;
	}
}
