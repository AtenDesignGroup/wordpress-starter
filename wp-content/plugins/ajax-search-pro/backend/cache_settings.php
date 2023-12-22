<?php
/* Prevent direct access */

use WPDRMS\ASP\Cache\TextCache;

defined( 'ABSPATH' ) or die( "You can't access this file directly." );

$cache_options = wd_asp()->o['asp_caching'];
if (ASP_DEMO) $_POST = null;
?>
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'settings/assets/sidebar.css?v='.ASP_CURR_VER; ?>" />
<div id='wpdreams' class='asp-be wpdreams wrap<?php echo isset($_COOKIE['asp-accessibility']) ? ' wd-accessible' : ''; ?>'>
	<?php do_action('asp_admin_notices'); ?>

	<!-- This forces custom Admin Notices location -->
	<div style="display:none;"><h2 style="display: none;"></h2></div>
	<!-- This forces custom Admin Notices location -->

	<?php if ( wd_asp()->updates->needsUpdate() ) { wd_asp()->updates->printUpdateMessage(); } ?>

	<div class="wpdreams-box" style="float:left;">
		<?php ob_start(); ?>
		<div class="item item-flex-nogrow item-flex-wrap">
			<?php
            $o = new wpdreamsYesNo( "caching", __('Caching activated', 'ajax-search-pro'), $cache_options["caching"]);
            $o = new wpdreamsCustomSelect('caching_method', __('Caching method', 'ajax-search-pro'),
                array(
                    'selects' => array(
                        //array('option' => __('Super File Cache', 'ajax-search-pro'), 'value' => 'sc_file'),
                        array('option' => __('File', 'ajax-search-pro'), 'value' => 'file'),
                        array('option' => __('Database', 'ajax-search-pro'), 'value' => 'db')
                    ),
                    'value' => $cache_options["caching_method"]
                ));
			?>
			<p class="descMsg">
                <a target="_blank" href="https://documentation.ajaxsearchpro.com/performance-tuning/cache">
                    <?php echo __('Documentation', 'ajax-search-pro'); ?>
                </a><br>
                <?php echo __('Not recommended, unless you have many search queries per minute.', 'ajax-search-pro'); ?>
                <?php echo __('This will enable search results to be cached into files in the cache directory/options database to bypass the search database query. Useful if you experience many repetitive queries.', 'ajax-search-pro'); ?>
            </p>
		</div>
		<div class="item">
			<p class='infoMsg'>
                <?php echo __('Turn this OFF if you are experiencing performance issues.', 'ajax-search-pro'); ?>
            </p>
			<?php $o = new wpdreamsYesNo( "image_cropping", __('Crop images for caching?', 'ajax-search-pro'), $cache_options["image_cropping"] ); ?>
			<p class="descMsg">
                <?php echo __('This disables the thumbnail generator, and the full sized images are used as cover. Not much difference visually, but saves a lot of CPU.', 'ajax-search-pro'); ?>
            </p>
		</div>
		<div class="item">
			<?php $o = new wpdreamsText( "cachinginterval", __('Caching interval (in minutes, default 43200, aka. 30 days)', 'ajax-search-pro'),
                $cache_options["cachinginterval"] ); ?>
		</div>
		<div class="item">
		<input type="hidden" name="asp_caching_nonce" value="<?php echo wp_create_nonce( 'asp_caching_nonce' ); ?>">
			<input type='submit' class='submit' value='<?php esc_attr_e('Save options', 'ajax-search-pro'); ?>'/>
		</div>
		<?php $_r = ob_get_clean(); ?>


		<?php
		$updated = false;
		if ( 
			isset( $_POST['asp_caching'], $_POST['asp_caching_nonce'] ) && 
			wp_verify_nonce( $_POST['asp_caching_nonce'], 'asp_caching_nonce' )
		) {
			$values = array(
				"caching"         => $_POST['caching'],
				"caching_method"  => $_POST['caching_method'],
				"image_cropping"  => $_POST['image_cropping'],
				"cachinginterval" => $_POST['cachinginterval']
			);
			update_option( 'asp_caching', $values );
            asp_parse_options();
			$updated = true;
            wd_asp()->css_manager->generator->generate();
			TextCache::generateSCFiles();
		}
		?>


		<div class='wpdreams-slider'>
			<?php if (ASP_DEMO): ?>
				<p class="infoMsg">DEMO MODE ENABLED - Please note, that these options are read-only</p>
			<?php endif; ?>

			<form name='asp_caching' method='post'>
				<?php if ( $updated ): ?>
					<div class='successMsg'>
                        <?php echo __('Search caching settings successfuly updated!', 'ajax-search-pro'); ?>
                    </div>
                <?php endif; ?>
				<fieldset>
					<legend>
                        <?php echo __('Caching Options', 'ajax-search-pro'); ?>
                    </legend>
					<?php print $_r; ?>
					<input type='hidden' name='asp_caching' value='1'/>
				</fieldset>
			</form>


			<fieldset>
				<legend><?php echo __('Clear Cache'); ?></legend>
				<div class="item">
					<p class='infoMsg'><?php echo __('Will clear all the images and precached search phrases.', 'ajax-search-pro'); ?></p>
					<input type="hidden" id="asp_delete_cache_request_nonce" value="<?php echo wp_create_nonce( 'asp_delete_cache_request_nonce' ); ?>">
					<input type='submit' class="red" name='Clear Cache' id='clearcache' value='<?php echo esc_attr__('Clear the cache!', 'ajax-search-pro'); ?>'>
				</div>
			</fieldset>
		</div>

		<script>
			jQuery(function ($) {
			    function format() {
                    var a, b, c;
                    a = arguments[0];
                    b = [];
                    for(c = 1; c < arguments.length; c++){
                        b.push(arguments[c]);
                    }
                    for (c in b) {
                        a = a.replace(/%[a-z]/, b[c]);
                    }
                    return a;
                }
				$('#clearcache').on('click', function () {
					var r = confirm('<?php echo esc_html__('Do you really want to clear the cache?', 'ajax-search-pro'); ?>');
					if (r !== true) return;
					var button = $(this),
						nonce = $('#asp_delete_cache_request_nonce').val();
					var data = {
						action: 'ajaxsearchpro_deletecache',
						'asp_delete_cache_request_nonce': nonce
					};
					button.attr("disabled", true);
					var oldVal = button.attr("value");
					button.attr("value", "Loading...");
					button.addClass('blink');
					$.post(ASP.ajaxurl, data, function (response) {
						var currentdate = new Date();
						var datetime = currentdate.getDate() + "/"
							+ (currentdate.getMonth() + 1) + "/"
							+ currentdate.getFullYear() + " @ "
							+ currentdate.getHours() + ":"
							+ currentdate.getMinutes() + ":"
							+ currentdate.getSeconds();
						button.attr("disabled", false);
						button.removeClass('blink');
						button.attr("value", oldVal);
						var cleared = '<?php echo esc_html__('%s record(s) deleted at %s', 'ajax-search-pro'); ?>';
						cleared = format(cleared, response, datetime);
						button.parent().parent().append('<div class="successMsg">Cache succesfully cleared! ' + cleared + '</div>');
					}, "json");
				});

			    $('.item input[name="caching"]').on('change', function(e){
			        var $m = $(this).closest('.item').find('select[name=caching_method]');
			        if ( $(this).val() == 1 ) {
                        $m.removeClass('disabled');
                    } else {
                        $m.addClass('disabled');
                    }
                });
			    $('.item input[name="caching"]').trigger('change');
			});
		</script>

	</div>

    <?php include(ASP_PATH . "backend/sidebar.php"); ?>

	<div class="clear"></div>
</div>