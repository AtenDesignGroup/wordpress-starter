<?php
namespace WPDRMS\ASP\Utils;

use Exception;
use Imagick;

defined('ABSPATH') or die("You can't access this file directly.");

class Pdf {
	public static function getThumbnail( $pdf_id, $regenerate = false ){
		if ( class_exists('\\Imagick') ) {
			$thumbnail = get_post_meta( $pdf_id, '_asp_pdf_thumbnail', true );
			if( $thumbnail && !$regenerate ){
				$filepath = get_attached_file( $pdf_id );
				$thumbnail_filepath = str_replace( basename( $filepath ), $thumbnail, $filepath );
				if( file_exists( $thumbnail_filepath ) ){
					$thumbnail_url = wp_get_attachment_url( $pdf_id );
					return str_replace( basename( $filepath ), $thumbnail, $thumbnail_url );
				}
			}

			set_time_limit( 5 );

			$max_width = 512;
			$max_height = 512;
			$quality = 60;
			$type = 'png';
			$page_number = 0;
			$resolution = ceil( max( $max_height, $max_width ) * 0.16 );
			$bgcolor = 'white';

			$filepath = get_attached_file( $pdf_id );

			$new_filename = sanitize_file_name( basename( $filepath ) . '.' . $type );
			$new_filename = wp_unique_filename( dirname( $filepath ), $new_filename );
			$new_filepath = str_replace( basename( $filepath ), $new_filename, $filepath );

			try {
				$imagick = new Imagick();
				$imagick->setResolution( $resolution, $resolution );
				$imagick->readimage( $filepath . '[' . $page_number . ']' );
				$imagick->setCompressionQuality( $quality );
				$imagick->scaleImage( $max_width, $max_height, true );
				$imagick->setImageFormat( $type );
				$imagick->setImageBackgroundColor( $bgcolor );
				if( method_exists( 'Imagick', 'setImageAlphaChannel' ) ){
					if( defined('Imagick::ALPHACHANNEL_REMOVE') ){
						$imagick->setImageAlphaChannel( Imagick::ALPHACHANNEL_REMOVE );
					}else{
						$imagick->setImageAlphaChannel( 11 );
					}
				}
				if( method_exists( '\\Imagick','mergeImageLayers' ) ){
					$imagick->mergeImageLayers( Imagick::LAYERMETHOD_FLATTEN );
				} else if ( method_exists( '\\Imagick','flattenImages' ) ){
					$imagick = $imagick->flattenImages();
				}
				$imagick->stripImage();
				$imagick->writeImage( $new_filepath );
				$imagick->clear();
				update_post_meta( $pdf_id, '_asp_pdf_thumbnail', $new_filename );

				$thumbnail_filepath = str_replace( basename( $filepath ), $new_filename, $filepath );
				if( file_exists( $thumbnail_filepath ) ){
					$thumbnail_url = wp_get_attachment_url( $pdf_id );
					return str_replace( basename( $filepath ), $new_filename, $thumbnail_url );
				}
			} catch( Exception $err ) {
				return '';
			}
		}

		return '';
	}
}