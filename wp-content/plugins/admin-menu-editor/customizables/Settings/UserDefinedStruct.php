<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

use YahnisElsts\AdminMenuEditor\Customizable\Builders;
use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;

class UserDefinedStruct extends AbstractStructSetting {

	public function __construct($id, StorageInterface $store = null, $params = array()) {
		parent::__construct($id, $store, $params);

		if ( isset($params['childGenerator']) && is_callable($params['childGenerator']) ) {
			$childFactory = new Builders\StructChildSettingFactory($this);

			//Children inherit the parent's tags.
			$childFactory->setTags(...$this->tags);

			$children = call_user_func($params['childGenerator'], $childFactory);
			if ( is_array($children) ) {
				$expectedIdPrefix = $this->getId() . '.';
				$idPrefixLength = strlen($expectedIdPrefix);

				foreach ($children as $child) {
					//This is a hack. There's no convenient way to pass the child key
					//from the factory to this constructor. So the factory keeps a list
					//of IDs and keys, and we use that or fall back to stripping our
					//prefix from the ID.
					$id = $child->getId();
					$childKey = $childFactory->getChildKeyFromId($id);
					if ( $childKey === null ) {
						if ( substr($id, 0, $idPrefixLength) === $expectedIdPrefix ) {
							$childKey = substr($id, $idPrefixLength);
						} else {
							throw new \InvalidArgumentException('Child setting ID must be prefixed with the parent ID');
						}
					}

					$this->registerChild($childKey, $child);
				}
			}
		}
	}

	//Make this public, allowing external code to add children.
	public function createChild($childKey, $className, ...$constructorParams) {
		return parent::createChild($childKey, $className, ...$constructorParams);
	}
}