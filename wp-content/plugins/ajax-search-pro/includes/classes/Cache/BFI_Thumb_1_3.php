<?php
namespace WPDRMS\ASP\Cache;
/*
* bfi_thumb - WP Image Resizer v1.3
*
* (c) 2013 Benjamin F. Intal / Gambit
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

use Exception;
use Imagick;
use WP_Error;
use WP_Image_Editor_GD;
use WP_Image_Editor_Imagick;

/** Uses WP's Image Editor Class to resize and filter images
*
* @param $url string the local image URL to manipulate
* @param $params array the options to perform on the image. Keys and values supported:
*          'width' int pixels
*          'height' int pixels
*          'opacity' int 0-100
*          'color' string hex-color #000000-#ffffff
*          'grayscale' bool
*          'negate' bool
*          'crop' bool
*          'crop_only' bool
*          'crop_x' bool string
*          'crop_y' bool string
*          'crop_width' bool string
*          'crop_height' bool string
*			'quality' int 1-100
* @param $single boolean, if false then an array of data will be returned
* @return string|array containing the url of the resized modified image
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
* Include the WP Image classes
*/

require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
require_once ABSPATH . WPINC . '/class-wp-image-editor-imagick.php';
require_once ABSPATH . WPINC . '/class-wp-image-editor-gd.php';


/*
* Enhanced Imagemagick Image Editor
*/

class BFI_Image_Editor_Imagick_1_3 extends WP_Image_Editor_Imagick {

	/** Changes the opacity of the image
	 *
	 * @supports 3.5.1
	 * @access public
	 *
	 * @param float $opacity (0.0-1.0)
	 * @return boolean|WP_Error
	 */
	public function opacity( $opacity ) {
		$opacity /= 100;

		try {
			// From: http://stackoverflow.com/questions/3538851/php-imagick-setimageopacity-destroys-transparency-and-does-nothing
			// preserves transparency
			//$this->image->setImageOpacity($opacity);
			return $this->image->evaluateImage( Imagick::EVALUATE_MULTIPLY, $opacity, Imagick::CHANNEL_ALPHA );

		} catch ( Exception $e ) {
			return new WP_Error( 'image_opacity_error', $e->getMessage() );
		}
	}


	/** Tints the image a different color
	 *
	 * @supports 3.5.1
	 * @access public
	 *
	 * @param string hex color e.g. #ff00ff
	 * @return boolean|WP_Error
	 */
	public function colorize( $hexColor ) {
		try {
			return $this->image->colorizeImage( $hexColor, 1.0 );
		} catch ( Exception $e ) {
			return new WP_Error( 'image_colorize_error', $e->getMessage() );
		}
	}


	/** Makes the image grayscale
	 *
	 * @supports 3.5.1
	 * @access public
	 *
	 * @return boolean|WP_Error
	 */
	public function grayscale() {
		try {
			return $this->image->modulateImage( 100, 0, 100 );
		} catch ( Exception $e ) {
			return new WP_Error( 'image_grayscale_error', $e->getMessage() );
		}
	}


	/** Negates the image
	 *
	 * @supports 3.5.1
	 * @access public
	 *
	 * @return boolean|WP_Error
	 */
	public function negate() {
		try {
			return $this->image->negateImage( false );
		} catch ( Exception $e ) {
			return new WP_Error( 'image_negate_error', $e->getMessage() );
		}
	}
}


/*
* Enhanced GD Image Editor
*/


class BFI_Image_Editor_GD_1_3 extends WP_Image_Editor_GD {

	/** Rotates current image counter-clockwise by $angle.
	 * Ported from image-edit.php
	 * Added presevation of alpha channels
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param float $angle
	 * @return boolean|WP_Error
	 */
	public function rotate( $angle ) {
		if ( function_exists('imagerotate') ) {
			$rotated = imagerotate( $this->image, $angle, 0 );

			// Add alpha blending
			imagealphablending( $rotated, true );
			imagesavealpha( $rotated, true );

			if ( is_resource( $rotated ) ) {
				imagedestroy( $this->image );
				$this->image = $rotated;
				$this->update_size();
				return true;
			}
		}

		return new WP_Error( 'image_rotate_error', __( 'Image rotate failed.', 'default' ), $this->file );
	}


