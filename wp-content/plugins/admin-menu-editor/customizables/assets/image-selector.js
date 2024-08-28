'use strict';
var AmeImageSelectorApi;
(function (AmeImageSelectorApi) {
    const $ = jQuery;
    const _ = wsAmeLodash;
    let mediaFrame = null, currentImageSelector = null;
    const imageChangeEvents = [
        'admin-menu-editor:media-image-removed',
        'admin-menu-editor:media-image-selected',
        'admin-menu-editor:external-image-selected'
    ];
    AmeImageSelectorApi.imageChangeEventString = imageChangeEvents.join(' ');
    class ImageSelector {
        constructor($container, inputOptions = {}, initialImage = null) {
            this.lastTriggeredImage = null;
            this.$container = $container;
            const usingExistingDom = ($container.find('input').length > 0);
            if (!usingExistingDom) {
                this.generateSelectorDom($container);
            }
            this.$attachmentId = $container.find('input.ame-image-attachment-id');
            this.$attachmentSiteId = $container.find('input.ame-image-attachment-site-id');
            this.$attachmentUrl = $container.find('input.ame-image-attachment-url');
            this.$externalUrl = $container.find('.ame-external-image-url');
            this.$externalUrlPreview = $container.find('.ame-external-image-url-preview');
            this.$width = $container.find('input.ame-detected-image-width');
            this.$height = $container.find('input.ame-detected-image-height');
            this.$selectImageButton = $container.find('.ame-select-image');
            this.$removeImageButton = $container.find('.ame-remove-image-link');
            this.$externalUrlButton = $container.find('.ame-set-external-image-url');
            const $previewPlaceholder = $container.find('.ame-image-preview .ame-image-preview-placeholder');
            const defaultOptions = {
                canSelectMedia: usingExistingDom ? (!this.$selectImageButton.prop('disabled')) : true,
                externalUrlsAllowed: usingExistingDom ? (this.$externalUrlPreview.length > 0) : true,
                noImageText: usingExistingDom ? $previewPlaceholder.data('noImageText') : 'No image selected',
                loadingText: usingExistingDom ? $previewPlaceholder.data('loadingText') : 'Loading...',
            };
            this.options = $.extend({}, defaultOptions, inputOptions);
            if (!this.options.canSelectMedia) {
                this.$selectImageButton.prop('disabled', true);
            }
            if (!this.options.externalUrlsAllowed) {
                this.$externalUrlPreview.hide();
                this.$externalUrlButton.hide();
            }
            if (initialImage) {
                this.lastTriggeredImage = this.withDefaults(initialImage);
                this.updateDom(initialImage, null);
            }
            //The "Select Image" button.
            this.$selectImageButton.on('click', (event) => {
                event.preventDefault();
                currentImageSelector = this;
                //If the media frame already exists, reopen it.
                if (mediaFrame) {
                    mediaFrame.open();
                    return;
                }
                //Initialize the media frame.
                mediaFrame = wp.media({
                    title: 'Select Image',
                    button: {
                        text: 'Select Image'
                    },
                    library: {
                        type: 'image'
                    },
                    multiple: false //Only select one image.
                });
                //Save the choice when the user clicks the select button.
                mediaFrame.on('select', function () {
                    //Get media attachment details from the frame.
                    const attachment = mediaFrame.state().get('selection').first().toJSON();
                    let image = {
                        //Store the attachment ID.
                        attachmentId: attachment.id,
                        //Store the site ID. I assume that the Media Library only shows images
                        //from the current site, and the site ID doesn't change after page load.
                        attachmentSiteId: AmeIscBlogId || 0,
                        //In most cases, we don't need to store the attachment URL because
                        //the server-side code will get from the ID and overwrite the value.
                        //However, let's do it for screens that don't have full server-side processing.
                        attachmentUrl: attachment.url
                    };
                    if (currentImageSelector !== null) {
                        currentImageSelector.setImage(image, attachment.url, 'admin-menu-editor:media-image-selected');
                    }
                });
                //Open the modal.
                mediaFrame.open();
            });
            //The "Remove Image" link.
            this.$removeImageButton.on('click', (event) => {
                event.preventDefault();
                this.setImage({}, null, 'admin-menu-editor:media-image-removed');
            });
            //The "Set External URL" button.
            this.$externalUrlButton.on('click', (event) => {
                event.preventDefault();
                const oldUrl = this.$externalUrl.val();
                const newUrl = window.prompt('Please enter the image URL:', oldUrl);
                if ((newUrl === null) || (newUrl === '')) {
                    //The user cancelled the prompt or left it empty. Do nothing.
                    return;
                }
                else if (!isPlausibleImageUrl(newUrl)) {
                    alert('Sorry, that doesn\'t look like a fully qualified image URL.');
                    return;
                }
                this.setImage({ externalUrl: newUrl }, newUrl, 'admin-menu-editor:external-image-selected');
            });
            this.$container.on('adminMenuEditor:observableValueChanged', (event, data) => {
                this.updateDom(data, null);
            });
            //If the control is used in the Admin Customizer, use shorter
            //labels for its buttons because the space is limited.
            if (this.$container.closest('#ame-ac-admin-customizer').length > 0) {
                this.shortenLabels();
            }
        }
        setImage(image, previewUrl, eventType = '') {
            if ((image === null) || (typeof image === 'undefined')) {
                image = {};
            }
            this.updateDom(image, previewUrl);
            this.triggerChangeEvents(image, eventType);
        }
        updateDom(image, previewUrl) {
            this.$attachmentId.val(image.attachmentId || 0);
            this.$attachmentSiteId.val(image.attachmentSiteId || 0);
            this.$attachmentUrl.val(image.attachmentUrl || '');
            this.$externalUrl.val(image.externalUrl || '');
            this.$width.val(image.width || '');
            this.$height.val(image.height || '');
            const hasAttachment = !!image.attachmentId;
            const hasExternalUrl = !!image.externalUrl;
            const hasImage = hasAttachment || hasExternalUrl;
            this.$externalUrlPreview.toggle(hasExternalUrl);
            this.$removeImageButton.toggle(hasImage);
            //Handle some cases where the image exists but the preview URL is not specified.
            if (hasImage && !previewUrl) {
                if (hasExternalUrl && (typeof image.externalUrl !== 'undefined')) {
                    previewUrl = image.externalUrl;
                }
                else if (hasAttachment && (typeof image.attachmentId !== 'undefined')) {
                    previewUrl = wp.media.attachment(image.attachmentId).get('url');
                    //This may return undefined if the attachment hasn't been loaded yet.
                    //setPreviewImage() should handle that situation.
                }
            }
            this.setPreviewImage(previewUrl, image.attachmentId);
        }
        setPreviewImage(imageUrl, attachmentId = null) {
            const $preview = this.$container.find('.ame-image-preview'), $placeholder = $preview.find('.ame-image-preview-placeholder');
            //Remove the old image.
            $preview.find('img').remove();
            if (!imageUrl && !attachmentId) {
                //No image? Just show the placeholder.
                $placeholder.text(this.options.noImageText).show();
                $preview.addClass('ame-image-preview-empty');
                return;
            }
            const addImage = (url) => {
                $placeholder.hide();
                $preview.removeClass('ame-image-preview-empty');
                //Add a new image element.
                const $img = $('<img src="" alt="Image preview">');
                //Some modules need to know the dimensions of the image, e.g. to properly
                //size a container element. This is easy for attachments, but in case the user
                //chooses an external URL, we'll also store the width & height in hidden fields
                //once the image loads.
                this.$width.val('');
                this.$height.val('');
                //To ensure we don't miss the event, let's add the listener before setting
                //the "src" attribute.
                $img.on('load', () => {
                    const image = $img.get(0);
                    if (image && image.naturalWidth && image.naturalHeight) {
                        this.$width.val(image.naturalWidth);
                        this.$height.val(image.naturalHeight);
                        //Some fields have changed, so let's notify Knockout bindings.
                        this.triggerChangeEvents(this.readImageFromDom());
                    }
                });
                //Load the new image.
                $img.attr('src', url);
                $preview.append($img);
            };
            if ((typeof imageUrl === 'string') && (imageUrl !== '')) {
                addImage(imageUrl);
                return;
            }
            //Try to load the attachment.
            if (attachmentId) {
                $placeholder.text(this.options.loadingText).show();
                /**
                 * Is the same attachment still selected? Note the intentional loose comparison.
                 */
                const isStillSameAttachment = () => {
                    return (this.$attachmentId.val() == attachmentId);
                };
                const onLoadingDone = (url) => {
                    if (isStillSameAttachment()) {
                        if (url) {
                            addImage(url);
                        }
                        else {
                            //Failed to load the attachment. Show an error message.
                            $placeholder.text('Failed to load the image.').show();
                        }
                    }
                };
                wp.media.attachment(attachmentId).fetch().then(
                //Success.
                (attachment) => onLoadingDone(attachment && attachment.url), 
                //Error.
                () => onLoadingDone(null));
            }
            else {
                this.setPreviewImage(null);
            }
        }
        withDefaults(image) {
            const defaults = {
                attachmentId: 0,
                attachmentSiteId: 0,
                attachmentUrl: null,
                externalUrl: null,
                width: null,
                height: null,
            };
            let result = $.extend({}, defaults, image);
            //Normalize empty URLs to null.
            if (result.attachmentUrl === '') {
                result.attachmentUrl = null;
            }
            if (result.externalUrl === '') {
                result.externalUrl = null;
            }
            return result;
        }
        triggerChangeEvents(image, eventType = '') {
            const fullImageObject = this.withDefaults(image);
            //Avoid potential infinite loops: don't trigger events if the image hasn't changed.
            if (this.lastTriggeredImage && _.isEqual(this.lastTriggeredImage, fullImageObject)) {
                return;
            }
            this.lastTriggeredImage = fullImageObject;
            //General image selector events.
            if (eventType) {
                this.$container.trigger(eventType, [fullImageObject]);
            }
            //Knockout integration event.
            if ((typeof ko !== 'undefined') && this.$container.attr('data-bind')) {
                this.$container.trigger('adminMenuEditor:controlValueChanged', [fullImageObject]);
            }
        }
        readImageFromDom() {
            return {
                attachmentId: parseInt(this.$attachmentId.val(), 10) || 0,
                attachmentSiteId: parseInt(this.$attachmentSiteId.val(), 10) || 0,
                attachmentUrl: this.$attachmentUrl.val() || null,
                externalUrl: this.$externalUrl.val() || null,
                width: parseInt(this.$width.val(), 10) || null,
                height: parseInt(this.$height.val(), 10) || null,
            };
        }
        shortenLabels() {
            this.$container.find('.ame-image-selector-actions [data-ac-label]')
                .each(function () {
                const $action = $(this);
                const label = $action.data('ac-label');
                if (label) {
                    if ($action.is('input')) {
                        $action.attr('value', label);
                    }
                    else {
                        $action.text(label);
                    }
                }
            });
        }
        generateSelectorDom($container) {
            $container.html(`
				<input type="hidden" class="ame-image-attachment-id" value="0">
				<input type="hidden" class="ame-image-attachment-site-id" value="">
				<input type="hidden" class="ame-image-attachment-url" value="">
				<input type="hidden" class="ame-detected-image-width" value="">
				<input type="hidden" class="ame-detected-image-height" value="">
				<div class="ame-image-preview ame-image-preview-empty">
					<span class="ame-image-preview-placeholder">No image selected</span>
				</div>
				<div class="ame-external-image-url-preview">
					<label>
						<input type="text" class="regular-text large-text code ame-external-image-url" 
							placeholder="Image URL" readonly value="">
						<span class="screen-reader-text">External image URL</span>
					</label>
				</div>
				<div class="ame-image-selector-actions">
					<input type="button" class="button button-secondary ame-select-image"
						data-ac-label="Select Image"
						value="Select Image">
					<input type="button" class="button button-secondary ame-set-external-image-url"
						data-ac-label="Enter URL"
						value="Set External URL">
					<a href="#" class="ame-remove-image-link" data-ac-label="Remove">Remove Image</a>
				</div>
			`);
        }
    }
    AmeImageSelectorApi.ImageSelector = ImageSelector;
    function isPlausibleImageUrl(input) {
        if (typeof URL !== 'undefined') {
            try {
                const url = new URL(input);
                return (
                //Accept only HTTP(S).
                ((url.protocol === "http:") || (url.protocol === "https:"))
                    //An image URL will usually have a path that's not just "/".
                    //In rare cases, it might be a root URL, but then it should have a query string.
                    //(URL.search includes the "?" character, so we need to check for a length > 1.)
                    && ((url.pathname.length > 1) || (url.search.length > 1)));
            }
            catch (e) {
                return false;
            }
        }
        else {
            const basicUrlValidator = /^https?:\/\/[-\w]+(?:\.[-\w]+)*(:\d{1,6})?\/./;
            return !basicUrlValidator.test(input);
        }
    }
})(AmeImageSelectorApi || (AmeImageSelectorApi = {}));
jQuery(function ($) {
    //Initialize image selectors.
    $('.ame-image-selector-v2').each(function () {
        const $this = $(this);
        if ($this.data('ameIsComponent')) {
            return; //Let components handle their own initialization.
        }
        new AmeImageSelectorApi.ImageSelector($(this));
    });
    //If an image selector is used in the Admin Customizer module,
    //use the shorter labels because the sidebar is too narrow.
    $('#ame-ac-admin-customizer')
        .find('.ame-image-selector-v2 .ame-image-selector-actions [data-ac-label]')
        .each(function () {
        const $action = $(this);
        const label = $action.data('ac-label');
        if (label) {
            if ($action.is('input')) {
                $action.attr('value', label);
            }
            else {
                $action.text(label);
            }
        }
    });
});
//# sourceMappingURL=image-selector.js.map