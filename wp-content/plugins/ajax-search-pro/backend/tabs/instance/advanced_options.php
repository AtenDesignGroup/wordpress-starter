<ul id="subtabs"  class='tabs'>
	<li><a tabid="701" class='subtheme current'><?php echo __('Content & Fields', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="706" class='subtheme'><?php echo __('Exclude/Include Results', 'ajax-search-pro'); ?></a></li>
	<li><a tabid="702" class='subtheme'><?php echo __('Grouping', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="703" class='subtheme'><?php echo __('Animations, Visual & Others', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="704" class='subtheme'><?php echo __('Keyword exceptions', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="705" class='subtheme'><?php echo __('Accessibility', 'ajax-search-pro'); ?></a></li>
</ul>
<div class='tabscontent'>
	<div tabid="701">
        <?php include(ASP_PATH."backend/tabs/instance/advanced/content.php"); ?>
	</div>
    <div tabid="706">
        <fieldset>
            <legend>
                <?php echo __('Exclude/Include results', 'ajax-search-pro'); ?>
                <span class="asp_legend_docs">
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/advanced-options/excluding-and-including-results"><span class="fa fa-book"></span>
                        <?php echo __('Documentation', 'ajax-search-pro'); ?>
                    </a>
                </span>
            </legend>
            <?php include(ASP_PATH."backend/tabs/instance/advanced/exclude_results.php"); ?>
        </fieldset>
    </div>
	<div tabid="702">
		<fieldset>
			<legend>
                <?php echo __('Grouping', 'ajax-search-pro'); ?>
                <span class="asp_legend_docs">
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/advanced-options/grouping-results"><span class="fa fa-book"></span>
                        <?php echo __('Documentation', 'ajax-search-pro'); ?>
                    </a>
                </span>
            </legend>
			<?php include(ASP_PATH."backend/tabs/instance/advanced/grouping.php"); ?>
		</fieldset>
	</div>
    <div tabid="703">
        <fieldset>
            <legend><?php echo __('Animations', 'ajax-search-pro'); ?></legend>
            <?php include(ASP_PATH."backend/tabs/instance/advanced/animations.php"); ?>
        </fieldset>
    </div>
    <div tabid="704">
        <fieldset>
            <legend><?php echo __('Keyword exceptions', 'ajax-search-pro'); ?></legend>
            <?php include(ASP_PATH."backend/tabs/instance/advanced/kw_exceptions.php"); ?>
        </fieldset>
    </div>
	<div tabid="705">
		<fieldset>
			<legend><?php echo __('Accessibility', 'ajax-search-pro'); ?></legend>
			<?php include(ASP_PATH."backend/tabs/instance/advanced/accessibility.php"); ?>
		</fieldset>
	</div>
</div>
<div class="item">
    <input name="reset_<?php echo $search['id']; ?>" class="asp_submit asp_submit_transparent asp_submit_reset" type="button" value="<?php echo esc_attr__('Restore defaults', 'ajax-search-pro'); ?>">
    <input type="hidden" name='asp_submit' value=1 />
    <input name="submit_<?php echo $search['id']; ?>" type="submit" value="<?php echo esc_attr__('Save this search!', 'ajax-search-pro'); ?>" />
</div>