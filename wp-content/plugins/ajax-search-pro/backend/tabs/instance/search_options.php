<ul id="subtabs"  class='tabs'>
	<li><a tabid="201" class='subtheme asp_be_live_subtab'><?php echo __('Magnifier and Return Actions', 'ajax-search-pro'); ?></a></li>
	<li><a tabid="202" class='subtheme asp_be_live_subtab'><?php echo __('Keyword Logic and Matching', 'ajax-search-pro'); ?></a></li>
	<li><a tabid="203" class='subtheme asp_be_live_subtab'><?php echo __('Live Search Triggers', 'ajax-search-pro'); ?></a></li>
	<li><a tabid="204" class='subtheme asp_be_live_subtab'><?php echo __('Search | Elementor | Archive | Shop page Live Results', 'ajax-search-pro'); ?></a></li>
	<li><a tabid="205" class='subtheme asp_be_live_subtab'><?php echo __('Mobile Device Behavior', 'ajax-search-pro'); ?></a></li>
</ul>
<div class='tabscontent' id="asp_be_live_subtabs">
	<div tabid="201" class="asp_be_rel_subtab">
		<?php include(ASP_PATH."backend/tabs/instance/search_options/magnifier_click.php"); ?>
	</div>
	<div tabid="202" class="asp_be_rel_subtab">
		<?php include(ASP_PATH."backend/tabs/instance/search_options/keyword_logic.php"); ?>
	</div>
	<div tabid="203" class="asp_be_rel_subtab">
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/relevance-options"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>

		<?php include(ASP_PATH."backend/tabs/instance/search_options/triggers.php"); ?>
	</div>
	<div tabid="204" class="asp_be_rel_subtab">
		<?php include(ASP_PATH."backend/tabs/instance/search_options/archive_pages.php"); ?>
	</div>
	<div tabid="205" class="asp_be_rel_subtab">
		<?php include(ASP_PATH."backend/tabs/instance/search_options/mobile_behavior.php"); ?>
	</div>
</div>
<div class="item">
	<input name="reset_<?php echo $search['id']; ?>" class="asp_submit asp_submit_transparent asp_submit_reset" type="button" value="<?php echo esc_attr__('estore defaults', 'ajax-search-pro'); ?>">
	<input name="submit_<?php echo $search['id']; ?>" type="submit" value="<?php echo esc_attr__('Save all tabs!', 'ajax-search-pro'); ?>" />
</div>