<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * This is the default template for one vertical result
 *
 * !!!IMPORTANT!!!
 * Do not make changes directly to this file! To have permanent changes copy this
 * file to your theme directory under the "asp" folder like so:
 *    wp-content/themes/your-theme-name/asp/vertical.php
 *
 * It's also a good idea to use the actions to insert content instead of modifications.
 *
 * WARNING: Modifying anything in this file might result in search malfunctioning,
 * so be careful and use your test environment.
 *
 * You can use any WordPress function here.
 * Variables to mention:
 *      Object() $r - holding the result details
 *      Array[]  $s_options - holding the search options
 *
 * I DO NOT RECOMMEND PUTTING ANYTHING BEFORE OR AFTER THE
 * <div class='item'>..</div><div class="asp_spacer"></div> structure
 *
 * You can leave empty lines for better visibility, they are cleared before output.
 *
 * MORE INFO: https://wp-dreams.com/knowledge-base/result-templating/
 *
 * @since: 4.0
 */
?>
<div id="asp-res-<?php echo $r->id; ?>" class='item<?php echo apply_filters('asp_result_css_class', $asp_res_css_class, $r->id, $r); ?>'>

    <?php do_action('asp_res_vertical_begin_item'); ?>

    <div class='asp_content'>


        <?php if (!empty($r->image)): ?>

            <?php do_action('asp_res_vertical_before_image'); ?>

            <?php if ( $load_lazy == 1 ): ?>
                <a class='asp_res_image_url' href='<?php echo $r->link; ?>'<?php echo ($s_options['results_click_blank'])?" target='_blank'":""; ?>>
                    <div class='asp_image asp_lazy' data-src="<?php echo esc_attr($r->image); ?>">
                        <div class='void'></div>
                    </div>
                </a>
            <?php else: ?>
                <a class='asp_res_image_url' href='<?php echo $r->link; ?>'<?php echo ($s_options['results_click_blank'])?" target='_blank'":""; ?>>
                    <div class='asp_image' data-src="<?php echo esc_attr($r->image); ?>" style='background-image: url("<?php echo $r->image; ?>");'>
                        <div class='void'></div>
                    </div>
                </a>
            <?php endif; ?>

            <?php do_action('asp_res_vertical_after_image'); ?>

        <?php endif; ?>


        <h3><a class="asp_res_url" href='<?php echo $r->link; ?>'<?php echo ($s_options['results_click_blank'])?" target='_blank'":""; ?>>
                <?php echo $r->title; ?>
                <?php if ($s_options['resultareaclickable'] == 1): ?>
                <span class='overlap'></span>
                <?php endif; ?>
        </a></h3>

        <div class='etc'>

            <?php if ( $s_options['showauthor'] == 1 && !empty($r->author) ): ?>
            <span class='asp_author'><?php echo $r->author; ?></span>
            <?php endif; ?>

            <?php if ( $s_options['showdate'] == 1 && !empty($r->date) ): ?>
            <span class='asp_date'><?php echo $r->date; ?></span>
            <?php endif; ?>

        </div>

        <?php if ($show_description == 1): ?>
        <div class="asp_res_text">
            <?php echo $r->content; ?>
        </div>
        <?php endif; ?>

    </div>

    <?php do_action('asp_res_vertical_after_content'); ?>

    <div class='clear'></div>

    <?php do_action('asp_res_vertical_end_item'); ?>

</div>