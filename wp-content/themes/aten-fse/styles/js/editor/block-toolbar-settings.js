wp.domReady( () => {

	wp.data.dispatch( 'core/edit-post' ).removeEditorPanel( 'taxonomy-panel-news_categories' );

	/**
	 * Rich Text Component Customizations
	 */
	// Removing additional buttons from richText blocks, only allowing bold, italics, and links
    wp.richText.unregisterFormatType( 'core/text-color' );
	wp.richText.unregisterFormatType( 'core/code' );
	wp.richText.unregisterFormatType( 'core/keyboard' );
	wp.richText.unregisterFormatType( 'core/image' );
	wp.richText.unregisterFormatType( 'core/keyboard' );
	wp.richText.unregisterFormatType( 'core/superscript' );
	wp.richText.unregisterFormatType( 'core/subscript' );
	wp.richText.unregisterFormatType( 'core/underline' );
    wp.richText.unregisterFormatType( 'core/strikethrough' );

	/**
	 * Table Block Customizations
	 */
	// Removing default style options from Table blocks
	wp.blocks.unregisterBlockStyle( 'core/table', 'stripes' );

	// Adding custom style options to Table blocks
	wp.blocks.registerBlockStyle( 'core/table', {
		name: 'wide',
		label: 'Wide'
  	} );

	/**
	 * Heading Block Customizations
	 */
	// Adding custom style option to Heading blocks
	wp.blocks.registerBlockStyle( 'core/heading', {
		name: 'heading-stylized',
		label: 'Stylized'
	} );

	/**
	 * Button Block Customizations
	 */
	// Removing default style options from Button blocks
	wp.blocks.unregisterBlockStyle( 'core/button', 'outline' );
	wp.blocks.unregisterBlockStyle( 'core/button', 'fill' );

	// Adding custom style options to Button blocks
	wp.blocks.registerBlockStyle( 'core/button', {
		name: 'default-navy',
		label: 'Navy',
		isDefault: 'true'
	} );
	wp.blocks.registerBlockStyle( 'core/button', {
		name: 'default-gold',
		label: 'Gold',
	} );
	wp.blocks.registerBlockStyle( 'core/button', {
		name: 'default-sky-blue',
		label: 'Sky Blue',
	} );
	wp.blocks.registerBlockStyle( 'core/button', {
		name: 'default-sky-blue',
		label: 'Sky Blue',
	} );
	wp.blocks.registerBlockStyle( 'core/button', {
		name: 'no-bg-green',
		label: 'Green - No BG',
	} );

	/**
	 * Image Block Customizations
	 */
	// Removing default style options from Image blocks
	wp.blocks.unregisterBlockStyle( 'core/image', 'rounded' );
	wp.blocks.unregisterBlockStyle( 'core/image', 'default' );

	// Adding custom style options to Image blocks
	wp.blocks.registerBlockStyle( 'core/image', {
		name: 'block-large',
		label: 'Large Block',
		isDefault: 'true'
	} );
	wp.blocks.registerBlockStyle( 'core/image', {
		name: 'inline-white',
		label: 'Inline - White',
	} );
	wp.blocks.registerBlockStyle( 'core/image', {
		name: 'inline-blue',
		label: 'Inline - Blue',
	} );
	wp.blocks.registerBlockStyle( 'core/image', {
		name: 'inline-large-white',
		label: 'Inline Large - White',
	} );
	wp.blocks.registerBlockStyle( 'core/image', {
		name: 'inline-large-blue',
		label: 'Inline Large - Blue',
	} );

	tinymce.init({
		selector: 'textarea', 
		toolbar: 'fontsize',
		font_size_formats: '16px 18px 20px'
	});
});