/// <reference types="@types/lodash" />

/// <reference path="knockout.d.ts" />
/// <reference path="common.d.ts" />

declare let wsAmeActorData: any;
declare var wsAmeLodash: _.LoDashStatic;
// noinspection ES6ConvertVarToLetConst -- Intentionally global variable
var AmeActors: AmeActorManager;

type Falsy = false | null | '' | undefined | 0;
type Truthy = true | string | 1;

interface CapabilityMap {
	[capabilityName: string]: boolean;
}

interface IAmeActor {
	getId(): string;

	getDisplayName(): string;

	isUser(): this is IAmeUser;

	hasOwnCap(capability: string): boolean | null;
}

interface IAmeUser extends IAmeActor {
	userLogin: string;
	isSuperAdmin: boolean;

	/**
	 * Get all roles that this user has.
	 *
	 * Note that this returns role IDs, not role objects or actor IDs.
	 */
	getRoleIds(): string[];
}

abstract class AmeBaseActor implements IAmeActor {
	public id: string;
	public displayName: string = '[Error: No displayName set]';
	public capabilities: CapabilityMap;
	public metaCapabilities: CapabilityMap;

	groupActors: string[] = [];

	protected constructor(
		id: string,
		displayName: string,
		capabilities: CapabilityMap,
		metaCapabilities: CapabilityMap = {}
	) {
		this.id = id;
		this.displayName = displayName;
		this.capabilities = capabilities;
		this.metaCapabilities = metaCapabilities;
	}

	/**
	 * Get the capability setting directly from this actor, ignoring capabilities
	 * granted by roles, the Super Admin flag, or the grantedCapabilities feature.
	 *
	 * Returns NULL for capabilities that are neither explicitly granted nor denied.
	 *
	 * @param {string} capability
	 * @returns {boolean|null}
	 */
	hasOwnCap(capability: string): boolean | null {
		if (this.capabilities.hasOwnProperty(capability)) {
			return this.capabilities[capability];
		}
		if (this.metaCapabilities.hasOwnProperty(capability)) {
			return this.metaCapabilities[capability];
		}
		return null;
	}

	static getActorSpecificity(actorId: string) {
		let actorType = actorId.substring(0, actorId.indexOf(':')),
			specificity;
		switch (actorType) {
			case 'role':
				specificity = 1;
				break;
			case 'special':
				specificity = 2;
				break;
			case 'user':
				specificity = 10;
				break;
			default:
				specificity = 0;
		}
		return specificity;
	}

	toString(): string {
		return this.displayName + ' [' + this.id + ']';
	}

	getId(): string {
		return this.id;
	}

	getDisplayName(): string {
		return this.displayName;
	}

	isUser(): this is IAmeUser {
		return false;
	}
}

class AmeRole extends AmeBaseActor {
	name: string;

	constructor(
		roleId: string,
		displayName: string,
		capabilities: CapabilityMap,
		metaCapabilities: CapabilityMap = {}
	) {
		super('role:' + roleId, displayName, capabilities, metaCapabilities);
		this.name = roleId;
	}

	hasOwnCap(capability: string): boolean | null {
		//In WordPress, a role name is also a capability name. Users that have the role "foo" always
		//have the "foo" capability. It's debatable whether the role itself actually has that capability
		//(WP_Role says no), but it's convenient to treat it that way.
		if (capability === this.name) {
			return true;
		}
		return super.hasOwnCap(capability);
	}
}

interface AmeUserPropertyMap {
	user_login: string;
	display_name: string;
	capabilities: CapabilityMap;
	meta_capabilities: CapabilityMap;
	roles: string[];
	is_super_admin: boolean;
	id?: number;
	avatar_html?: string;
}

class AmeUser extends AmeBaseActor implements IAmeUser {
	userLogin: string;
	userId: number = 0;
	roles: string[];
	isSuperAdmin: boolean = false;
	avatarHTML: string = '';

	constructor(
		userLogin: string,
		displayName: string,
		capabilities: CapabilityMap,
		roles: string[],
		isSuperAdmin: boolean = false,
		userId?: number,
		metaCapabilities: CapabilityMap = {}
	) {
		super('user:' + userLogin, displayName, capabilities, metaCapabilities);

		this.userLogin = userLogin;
		this.roles = roles;
		this.isSuperAdmin = isSuperAdmin;
		this.userId = userId || 0;

		if (this.isSuperAdmin) {
			this.groupActors.push(AmeSuperAdmin.permanentActorId);
		}
		for (let i = 0; i < this.roles.length; i++) {
			this.groupActors.push('role:' + this.roles[i]);
		}
	}

