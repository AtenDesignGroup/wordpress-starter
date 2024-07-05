<?php
/**
 * Aten Accessible Megamenu
 *
 * @category Plugin
 * @package  a11y-megamenu
 * @author   Aten Design Group <https://aten.io/>
 * @license  https://opensource.org/licenses/MIT MIT License
 */

/**
 * Plugin Name:       Aten Accessible Megamenu
 * Plugin URI:        https://aten.io/
 * Description:       A custom FSE-compatible block that generates a fully-accessible mega menu from any WordPress menu.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Aten Design Group
 * Autor URI:         https://aten.io/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       adg-a11y-megamenu
 */

require_once plugin_dir_path( __FILE__ ) . 'class-adg-a11y-nav-walker.php';

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function adg_a11y_megamenu_block_init() {
	register_block_type(
		__DIR__ . '/build',
		array(
			'render_callback' => 'adg_a11y_menu_render_menu',
		)
	);
}
add_action( 'init', 'adg_a11y_megamenu_block_init' );

/**
 * Loads the dashicon library for use with UI elements
 */
function adg_load_dashicons() {
	wp_enqueue_style( 'dashicons' );
}
add_action( 'wp_enqueue_scripts', 'adg_load_dashicons' );

/**
 * Renders the menu using a custom WP Menu Walker.
 *
 * @param object $attr Used to store the menu object being rendered.
 * @see https://developer.wordpress.org/reference/classes/walker_nav_menu/
 */
function adg_a11y_menu_render_menu( $attr ) {
	wp_register_script( 'adg-view-script', plugins_url( 'src/view.js', __FILE__ ), array( 'jquery' ), '3.0.0', array( 'in_footer' => 'true' ) );
	wp_enqueue_script( 'adg-view-script' );

	ob_start();

	if ( '' !== $attr['menu_id'] ) :
		$menu_id           = $attr['menu_id'];
		$menu_obj          = wp_get_nav_menu_object( $menu_id );
		$menu_name         = ( $attr['menu_name'] ) ? $attr['menu_name'] : $menu_obj->name;
		$menu_slug         = ( $attr['menu_slug'] ) ? $attr['menu_slug'] : $menu_obj->slug;
		$mobile_breakpoint = ( $attr['mobile_breakpoint'] ) ? $attr['mobile_breakpoint'] : '1024';
		$menu_prefixed_id  = 'adg-a11y-megamenu-' . $menu_slug; ?>

			<nav id="<?php echo esc_html( $menu_prefixed_id ); ?>-wrap" class="adg-a11y-megamenu-wrap" aria-label="<?php echo esc_html( $menu_name ); ?>" data-mobile-breakpoint="<?php echo esc_html( $mobile_breakpoint ); ?>">

				<?php
				register_nav_menus( array( $menu_slug => esc_html( $menu_name ) ) );

				wp_nav_menu(
					array(
						'menu'            => $menu_id,
						'container'       => 'div',
						'container_class' => 'adg-a11y-megamenu-nav-container',
						'menu_class'      => 'adg-a11y-megamenu',
						'menu_id'         => $menu_prefixed_id,
						'walker'          => new ADG_A11y_Nav_Walker(),
					)
				);
				?>

			</nav>

		<?php
		endif;
	return ob_get_clean();
}
