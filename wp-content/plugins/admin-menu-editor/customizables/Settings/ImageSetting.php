<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Settings;

use YahnisElsts\AdminMenuEditor\Customizable\Storage\StorageInterface;

class ImageSetting extends CompositeSetting {
	protected $label = 'Image';

	/**
	 * @var array
	 */
	protected $settings = array();

	protected $externalUrlsAllowed = true;

	public function __construct($id, StorageInterface $store = null, $params = array()) {
		parent::__construct($id, $store, $params);

		if ( isset($params['externalUrlsAllowed']) ) {
			$this->externalUrlsAllowed = $params['externalUrlsAllowed'];
		}

		//Image attachment ID.
		$this->createChild(
			'attachmentId',
			IntegerSetting::class,
			array('default' => 0, 'minValue' => 0)
		);

		//Site ID for Multisite.
		$this->createChild(
			'attachmentSiteId',
			IntegerSetting::class,
			array('default' => 0, 'minValue' => 0)
		);

		//Cached attachment URL.
		$this->createChild(
			'attachmentUrl',
			UrlSetting::class,
			array('default' => null)
		);

		//TODO: Allow shortcodes in the URL. This will require more lenient validation (also in JS).
		//External image URL. Alternative to attachments.
		$this->createChild(
			'externalUrl',
			UrlSetting::class,
			array('default' => null)
		);

		//Cached width and height for features that need them.
		$this->createChild(
			'width',
			IntegerSetting::class,
			array('default' => null, 'minValue' => 0)
		);
		$this->createChild(
			'height',
			IntegerSetting::class,
			array('default' => null, 'minValue' => 0)
		);
	}

	/**
	 * Get a fully qualified URL for the image.
	 *
	 * @return string|null
	 */
	public function getImageUrl($useCachedDetails = true) {
		//Try the external URL.
		$externalUrl = $this->settings['externalUrl']->getValue();
		if ( !empty($externalUrl) ) {
			return $externalUrl;
		}

		if ( $useCachedDetails ) {
			//Try the cached attachment URL.
			$attachmentUrl = $this->settings['attachmentUrl']->getValue();
			if ( !empty($attachmentUrl) ) {
				return $attachmentUrl;
			}
		}

		$result = $this->getImage($useCachedDetails);
		return $result['url'];
	}

	/**
	 * Get the URL and dimensions of the image.
	 *
	 * If there is no image, returns an array of NULLs.
	 *
	 * @return array{url: string, width: int, height: int}
	 */
	public function getImage($useCachedDetails = true) {
		//Prioritize external URLs over attachments.
		$externalUrl = $this->settings['externalUrl']->getValue();
		if ( !empty($externalUrl) ) {
			return array(
				'url'    => $externalUrl,
				'width'  => $this->settings['width']->getValue(0),
				'height' => $this->settings['height']->getValue(0),
			);
		}

		$attachmentId = $this->settings['attachmentId']->getValue(0);
		$siteId = $this->settings['attachmentSiteId']->getValue(0);
		if ( ($attachmentId > 0) ) {
			if ( $useCachedDetails ) {
				$attachmentUrl = $this->settings['attachmentUrl']->getValue();
				$width = $this->settings['width']->getValue(0);
				$height = $this->settings['height']->getValue(0);
			}

			if (
				//If caching is disabled
				!$useCachedDetails
				//Or any of the cached details are missing...
				|| (empty($attachmentUrl) || empty($width) || empty($height))
			) {
				//Load the attachment from the database.
				list($attachmentUrl, $width, $height) = $this->fetchImageAttachment($attachmentId, $siteId);
			}
			if ( !empty($attachmentUrl) ) {
				return array(
					'url'    => $attachmentUrl,
					'width'  => $width,
					'height' => $height,
				);
			}
		}

		return array('url' => null, 'width' => null, 'height' => null,);
	}

	public function validate($errors, $value, $stopOnFirstError = false) {
		$validatedValues = parent::validate($errors, $value);
		if ( is_wp_error($validatedValues) || ($validatedValues === null) ) {
			return $validatedValues;
		}

		//Validate an image attachment.
		$attachmentId = isset($validatedValues['attachmentId']) ? intval($validatedValues['attachmentId']) : 0;
		$siteId = isset($validatedValues['attachmentSiteId']) ? intval($validatedValues['attachmentSiteId']) : 0;
		if ( $attachmentId > 0 ) {
			$switched = false;
			if ( is_multisite() && ($siteId > 0) && ($siteId !== get_current_blog_id()) ) {
				switch_to_blog($siteId);
				$switched = true;
			}

			if ( !wp_attachment_is_image($attachmentId) ) {
				$errors->add('invalid_attachment_type', 'Attachment must be a valid image');
			}

			if ( $switched ) {
				restore_current_blog();
			}
		}

		if ( $errors->has_errors() ) {
			return $errors;
		}
		return $validatedValues;
	}

	/**
	 * @param int $attachmentId
	 * @param int $siteId
	 * @return array{0: string, 1: int, 2: int} URL, width, height. All NULLs if no image.
	 */
	protected function fetchImageAttachment($attachmentId, $siteId) {
		if ( !is_numeric($attachmentId) || ($attachmentId < 0) ) {
			return array(null, null, null);
		}

		$switched = false;
		if ( is_multisite() && ($siteId > 0) && ($siteId !== get_current_blog_id()) ) {
			switch_to_blog($siteId);
			$switched = true;
		}

		$attachment = wp_get_attachment_image_src($attachmentId, 'full');

		if ( $switched ) {
			restore_current_blog();
		}

		if ( empty($attachment) || (count($attachment) < 3) ) {
			return array(null, null, null);
		}
		return array_slice($attachment, 0, 3);
	}

	public function filterNewValues($values) {
		$values = parent::filterNewValues($values);

		//If we're using an attachment, cache its size and URL.
		$attachmentId = isset($values['attachmentId']) ? intval($values['attachmentId']) : 0;
		$siteId = isset($values['attachmentSiteId']) ? intval($values['attachmentSiteId']) : 0;
		if ( $attachmentId > 0 ) {
			list($url, $width, $height) = $this->fetchImageAttachment($attachmentId, $siteId);
			if ( !empty($url) && isset($width, $height) ) {
				$values['width'] = $width;
				$values['height'] = $height;
				$values['attachmentUrl'] = $url;
			}
		}

		return $values;
	}

	public function areExternalUrlsAllowed() {
		return $this->externalUrlsAllowed;
	}
}