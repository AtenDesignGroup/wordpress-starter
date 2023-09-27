var pdfjsLib = window['pdfjs-dist/build/pdf'];

// Disable right click?
var containerDiv = document.getElementById('pdf-embed');
if (
	undefined !== containerDiv.dataset.disablerightclick &&
	(containerDiv.dataset.disablerightclick == '1' ||
		containerDiv.dataset.disablerightclick == 'on')
) {
	containerDiv.oncontextmenu = function () {
		return false;
	};
}

jQuery(document).ready(function ($) {
	/* this fixes the close 'X' from the legacy code for full screen  */
	// jQuery( 'body' ).on( 'click', 'a.fsp-close', function( e ) {
	//   e.preventDefault();
	//   jQuery('button.toolbarButton.fullscreen.fsp-close').first().click();
	// });

	/* fullscreen CSS change */
	jQuery('body').on('click', '.toolbar button#fullscreen', function (e) {
		var this_button = this,
			the_index = jQuery('#pdf-embed').data('index');
		jQuery(parent.document).trigger('eventhandler', [the_index]);
	});
	window.pdfFullscreen = false;
	jQuery(document).on('keydown', function (event) {
		if (event.key == 'Escape') {
			if (jQuery('body').hasClass('fullscreen')) {
				jQuery('.toolbar button#fullscreen').click();
			}
		}
	});

	/* the following should only apply if there's a second toolbar, in JS we will detect the .toolbox.alt, otherwise we don't need this extra JS */

	if (jQuery('.toolbar.alt').length > 0) {
		/* this syncs the bottom page up button */
		jQuery('.toolbar.alt').on('click', 'button.pageUp', function (e) {
			e.preventDefault();
			jQuery('.toolbar.toolbar-display-top button.pageUp').click();
		});

		/* this syncs the bottom page down button */
		jQuery('.toolbar.alt').on('click', 'button.pageDown', function (e) {
			e.preventDefault();
			jQuery('.toolbar.toolbar-display-top button.pageDown').click();
		});

		/* these sync the top and bottom scale dropdowns */
		jQuery('body').on(
			'change',
			'.toolbar.toolbar-display-top #scaleSelect',
			function (e) {
				jQuery('.toolbar.toolbar-display-bottom #scaleSelect').val(
					jQuery('.toolbar.toolbar-display-top #scaleSelect').value,
				);
			},
		);

		jQuery('body').on(
			'change',
			'.toolbar.toolbar-display-bottom #scaleSelect',
			function (e) {
				jQuery('.toolbar.toolbar-display-top #scaleSelect').val(
					jQuery('.toolbar.toolbar-display-bottom #scaleSelect')
						.value,
				);
			},
		);

		/* this will sync the page number checkboxes */
		jQuery('body').on('change', '.toolbar input.pageNumber', function (e) {
			var this_box = this,
				new_page = parseInt(this_box.value);

			if (new_page > 0) {
				jQuery('.toolbar input.pageNumber').val(new_page);
				jQuery('.toolbar.alt input.pageNumber').val(new_page);
				PDFViewerApplication.page = new_page;
			}
		});

		/* bookmark sync */
		jQuery('.toolbar.toolbar-display-bottom a.bookmark').attr(
			'href',
			jQuery('.toolbar.toolbar-display-top a.bookmark').attr('href'),
		);

		/* this is a UI event that is triggered by certain actions in the PDF JS, allowing us to manually sync toolbars */
		jQuery(document).on(
			'pdf-ui-attribute-changed',
			function (
				event,
				pageNumber,
				pagesCount,
				firstPageDisabled = false,
				lastPageDisabled = false,
			) {
				if (jQuery('.toolbar button.pageUp').prop('disabled')) {
					jQuery('.toolbar.alt button.pageUp').prop('disabled', true);
				} else {
					jQuery('.toolbar.alt button.pageUp').prop(
						'disabled',
						false,
					);
				}
				jQuery('.toolbar input.pageNumber').val(pageNumber);
				jQuery('.toolbar.alt input.pageNumber').val(pageNumber);

				if (pageNumber <= 1) {
					jQuery('.toolbar.alt button.pageUp').prop('disabled', true);
				} else {
					jQuery('.toolbar.alt button.pageUp').prop(
						'disabled',
						false,
					);
				}
				if (pageNumber >= pagesCount) {
					jQuery('.toolbar.alt button.pageDown').prop(
						'disabled',
						true,
					);
				} else {
					jQuery('.toolbar.alt button.pageDown').prop(
						'disabled',
						false,
					);
				}

				jQuery('.toolbar.toolbar-display-bottom a.bookmark').attr(
					'href',
					jQuery('.toolbar.toolbar-display-top a.bookmark').attr(
						'href',
					),
				);
			},
		);

		/* this syncs the bottom zoom in and out buttons */
		jQuery('.toolbar.alt').on('click', 'button.zoomOut', function (e) {
			e.preventDefault();
			jQuery('.toolbar.toolbar-display-top button.zoomOut').click();
		});
		jQuery('.toolbar.alt').on('click', 'button.zoomIn', function (e) {
			e.preventDefault();
			jQuery('.toolbar.toolbar-display-top button.zoomIn').click();
		});

		/* this syncs print and misc buttons that can be turned on */
		jQuery('.toolbar.alt').on('click', 'button.print', function (e) {
			e.preventDefault();
			jQuery('.toolbar.toolbar-display-top button.print').click();
		});
		jQuery('.toolbar.alt').on(
			'click',
			'button.presentationMode',
			function (e) {
				e.preventDefault();
				jQuery(
					'.toolbar.toolbar-display-top button.presentationMode',
				).click();
			},
		);
		jQuery('.toolbar.alt').on('click', 'button.download', function (e) {
			e.preventDefault();
			jQuery('.toolbar.toolbar-display-top button.download').click();
		});
		jQuery('.toolbar.alt').on('click', 'a.bookmark', function (e) {
			e.preventDefault();
			jQuery('.toolbar.toolbar-display-bottom a.bookmark').attr(
				'href',
				jQuery('.toolbar.toolbar-display-top a.bookmark').attr('href'),
			);
			window.location.href = jQuery(
				'.toolbar.toolbar-display-top a.bookmark',
			).attr('href');
		});
		jQuery('.toolbar.alt').on('click', 'button.searchButton', function (e) {
			e.preventDefault();
			jQuery('.toolbar.toolbar-display-top button.searchButton').click();
		});

		/* this syncs the zoom dropdown */
		jQuery('body').on(
			'change',
			'.toolbar.toolbar-display-bottom select#scaleSelect',
			function (e) {
				var this_box = this,
					new_text = jQuery(
						'.toolbar.toolbar-display-bottom select#scaleSelect option:selected',
					).text(),
					new_value = jQuery(
						'.toolbar.toolbar-display-bottom select#scaleSelect option:selected',
					).val();

				jQuery('.toolbar.toolbar-display-top select#scaleSelect').val(
					new_value,
				);
				jQuery(
					'.toolbar.toolbar-display-top select#scaleSelect',
				).trigger('click');
			},
		);

		jQuery(document).on(
			'pdf-customScaleOption-changed',
			function (event, msg, scaleValue) {
				jQuery(
					'.toolbar.toolbar-display-bottom select#scaleSelect #customScaleOption',
				).val('custom');
				jQuery(
					'.toolbar.toolbar-display-bottom select#scaleSelect #customScaleOption',
				).text(msg);
				jQuery('.toolbar.toolbar-display-bottom #scaleSelect').val(
					'custom',
				); // David

				jQuery('.toolbar.toolbar-display-bottom a.bookmark').attr(
					'href',
					jQuery('.toolbar.toolbar-display-top a.bookmark').attr(
						'href',
					),
				);
			},
		);
	}
});
