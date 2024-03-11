<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;

/**
 * This control just outputs a predefined HTML string.
 */
class StaticHtml extends ClassicControl {
	protected $type = 'static';
	protected $koComponentName = 'ame-static-html';

	/**
	 * @var bool The HTML code may or may not have line breaks, but adding them
	 *           externally is a bad idea and could produce invalid HTML.
	 */
	protected $declinesExternalLineBreaks = true;

	protected $html = '';

	protected $koComponentContainerType = 'span';

	public function __construct($html = '', $params = []) {
		parent::__construct([], $params);
		$this->html = $html;
		if ( isset($params['componentContainer']) ) {
			$this->koComponentContainerType = $params['componentContainer'];
		}
	}

	public function renderContent(Renderer $renderer) {
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is the point.
		echo $this->html;
	}

	protected function getKoComponentParams() {
		return array_merge(
			parent::getKoComponentParams(),
			[
				'html'      => $this->html,
				'container' => $this->koComponentContainerType,
			]
		);
	}
}