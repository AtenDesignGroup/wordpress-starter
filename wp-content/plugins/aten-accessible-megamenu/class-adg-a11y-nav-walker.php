<?php
/**
 * Aten Accessible Megamenu
 *
 * @category ADG_Ally_Nav_Walker_Class
 * @package  a11y-megamenu
 * @author   Aten Design Group <https://aten.io/>
 * @license  https://opensource.org/licenses/MIT MIT License
 */

if ( ! class_exists( 'ADG_A11y_Nav_Walker' ) ) {
	/**
	 * This class extends the WP Core Nav Walker class to create list output
	 * with buttons instead of links for items with submenus.
	 *
	 * @see https://developer.wordpress.org/reference/classes/walker_nav_menu/
	 */
	class ADG_A11y_Nav_Walker extends Walker_Nav_Menu {
		/**
		 * Starts the element output for the menu.
		 *
		 * @param string $output Used to append additional content (passed by reference).
		 * @param object $item Menu item data object.
		 * @param int    $depth Depth of menu item.
		 * @param object $args An array of wp_nav_menu() arguments.
		 * @param int    $id ID of the current menu item. Default 0.
		 * @see https://developer.wordpress.org/reference/classes/walker_nav_menu/start_el/
		 */
		public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
			$icon      = get_field( 'menu_item_icon', $item );
			$icon_html = ( $icon ) ? '<icon class="adg-menu-icon" aria-hidden="true">' . file_get_contents( get_stylesheet_directory_uri() . '/assets/icons/' . $icon . '.svg' ) . '</icon>' : '';

			$output .= "<li class='" . implode( ' ', $item->classes ) . ' adg-a11y-menu-item-level-' . $depth . "'>";
			if ( ! ( in_array( 'menu-item-has-children', $item->classes, true ) ) ) {
				if ( ( in_array( 'current-menu-item', $item->classes, true ) ) ) {
					$output .= '<a href="' . $item->url . '" aria-current="page">';
				} else {
					$output .= '<a href="' . $item->url . '">';
				}
			} else {
				$output .= '<button class="adg-a11y-megamenu-button" aria-haspopup="true" aria-expanded="false">';
			}
			if ( ! ( in_array( 'menu-item-has-children', $item->classes, true ) ) ) {
				if ( ! ( in_array( 'with-icon', $item->classes, true ) ) ) {
					$white_space_wrap_point = strrpos( $item->title, ' ' );
					$wrapped_title          = substr_replace( $item->title, ' <span class="white-space-nowrap">', $white_space_wrap_point, 0 );
					$output                .= $wrapped_title;
					$output                .= $icon_html;
					$output                .= '</span></a>';
				} else {
					$output .= $item->title;
					$output .= $icon_html;
					$output .= '</a>';
				}
			} else {
				$output .= $item->title;
				$output .= $icon_html;
				$output .= '</button>';
			}
		}
	}
}
