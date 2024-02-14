<?php
/**
 * Displays the main navigation
 *
 * @package WordPress
 * @subpackage ccc
 */

// // Creating custom Nav Walker to have control over A11y
// if ( !class_exists('CCC_Nav_Walker') ) {
//    class CCC_Nav_Walker extends Walker_Nav_Menu {
//        function start_el(&$output, $item, $depth=0, $args=[], $id=0) {
//            $output .= "<li class='" .  implode(" ", $item->classes) . "'>";
//            if (!(in_array("menu-item-has-children", $item->classes))) {
//                if((in_array("current-menu-item", $item->classes))) {
//                    $output .= '<a href="' . $item->url . '" aria-current="page">';
//                } else {
//                    $output .= '<a href="' . $item->url . '">';
//                }
//
//            } else {
//                $output .= '<button class="ccc-megamenu-button" aria-haspopup="true" aria-expanded="false">';
//            }
//            $output .= $item->title;
//            if (!(in_array("menu-item-has-children", $item->classes))) {
//                $output .= '</a>';
//            } else {
//                $output .= '</button>';
//            }
//        }
//    }
//}

$menu_id = '';
//if (is_user_logged_in()) {
//	$user_id          = get_current_user_id(); // Get the user's ID
//	$user_data = get_userdata($user_id);
//	$user_roles = $user_data->roles;
//
//	if (!empty($user_roles)) {
//		// The user's role is stored in the $user_roles array. You can access it like this:
//		$user_role = $user_roles[0]; // In case a user has multiple roles, you can choose the primary role.
//	}
//	$membership_level = pmpro_getMembershipLevelForUser( $user_id ); // Get the user's membership level
//	if(in_array('administrator', $user_roles)){
//		// User is logged in as an administrator
//		$menu_id = 4; // admins
//	}
//	elseif ( $membership_level ) {
//		$level_id = $membership_level->id;
//		$approval_status = get_user_meta( $user_id, 'pmpro_approval_' . $level_id, true );
//        if ($approval_status['status'] !== 'pending') {
//			// User is logged in and has an approved membership
//			$menu_id = 4; // Menu for approved members
//		} else {
//			// User is logged in but not an approved, pending, or denied member
//			$menu_id = 5; // Menu for everyone else
//		}
//	} else {
//		// User is logged in but not a member of any level
//		$menu_id = 5; // Menu for everyone else
//	}
//} else {
//	// User is logged in but not a member of any level
//	$menu_id = 5; // Menu for everyone else
//}



//$menu_name = 'Main Navigation Menu';
//$menu_slug = 'main-nav-menu';
//$menu_prefixed_id = 'ccc-megamenu-' . $menu_slug;
//
//?>
<!--<div class="main-nav">-->
<!--    <nav id="ccc-main-menu-nav" class="ccc-megamenu-nav" aria-label="Main Navigation Menu">-->
<!--        <button id="mobile-menu-button" class="ccc-mobile-toggle-button" aria-expanded="false" aria-controls="ccc-megamenu-main-nav-menu">-->
<!--            <img class="menu-collapsed-icon menu-toggle-icon active-icon" src="--><?php //echo get_stylesheet_directory_uri(); ?><!--/assets/icons/ui/dark_purple/bars.svg" alt="" />-->
<!--            <img class="menu-expanded-icon menu-toggle-icon" src="--><?php //echo get_stylesheet_directory_uri(); ?><!--/assets/icons/ui/dark_purple/close.svg" alt="" />-->
<!--            <span class="menu-text">Menu</span>-->
<!--        </button>-->
<!--        --><?php //
//            wp_nav_menu( array(
//                'menu'			 		=> $menu_id,
//                'container'		 		=> '',
//                'menu_class'	 		=> 'ccc-megamenu',
//                'menu_id'		  	    => $menu_prefixed_id,
//                'walker' 			    => new CCC_Nav_Walker
//            ) );
//        ?>
<!--    </nav>-->
<!--</div>-->