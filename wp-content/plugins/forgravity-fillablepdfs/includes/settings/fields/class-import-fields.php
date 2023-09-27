<?php

namespace ForGravity\Fillable_PDFs\Settings\Fields;

use Gravity_Forms\Gravity_Forms\Settings\Fields\Hidden;

defined( 'ABSPATH' ) || die();

class Import_Fields extends Hidden {

	/**
	 * Field type.
	 *
	 * @since 2.4
	 *
	 * @var string
	 */
	public $type = 'fg_fillablepdfs_import_fields';





	// # RENDER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Render field.
	 *
	 * @since 2.4
	 *
	 * @return string
	 */
	public function markup() {

		return sprintf(
			'%s%s%s',
			parent::markup(),
			$this->get_description(),
			'<div id="fillablepdfs-import-fields"></div>'
		);

	}

}