	static createFromProperties(properties: AmeUserPropertyMap): AmeUser {
		let user = new AmeUser(
			properties.user_login,
			properties.display_name,
			properties.capabilities,
			properties.roles,
			properties.is_super_admin,
			properties.id,
			properties.meta_capabilities
		);

		if (properties.avatar_html) {
			user.avatarHTML = properties.avatar_html;
		}

		return user;
	}

	isUser(): this is IAmeUser {
		return true;
	}

	getRoleIds(): string[] {
		return this.roles;
	}
}

class AmeSuperAdmin extends AmeBaseActor {
	static permanentActorId = 'special:super_admin';

	constructor() {
		super(AmeSuperAdmin.permanentActorId, 'Super Admin', {});
	}

	hasOwnCap(capability: string): boolean {
		//The Super Admin has all possible capabilities except the special "do_not_allow" flag.
		return (capability !== 'do_not_allow');
	}
}

interface AmeGrantedCapabilityMap {
	[actorId: string]: {
		[capability: string]: any
	}
}

interface AmeCapabilitySuggestion {
	role: AmeRole;
	capability: string;
}

class AmeActorManager implements AmeActorManagerInterface {
	private static _ = wsAmeLodash;

	private roles: { [roleId: string]: AmeRole } = {};
	private users: { [userLogin: string]: AmeUser } = {};
	private specialActors: { [actorId: string]: IAmeActor } = {};
	private grantedCapabilities: AmeGrantedCapabilityMap = {};

	public readonly isMultisite: boolean = false;
	private readonly superAdmin: AmeSuperAdmin;
	private exclusiveSuperAdminCapabilities: Record<string, boolean> = {};

	private tagMetaCaps: Record<string, boolean> = {};
	private suspectedMetaCaps: CapabilityMap;

	private suggestedCapabilities: AmeCapabilitySuggestion[] = [];

	constructor(
		roles: Record<string, { name: string, capabilities: CapabilityMap }>,
		users: Record<string, AmeUserPropertyMap>,
		isMultisite: Truthy | Falsy = false,
		suspectedMetaCaps: CapabilityMap = {}
	) {
		this.isMultisite = !!isMultisite;

		AmeActorManager._.forEach(roles, (roleDetails, id) => {
			if (typeof id === 'undefined') {
				return;
			}

			const role = new AmeRole(
				id,
				roleDetails.name,
				roleDetails.capabilities,
				AmeActorManager._.get(roleDetails, 'meta_capabilities', {})
			);
			this.roles[role.name] = role;
		});

		AmeActorManager._.forEach(users, (userDetails: AmeUserPropertyMap) => {
			const user = AmeUser.createFromProperties(userDetails);
			this.users[user.userLogin] = user;
		});

		this.superAdmin = new AmeSuperAdmin();

		this.suspectedMetaCaps = suspectedMetaCaps;

		const exclusiveCaps: string[] = [
			'update_core', 'update_plugins', 'delete_plugins', 'install_plugins', 'upload_plugins', 'update_themes',
			'delete_themes', 'install_themes', 'upload_themes', 'update_core', 'edit_css', 'unfiltered_html',
			'edit_files', 'edit_plugins', 'edit_themes', 'delete_user', 'delete_users'
		];
		for (let i = 0; i < exclusiveCaps.length; i++) {
			this.exclusiveSuperAdminCapabilities[exclusiveCaps[i]] = true;
		}

		const tagMetaCaps = [
			'manage_post_tags', 'edit_categories', 'edit_post_tags', 'delete_categories',
			'delete_post_tags'
		];
		for (let i = 0; i < tagMetaCaps.length; i++) {
			this.tagMetaCaps[tagMetaCaps[i]] = true;
		}
	}

	// noinspection JSUnusedGlobalSymbols
	actorCanAccess(
		actorId: string,
		grantAccess: { [actorId: string]: boolean },
		defaultCapability: string | null = null
	): boolean | null {
		if (grantAccess.hasOwnProperty(actorId)) {
			return grantAccess[actorId];
		}
		if (defaultCapability !== null) {
			return this.hasCap(actorId, defaultCapability, grantAccess);
		}
		return true;
	}

	getActor(actorId: string): IAmeActor | null {
		if (actorId === AmeSuperAdmin.permanentActorId) {
			return this.superAdmin;
		}

		const separator = actorId.indexOf(':'),
			actorType = actorId.substring(0, separator),
			actorKey = actorId.substring(separator + 1);

		if (actorType === 'role') {
			return this.roles.hasOwnProperty(actorKey) ? this.roles[actorKey] : null;
		} else if (actorType === 'user') {
			return this.users.hasOwnProperty(actorKey) ? this.users[actorKey] : null;
		} else if (this.specialActors.hasOwnProperty(actorId)) {
			return this.specialActors[actorId];
		}

		throw {
			name: 'InvalidActorException',
			message: "There is no actor with that ID, or the ID is invalid.",
			value: actorId
		};
	}

