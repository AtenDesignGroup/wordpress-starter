<?php

namespace ForGravity\Fillable_PDFs\Integrations\Gravity_Flow;

use Gravity_Flow_Step_Feed_Add_On;

// If Gravity Forms is not loaded, exit.
if ( ! class_exists( 'GFForms' ) ) {
	die();
}

/**
 * Fillable PDFs Step for Gravity Flow
 *
 * @since     1.0
 * @package   FillablePDFs
 * @author    ForGravity
 * @copyright Copyright (c) 2020, ForGravity
 */
class Step extends Gravity_Flow_Step_Feed_Add_On {

	/**
	 * The Add-On slug.
	 *
	 * @var string
	 */
	protected $_slug = 'forgravity-fillablepdfs';

	/**
	 * The step type.
	 *
	 * @var string
	 */
	public $_step_type = 'fillablepdfs';

	/**
	 * The name of the class used by the Add-On.
	 *
	 * @var string
	 */
	protected $_class_name = '\ForGravity\Fillable_PDFs\Fillable_PDFs';

	/**
	 * Returns the step label.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_label() {

		return esc_html__( 'Fillable PDFs', 'forgravity_fillablepdfs' );

	}

	/**
	 * Returns the URL for the step icon.
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_icon_url() {

		return fg_fillablepdfs()->get_base_url() . '/dist/images/gravityflow-step.svg';

	}

}
