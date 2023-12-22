<ul id="subtabs"  class='tabs'>
    <li><a tabid="401" class='subtheme current'><?php echo __('Search box layout', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="402" class='subtheme'><?php echo __('Results layout & Fields', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="403" class='subtheme'><?php echo __('Results Behaviour', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="405" class='subtheme'><?php echo __('Highlighter and Load more feature', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="404" class='subtheme'><?php echo __('Compact box layout', 'ajax-search-pro'); ?></a></li>
</ul>
<div class='tabscontent'>
    <div tabid="401">
        <?php include(ASP_PATH."backend/tabs/instance/layout/search_box_layout.php"); ?>
    </div>
    <div tabid="402">
        <?php include(ASP_PATH."backend/tabs/instance/layout/results_layout.php"); ?>
    </div>
    <div tabid="403">
        <fieldset>
            <legend>
                <?php echo __('Results Behaviour', 'ajax-search-pro'); ?>
                <span class="asp_legend_docs">
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/layout-settings/results-behavior"><span class="fa fa-book"></span>
                        <?php echo __('Documentation', 'ajax-search-pro'); ?>
                    </a>
                </span>
            </legend>
            <?php include(ASP_PATH."backend/tabs/instance/layout/results_behaviour.php"); ?>
        </fieldset>
    </div>
    <div tabid="404">
        <fieldset>
            <legend>
                <?php echo __('Compact Box layout', 'ajax-search-pro'); ?>
                <span class="asp_legend_docs">
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/layout-settings/compact-search-box-layout"><span class="fa fa-book"></span>
                        <?php echo __('Documentation', 'ajax-search-pro'); ?>
                    </a>
                </span>
            </legend>
            <?php include(ASP_PATH."backend/tabs/instance/layout/compact_box_layout.php"); ?>
        </fieldset>
    </div>
    <div tabid="405">
        <?php include(ASP_PATH."backend/tabs/instance/layout/highlight_loadmore.php"); ?>
    </div>
</div>
<div class="item">
    <input name="reset_<?php echo $search['id']; ?>" class="asp_submit asp_submit_transparent asp_submit_reset" type="button" value="<?php echo esc_attr__('Restore defaults', 'ajax-search-pro'); ?>">
    <input name="submit_<?php echo $search['id']; ?>" type="submit" value="<?php echo esc_attr__('Save all tabs!', 'ajax-search-pro'); ?>" />
</div>