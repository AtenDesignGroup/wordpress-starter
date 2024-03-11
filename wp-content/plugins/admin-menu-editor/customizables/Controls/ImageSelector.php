<?php

namespace YahnisElsts\AdminMenuEditor\Customizable\Controls;

use YahnisElsts\AdminMenuEditor\Customizable\HtmlHelper;
use YahnisElsts\AdminMenuEditor\Customizable\Rendering\Renderer;

class ImageSelector extends ClassicControl {
	protected $type = 'imageSelector';
	protected $koComponentName = 'ame-image-selector';

	/**
	 * @var \YahnisElsts\AdminMenuEditor\Customizable\Settings\ImageSetting
	 */
	protected $mainSetting;

	public function renderContent(Renderer $renderer) {
		self::enqueueDependencies();

		$attachmentId = $this->mainSetting->getChildValue('attachmentId', 0);
		if ( !empty($attachmentId) ) {
			//Verify that the attachment exists.
			$attachmentUrl = wp_get_attachment_image_url($attachmentId, 'full');
			if ( !$attachmentUrl ) {
				$attachmentId = 0;
			}
		}

		$externalUrlsAllowed = $this->mainSetting->areExternalUrlsAllowed();
		$externalUrl = $this->mainSetting->getChildValue('externalUrl', '');

		$imageUrl = $this->mainSetting->getImageUrl();

		$canSelectAttachment = $this->isEnabled() && current_user_can('upload_files');
		$canSelectExternalUrl = $this->isEnabled() && $externalUrlsAllowed;

		echo HtmlHelper::tag('div', [
			'class'     => 'ame-image-selector-v2',
			'data-bind' => 'ameObservableChangeEvents: ' . $this->getKoObservableExpression($imageUrl),
		]);
		?>
		<?php if ( defined('IS_DEMO_MODE') && constant('IS_DEMO_MODE') ): ?>
			<p><em>Sorry, this feature is not available in the demo because image upload is disabled.</em></p>
		<?php endif; ?>
		<div class="ame-image-preview <?php if ( empty($imageUrl) ) {
			echo ' ame-image-preview-empty';
		} ?>"><?php
			$noImageText = 'No image selected';
			printf(
				'<span class="ame-image-preview-placeholder" style="%s" data-no-image-text="%s" data-loading-text="%s">%s</span>',
				empty($imageUrl) ? '' : 'display: none;',
				esc_attr($noImageText),
				esc_attr('Loading...'),
				esc_html($noImageText)
			);
			if ( !empty($imageUrl) ) {
				/** @noinspection HtmlUnknownTarget */
				printf('<img src="%s" alt="Image preview">', esc_attr($imageUrl));
			}
			?></div>
		<?php
		echo HtmlHelper::tag('input', array(
			'type'  => 'hidden',
			'name'  => $this->getFieldName('attachmentId'),
			'value' => $attachmentId,
			'class' => 'ame-image-attachment-id',
		));
		echo HtmlHelper::tag('input', array(
			'type'  => 'hidden',
			'name'  => $this->getFieldName('attachmentSiteId'),
			'value' => $this->mainSetting['attachmentSiteId']->getValue(0),
			'class' => 'ame-image-attachment-site-id',
		));
		//Attachment URL will usually be overwritten on the server side, but it's useful
		//for screens that don't trigger server-side post-processing (e.g. the "Style" dialog).
		echo HtmlHelper::tag('input', array(
			'type'  => 'hidden',
			'name'  => $this->getFieldName('attachmentUrl'),
			'value' => $this->mainSetting['attachmentUrl']->getValue(''),
			'class' => 'ame-image-attachment-url',
		));
		?>
		<?php if ( $externalUrlsAllowed ): ?>
			<div class="ame-external-image-url-preview" <?php
			if ( empty($externalUrl) ) {
				echo ' style="display: none" ';
			}
			?>>
				<label>
					<?php
					echo HtmlHelper::tag('input', array(
						'type'        => 'text',
						'name'        => $this->getFieldName('externalUrl'),
						'value'       => $externalUrl,
						'class'       => 'regular-text large-text code ame-external-image-url',
						'placeholder' => 'Image URL',
						'readonly'    => true,
					));
					?>
					<span class="screen-reader-text">External image URL</span>
				</label>
			</div>
			<?php
			foreach (array('width', 'height') as $dimension) {
				echo HtmlHelper::tag('input', array(
					'type'  => 'hidden',
					'name'  => $this->getFieldName($dimension),
					'value' => $this->mainSetting->getChildValue($dimension, ''),
					'class' => 'ame-detected-image-' . $dimension,
				));
			}
			?>
		<?php endif; ?>

		<div class="ame-image-selector-actions">
			<input type="button" class="button button-secondary ame-select-image"
			       data-ac-label="Select Image"
			       value="Select Image" <?php disabled(!$canSelectAttachment); ?>>
			<?php if ( $externalUrlsAllowed ): ?>
				<input type="button" class="button button-secondary ame-set-external-image-url"
				       data-ac-label="Enter URL"
				       value="Set External URL" <?php disabled(!$canSelectExternalUrl); ?>>
			<?php endif; ?>
			<a href="#" class="ame-remove-image-link" data-ac-label="Remove" <?php
			if ( (!$this->isEnabled()) || (empty($attachmentId) && empty($externalUrl)) ) {
				echo ' style="display: none;" ';
			}
			?>>Remove Image</a>
		</div>
		<?php $this->outputSiblingDescription(); ?>
		<?php
		echo '</div>'; //Close the container div.
	}

	protected static function enqueueDependencies() {
		static $done = false;
		if ( $done ) {
			return;
		}
		$done = true;

		parent::enqueueDependencies();

		wp_enqueue_media();

		wp_enqueue_auto_versioned_script(
			'ame-image-selector-control-v2',
			plugins_url('assets/image-selector.js', AME_CUSTOMIZABLE_BASE_FILE),
			array('jquery', 'ame-lodash')
		);

		wp_add_inline_script(
			'ame-image-selector-control-v2',
			sprintf("var AmeIscBlogId = %d;", get_current_blog_id())
		);
	}

	protected function getKoComponentParams() {
		$params = parent::getKoComponentParams();
		$params['externalUrlsAllowed'] = $this->mainSetting->areExternalUrlsAllowed();
		$params['canSelectMedia'] = current_user_can('upload_files');
		return $params;
	}
}