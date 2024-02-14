<?php
/**
 * Displays the site header including the logo and navigation
 *
 * @package WordPress
 * @subpackage ccc
 */

 global $post;
 $post_id = ($post) ? $post->ID : '';

// Check for dark mode pages: Homepage and User Dashboard
 $color_mode = 'light-mode';
 $logo_color = 'color';
 $dark_mode_pages = array(34, 40);
 if(in_array($post_id, $dark_mode_pages)) {
    $color_mode = 'dark-mode';
    $logo_color = 'white';
 }
// if($post_id == 40 && !check_user_authentication())  {
//    $color_mode = 'light-mode';
//    $logo_color = 'color';
// }
//
// // Update logo link to User Dashboard if user is logged in
// $logo_link = check_user_authentication() ? get_home_url() . '/dashboard' : get_home_url();

?>
<div class="header-wrap <?php $color_mode; ?>">
    <div class="header-logo-wrapper <?php echo $color_mode; ?>">
        <a class="logo-link" href="<?php echo $logo_link; ?>" title="Cost & Coverage Collective">
            <img class="logo-img logo-inactive ccc-logo-desktop" id="ccc-logo-desktop-<?php echo $logo_color; ?>" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logos/ccc-logo-horizontal-<?php echo $logo_color; ?>.svg" alt="Cost & Coverage Collective" title="Cost & Coverage Collective" />
            <?php if($color_mode === 'light-mode') { ?><img class="logo-img logo-inactive ccc-logo-mobile" id="ccc-logo-mobile-color" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logos/ccc-logo-mark-color.svg" alt="Cost & Coverage Collective" title="Cost & Coverage Collective" /><?php } ?>
            <img class="logo-img logo-inactive ccc-logo-mobile" id="ccc-logo-mobile-white" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/logos/ccc-logo-mark-white.svg" alt="Cost & Coverage Collective" title="Cost & Coverage Collective" />
        </a>
    </div>

    <?php get_template_part( 'template-parts/header/main-navigation' ); ?>
</div>