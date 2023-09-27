<?php
$_red_opts = array(
    array("option" => __('Trigger live search', 'ajax-search-pro'), "value" => "ajax_search"),
    array("option" => __('Redirect to: First matching result', 'ajax-search-pro'), "value" => "first_result"),
    array("option" => __('Redirect to: Results page', 'ajax-search-pro'), "value" => "results_page"),
    array("option" => __('Redirect to: Woocommerce results page', 'ajax-search-pro'), "value" => "woo_results_page"),
    array("option" => __('Redirect to: Elementor post widget page', 'ajax-search-pro'), "value" => "elementor_page"),
    array("option" => __('Redirect to: Custom URL', 'ajax-search-pro'), "value" => "custom_url"),
    array("option" => __('Do nothing', 'ajax-search-pro'), "value" => "nothing")
);
if ( !class_exists("WooCommerce") ) unset($_red_opts[3]);
?>
<ul id="subtabs"  class='tabs'>
    <li><a tabid="101" class='subtheme current'><?php echo __('Post Type Search', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="105" class='subtheme'><?php echo __('Taxonomy Terms Search', 'ajax-search-pro'); ?></a></li>
	<li><a tabid="109" class='subtheme'><?php echo __('Media Files Search', 'ajax-search-pro'); ?></a></li>
	<li><a tabid="108" class='subtheme'><?php echo __('User Search', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="103" class='subtheme'><?php echo __('Image Options', 'ajax-search-pro'); ?></a></li>
    <?php if ( function_exists('bp_core_get_user_domain') ): ?>
    <li><a tabid="104" class='subtheme'><?php echo __('BuddyPress', 'ajax-search-pro'); ?></a></li>
    <?php endif; ?>
    <?php if ( class_exists('PeepSoGroup') ): ?>
    <li><a tabid="112" class='subtheme'><?php echo __('PeepSo', 'ajax-search-pro'); ?></a></li>
    <?php endif; ?>
    <li><a tabid="111" class='subtheme'><?php echo __('Limits', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="107" class='subtheme'><?php echo __('Ordering', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="113" class='subtheme'><?php echo __('Grouping & Other', 'ajax-search-pro'); ?></a></li>
	<li><a tabid="114" class='subtheme'><?php echo __('Relevance Options', 'ajax-search-pro'); ?></a></li>
</ul>
<div class='tabscontent'>
    <div tabid="101">
        <fieldset>
            <legend>
                <?php echo __('Post Type Search', 'ajax-search-pro'); ?>
                <span class="asp_legend_docs">
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/general-settings/search-in-posts-and-pages"><span class="fa fa-book"></span>
                        <?php echo __('Post Types', 'ajax-search-pro'); ?>
                    </a>
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/general-settings/search-title-content-excerpt"><span class="fa fa-book"></span>
                        <?php echo __('Fields', 'ajax-search-pro'); ?>
                    </a>
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/general-settings/search-in-terms-categories-tags..."><span class="fa fa-book"></span>
                        <?php echo __('Categories', 'ajax-search-pro'); ?>
                    </a>
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/general-settings/search-in-custom-fields"><span class="fa fa-book"></span>
                        <?php echo __('Custom Fields', 'ajax-search-pro'); ?>
                    </a>
                </span>
            </legend>
            <?php include( ASP_PATH . 'backend/tabs/instance/general/sources.php'); ?>
        </fieldset>
    </div>
    <div tabid="103">
        <fieldset>
			<legend>
				<?php echo __('Image Options', 'ajax-search-pro'); ?>
				<span class="asp_legend_docs">
					<a target="_blank" href="https://documentation.ajaxsearchpro.com/general-settings/image-options"><span class="fa fa-book"></span>
						<?php echo __('Documentation', 'ajax-search-pro'); ?>
					</a>
				</span>
			</legend>
            <?php include(ASP_PATH."backend/tabs/instance/general/image_options.php"); ?>
        </fieldset>
    </div>
    <div tabid="104">
        <fieldset>
            <legend><?php echo __('BuddyPress Options', 'ajax-search-pro'); ?></legend>
            <?php include(ASP_PATH."backend/tabs/instance/general/buddypress_options.php"); ?>
        </fieldset>
    </div>
    <div tabid="108">
        <fieldset>
            <legend>
                <?php echo __('User Search', 'ajax-search-pro'); ?>
                <span class="asp_legend_docs">
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/general-settings/search-in-users"><span class="fa fa-book"></span>
                        <?php echo __('Documentation', 'ajax-search-pro'); ?>
                    </a>
                </span>
            </legend>
            <?php include(ASP_PATH."backend/tabs/instance/general/user_search.php"); ?>
        </fieldset>
    </div>
    <div tabid="105">
        <fieldset>
            <legend>
                <?php echo __('Searching Taxonomy Terms and returning them as Results', 'ajax-search-pro'); ?>
                <span class="asp_legend_docs">
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/general-settings/categories-and-terms-as-results"><span class="fa fa-book"></span>
                        <?php echo __('Taxonomy Terms as results', 'ajax-search-pro'); ?>
                    </a>
                </span>
            </legend>
            <?php include(ASP_PATH."backend/tabs/instance/general/sources2.php"); ?>
        </fieldset>
    </div>
    <div tabid="111">
        <fieldset>
            <legend><?php echo __('Limits', 'ajax-search-pro'); ?></legend>
            <?php include(ASP_PATH."backend/tabs/instance/general/limits.php"); ?>
        </fieldset>
    </div>
    <?php if ( class_exists('PeepSoGroup') ): ?>
    <div tabid="112">
        <?php include(ASP_PATH."backend/tabs/instance/general/peepso.php"); ?>
    </div>
    <?php endif; ?>
    <div tabid="107">
        <?php include(ASP_PATH."backend/tabs/instance/general/ordering.php"); ?>
    </div>
    <div tabid="113">
        <?php include(ASP_PATH."backend/tabs/instance/general/grouping.php"); ?>
    </div>
	<div tabid="109">
		<fieldset>
			<legend>
                <?php echo __('Media File Search', 'ajax-search-pro'); ?>
                <span class="asp_legend_docs">
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/general-settings/search-in-attachments"><span class="fa fa-book"></span>
                        <?php echo __('Search in Media Files', 'ajax-search-pro'); ?>
                    </a>
                </span>
            </legend>
			<?php include(ASP_PATH."backend/tabs/instance/general/attachment_results.php"); ?>
		</fieldset>
	</div>
	<div tabid="114">
		<?php include(ASP_PATH."backend/tabs/instance/general/relevance.php"); ?>
	</div>
</div>
<div class="item">
    <input name="reset_<?php echo $search['id']; ?>" class="asp_submit asp_submit_transparent asp_submit_reset" type="button" value="<?php echo esc_attr__('Restore defaults', 'ajax-search-pro'); ?>">
    <input name="submit_<?php echo $search['id']; ?>" type="submit" value="<?php echo esc_attr__('Save all tabs!', 'ajax-search-pro'); ?>" />
</div>