	actorExists(actorId: string): boolean {
		try {
			return (this.getActor(actorId) !== null);
		} catch (exception) {
			const exceptionAsAny = exception as any;
			if (
				(typeof exceptionAsAny === 'object')
				&& (exceptionAsAny !== null)
				&& (typeof exceptionAsAny.name === 'string')
				&& (exceptionAsAny.name === 'InvalidActorException')
			) {
				return false;
			} else {
				throw exception;
			}
		}
	}

	hasCap(actorId: string, capability: string, context?: { [actor: string]: any }): boolean | null {
		context = context || {};
		return this.actorHasCap(actorId, capability, [context, this.grantedCapabilities]);
	}

	hasCapByDefault(actorId: string, capability: string) {
		return this.actorHasCap(actorId, capability);
	}

	private actorHasCap(
		actorId: string,
		capability: string,
		contextList?: Array<Record<string, any>>
	): (boolean | null) {
		//It's like the chain-of-responsibility pattern.

		//Everybody has the "exist" cap, and it can't be removed or overridden by plugins.
		if (capability === 'exist') {
			return true;
		}

		capability = this.mapMetaCap(capability);
		let result = null;

		//Step #1: Check temporary context - unsaved caps, etc. Optional.
		//Step #2: Check granted capabilities. Default on, but can be skipped.
		if (contextList) {
			//Check for explicit settings first.
			let actorValue, len = contextList.length;
			for (let i = 0; i < len; i++) {
				if (contextList[i].hasOwnProperty(actorId)) {
					actorValue = contextList[i][actorId];
					if (typeof actorValue === 'boolean') {
						//Context: grant_access[actorId] = boolean. Necessary because enabling a menu item for a role
						//should also enable it for all users who have that role (unless explicitly disabled for a user).
						return actorValue;
					} else if (actorValue.hasOwnProperty(capability)) {
						//Context: grantedCapabilities[actor][capability] = boolean|[boolean, ...]
						result = actorValue[capability];
						return (typeof result === 'boolean') ? result : result[0];
					}
				}
			}
		}

		//Step #3: Check owned/default capabilities. Always checked.
		let actor = this.getActor(actorId);
		if (actor === null) {
			return false;
		}
		let hasOwnCap = actor.hasOwnCap(capability);
		if (hasOwnCap !== null) {
			return hasOwnCap;
		}

		//Step #4: Users can get a capability through their roles or the "super admin" flag.
		//Only users can have inherited capabilities, so if this actor is not a user, we're done.
		if (actor instanceof AmeUser) {
			//Note that Super Admin has priority. If the user is a super admin, their roles are ignored.
			if (actor.isSuperAdmin) {
				return this.actorHasCap('special:super_admin', capability, contextList);
			}

			//Check if any of the user's roles have the capability.
			result = null;
			for (let index = 0; index < actor.roles.length; index++) {
				let roleHasCap = this.actorHasCap('role:' + actor.roles[index], capability, contextList);
				if (roleHasCap !== null) {
					result = result || roleHasCap;
				}
			}
			if (result !== null) {
				return result;
			}
		}

		if (this.suspectedMetaCaps.hasOwnProperty(capability)) {
			return null;
		}
		return false;
	}

	private mapMetaCap(capability: string): string {
		if (capability === 'customize') {
			return 'edit_theme_options';
		} else if (capability === 'delete_site') {
			return 'manage_options';
		}
		//In Multisite, some capabilities are only available to Super Admins.
		if (this.isMultisite && this.exclusiveSuperAdminCapabilities.hasOwnProperty(capability)) {
			return AmeSuperAdmin.permanentActorId;
		}
		if (this.tagMetaCaps.hasOwnProperty(capability)) {
			return 'manage_categories';
		}
		if ((capability === 'assign_categories') || (capability === 'assign_post_tags')) {
			return 'edit_posts';
		}
		return capability;
	}

	addSpecialActor(actor: IAmeActor) {
		if (actor.getId() === AmeSuperAdmin.permanentActorId) {
			throw 'The Super Admin actor is immutable and cannot be replaced.';
		}
		this.specialActors[actor.getId()] = actor;
	}

	/* -------------------------------
	 * Roles
	 * ------------------------------- */

	getRoles() {
		return this.roles;
	}

	roleExists(roleId: string): boolean {
		return this.roles.hasOwnProperty(roleId);
	};

