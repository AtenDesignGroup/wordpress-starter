( function( $ ) {

	/**
	 * Move error container below heading tab.
	 */
	$( '#tab_import_pdf .error.move-me' ).each( function() {

		$( this ).removeClass( '.move-me' ).after( $( '.import_pdf h2' ) );

	} );

	/**
	 * Initialize Chosen for select inputs.
	 */
	$( 'select' ).chosen();

	/**
	 * Submit import form when a template is selected.
	 */
	$( 'select[name="template_id"]' ).on( 'change', function() {

		$( this ).closest( 'form' ).submit();

	} );


} )( jQuery );

FillablePDFs_Field_Choices = function( args ) {

	var self = this,
	    $    = jQuery;

	for ( var prop in args ) {
		if ( args.hasOwnProperty( prop ) ) {
			self[ prop ] = args[ prop ];
		}
	}

	/**
	 * Initialize the import file selector.
	 */
	 self.init = function() {

		// Assign strings to object.
		self.strings = forgravity_fillablepdfs_import_strings;

		// Assign editor element to object.
		self.editor = $( '.fillablepdfs-choices-editor' );

		// Bind events.
		self.bindCancelEvent();
		self.bindSaveEvent();
		self.changeButtonVisibility();
		self.openModal();

	}

	/**
	 * Close modal when canceling changes.
	 */
	self.bindCancelEvent = function() {

		self.editor.on( 'click', 'a[data-action="cancel"]', function( e ) {

			// Prevent default event.
			e.preventDefault();

			// Close modal.
			tb_remove();

		} );

	}

	/**
	 * Save choices.
	 */
	self.bindSaveEvent = function() {

		self.editor.on( 'click', 'a[data-action="save"]', function( e ) {

			// Prevent default event.
			e.preventDefault();
	
			// Get choices
			var choices = $( 'textarea', self.editor ).val().split( "\n" );
	
			// Remove empty choices.
			choices = $.grep( choices, function( n ) { return n == 0 || n } );
	
			// Convert choices to JSON string.
			choices = JSON.stringify( choices );
	
			// Get field index.
			var fieldIndex = $( 'input[name="index"]', self.editor ).val();
	
			// Save choices.
			$( 'input[name="form_fields[' + fieldIndex + '][choices]"]' ).val( choices );
	
			// Close modal.
			tb_remove();
	
		} );

	}

	/**
	 * Toggle visibility of choices button.
	 */
	self.changeButtonVisibility = function() {

		$( document ).on( 'change', '.fillablepdfs-import-pdf-mapping select', function( e ) {
	
			var fieldType = $( this ).val(),
			    choicesButton = $( this ).siblings( '.choices-button' ),
			    choicesFieldTypes = [ 'select', 'multiselect', 'checkbox', 'radio' ];
	
			if ( choicesFieldTypes.indexOf( fieldType ) > -1 ) {
				choicesButton.show();
			} else {
				choicesButton.hide();
			}
	
		} );

	}

	/**
	 * Open modal.
	 */
	self.openModal = function() {
		
		$( document ).on( 'click', '.button.choices-button', function( e ) {
	
			// Prevent default event.
			e.preventDefault();
	
			// Get choices and index.
			var choicesDOM = $( this ).siblings( 'input[type="hidden"]' ),
			    choices    = $.parseJSON( choicesDOM.val() );

			// Set index.
			$( 'input[name="index"]', self.editor ).val( choicesDOM.data( 'index' ) );
	
			// Set textarea value to choices.
			$( 'textarea', self.editor ).val( choices.join( "\r" ) );
	
			// Open modal.
			tb_show( self.strings.modal_title, 'TB_inline?height=264&width=300&inlineId=fillablepdfs-choices-editor' );
	
		} );
		
	}

	self.init();

}

FillablePDFs_File_Select = function( args ) {

	var self = this,
	    $    = jQuery;

	for ( var prop in args ) {
		if ( args.hasOwnProperty( prop ) ) {
			self[ prop ] = args[ prop ];
		}
	}

	/**
	 * Initialize the import file selector.
	 */
	self.init = function() {

		// Get file upload container.
		var container = $( '#fillablepdfs_template_file' ).parent();

		// If file upload container does not exist, exit.
		if ( container.length === 0 ) {
			return;
		}

		// Assign strings to object.
		self.strings = forgravity_fillablepdfs_import_strings;

		// Assign upload container to object.
		self.container = container;

		// Bind file change event.
		self.bindFileChangeEvent();

	}

	/**
	 * Bind file input change event.
	 */
	 self.bindFileChangeEvent = function() {

	 	// Save instance to variable.
	 	var fileUpload = this;

	 	// Get file input.
		fileUpload.input = $( '#fillablepdfs_template_file', self.container );

		// Bind change event.
		fileUpload.input.on( 'change', function( e ) {

			// Reset UI.
			fileUpload.resetUI();

			// If file API is not supported within browser, return.
			if ( ! window.FileReader || ! window.File || ! window.FileList || ! window.Blob ) {
				return;
			}

			// Get selected file.
			var file = this.files[0];

			// Initialize valid file type variable.
			fileUpload.validFileType = true;

			// Validate file type.
			fileUpload.validateFileType( file );

		} );

	}

	/**
	 * Reset the file selection UI.
	 */
	self.resetUI = function() {

		 // Clear error message and file name.
		 $( '.error-message', self.container ).html( '' );

	}

	/**
	 * Validate the type of the selected file.
	 */
	self.validateFileType = function( file ) {

		// If invalid file type, set validation message and unset file selection.
		if ( file.type !== 'application/pdf' ) {

			// Set valid file type flag to false.
			self.validFileType = false;

			// Set validation message.
			$( '.error-message', self.container ).html( self.strings.illegal_file_type );

			// Unset file selection.
			self.input.replaceWith( self.input.val( '' ).clone( true ) );

		} else {

			self.container.closest( 'form' ).submit();

		}

	}

	self.init();

}

new FillablePDFs_Field_Choices();
new FillablePDFs_File_Select();
