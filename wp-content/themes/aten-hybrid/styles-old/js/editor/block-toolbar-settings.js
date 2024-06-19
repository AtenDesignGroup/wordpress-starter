wp.domReady( () => {
	/**
	 * Rich Text Component Customizations
	 */
	// Removing additional buttons from richText blocks, only allowing bold, italics, and links
    wp.richText.unregisterFormatType( 'core/text-color' );
	wp.richText.unregisterFormatType( 'core/code' );
	wp.richText.unregisterFormatType( 'core/keyboard' );
	wp.richText.unregisterFormatType( 'core/image' );
	wp.richText.unregisterFormatType( 'core/superscript' );
	wp.richText.unregisterFormatType( 'core/subscript' );
	wp.richText.unregisterFormatType( 'core/underline' );
    wp.richText.unregisterFormatType( 'core/strikethrough' );

	/**
	 * Button Block Customizations
	 */
	// Removing default style options from Button blocks
    wp.blocks.unregisterBlockStyle('core/button', 'fill');
    wp.blocks.unregisterBlockStyle('core/button', 'outline');

	// Adding custom style options to Button blocks
	wp.blocks.registerBlockStyle( 'core/button', {
		name: 'default-purple',
		label: 'Purple',
		isDefault: 'true'
	} );
    wp.blocks.registerBlockStyle( 'core/button', {
		name: 'no-icon-purple',
		label: 'No Icon',
	} );
    // wp.blocks.registerBlockStyle( 'core/button', {
	// 	name: 'default-white',
	// 	label: 'White',
	// } );
    // wp.blocks.registerBlockStyle( 'core/button', {
	// 	name: 'no-icon-white',
	// 	label: 'No Icon White',
	// } );
	wp.blocks.registerBlockStyle( 'core/button', {
		name: 'text-icon',
		label: 'Text with Icon',
	} );
	wp.blocks.registerBlockStyle( 'core/button', {
		name: 'text-no-icon',
		label: 'Text No Icon',
	} );
	// wp.blocks.registerBlockStyle( 'core/button', {
	// 	name: 'text-icon-white',
	// 	label: 'White Text with Icon',
	// } );
	// wp.blocks.registerBlockStyle( 'core/button', {
	// 	name: 'text-no-icon-white',
	// 	label: 'White Text No Icon',
	// } );
	wp.blocks.registerBlockStyle( 'core/button', {
		name: 'download-purple',
		label: 'Download Purple',
	} );
	wp.blocks.registerBlockStyle( 'core/button', {
		name: 'download-gray',
		label: 'Download Gray',
	} );

	/* Removing unnecessary separator styles */
	wp.blocks.unregisterBlockStyle('core/separator', 'twentytwentyone-separator-thick');
	wp.blocks.unregisterBlockStyle('core/separator', 'dots');

	/* Removing unnecessary table styles */
	wp.blocks.unregisterBlockStyle('core/table', 'stripes');

	/* Removing unnecessary image styles */
	wp.blocks.unregisterBlockStyle('core/image', 'rounded');
	wp.blocks.unregisterBlockStyle('core/image', 'twentytwentyone-image-frame');
	wp.blocks.unregisterBlockStyle('core/image', 'twentytwentyone-border');

	// Registering custom rounded image style
	wp.blocks.registerBlockStyle( 'core/image', {
		name: 'rounded-corners',
		label: 'Rounded Corners',
	} );
});