	getSuperAdmin(): AmeSuperAdmin {
		return this.superAdmin;
	}

	/* -------------------------------
	 * Users
	 * ------------------------------- */

	getUsers() {
		return this.users;
	}

	getUser(login: string): IAmeUser | null {
		return this.users.hasOwnProperty(login) ? this.users[login] : null;
	}

	addUsers(newUsers: AmeUser[]) {
		AmeActorManager._.forEach(newUsers, (user) => {
			this.users[user.userLogin] = user;
		});
	}

	getGroupActorsFor(userLogin: string) {
		return this.users[userLogin].groupActors;
	}

	/* -------------------------------
	 * Granted capability manipulation
	 * ------------------------------- */

	setGrantedCapabilities(newGrants: AmeGrantedCapabilityMap) {
		this.grantedCapabilities = AmeActorManager._.cloneDeep(newGrants);
	}

	getGrantedCapabilities(): AmeGrantedCapabilityMap {
		return this.grantedCapabilities;
	}

	/**
	 * Grant or deny a capability to an actor.
	 */
	setCap(actor: string, capability: string, hasCap: boolean, sourceType?: string, sourceName?: string) {
		this.setCapInContext(this.grantedCapabilities, actor, capability, hasCap, sourceType, sourceName);
	}

	public setCapInContext(
		context: AmeGrantedCapabilityMap,
		actor: string,
		capability: string,
		hasCap: boolean,
		sourceType?: string,
		sourceName?: string
	) {
		capability = this.mapMetaCap(capability);

		const grant = sourceType ? [hasCap, sourceType, sourceName || null] : hasCap;
		AmeActorManager._.set(context, [actor, capability], grant);
	}

	public resetCapInContext(context: AmeGrantedCapabilityMap, actor: string, capability: string) {
		capability = this.mapMetaCap(capability);

		if (AmeActorManager._.has(context, [actor, capability])) {
			delete context[actor][capability];
		}
	}

	/**
	 * Reset all capabilities granted to an actor.
	 * @param actor
	 * @return boolean TRUE if anything was reset or FALSE if the actor didn't have any granted capabilities.
	 */
	resetActorCaps(actor: string): boolean {
		if (AmeActorManager._.has(this.grantedCapabilities, actor)) {
			delete this.grantedCapabilities[actor];
			return true;
		}
		return false;
	}

	/**
	 * Remove redundant granted capabilities.
	 *
	 * For example, if user "jane" has been granted the "edit_posts" capability both directly and via the Editor role,
	 * the direct grant is redundant. We can remove it. Jane will still have "edit_posts" because she's an editor.
	 */
	pruneGrantedUserCapabilities(): AmeGrantedCapabilityMap {
		let _ = AmeActorManager._,
			pruned = _.cloneDeep(this.grantedCapabilities),
			context = [pruned];

		let actorKeys = _(pruned).keys().filter((actorId) => {
			//Skip users that are not loaded.
			const actor = this.getActor(actorId);
			if (actor === null) {
				return false;
			}
			return (actor instanceof AmeUser);
		}).value();

		_.forEach(actorKeys, (actor) => {
			_.forEach(_.keys(pruned[actor]), (capability) => {
				const grant = pruned[actor][capability];
				delete pruned[actor][capability];

				const hasCap = _.isArray(grant) ? grant[0] : grant,
					hasCapWhenPruned = !!this.actorHasCap(actor, capability, context);

				if (hasCap !== hasCapWhenPruned) {
					pruned[actor][capability] = grant; //Restore.
				}
			});
		});

		this.setGrantedCapabilities(pruned);
		return pruned;
	};


	/**
	 * Compare the specificity of two actors.
	 *
	 * Returns 1 if the first actor is more specific than the second, 0 if they're both
	 * equally specific, and -1 if the second actor is more specific.
	 *
	 * @return {Number}
	 */
	static compareActorSpecificity(actor1: string, actor2: string): Number {
		let delta = AmeBaseActor.getActorSpecificity(actor1) - AmeBaseActor.getActorSpecificity(actor2);
		if (delta !== 0) {
			delta = (delta > 0) ? 1 : -1;
		}
		return delta;
	};

