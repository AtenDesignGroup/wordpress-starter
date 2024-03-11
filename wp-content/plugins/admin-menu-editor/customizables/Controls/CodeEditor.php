<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;

class CodeEditor extends ClassicControl {
	protected $type = 'codeEditor';

	protected $koComponentName = 'ame-code-editor';

	/**
	 * @var string MIME type for CodeMirror.
	 */
	protected $mimeType = 'text/html';

	const SCRIPT_ACTION = 'admin_print_footer_scripts';
	protected $editorSettings = [];
	protected $editorId = null;

	protected $triedToEnqueueEditor = false;

	public function __construct($settings = [], $params = []) {
		parent::__construct($settings, $params);
		if ( isset($params['mimeType']) ) {
			$this->mimeType = $params['mimeType'];
		}
	}

	public function renderContent(Renderer $renderer) {
		$id = '_acm_' . $this->id;
		$this->editorId = $id;

		$stringValue = $this->getMainSettingValue('');
		if ( $stringValue === null ) {
			$stringValue = '';
		}

		echo '<div class="ame-code-editor-control-wrap">';
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- buildInputElement() is safe.
		echo $this->buildInputElement(
			[
				'id'    => $id,
				'class' => 'large-text',
				'cols'  => 100,
				'rows'  => 5,
			],
			'textarea',
			esc_textarea($stringValue)
		);
		$this->outputSiblingDescription();
		echo '</div>';

		if ( $this->enqueueCodeEditor() ) {
			static::enqueueDependencies();
			if ( did_action(self::SCRIPT_ACTION) ) {
				$this->outputInitScript();
			} else {
				add_action(self::SCRIPT_ACTION, [$this, 'outputInitScript']);
			}
		}
	}

	protected function enqueueCodeEditor() {
		//Don't enqueue the editor more than once.
		if ( $this->triedToEnqueueEditor ) {
			return !empty($this->editorSettings);
		}
		$this->triedToEnqueueEditor = true;

		$additionalCodeMirrorOptions = [];
		//Strangely, WordPress disables linting for CSS by default even though
		//it is actually supported. Let's enable it explicitly. This needs to be
		//done *before* calling wp_enqueue_code_editor() because that function
		//uses the "lint" option to decide whether to enqueue the linter(s).
		if ($this->mimeType === 'text/css') {
			$additionalCodeMirrorOptions['lint'] = true;
		}

		//This can return false if, for example, the user has disabled syntax highlighting.
		$this->editorSettings = wp_enqueue_code_editor([
			'type' => $this->mimeType,
			'codemirror' => $additionalCodeMirrorOptions,
		]);
		if ( empty($this->editorSettings) ) {
			return false;
		}

		//Enable linting and a few other things for CSS and JavaScript.
		if (
			isset($this->editorSettings['codemirror'])
			&& in_array(
				$this->mimeType,
				['text/css', 'text/javascript', 'application/javascript']
			)
		) {
			$this->editorSettings['codemirror'] = array_merge(
				$this->editorSettings['codemirror'],
				[
					'lint'              => true,
					'autoCloseBrackets' => true,
					'matchBrackets'     => true,
				]
			);

			if ( empty($this->editorSettings['codemirror']['gutters']) ) {
				$this->editorSettings['codemirror']['gutters'][] = 'CodeMirror-lint-markers';
			}

			//For CSS in particular, setting mode to "css" appears to enable
			//some additional linting, like warnings for unknown CSS properties.
			if ( $this->mimeType === 'text/css' ) {
				$this->editorSettings['codemirror']['mode'] = 'css';
			}
		}

		return true;
	}

	public function outputInitScript() {
		if ( !$this->editorId || empty($this->editorSettings) ) {
			//Code editor was not enqueued for some reason.
			return;
		}
		?>
		<script type="text/javascript">
			jQuery(function () {
				if (typeof wp['codeEditor'] !== 'undefined') {
					wp.codeEditor.initialize(
						<?php echo wp_json_encode($this->editorId); ?>,
						<?php echo wp_json_encode($this->editorSettings); ?>
					);
				}
			});
		</script>
		<?php
	}

	public function enqueueKoComponentDependencies() {
		parent::enqueueKoComponentDependencies();

		$this->enqueueCodeEditor();
	}

	protected function getKoComponentParams() {
		if ( !$this->triedToEnqueueEditor ) {
			$this->enqueueCodeEditor();
		}

		$params = parent::getKoComponentParams();
		$params['mimeType'] = $this->mimeType;
		$params['editorSettings'] = $this->editorSettings;
		return $params;
	}
}