<?php

namespace ForGravity\Fillable_PDFs\Settings\Fields;

use Gravity_Forms\Gravity_Forms\Settings\Fields\Hidden;

defined( 'ABSPATH' ) || die();

class Import_File extends Hidden {

	/**
	 * Field type.
	 *
	 * @since 2.4
	 *
	 * @var string
	 */
	public $type = 'fg_fillablepdfs_import_file';





	// # RENDER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Render field.
	 *
	 * @since 2.4
	 *
	 * @return string
	 */
	public function markup() {

		// Get file meta.
		$file_meta = $this->get_value();

		// Insert hidden field to retain state.
		$html = parent::markup();

		// Prepare template info markup.
		$html .= sprintf(
			'<div class="fillablepdfs-template-info">
				<img src="%3$s/dist/images/templates/placeholder.svg" width="100" class="fillablepdfs-template-info__placeholder" alt="%1$s">
				<span class="fillablepdfs-template-info__meta">
					<span class="fillablepdfs-template-info__file-name">%1$s</span>
					<span class="fillablepdfs-template-info__file-size">%2$s</span>
				</span>
			</div>',
			rgar( $file_meta, 'name' ),
			size_format( rgar( $file_meta, 'size', 0 ) ),
			fg_fillablepdfs()->get_asset_url()
		);

		return $html;

	}

}