	/** Changes the opacity of the image
	 *
	 * @supports 3.5.1
	 * @access public
	 *
	 * @param float $opacity (0.0-1.0)
	 * @return boolean|WP_Error
	 */
	public function opacity( $opacity ) {
		$opacity /= 100;

		$filtered = $this->_opacity( $this->image, $opacity );

		if ( is_resource( $filtered ) ) {
			// imagedestroy($this->image);
			$this->image = $filtered;
			return true;
		}

		return new WP_Error( 'image_opacity_error', __('Image opacity change failed.', 'default' ), $this->file );
	}


	// from: http://php.net/manual/en/function.imagefilter.php
	// params: image resource id, opacity (eg. 0.0-1.0)
	protected function _opacity( $image, $opacity ) {
		if ( ! function_exists( 'imagealphablending' ) ||
			 ! function_exists( 'imagecolorat' ) ||
			 ! function_exists( 'imagecolorallocatealpha' ) ||
			 ! function_exists( 'imagesetpixel' ) ) {
			return false;
		}

		// get image width and height
		$w = imagesx( $image );
		$h = imagesy( $image );

		// turn alpha blending off
		imagealphablending( $image, false );

		// find the most opaque pixel in the image (the one with the smallest alpha value)
		$minalpha = 127;
		for ( $x = 0; $x < $w; $x++ ) {
			for ( $y = 0; $y < $h; $y++ ) {
				$alpha = ( imagecolorat( $image, $x, $y ) >> 24 ) & 0xFF;
				if ( $alpha < $minalpha ) {
					$minalpha = $alpha;
				}
			}
		}

		// loop through image pixels and modify alpha for each
		for ( $x = 0; $x < $w; $x++ ) {
			for ( $y = 0; $y < $h; $y++ ) {

				// get current alpha value (represents the TANSPARENCY!)
				$colorxy = imagecolorat( $image, $x, $y );
				$alpha = ( $colorxy >> 24 ) & 0xFF;

				// calculate new alpha
				if ( $minalpha !== 127 ) {
					$alpha = 127 + 127 * $opacity * ( $alpha - 127 ) / ( 127 - $minalpha );
				} else {
					$alpha += 127 * $opacity;
				}

				// get the color index with new alpha
				$alphacolorxy = imagecolorallocatealpha( $image, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );

				// set pixel with the new color + opacity
				if( ! imagesetpixel( $image, $x, $y, $alphacolorxy ) ) {
					return false;
				}
			}
		}

		imagesavealpha( $image, true );

		return $image;
	}


	/** Tints the image a different color
	 *
	 * @supports 3.5.1
	 * @access public
	 *
	 * @param string hex color e.g. #ff00ff
	 * @return boolean|WP_Error
	 */
	public function colorize( $hexColor ) {
		if ( function_exists( 'imagefilter' ) &&
			 function_exists( 'imagesavealpha' ) &&
			 function_exists( 'imagealphablending' ) ) {

			$hexColor = preg_replace( '#^\##', '', $hexColor );
			$r = hexdec( substr( $hexColor, 0, 2 ) );
			$g = hexdec( substr( $hexColor, 2, 2 ) );
			$b = hexdec( substr( $hexColor, 2, 2 ) );

			imagealphablending( $this->image, false );
			if ( imagefilter( $this->image, IMG_FILTER_COLORIZE, $r, $g, $b, 0 ) ) {
				imagesavealpha( $this->image, true );
				return true;
			}
		}
		return new WP_Error( 'image_colorize_error', __( 'Image color change failed.', 'default' ), $this->file );
	}


	/** Makes the image grayscale
	 *
	 * @supports 3.5.1
	 * @access public
	 *
	 * @return boolean|WP_Error
	 */
	public function grayscale() {
		if ( function_exists( 'imagefilter' ) ) {
			if ( imagefilter( $this->image, IMG_FILTER_GRAYSCALE ) ) {
				return true;
			}
		}
		return new WP_Error( 'image_grayscale_error', __( 'Image grayscale failed.', 'default' ), $this->file );
	}


