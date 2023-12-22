/*
 * Gutenberg block Javascript code
 */
    var __               = wp.__nx; // The _() function for internationalization.
    var createElement     = wp.element.createElement; // The wp.element.createElement() function to create elements.
    var registerBlockType = wp.blocks.registerBlockType; // The registerBlockType() function to register blocks.


    var make_title_from_url = function(url) {
        var re = RegExp('/([^/]+?)(\\.pdf(\\?[^/]*)?)?$', 'i');
        var matches = url.match(re);
        if (matches.length >= 2) {
            return matches[1];
        }
        return url;
    }
	/**
     * Register block
     *
     * @param  {string}   name     Block name.
     * @param  {Object}   settings Block settings.
     * @return {?WPBlock}          Block itself, if registered successfully,
     *                             otherwise "undefined".
     */
    registerBlockType(
		'pdfemb/pdf-embedder-viewer', // Block name. Must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
        {
            title: 'PDF Embedder', // Block title. __() function allows for internationalization.
            icon: 'media-document', // Block icon from Dashicons. https://developer.wordpress.org/resource/dashicons/.
			category: 'common', // Block category. Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
            attributes: {
				pdfID: {
                    type: 'number'
                },
                url: {
                    type: 'string'
                },
                width: {
                    type: 'string'
                },
                height: {
                    type: 'string'
                },
				toolbar: {
                    type: 'string',
                    default: 'default'
                },
                toolbarfixed: {
                    type: 'string',
                    default: 'off'
                },
                externalButton: {
                    type: 'boolean',
                },
                searchButton: {
                    type: 'boolean',
                },
                downloadButton: {
                    type: 'boolean',
                },
                printButton: {
                    type: 'boolean',
                },
                bookmarkButton: {
                    type: 'boolean',
                },
                presentationButton: {
                    type: 'boolean',
                },
                sidebarColor: {
                    type: 'string',
                    default: '#000000'
                },
                iconColor: {
                    type: 'string',
                    default: '#2a303c'
                },
                buttonColor: {
                    type: 'string',
                    default: '#ffffff'
                },
                sidebarColorTest: {
                    type: 'string',
                    default: '000000'
                }
            },

            // Defines the block within the editor.
            edit: function( props ) {

				var {attributes , setAttributes, focus, className} = props;

				var InspectorControls = wp.editor.InspectorControls;
				var Button = wp.components.Button;
				var RichText = wp.editor.RichText;
				var Editable = wp.blocks.Editable; // Editable component of React.
				var MediaUpload = wp.editor.MediaUpload;
				var btn = wp.components.Button;
				var TextControl = wp.components.TextControl;
				var SelectControl = wp.components.SelectControl;
				var RadioControl = wp.components.RadioControl;
                var CheckboxControl = wp.components.CheckboxControl;
                var ColorPickerControl = wp.components.ColorPicker;
                var ColorPaletteControl = wp.components.ColorPalette;

                var PanelBody = wp.components.PanelBody;

                var Card = wp.components.Card;
                var CardBody = wp.components.CardBody;

				var onSelectPDF = function(media) {
                    return props.setAttributes({
                        url: media.url,
                        pdfID: media.id
                    });
                }

                function onChangeWidth(v) {
                    setAttributes( {width: v} );
                }

                function onChangeHeight(v) {
                    setAttributes( {height: v} );
                }

				function onChangeToolbar(v) {
                    setAttributes( {toolbar: v} );
                }

                function onChangeToolbarfixed(v) {
                    setAttributes( {toolbarfixed: v} );
                }

                function onChangeExternalButton(v) {
                    setAttributes( {externalButton: v} );
                }

                function onChangeSearchButton(v) {
                    setAttributes( {searchButton: v} );
                }

                return [
					createElement(
                        MediaUpload,
                        {
                            onSelect: onSelectPDF,
                            type: 'application/pdf',
                            value: attributes.pdfID,
                            render: function(open) {
                                return createElement(btn,{onClick: open.open },
                                    attributes.url ? 'PDF: ' + attributes.url : 'Click here to Open Media Library to select PDF')
                            }
                        }
					),

					createElement( InspectorControls, { key: 'inspector' }, // Display the block options in the inspector pancreateElement.
						createElement(PanelBody,{ title: 'Main Options', initialOpen: true, className: 'components-panel__body pdf-inspector-panelbody is-opened'},
							createElement(
								'p',
								{},
								'Enter "max" or an integer number of pixels to change the height and width.'
							),
							createElement(
								TextControl,
								{
									label: 'Width',
									value: attributes.width,
									onChange: onChangeWidth
								}
							),
							createElement(
								TextControl,
								{
									label: 'Height',
									value: attributes.height,
									onChange: onChangeHeight
								}
							),
							createElement(
								'hr',
								{},
							),
							createElement(
								SelectControl,
									{
										label: 'Toolbar Location',
										value: attributes.toolbar,
										options: [
                                            { label: '(Default)', value: 'default' },
											{ label: 'Top', value: 'top' },
											{ label: 'Bottom', value: 'bottom' },
											{ label: 'Both', value: 'both' },
											{ label: 'None', value: 'none' }
										],
										onChange: onChangeToolbar
									}
							),
							createElement(
								RadioControl,
								{
									label: 'Toolbar Hover',
									selected: attributes.toolbarfixed,
									options: [
										{ label: 'On hover over document only', value: 'off' },
										{ label: 'Always visible ', value: 'on' }
									],
									onChange: onChangeToolbarfixed
								}
							),
                        ),
                        createElement(PanelBody,{ title: 'Additional Options', initialOpen: true, className: 'components-panel__body pdf-inspector-panelbody is-opened'},
							createElement(
								CheckboxControl,
								{
                                    label: 'External Links',
                                    className: 'pdf-external-button-gb',
                                    onChange: onChangeExternalButton,
                                    checked: attributes.externalButton,
								}
							),
							createElement(
								CheckboxControl,
								{
                                    label: 'Search Button',
                                    className: 'pdf-search-button-gb',
                                    onChange: onChangeSearchButton,
                                    checked: attributes.searchButton,
								}
							),
						),

					),
                ];
            },

            // Defines the saved block.
            save: function( props ) {
				return createElement(
                    'p',
                    {
                        className: props.className,
						key: 'return-key',
                    },props.attributes.content);
			},
        }
    );