	generateCapabilitySuggestions(capPower: Record<string, number>): void {
		let _ = AmeActorManager._;

		interface CapAndPower {
			capability: string;
			power: number;
		}

		let capsByPower = _.memoize((role: AmeRole): CapAndPower[] => {
			let sortedCaps = _.reduce(role.capabilities, (result: CapAndPower[], hasCap, capability) => {
				if (hasCap) {
					result.push({
						capability: capability,
						power: _.get(capPower, [capability], 0)
					});
				}
				return result;
			}, []);

			sortedCaps = _.sortBy(sortedCaps, (item) => -item.power);
			return sortedCaps;
		});

		let rolesByPower: AmeRole[] = _.values<AmeRole>(this.getRoles()).sort(function (a: AmeRole, b: AmeRole) {
			let aCaps = capsByPower(a),
				bCaps = capsByPower(b);

			//Prioritise roles with the highest number of the most powerful capabilities.
			let i = 0, limit = Math.min(aCaps.length, bCaps.length);
			for (; i < limit; i++) {
				let delta = bCaps[i].power - aCaps[i].power;
				if (delta !== 0) {
					return delta;
				}
			}

			//Give a tie to the role that has more capabilities.
			let delta = bCaps.length - aCaps.length;
			if (delta !== 0) {
				return delta;
			}

			//Failing that, just sort alphabetically.
			if (a.displayName > b.displayName) {
				return 1;
			} else if (a.displayName < b.displayName) {
				return -1;
			}
			return 0;
		});

		let preferredCaps = [
			'manage_network_options',
			'install_plugins', 'edit_plugins', 'delete_users',
			'manage_options', 'switch_themes',
			'edit_others_pages', 'edit_others_posts', 'edit_pages',
			'unfiltered_html',
			'publish_posts', 'edit_posts',
			'read'
		];

		let deprecatedCaps = _(_.range(0, 10)).map((level) => 'level_' + level).value();
		deprecatedCaps.push('edit_files');

		let findDiscriminant = (caps: string[], includeRoles: AmeRole[], excludeRoles: AmeRole[]): string => {
			let getEnabledCaps = (role: AmeRole): string[] => {
				return _.keys(_.pickBy(role.capabilities, _.identity));
			};

			//Find caps that all the includeRoles have and excludeRoles don't.
			let includeCaps = _.intersection(..._.map(includeRoles, getEnabledCaps)),
				excludeCaps = _.union(..._.map(excludeRoles, getEnabledCaps)),
				possibleCaps = _.without(includeCaps, ...excludeCaps, ...deprecatedCaps);

			let bestCaps = _.intersection(preferredCaps, possibleCaps);

			if (bestCaps.length > 0) {
				return bestCaps[0];
			} else if (possibleCaps.length > 0) {
				return possibleCaps[0];
			}
			return '';
		};

		let suggestedCapabilities = [];
		for (let i = 0; i < rolesByPower.length; i++) {
			let role = rolesByPower[i];

			let cap = findDiscriminant(
				preferredCaps,
				_.slice(rolesByPower, 0, i + 1),
				_.slice(rolesByPower, i + 1, rolesByPower.length)
			);
			suggestedCapabilities.push({role: role, capability: cap});
		}

		let previousSuggestion = null;
		for (let i = suggestedCapabilities.length - 1; i >= 0; i--) {
			if (suggestedCapabilities[i].capability === null) {
				suggestedCapabilities[i].capability =
					previousSuggestion ? previousSuggestion : 'exist';
			} else {
				previousSuggestion = suggestedCapabilities[i].capability;
			}
		}

		this.suggestedCapabilities = suggestedCapabilities;
	}

	public getSuggestedCapabilities(): AmeCapabilitySuggestion[] {
		return this.suggestedCapabilities;
	}

	createUserFromProperties(properties: AmeUserPropertyMap): IAmeUser {
		return AmeUser.createFromProperties(properties);
	}
}

interface AmeActorManagerInterface {
	getUsers(): AmeDictionary<IAmeUser>;

	getUser(login: string): IAmeUser | null;

	addUsers(newUsers: IAmeUser[]): void;

	createUserFromProperties(properties: AmeUserPropertyMap): IAmeUser;

	getRoles(): AmeDictionary<IAmeActor>;

	getSuperAdmin(): IAmeActor;

	getActor(actorId: string): IAmeActor | null;

	actorExists(actorId: string): boolean;
}

type AmeActorFeatureMapData = { [actorId: string]: boolean };

interface AmeActorFeatureMap {
	get(actorId: string, defaultValue: boolean | null): boolean | null;

	set(actorId: string, value: boolean): void;

	setAll(data: AmeActorFeatureMapData): void;

	getAll(): AmeActorFeatureMapData;

	reset(actorId: string): void;

	resetAll(): void;
}

class AmeObservableActorFeatureMap implements AmeActorFeatureMap {
	private items: { [actorId: string]: KnockoutObservable<boolean | null>; } = {};
	private readonly numberOfObservables: KnockoutObservable<number>;

	constructor(initialData?: AmeDictionary<boolean> | null) {
		this.numberOfObservables = ko.observable(0);
		if (initialData) {
			this.setAll(initialData);
		}
	}

