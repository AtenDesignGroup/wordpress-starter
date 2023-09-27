<ul id="subtabs"  class='tabs'>
    <li><a tabid="801" class='subtheme current asp_be_rel_subtab asp_be_rel_regular'><?php echo __('Regular Engine', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="802" class='subtheme asp_be_rel_subtab asp_be_rel_index'><?php echo __('Index Table Engine', 'ajax-search-pro'); ?></a></li>
</ul>
<div class='tabscontent' id="asp_be_rel_subtabs">
    <p class='infoMsg'>
        <?php echo __('Every result gets a relevance value based on the weight numbers set below. The weight is the measure of importance.', 'ajax-search-pro'); ?><br/>
        <?php echo __('If you wish to change the the results basic ordering, then you can do it under the <a href="#107">General Options -> Ordering</a> panel.', 'ajax-search-pro'); ?>
    </p>

    <div tabid="801" class="asp_be_rel_subtab asp_be_rel_regular">
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/relevance-options"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>

        <?php include(ASP_PATH."backend/tabs/instance/relevance/regular.php"); ?>

    </div>
    <div tabid="802" class="asp_be_rel_subtab asp_be_rel_index">

        <?php include(ASP_PATH."backend/tabs/instance/relevance/index_table.php"); ?>

    </div>
</div>
<div class="item">
    <input name="reset_<?php echo $search['id']; ?>" class="asp_submit asp_submit_transparent asp_submit_reset" type="button" value="<?php echo esc_attr__('estore defaults', 'ajax-search-pro'); ?>">
    <input name="submit_<?php echo $search['id']; ?>" type="submit" value="<?php echo esc_attr__('Save all tabs!', 'ajax-search-pro'); ?>" />
</div>

