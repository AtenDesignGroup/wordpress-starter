( function() {

	'use strict';

	var localStorageKeyName = 'easy_notification_bar_is_hidden';

	if ( 'object' === typeof easyNotificationBar ) {
		localStorageKeyName = easyNotificationBar.local_storage_keyname;
	}

	var isHidden = function() {
		if ( 'undefined' !== typeof localStorage && 'yes' === localStorage.getItem( localStorageKeyName ) ) {
			return true;
		} else {
			return false;
		}
	};

	var noticeInit = function() {
		let notice = document.querySelector( '.easy-notification-bar' );
		if ( notice ) {
			if ( isHidden() ) {
				document.body.classList.remove( 'has-easy-notification-bar' );
			} else {
				notice.classList.remove( 'easy-notification-bar--hidden' );
			}
		}
	};

	var removeOldKeys = function() {
		var oldKeys = [];
		for (let i = 0; i < localStorage.length; i++){
			if ( 'easy_notification_bar_is_hidden' === localStorage.key(i).substring(0,31) ) {
				oldKeys.push(localStorage.key(i));
			}
		}
		for (let i = 0; i < oldKeys.length; i++) {
			localStorage.removeItem(oldKeys[i]);
		}
	};

	var noticeClose = function() {

		document.addEventListener( 'click', (e) => {
			let targetElement = e.target || e.srcElement;
			let toggle = targetElement.closest( '.easy-notification-bar__close' );

			if ( ! toggle ) {
				return;
			}

			e.preventDefault();

			let notice = document.querySelector( '.easy-notification-bar' );

			notice.classList.add( 'easy-notification-bar--hidden' );
			document.body.classList.remove( 'has-easy-notification-bar' );

			if ( 'undefined' !== typeof localStorage ) {
				removeOldKeys();
				localStorage.setItem( localStorageKeyName, 'yes' );
			}

		} );

	};

	document.addEventListener( 'DOMContentLoaded', function() {
		noticeInit();
		noticeClose();
	} );

} )();