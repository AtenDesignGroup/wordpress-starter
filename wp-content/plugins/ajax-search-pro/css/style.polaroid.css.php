<?php
/**
 * WARNING! DO NOT EDIT THIS FILE DIRECTLY!
 *
 * FOR CUSTOM CSS USE THE PLUGIN THEME OPTIONS->CUSTOM CSS PANEL.
 */

/* Prevent direct access */
use WPDRMS\ASP\Utils\Css;defined('ABSPATH') or die("You can't access this file directly.");
?>

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?> .photostack,
    <?php echo $asp_res_ids2; ?> .photostack,
<?php endif; ?>
<?php echo $asp_res_ids; ?> .photostack {
  <?php wpdreams_gradient_css($style['prescontainerbg']); ?>
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?> .photostack figure,
    <?php echo $asp_res_ids2; ?> .photostack figure,
<?php endif; ?>
<?php echo $asp_res_ids; ?> .photostack figure {
	width: <?php echo $style['preswidth'] ?>;
    height: auto;
	max-height: <?php echo $style['presheight'] ?>;
	position: absolute;
	display: inline-block;
	background: #fff;
	padding: <?php echo $style['prespadding'] ?>;
	text-align: center;
	margin: 5px;
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?> .photostack figcaption h2,
    <?php echo $asp_res_ids2; ?> .photostack figcaption h2,
<?php endif; ?>
<?php echo $asp_res_ids; ?> .photostack figcaption h2 {
	margin: 20px 0 0 0;
    <?php echo Css::font($style['prestitlefont']); ?>
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?> .photostack figcaption h2 a,
    <?php echo $asp_res_ids2; ?> .photostack figcaption h2 a,
<?php endif; ?>
<?php echo $asp_res_ids; ?> .photostack figcaption h2 a {
    <?php echo Css::font($style['prestitlefont']); ?>
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?> .photostack .etc,
    <?php echo $asp_res_ids2; ?> .photostack .etc,
<?php endif; ?>
<?php echo $asp_res_ids; ?> .photostack .etc {
    <?php echo Css::font($style['pressubtitlefont']); ?>
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?> .asp_image,
    <?php echo $asp_res_ids2; ?> .asp_image,
<?php endif; ?>
<?php echo $asp_res_ids; ?> .asp_image {
	width: <?php echo (wpdreams_width_from_px($style['preswidth'])- 2*wpdreams_width_from_px($style['prespadding'])); ?>px;
	height: <?php echo (wpdreams_width_from_px($style['preswidth'])- 2*wpdreams_width_from_px($style['prespadding'])); ?>px;
    <?php echo Css::font($style['pressubtitlefont']); ?>
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?> .photostack-back,
    <?php echo $asp_res_ids2; ?> .photostack-back,
<?php endif; ?>
<?php echo $asp_res_ids; ?> .photostack-back {
    <?php echo Css::font($style['presdescfont']); ?>
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?> .photostack nav span,
    <?php echo $asp_res_ids2; ?> .photostack nav span,
<?php endif; ?>
<?php echo $asp_res_ids; ?> .photostack nav span {
  <?php wpdreams_gradient_css($style['pdotssmallcolor']); ?>
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?> .photostack nav span.current,
    <?php echo $asp_res_ids2; ?> .photostack nav span.current,
<?php endif; ?>
<?php echo $asp_res_ids; ?> .photostack nav span.current {
  <?php wpdreams_gradient_css($style['pdotscurrentcolor']); ?>
}

<?php if ($use_compatibility == true): ?>
    <?php echo $asp_res_ids1; ?> .photostack nav span.current.flip,
    <?php echo $asp_res_ids2; ?> .photostack nav span.current.flip,
<?php endif; ?>
<?php echo $asp_res_ids; ?> .photostack nav span.current.flip {
  <?php wpdreams_gradient_css($style['pdotsflippedcolor']); ?>
}