	get(actor: string, defaultValue: boolean | null = null): boolean | null {
		if (this.items.hasOwnProperty(actor)) {
			const value = this.items[actor]();
			if (value === null) {
				return defaultValue;
			}
			return value;
		}
		this.numberOfObservables(); //Establish a dependency.
		return defaultValue;
	}

	set(actor: string, value: boolean) {
		if (!this.items.hasOwnProperty(actor)) {
			this.items[actor] = ko.observable<boolean | null>(value);
			this.numberOfObservables(this.numberOfObservables() + 1);
		} else {
			this.items[actor](value);
		}
	}

	getAll(): AmeActorFeatureMapData {
		let result: AmeActorFeatureMapData = {};
		for (let actorId in this.items) {
			if (this.items.hasOwnProperty(actorId)) {
				const value = this.items[actorId]();
				if (value !== null) {
					result[actorId] = value;
				}
			}
		}
		return result;
	}

	setAll(values: AmeActorFeatureMapData) {
		for (let actorId in values) {
			if (values.hasOwnProperty(actorId)) {
				this.set(actorId, values[actorId]);
			}
		}
	}

	reset(actorId: string): void {
		if (this.items.hasOwnProperty(actorId)) {
			this.items[actorId](null);
		}
	}

	/**
	 * Reset all values to null.
	 */
	resetAll() {
		for (let actorId in this.items) {
			if (this.items.hasOwnProperty(actorId)) {
				this.items[actorId](null);
			}
		}
	}

	isEnabledFor(
		selectedActor: IAmeActor | null,
		allActors: IAmeActor[] | null = null,
		roleDefault: boolean | null = false,
		superAdminDefault: boolean | null = null,
		noValueDefault: boolean = false,
		outIsIndeterminate: KnockoutObservable<boolean> | null = null
	): boolean {
		if (selectedActor === null) {
			if (allActors === null) {
				throw 'When the selected actor is NULL, you must provide ' +
				'a list of all visible actors to determine if the item is enabled for all/any of them';
			}

			//All: Enabled only if it's enabled for all actors.

			//Handle the theoretically impossible case where the actor list is empty.
			const actorCount = allActors.length;
			if (actorCount <= 0) {
				return noValueDefault;
			}

			let isEnabledForSome = false, isDisabledForSome = false;
			for (let index = 0; index < actorCount; index++) {
				if (this.isEnabledFor(allActors[index], allActors, roleDefault, superAdminDefault, noValueDefault)) {
					isEnabledForSome = true;
				} else {
					isDisabledForSome = true;
				}
			}

			if (outIsIndeterminate !== null) {
				outIsIndeterminate(isEnabledForSome && isDisabledForSome);
			}

			return isEnabledForSome && (!isDisabledForSome);
		}

		//Is there an explicit setting for this actor?
		let ownSetting = this.get(selectedActor.getId(), null);
		if (ownSetting !== null) {
			return ownSetting;
		}

		if (selectedActor instanceof AmeUser) {
			//The "Super Admin" setting takes precedence over regular roles.
			if (selectedActor.isSuperAdmin) {
				let superAdminSetting = this.get(AmeSuperAdmin.permanentActorId, superAdminDefault);
				if (superAdminSetting !== null) {
					return superAdminSetting;
				}
			}

			//Use role settings.
			//Enabled for at least one role = enabled.
			//Disabled for at least one role and no settings for other roles = disabled.
			let isEnabled: boolean | null = null;
			for (let i = 0; i < selectedActor.roles.length; i++) {
				let roleSetting = this.get('role:' + selectedActor.roles[i], roleDefault);
				if (roleSetting !== null) {
					if (isEnabled === null) {
						isEnabled = roleSetting;
					} else {
						isEnabled = isEnabled || roleSetting;
					}
				}
			}

			if (isEnabled !== null) {
				return isEnabled;
			}

			//If we get this far, it means that none of the user's roles have
			//a setting for this item. Fall through to the final default.
		}

		return noValueDefault;
	}

	setEnabledFor(
		selectedActor: IAmeActor | null,
		enabled: boolean,
		allActors: IAmeActor[] | null = null,
		defaultValue: boolean | null = null
	) {
		if (selectedActor === null) {
			if (allActors === null) {
				throw 'When the selected actor is NULL, you must provide ' +
				'a list of all visible actors so that the item can be enabled or disabled for all of them';
			}

			//Enable/disable the item for all actors.
			if (enabled === defaultValue) {
				//Since the new value is the same as the default,
				//this is equivalent to removing all settings.
				this.resetAll();
			} else {
				for (let i = 0; i < allActors.length; i++) {
					this.set(allActors[i].getId(), enabled);
				}
			}
		} else {
			this.set(selectedActor.getId(), enabled);
		}
	}
}

