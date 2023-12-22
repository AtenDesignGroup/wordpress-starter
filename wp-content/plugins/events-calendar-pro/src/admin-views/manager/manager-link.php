<?php
/**
 * Admin View: Admin Manager Page Link
 *
 * @version  5.9.0
 *
 * @var string $manager_link Manager link HTML.
 */
echo $manager_link;

// To avoid a delay in the manager link being injected into the correct spot, we'll use raw JS and include the JS inline
// so that it executes immediately. If we use jQuery or even just raw JS in a separate file, we introduce a lag where
// the button does not appear until after page render.
?>
<script>
	const tecAdminManagerLink = document.querySelectorAll( '.tec-admin-manager__link' );
	const tecAdminManagerPageActionLinks = document.querySelectorAll( '#wpbody-content .wrap .page-title-action' );
	const tecAdminManagerListLink = document.querySelectorAll( '#wpbody-content .wrap .tec-admin-manager__link--list' );

	if ( tecAdminManagerPageActionLinks.length && ! tecAdminManagerListLink.length ) {
		tecAdminManagerPageActionLinks[ tecAdminManagerPageActionLinks.length - 1 ]
			.parentNode
			.insertBefore(
				tecAdminManagerLink[0],
				tecAdminManagerPageActionLinks[ tecAdminManagerPageActionLinks.length - 1 ].nextSibling
			);
	}
</script>
