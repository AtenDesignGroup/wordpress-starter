jQuery(document).ready(function($) {
    // Check if the user has previously dismissed the modal
    if (document.cookie.indexOf('modalDismissed=true') === -1) {
        // If the cookie doesn't exist, display the modal
        setTimeout(showLegalModal, 1500);
    }

    // Add a click event listener to the dismiss button
    const dismissButton = $('#dismiss-modal-button');
    if (dismissButton.length) {
        dismissButton.on('click', function() {
            // Set a cookie that expires in 7 days
            const expirationDate = new Date();
            expirationDate.setDate(expirationDate.getDate() + 7);
            document.cookie = 'modalDismissed=true; expires=' + expirationDate.toUTCString() + '; path=/';

            // Close the modal
            closeLegalModal();
        });
    }

    // Function to display the modal
    function showLegalModal() {
        // Get a reference to the modal element
        const modal = $('#legal-disclaimer-modal');

        // Show the modal by setting its display property to 'block'
        modal.css('display', 'block');
        modal.animate({
            "right": '30px',
            "opacity": 1
        }, 1500);
    }

    // Function to close the modal
    function closeLegalModal() {
        // Get a reference to the modal element
        const modal = $('#legal-disclaimer-modal');

        // Hide the modal by setting its display property to 'none'
        modal.animate({
            "right": '-100vw',
            "opacity": 0
        }, 1500, function() {
            modal.css('display', 'none');
        });
    }
});