enum AmeRoleCombinationMode {
	/**
	 * Enabled if enabled for every role the user has.
	 */
	Every,
	/**
	 * Enabled if enabled for at least one role.
	 */
	Some,
	/**
	 * As "Some", except when at least role one has a custom setting that is `false`
	 * (i.e. disabled) and none of the other roles have custom settings.
	 *
	 * This way explicit "disable"/"deny" settings take precedence over settings
	 * or permissions that are enabled by default.
	 */
	CustomOrSome
}

interface AmeActorFeatureStrategySettings {
	getSelectedActor: () => IAmeActor | null;
	getAllActors: () => IAmeActor[];
	superAdminDefault: boolean | null;
	roleDefault: boolean | null | ((roleName: string) => boolean | null);
	roleCombinationMode: AmeRoleCombinationMode
	noValueDefault: boolean;

	/**
	 * Whether to automatically reset (i.e. remove) all settings when changing
	 * all actors to a new value that is the same as the default.
	 */
	autoResetAll: boolean;
}

type AmeRequiredFeatureStrategyKeys = 'getSelectedActor' | 'getAllActors';

/**
 * Most of the settings are optional when creating a new strategy.
 */
type AmeFeatureStrategyConstructorSettings =
	Partial<Omit<AmeActorFeatureStrategySettings, AmeRequiredFeatureStrategyKeys>>
	& Pick<AmeActorFeatureStrategySettings, AmeRequiredFeatureStrategyKeys>;

const AmeActorFeatureStrategyDefaults: Omit<AmeActorFeatureStrategySettings, AmeRequiredFeatureStrategyKeys> = {
	superAdminDefault: null,
	roleDefault: null,
	roleCombinationMode: AmeRoleCombinationMode.CustomOrSome,
	noValueDefault: false,
	autoResetAll: true,
}

class AmeActorFeatureStrategy {
	private readonly settings: AmeActorFeatureStrategySettings;

	constructor(settings: AmeFeatureStrategyConstructorSettings) {
		this.settings = Object.assign({}, AmeActorFeatureStrategyDefaults, settings);
	}

	isFeatureEnabled(
		actorFeatureMap: AmeActorFeatureMap,
		outIsIndeterminate: KnockoutObservable<boolean> | null = null
	): boolean {
		return this.isFeatureEnabledForActor(
			actorFeatureMap,
			this.settings.getSelectedActor(),
			outIsIndeterminate
		);
	}

	private isFeatureEnabledForActor(
		actorFeatureMap: AmeActorFeatureMap,
		actor: IAmeActor | null,
		outIsIndeterminate: KnockoutObservable<boolean> | null = null
	): boolean {
		if (actor === null) {
			return this.checkAllActors(actorFeatureMap, outIsIndeterminate);
		}

		if (outIsIndeterminate !== null) {
			//The result can only be indeterminate if there are multiple actors.
			outIsIndeterminate(false);
		}

		//Is there an explicit setting for this actor?
		const ownSetting = actorFeatureMap.get(actor.getId(), null);
		if (ownSetting !== null) {
			return ownSetting;
		}

		if (actor.isUser()) {
			//The "Super Admin" setting takes precedence over regular roles.
			if (actor.isSuperAdmin) {
				const superAdminSetting = actorFeatureMap.get(
					AmeSuperAdmin.permanentActorId,
					this.settings.superAdminDefault
				);
				if (superAdminSetting !== null) {
					return superAdminSetting;
				}
			}

			const isEnabledForRoles = this.checkRoles(actorFeatureMap, actor.getRoleIds());
			if (isEnabledForRoles !== null) {
				return isEnabledForRoles;
			}

			//If we get this far, it means that none of the user's roles have
			//a setting for this item. Fall through to the final default.
		}

		return this.settings.noValueDefault;
	}

	private checkAllActors(
		actorFeatureMap: AmeActorFeatureMap,
		outIsIndeterminate: KnockoutObservable<boolean> | null = null
	): boolean {
		if (this.settings.getAllActors === null) {
			throw (
				'When the selected actor is NULL, you must provide ' +
				'a callback that retrieves all actors so that it is possible to determine if ' +
				'the item is enabled for all/any of them'
			);
		}
		const allActors = this.settings.getAllActors();
		//Handle the theoretically impossible case where the actor list is empty.
		const actorCount = allActors.length;
		if (actorCount <= 0) {
			return this.settings.noValueDefault;
		}

		let isEnabledForSome = false, isDisabledForSome = false;
		for (let i = 0; i < actorCount; i++) {
			const actor = allActors[i];
			if (this.isFeatureEnabledForActor(actorFeatureMap, actor)) {
				isEnabledForSome = true;
			} else {
				isDisabledForSome = true;
			}
		}

		if (outIsIndeterminate !== null) {
			outIsIndeterminate(isEnabledForSome && isDisabledForSome);
		}
		return isEnabledForSome && !isDisabledForSome;
	}

