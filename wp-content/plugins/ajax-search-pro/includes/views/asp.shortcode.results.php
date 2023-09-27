<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

$_pagination_arrow = file_get_contents(WP_PLUGIN_DIR . '/' . $style['i_pagination_arrow']);
?>
<div id='__original__ajaxsearchprores<?php echo $id; ?>' class='asp_w asp_r asp_r_<?php echo $real_id; ?> asp_r_<?php echo $id; ?> <?php echo $style['resultstype']; ?> ajaxsearchpro wpdreams_asp_sc wpdreams_asp_sc-<?php echo $real_id; ?>'
     data-id="<?php echo $real_id; ?>"
     data-instance="<?php echo self::$perInstanceCount[$real_id]; ?>">

    <?php if ( $style['results_top_box'] == 1 && $style['results_top_box_text'] != '' ): ?>
    <div class="asp_results_top" style="display:none;">
        <div class="asp_rt_phrase"><?php echo  stripslashes( asp_icl_t("Results header information box text, whith search phrase" . " ($id)", $style['results_top_box_text'] ) ); ?></div>
        <div class="asp_rt_nophrase"><?php echo  stripslashes( asp_icl_t("Results header information box text, without search phrase" . " ($id)", $style['results_top_box_text_nophrase'] ) ); ?></div>
    </div>
    <?php endif; ?>

    <?php if ($style['resultstype'] == "isotopic" &&
        ($style['i_pagination_position'] == 'top' || $style['i_pagination_position'] == 'both')): ?>
        <nav class="asp_navigation">

            <a class="asp_prev">
                <?php echo $_pagination_arrow; ?>
            </a>

            <a class="asp_next">
                <?php echo $_pagination_arrow; ?>
            </a>

            <ul></ul>

            <div class="clear"></div>

        </nav>
    <?php endif; ?>

    <?php do_action('asp_layout_before_results', $id); ?>

    <div class="results">

        <?php do_action('asp_layout_before_first_result', $id); ?>

        <div class="resdrg">
        </div>

        <?php do_action('asp_layout_after_last_result', $id); ?>

    </div>

    <?php do_action('asp_layout_after_results', $id); ?>

    <?php if ($style['showmoreresults'] == 1): ?>
        <?php do_action('asp_layout_before_showmore', $id); ?>
        <div class="asp_showmore_container">
            <p class='showmore'>
                <a class='asp_showmore' href="<?php echo esc_url(get_site_url()); ?>"><?php echo asp_icl_t("More results text" . " ($real_id)", $style['showmoreresultstext']) . " <span></span>"; ?></a>
            </p>
            <div class="asp_moreres_loader" style="display: none;">
                <div class="asp_moreres_loader-inner"></div>
            </div>
        </div>
        <?php do_action('asp_layout_after_showmore', $id); ?>
    <?php endif; ?>

    <?php if ($style['resultstype'] == "isotopic" &&
        ($style['i_pagination_position'] == 'bottom' || $style['i_pagination_position'] == 'both')): ?>
        <nav class="asp_navigation">

            <a class="asp_prev">
                <?php echo $_pagination_arrow; ?>
            </a>

            <ul></ul>

            <a class="asp_next">
                <?php echo $_pagination_arrow; ?>
            </a>

            <div class="clear"></div>

        </nav>
    <?php endif; ?>


    <div class="asp_res_loader hiddend">
        <?php if ( trim($style['loadingimage_custom']) == '' ): ?>
            <div class="asp_loader">
                <div class="asp_loader-inner asp_<?php echo $asp_loader_class; ?>">
                <?php
                for($i=0;$i<$asp_loaders[$asp_loader_class];$i++) {
                    echo "
                    <div></div>
                    ";
                }
                ?>
                </div>
            </div>
        <?php else: ?>
            <div class='asp_custom_loader'></div>
        <?php endif; ?>
    </div>
</div>