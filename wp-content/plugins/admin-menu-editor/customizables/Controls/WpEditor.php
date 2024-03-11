<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;

class WpEditor extends ClassicControl {
	protected $type = 'wpEditor';
	protected $koComponentName = 'ame-wp-editor';

	protected $rows = 6;
	protected $teeny = true;

	public function __construct($settings = array(), $params = array()) {
		$this->hasPrimaryInput = true;
		parent::__construct($settings, $params);

		if ( isset($params['rows']) ) {
			$this->rows = max(intval($params['rows']), 1);
		}
		if ( isset($params['teeny']) ) {
			$this->teeny = boolval($params['teeny']);
		}
	}

	public function renderContent(Renderer $renderer) {
		wp_editor(
			(string) $this->getMainSettingValue(),
			$this->getPrimaryInputId(),
			array(
				'textarea_name' => $this->getFieldName(),
				'textarea_rows' => $this->rows,
				'teeny'         => $this->teeny,
				'wpautop'       => true,
			)
		);

		$this->outputSiblingDescription();

		static::enqueueDependencies();
	}

	public function getPrimaryInputId() {
		//For wp_editor, the ID must only contain lowercase letters and underscores.
		return preg_replace('/[^a-z_]/', '_', parent::getPrimaryInputId());
	}

	public function supportsLabelAssociation() {
		//This control has a primary input and an ID, but a label element
		//cannot move focus to a visual editor, so we don't need a label.
		return false;
	}

	protected function getAutoAcSettingId() {
		//The visual editor probably needs additional care to detect
		//changes in JavaScript.
		return null;
	}

	public function enqueueKoComponentDependencies() {
		wp_enqueue_media(); //Required for the media button (tested in WP 6.1.1).
		wp_enqueue_editor();

		parent::enqueueKoComponentDependencies();
	}

	protected function getKoComponentParams() {
		$params = parent::getKoComponentParams();
		$params['rows'] = $this->rows;
		$params['teeny'] = $this->teeny;
		return $params;
	}
}