	private checkRoles(actorFeatureMap: AmeActorFeatureMap, roles: string[]): boolean | null {
		const length = roles.length;
		if (length === 0) {
			return null;
		}

		//Check role settings.
		let foundAnySettings = false;
		let areAllTrue = true;
		let areSomeTrue = false;

		let foundAnyCustomSettings = false;
		let areAllCustomTrue = true;
		let areSomeCustomTrue = false;

		for (let i = 0; i < length; i++) {
			let roleSetting = actorFeatureMap.get('role:' + roles[i], null);

			if (roleSetting !== null) {
				foundAnyCustomSettings = true;
				areSomeCustomTrue = areSomeCustomTrue || roleSetting;
				areAllCustomTrue = areAllCustomTrue && roleSetting;
			} else {
				roleSetting = (typeof this.settings.roleDefault === 'function')
					? this.settings.roleDefault(roles[i])
					: this.settings.roleDefault;
			}

			if (roleSetting !== null) {
				foundAnySettings = true;
				areAllTrue = areAllTrue && roleSetting;
				areSomeTrue = areSomeTrue || roleSetting;
			}
		}

		if (!foundAnySettings) {
			return null;
		}

		switch (this.settings.roleCombinationMode) {
			case AmeRoleCombinationMode.Every:
				return areAllTrue;
			case AmeRoleCombinationMode.Some:
				return areSomeTrue;
			case AmeRoleCombinationMode.CustomOrSome:
				return foundAnyCustomSettings ? areSomeCustomTrue : areSomeTrue;
		}
	}

	setFeatureEnabled(
		actorFeatureMap: AmeActorFeatureMap,
		enabled: boolean
	) {
		this.setFeatureEnabledForActor(
			actorFeatureMap,
			this.settings.getSelectedActor(),
			enabled
		);
	}

	private setFeatureEnabledForActor(
		actorFeatureMap: AmeActorFeatureMap,
		actor: IAmeActor | null,
		enabled: boolean
	) {
		if (actor === null) {
			this.setAllActorStates(actorFeatureMap, enabled);
			return;
		}

		actorFeatureMap.set(actor.getId(), enabled);
	}

	private setAllActorStates(actorFeatureMap: AmeActorFeatureMap, enabled: boolean) {
		if (this.settings.getAllActors === null) {
			throw (
				'When the selected actor is NULL, you must provide a callback that retrieves ' +
				'a list of all actors so that the item can be enabled or disabled for all of them'
			);
		}

		//Enable/disable the feature for all actors.
		if (this.settings.autoResetAll && (enabled === this.settings.noValueDefault)) {
			//Since the new value is the same as the configured default,
			//this is equivalent to removing all settings.
			actorFeatureMap.resetAll();
		} else {
			const allActors = this.settings.getAllActors();
			for (let i = 0; i < allActors.length; i++) {
				actorFeatureMap.set(allActors[i].getId(), enabled);
			}
		}
	}
}

class AmeActorFeatureState {
	public readonly isEnabled: KnockoutComputed<boolean>;
	public readonly isIndeterminate: KnockoutComputed<boolean>;

	constructor(
		public readonly actorFeatureMap: AmeActorFeatureMap,
		public readonly strategy: AmeActorFeatureStrategy
	) {
		const _isIndeterminate = ko.observable(false);
		this.isIndeterminate = ko.pureComputed(() => _isIndeterminate());

		this.isEnabled = ko.computed({
			read: () => {
				return this.strategy.isFeatureEnabled(this.actorFeatureMap, _isIndeterminate);
			},
			write: (value: any) => {
				const enabled = !!value;
				this.strategy.setFeatureEnabled(this.actorFeatureMap, enabled);
			}
		})
	}

	toJs(): AmeActorFeatureMapData {
		return this.actorFeatureMap.getAll();
	}
}

if (typeof wsAmeActorData !== 'undefined') {
	AmeActors = new AmeActorManager(
		wsAmeActorData.roles,
		wsAmeActorData.users,
		wsAmeActorData.isMultisite,
		wsAmeActorData.suspectedMetaCaps
	);

	if (typeof wsAmeActorData['capPower'] !== 'undefined') {
		AmeActors.generateCapabilitySuggestions(wsAmeActorData['capPower']);
	}
}
