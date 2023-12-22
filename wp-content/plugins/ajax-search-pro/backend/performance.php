<?php
/* Prevent direct access */

use WPDRMS\ASP\Misc\Performance;

defined('ABSPATH') or die("You can't access this file directly.");

$perf_options = wd_asp()->o['asp_performance'];

if (ASP_DEMO) $_POST = null;

$pstats = new Performance('asp_performance_stats');
$asp_performance = $pstats->get_data();
?>

<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'settings/assets/sidebar.css?v='.ASP_CURR_VER; ?>" />
<div id='wpdreams' class='asp-be wpdreams asp_performance wrap<?php echo isset($_COOKIE['asp-accessibility']) ? ' wd-accessible' : ''; ?>'>
	<?php do_action('asp_admin_notices'); ?>

	<!-- This forces custom Admin Notices location -->
	<div style="display:none;"><h2 style="display: none;"></h2></div>
	<!-- This forces custom Admin Notices location -->

	<?php if ( wd_asp()->updates->needsUpdate() ) { wd_asp()->updates->printUpdateMessage(); } ?>

    <div class="wpdreams-box" style="float:left;">
        <?php ob_start(); ?>
        <div class="item">
            <?php $o = new wpdreamsYesNo("enabled", __('Enable performance tracking?', 'ajax-search-pro'),
                $perf_options['enabled']
            ); ?>
        </div>
        <?php $_r = ob_get_clean(); ?>

        <?php
        $updated = false;
        if (
            isset($_POST['asp_performance'], $_POST['asp_performance_nonce']) && 
            wp_verify_nonce( $_POST['asp_performance_nonce'], 'asp_performance_nonce' )
        ) {
            $values = array(
                "enabled" => $_POST['enabled']
            );
            update_option('asp_performance', $values);
            asp_parse_options();
            $updated = true;
        }
        if (
            isset($_POST['asp_perf_clear'], $_POST['asp_performance_clear_nonce']) && 
            wp_verify_nonce( $_POST['asp_performance_clear_nonce'], 'asp_performance_clear_nonce' )
        ) {
            $pstats = new Performance('asp_performance_stats');
            $pstats->reset();
        }
        ?>

        <div class='wpdreams-slider'>
            
            <?php if (ASP_DEMO): ?>
                <p class="infoMsg">DEMO MODE ENABLED - Please note, that these options are read-only</p>
            <?php endif; ?>

            <form name='asp_performance_settings' class="asp_performance_settings" method='post'>
                <?php if($updated): ?><div class='successMsg'><?php echo __('Performance options successfuly updated!', 'ajax-search-pro'); ?></div><?php endif; ?>
                <fieldset>
                    <legend><?php echo __('Performance tracking options', 'ajax-search-pro'); ?></legend>
                    <?php print $_r; ?>
                    <input type='hidden' name='asp_performance' value='1' />
                    <input type="hidden" name="asp_performance_nonce"
                           value="<?php echo wp_create_nonce( 'asp_performance_nonce' ); ?>">

                </fieldset>
            </form>
            <form name='asp_performance_settings_clear' class="asp_performance_settings_clear" method='post'>
                <?php if (is_array($asp_performance)): ?>
                    <fieldset>
                        <legend><?php echo __('Performance statistics', 'ajax-search-pro'); ?></legend>
                        <ul>
                            <li><?php echo __('Search queries tracked:', 'ajax-search-pro'); ?> <strong><?php echo $asp_performance['run_count']; ?></strong></li>
                            <li><?php echo __('Average request runtime:', 'ajax-search-pro'); ?> <strong><?php echo number_format($asp_performance['average_runtime'], 3, '.', ''); ?> s</strong></li>
                            <li><?php echo __('Average request peak memory usage:', 'ajax-search-pro'); ?> <strong><?php echo wpd_mem_convert($asp_performance['average_memory']); ?></strong></li>
                            <li><?php echo __('Last request runtime:', 'ajax-search-pro'); ?> <strong><?php echo number_format($asp_performance['last_runtime'], 3, '.', ''); ?> s</strong></li>
                            <li><?php echo __('Last request peak memory usage:', 'ajax-search-pro'); ?> <strong><?php echo wpd_mem_convert($asp_performance['last_memory']); ?></strong></li>
                        </ul>
                        <div class="item">
                            <label for="perf_asp_submit"><?php echo __('Clear performace statistics?', 'ajax-search-pro'); ?></label>
                            <input type="hidden" name="asp_performance_clear_nonce"
                                   value="<?php echo wp_create_nonce( 'asp_performance_clear_nonce' ); ?>">
                            <input type='submit' name="asp_perf_clear" id="asp_perf_clear" class='submit' value='<?php echo esc_attr__('Clear', 'ajax-search-pro'); ?>'/>
                        </div>
                    </fieldset>
                <?php endif; ?>
            </form>
            <fieldset>
                <legend><?php echo esc_attr__('Performance quick FAQ', 'ajax-search-pro'); ?></legend>
                <dl>
                    <dt><?php echo esc_attr__('How come the performance tracker shows low runtime, yet the search results appear slower?', 'ajax-search-pro'); ?></dt>
                    <dd>
                        <?php echo __('The performance tracker only tracks the length of the search function.', 'ajax-search-pro'); ?><br>
                        <?php echo __('Before that WordPress initializes, loads all of the plugins, executes all the tasks needed and
                        then executes the search function. Depending on the number of plugins, server speed, this can take
                        some time. In this case not the search is slow, but actually the WordPress initialization.', 'ajax-search-pro'); ?>
                    </dd>
                    <dt><?php echo esc_attr__('How can I make the ajax request run faster?', 'ajax-search-pro'); ?></dt>
                    <dd>
                        <?php echo __('Using less plugins is usually the best solution. Lots of plugins will decrease the WordPress
                        performance - thus increasing the response time of ajax requests.', 'ajax-search-pro'); ?>
                        <?php echo sprintf( __('Running a <a href="%s">performance profiler plugin</a> might give you an insight on which plugins take the most
                        resources during loading - but it might be different for ajax requests.', 'ajax-search-pro'), 'https://wordpress.org/plugins/p3-profiler/' ); ?>
                    </dd>
                    <dt><?php echo esc_attr__('Can\'t the plugin bypass the WordPress initialization and just run the search query?', 'ajax-search-pro'); ?></dt>
                    <dd>
                        <?php echo __('Partially, yes. If you go to the <strong>Compatibility settings</strong> and enable the <strong>Use custom ajax handler</strong>
                        option, the search will use a custom handler, which bypasses some of the loading process.', 'ajax-search-pro'); ?><br>
                        <?php echo __('This might not work with some plugins or themes.', 'ajax-search-pro'); ?>
                    </dd>
                </dl>
            </fieldset>
        </div>
    </div>

    <?php include(ASP_PATH . "backend/sidebar.php"); ?>

    <div class="clear"></div>
    <script>
        jQuery(function ($) {
            $("form[name='asp_performance_settings'] .wpdreamsYesNoInner").on("click", function () {
                setTimeout(function () {
                    $("form[name='asp_performance_settings']").get(0).submit();
                }, 500);
            });
            $("form[name='asp_performance_settings_clear']").on("submit", function () {
                if (!confirm('<?php echo __('Do you want to clear the performance statistics?', 'ajax-search-pro'); ?>')) {
                     return false;
                }
            });
        });
    </script>
</div>