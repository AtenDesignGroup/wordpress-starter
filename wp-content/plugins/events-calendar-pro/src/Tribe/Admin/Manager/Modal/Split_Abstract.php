<?php
/**
 * Abstract Split Modal
 *
 * @since   5.9.0
 *
 * @package Tribe\Events\Pro\Admin\Manager\Modal
 */

namespace Tribe\Events\Pro\Admin\Manager\Modal;

use Tribe\Events\Pro\Admin\Manager\Page;

/**
 * Class Modal
 *
 * @package Tribe\Events\Pro\Admin\Manager\Modal
 *
 * @since 5.9.0
 */
abstract class Split_Abstract {
	/**
	 * Split type (single or upcoming).
	 *
	 * @since 5.9.0
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Helper method to defer template rendering to the Page.
	 *
	 * @since 5.9.0
	 *
	 * @param string $template_name Template name.
	 * @param array  $context       Template context arguments.
	 * @param bool   $echo          Whether to echo or return the template.
	 */
	protected function template( $template_name, $context = [], $echo = false ) {
		return tribe( Page::class )->get_template()->template( $template_name, $context, $echo );
	}

	/**
	 * Check if we should render the modal.
	 *
	 * @since 5.9.0
	 *
	 * @return boolean Whether we should render the modal.
	 */
	public function should_render() {
		return tribe( Page::class )->is_current_screen();
	}

	/**
	 * Render the modal.
	 *
	 * @since 5.9.0
	 */
	public function render_modal() {
		if ( ! $this->should_render() ) {
			return;
		}

		// Render the modal contents.
		echo $this->get_modal_content();
	}

	/**
	 * Get the modal ID.
	 *
	 * @return string
	 */
	protected function get_modal_id() {
		return "tec-pro-event-manager__split-{$this->type}-dialog";
	}

	/**
	 * Get the modal target.
	 *
	 * @return string
	 */
	protected function get_modal_target() {
		return "tec-pro-event-manager__split-{$this->type}-dialog";
	}

	/**
	 * Get the default modal args.
	 *
	 * @since 5.9.0
	 *
	 * @param array $args Override default args by sending them in the `$args`.
	 *
	 * @return array The default modal args.
	 */
	public function get_modal_args( $args = [] ) {
		$default_args = [
			'append_target'           => '#' . $this->get_modal_target(),
			'trigger'                 => 'trigger-dialog-' . $this->get_modal_id(),
			'content_wrapper_classes' => 'tribe-dialog__wrapper ' . $this->get_modal_id(),
			'title'                   => $this->get_title(),
		];

		return wp_parse_args( $args, $default_args );
	}

	/**
	 * Get the default modal contents.
	 *
	 * @since 5.9.0
	 *
	 * @param array $args Override default args by sending them in the `$args`.
	 *
	 * @return string The modal content.
	 */
	public function get_modal_content( $args = [] ) {
		$content       = $this->template( "manager/modal/split-{$this->type}-content", [], false );
		$args          = $this->get_modal_args( $args );
		$modal_content = tribe( 'dialog.view' )->render_warning( $content, $args, $this->get_modal_id(), false );

		$modal_template_args = [
			'modal_content' => $modal_content,
			'modal_id'      => $this->get_modal_id(),
			'modal_target'  => $this->get_modal_target(),
		];

		return $this->template( "manager/modal/split-{$this->type}", $modal_template_args, false );
	}

	/**
	 * Get the modal's title.
	 *
	 * @since 5.9.0
	 *
	 * @return string
	 */
	abstract protected function get_title();
}