<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * This is the default template for one polaroid result
 *
 * !!!IMPORTANT!!!
 * Do not make changes directly to this file! To have permanent changes copy this
 * file to your theme directory under the "asp" folder like so:
 *    wp-content/themes/your-theme-name/asp/polaroid.php
 *
 * It's also a good idea to use the actions to insert content instead of modifications.
 *
 * You can use any WordPress function here.
 * Variables to mention:
 *      Object() $r - holding the result details
 *      Array[]  $s_options - holding the search options
 *
 * DO NOT OUTPUT ANYTHING BEFORE OR AFTER THE <figure> element!!
 *
 * You can leave empty lines for better visibility, they are cleared before output.
 *
 * MORE INFO: https://wp-dreams.com/knowledge-base/result-templating/
 *
 * @since: 4.0
 */
?>
<figure id="asp-res-<?php echo $r->id; ?>" class='photostack-flip photostack-current<?php echo apply_filters('asp_result_css_class', $asp_res_css_class, $r->id, $r); ?>'>

    <?php if ( isset($r->image) ): ?>
        <a class="asp_res_url" href='<?php echo $r->link; ?>'<?php echo ($s_options['results_click_blank'])?" target='_blank'":""; ?>>
	        <div class='asp_image' style='background-image: url("<?php echo $r->image; ?>");' data-src="<?php echo esc_attr($r->image); ?>"></div>
        </a>
    <?php elseif ($s_options['pifnoimage'] == 'descinstead'): ?>
        <a class="asp_res_url" href='<?php echo $r->link; ?>'<?php echo ($s_options['results_click_blank'])?" target='_blank'":""; ?>><?php echo $r->content; ?></a>
    <?php endif; ?>

    <figcaption>

        <h2 class='photostack-title'><a href='<?php echo $r->link; ?>'<?php echo ($s_options['results_click_blank'])?" target='_blank'":""; ?>>
            <?php echo $r->title; ?>
            <?php if ($s_options['resultareaclickable'] == 1): ?>
                <span class='overlap'></span>
            <?php endif; ?>
        </a></h2>

        <div class='etc'>
            <?php if ($s_options['pshowsubtitle']): ?>
                <?php if ($s_options['showauthor'] == 1): ?>
                    <span class='asp_author'><?php echo $r->author; ?></span>
                <?php endif; ?>

                <?php if ($s_options['showdate'] == 1): ?>
                    <span class='asp_date'><?php echo $r->date; ?></span>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ($s_options['pshowdesc'] && !empty($r->content)): ?>
            <div class='photostack-back'><?php echo $r->content; ?></div>
        <?php endif; ?>

    </figcaption>

</figure>