FillablePDFs_File_Upload = function( args ) {

	var self = this,
		$   = jQuery;

	for ( var prop in args ) {
		if ( args.hasOwnProperty( prop ) ) {
			self[ prop ] = args[ prop ];
		}
	}

	/**
	 * Initialize the file uploader.
	 */
	self.init = function() {

		// Get file upload container.
		var container = $( '.fillablepdfs-template-file-upload' );

		// If file upload container does not exist, exit.
		if ( container.length === 0 ) {
			return;
		}

		// Assign strings to object.
		self.strings = forgravity_fillablepdfs_admin_strings;

		// Assign upload container to object.
		self.container = container;

		// Bind file change event.
		self.bindFileChangeEvent();

		// Handle change template file.
		self.bindChangeTemplateFile();

	}

	/**
	 * Bind file input change event.
	 */
	self.bindFileChangeEvent = function () {

		// Save instance to variable.
		var fileUpload = this;

		// Get file and template name inputs.
		fileUpload.input = $( 'input[type="file"]', self.container );
		fileUpload.nameInput = $( 'input#name' );

		// Bind change event.
		fileUpload.input.on( 'change', function ( e ) {

			// Reset UI.
			fileUpload.resetUI();

			// If file API is not supported within browser, return.
			if ( ! window.FileReader || ! window.File || ! window.FileList || ! window.Blob ) {
				return;
			}

			// Get selected file.
			var file = this.files[ 0 ];

			// Change template name.
			if ( ! fileUpload.nameInput.val() && file ) {
				fileUpload.nameInput.val( file.name.replace( /\.[^/.]+$/, '' ) );
			}

			// Initialize valid file type variable.
			fileUpload.validFileType = true;

			// Validate file type.
			fileUpload.validateFileType( file );

			// Display preview.
			fileUpload.displayPreview( file );

		} );

	}

	/**
	 * Bind change template file button.
	 */
	self.bindChangeTemplateFile = function() {

		$( document ).on( 'click', '.fillablepdfs-template-existing-preview button', function( e ) {

			e.preventDefault();

			$( '.fillablepdfs-template-existing-preview', document ).hide();
			$( '.fillablepdfs-template-file-upload', document ).show();

		} );

	}

	/**
	 * Display a preview of the selected file.
	 */
	self.displayPreview = function( file ) {

		// If file type is invalid, return.
		if ( ! self.validFileType ) {
			return;
		}

		// Display file name.
		$( '.file-name', self.container ).html( file.name );

		// If Safari is not the current browser, stop preview.
		if ( ! window.safari ) {
			return;
		}

		// Initialize new file reader object.
		var fileReader = new FileReader();

		// Load selected file into file reader.
		fileReader.readAsDataURL( file );

		// Once file reader is loaded, display thumbnail.
		fileReader.onload = function( fileReaderEvent ) {
			$( '.fillablepdfs-template-thumbnail', self.container ).attr( {
				'src': fileReaderEvent.target.result,
				'alt': file.name
			} ).show();
		}

	}

	/**
	 * Reset the file upload UI.
	 */
	self.resetUI = function() {

		// Clear error message and file name.
		$( '.file-name, .error-message', self.container ).html( '' );

		// Clear thumbnail.
		$( '.fillablepdfs-template-thumbnail', self.container ).attr( 'src', '' ).hide();

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

		}

	}

	self.init();

}

new FillablePDFs_File_Upload();