	/** Negates the image
	 *
	 * @supports 3.5.1
	 * @access public
	 *
	 * @return boolean|WP_Error
	 */
	public function negate() {
		if ( function_exists( 'imagefilter' ) ) {
			if ( imagefilter( $this->image, IMG_FILTER_NEGATE ) ) {
				return true;
			}
		}
		return new WP_Error( 'image_negate_error', __( 'Image negate failed.', 'default' ), $this->file );
	}
}


/*
* Main Class
*/

class BFI_Thumb_1_3 {

	/** Uses WP's Image Editor Class to resize and filter images
	 * Inspired by: https://github.com/sy4mil/Aqua-Resizer/blob/master/aq_resizer.php
	 *
	 * @param $url string the local image URL to manipulate
	 * @param $params array the options to perform on the image. Keys and values supported:
	 *          'width' int pixels
	 *          'height' int pixels
	 *          'opacity' int 0-100
	 *          'color' string hex-color #000000-#ffffff
	 *          'grayscale' bool
	 *          'crop' bool
	 *          'negate' bool
	 *          'crop_only' bool
	 *          'crop_x' bool string
	 *          'crop_y' bool string
	 *          'crop_width' bool string
	 *          'crop_height' bool string
	 *			'quality' int 1-100
	 * @param $single boolean, if false then an array of data will be returned
	 * @return string|array
	 */
	public static function thumb( $url, $params = array(), $single = true ) {
		extract( $params );

		//validate inputs
		if ( ! $url ) {
			return false;
		}

		$crop_only = isset( $crop_only ) ? $crop_only : false;

		//define upload path & dir
		$upload_info = wp_upload_dir();
		$upload_dir = $upload_info['basedir'];
		$upload_url = $upload_info['baseurl'];
		$theme_url = get_template_directory_uri();
		$theme_dir = get_template_directory();

		// find the path of the image. Perform 2 checks:
		// #1 check if the image is in the uploads folder
		if ( strpos( $url, $upload_url ) !== false ) {
			$rel_path = str_replace( $upload_url, '', $url );
			$img_path = $upload_dir . $rel_path;

		// #2 check if the image is in the current theme folder
		} else if ( strpos( $url, $theme_url ) !== false ) {
			$rel_path = str_replace( $theme_url, '', $url );
			$img_path = $theme_dir . $rel_path;
		}

		// Fail if we can't find the image in our WP local directory
		if ( empty( $img_path ) ) {
			return $url;
		}

		// check if img path exists, and is an image indeed
		if( ! @file_exists( $img_path ) || ! getimagesize( $img_path ) ) {
			return $url;
		}

		// This is the filename
		$basename = basename( $img_path );

		//get image info
		$info = pathinfo( $img_path );
		$ext = $info['extension'];
		list( $orig_w, $orig_h ) = getimagesize( $img_path );

		// support percentage dimensions. compute percentage based on
		// the original dimensions
		if ( isset( $width ) ) {
			if ( stripos( $width, '%' ) !== false ) {
				$width = (int) ( (float) str_replace( '%', '', $width ) / 100 * $orig_w );
			}
		}
		if ( isset( $height ) ) {
			if ( stripos( $height, '%' ) !== false ) {
				$height = (int) ( (float) str_replace( '%', '', $height ) / 100 * $orig_h );
			}
		}
		// The only purpose of this is to determine the final width and height
		// without performing any actual image manipulation, which will be used
		// to check whether a resize was previously done.
		if ( isset( $width ) && $crop_only === false ) {
			//get image size after cropping
			$dims = image_resize_dimensions( $orig_w, $orig_h, $width, isset( $height ) ? $height : null, isset( $crop ) ? $crop : false );
			if ( is_array($dims) ) {
				$dst_w = $dims[4];
				$dst_h = $dims[5];
			}

		} else if ( $crop_only === true ) {
			// we don't want a resize,
			// but only a crop in the image

			// get x position to start croping
			$src_x = ( isset( $crop_x ) ) ? $crop_x : 0;

			// get y position to start croping
			$src_y = ( isset( $crop_y ) ) ? $crop_y : 0;

			// width of the crop
			if ( isset( $crop_width ) ) {
				$src_w = $crop_width;
			} else if ( isset( $width ) ) {
				$src_w = $width;
			} else {
				$src_w = $orig_w;
			}

			// height of the crop
			if ( isset( $crop_height ) ) {
				$src_h = $crop_height;
			} else if ( isset( $height ) ) {
				$src_h = $height;
			} else {
				$src_h = $orig_h;
			}

			// set the width resize with the crop
			if ( isset( $crop_width ) && isset( $width ) ) {
				$dst_w = $width;
			} else {
				$dst_w = null;
			}

			// set the height resize with the crop
			if ( isset( $crop_height ) && isset( $height ) ) {
				$dst_h = $height;
			} else {
				$dst_h = null;
			}

			// allow percentages
			if ( isset( $dst_w ) ) {
				if ( stripos( $dst_w, '%' ) !== false ) {
					$dst_w = (int) ( (float) str_replace( '%', '', $dst_w ) / 100 * $orig_w );
				}
			}
			if ( isset( $dst_h ) ) {
				if ( stripos( $dst_h, '%' ) !== false ) {
					$dst_h = (int) ( (float) str_replace( '%', '', $dst_h ) / 100 * $orig_h );
				}
			}

			$dims = image_resize_dimensions( $src_w, $src_h, $dst_w, $dst_h, false );
			if ( is_array($dims) ) {
				$dst_w = $dims[4];
				$dst_h = $dims[5];
			}

			// Make the pos x and pos y work with percentages
			if ( stripos( $src_x, '%' ) !== false ) {
				$src_x = (int) ( (float) str_replace( '%', '', $width ) / 100 * $orig_w );
			}
			if ( stripos( $src_y, '%' ) !== false ) {
				$src_y = (int) ( (float) str_replace( '%', '', $height ) / 100 * $orig_h );
			}

			// allow center to position crop start
			if ( $src_x === 'center' ) {
				$src_x = ( $orig_w - $src_w ) / 2;
			}
			if ( $src_y === 'center' ) {
				$src_y = ( $orig_h - $src_h ) / 2;
			}
		}

		// create the suffix for the saved file
		// we can use this to check whether we need to create a new file or just use an existing one.
		$suffix = (string) filemtime( $img_path ) .
			( isset( $width ) ? str_pad( (string) $width, 5, '0', STR_PAD_LEFT ) : '00000' ) .
			( isset( $height ) ? str_pad( (string) $height, 5, '0', STR_PAD_LEFT ) : '00000' ) .
			( isset( $opacity ) ? str_pad( (string) $opacity, 3, '0', STR_PAD_LEFT ) : '100' ) .
			( isset( $color ) ? str_pad( preg_replace( '#^\##', '', $color ), 8, '0', STR_PAD_LEFT ) : '00000000' ) .
			( isset( $grayscale ) ? ( $grayscale ? '1' : '0' ) : '0' ) .
			( isset( $crop ) ? ( $crop ? '1' : '0' ) : '0' ) .
			( isset( $negate ) ? ( $negate ? '1' : '0' ) : '0' ) .
			( isset( $crop_only ) ? ( $crop_only ? '1' : '0' ) : '0' ) .
			( isset( $src_x ) ? str_pad( (string) $src_x, 5, '0', STR_PAD_LEFT ) : '00000' ) .
			( isset( $src_y ) ? str_pad( (string) $src_y, 5, '0', STR_PAD_LEFT ) : '00000' ) .
			( isset( $src_w ) ? str_pad( (string) $src_w, 5, '0', STR_PAD_LEFT ) : '00000' ) .
			( isset( $src_h ) ? str_pad( (string) $src_h, 5, '0', STR_PAD_LEFT ) : '00000' ) .
			( isset( $dst_w ) ? str_pad( (string) $dst_w, 5, '0', STR_PAD_LEFT ) : '00000' ) .
			( isset( $dst_h ) ? str_pad( (string) $dst_h, 5, '0', STR_PAD_LEFT ) : '00000' ) .
			( ( isset ( $quality ) && $quality > 0 && $quality <= 100 ) ? ( $quality ? (string) $quality : '0' ) : '0' );
		$suffix = self::base_convert_arbitrary( $suffix, 10, 36 );

		// use this to check if cropped image already exists, so we can return that instead
		$dst_rel_path = str_replace( '.' . $ext, '', basename( $img_path ) );

		// If opacity is set, change the image type to png
		if ( isset( $opacity ) ) {
			$ext = 'png';
		}


		// Create the upload subdirectory, this is where
		// we store all our generated images
		if ( defined( 'BFITHUMB_UPLOAD_DIR' ) ) {
			$upload_dir .= "/" . BFITHUMB_UPLOAD_DIR;
			$upload_url .= "/" . BFITHUMB_UPLOAD_DIR;
		} else {
			$upload_dir .= "/bfi_thumb";
			$upload_url .= "/bfi_thumb";
		}
		if ( ! is_dir( $upload_dir ) ) {
			wp_mkdir_p( $upload_dir );
		}


		// desination paths and urls
		$destfilename = "{$upload_dir}/{$dst_rel_path}-{$suffix}.{$ext}";

		// The urls generated have lower case extensions regardless of the original case
		$ext = strtolower( $ext );
		$img_url = "{$upload_url}/{$dst_rel_path}-{$suffix}.{$ext}";

		// if file exists, just return it
		if ( @file_exists( $destfilename ) && getimagesize( $destfilename ) ) {
		} else {
			// perform resizing and other filters
			$editor = wp_get_image_editor( $img_path );

			if ( is_wp_error( $editor ) ) return false;

			/*
			 * Perform image manipulations
			 */
			if ( $crop_only === false ) {
				if ( ( isset( $width ) && $width ) || ( isset( $height ) && $height ) ) {
					if ( is_wp_error( $editor->resize( isset( $width ) ? $width : null, isset( $height ) ? $height : null, isset( $crop ) ? $crop : false ) ) ) {
						// Instead of returning false, return the original yo?
						return $url;
					}
				}
			} else {
				if ( is_wp_error( $editor->crop( $src_x, $src_y, $src_w, $src_h, $dst_w, $dst_h ) ) ) {
					return $url;
				}
			}

			if ( isset( $negate ) ) {
				if ( $negate ) {
					if ( is_wp_error( $editor->negate() ) ) {
						return $url;
					}
				}
			}

			if ( isset( $opacity ) ) {
				if ( is_wp_error( $editor->opacity( $opacity ) ) ) {
					return $url;
				}
			}

			if ( isset( $grayscale ) ) {
				if ( $grayscale ) {
					if ( is_wp_error( $editor->grayscale() ) ) {
						return $url;
					}
				}
			}

			if ( isset( $color ) ) {
				if ( is_wp_error( $editor->colorize( $color ) ) ) {
					return $url;
				}
			}

			// set the image quality (1-100) to save this image at
			if ( isset( $quality ) && $quality > 0 && $quality <= 100 && $ext != 'png' ) {
				$editor->set_quality( $quality );
			}

			// save our new image
			$mime_type = isset( $opacity ) ? 'image/png' : null;
			$resized_file = $editor->save( $destfilename, $mime_type );
		}

		//return the output
		if ( $single ) {
			$image = $img_url;
		} else {
			//array return
			$image = array (
				0 => $img_url,
				1 => isset( $dst_w ) ? $dst_w : $orig_w,
				2 => isset( $dst_h ) ? $dst_h : $orig_h,
			);
		}

		return $image;
	}


	/** Shortens a number into a base 36 string
	 *
	 * @param $number - string a string of numbers to convert
	 * @param $fromBase - starting base
	 * @param $toBase - base to convert the number to
	 * @return string base converted characters
	 */
	protected static function base_convert_arbitrary( $number, $fromBase, $toBase ) {
		$digits = '0123456789abcdefghijklmnopqrstuvwxyz';
		$length = strlen( $number );
		$result = '';

		$nibbles = array();
		for ( $i = 0; $i < $length; ++$i ) {
			$nibbles[ $i ] = strpos( $digits, $number[ $i ] );
		}

		do {
			$value = 0;
			$newlen = 0;

			for ( $i = 0; $i < $length; ++$i ) {

				$value = $value * $fromBase + $nibbles[ $i ];

				if ( $value >= $toBase ) {
					$nibbles[ $newlen++ ] = (int) ( $value / $toBase );
					$value %= $toBase;

				} else if ( $newlen > 0 ) {
					$nibbles[ $newlen++ ] = 0;
				}
			}

			$length = $newlen;
			$result = $digits[ $value ] . $result;
		}
		while ( $newlen != 0 );

		return $result;
	}
}