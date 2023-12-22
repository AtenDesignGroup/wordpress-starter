<ul id="subtabs"  class='tabs'>
    <li><a tabid="301" class='subtheme current'><?php echo __('General', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="310" class='subtheme'><?php echo __('Generic filters', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="311" class='subtheme'><?php echo __('Content type', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="308" class='subtheme'><?php echo __('Post Types', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="309" class='subtheme'><?php echo __('Date filters', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="307" class='subtheme'><?php echo __('Categories & Taxonomy Terms', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="306" class='subtheme'><?php echo __('Post Tags', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="303" class='subtheme'><?php echo __('Custom Fields', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="312" class='subtheme'><?php echo __('Search Button', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="313" class='subtheme'><?php echo __('Reset Button', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="304" class='subtheme'><?php echo __('Advanced', 'ajax-search-pro'); ?></a></li>
</ul>
<div class='tabscontent'>
    <div tabid="301">

            <?php include(ASP_PATH."backend/tabs/instance/frontend/general.php"); ?>

    </div>
    <div tabid="310">

        <?php include(ASP_PATH."backend/tabs/instance/frontend/generic.php"); ?>

    </div>
    <div tabid="311">

        <?php include(ASP_PATH."backend/tabs/instance/frontend/content_type.php"); ?>

    </div>
    <div tabid="308">

        <?php include(ASP_PATH."backend/tabs/instance/frontend/post_and_cpt.php"); ?>

    </div>
    <div tabid="309">

        <?php include(ASP_PATH."backend/tabs/instance/frontend/date.php"); ?>

    </div>
    <div tabid="303">

            <?php include(ASP_PATH."backend/tabs/instance/frontend/custom_fields.php"); ?>

    </div>
    <div tabid="304">

            <?php include(ASP_PATH."backend/tabs/instance/frontend/advanced.php"); ?>

    </div>
    <div tabid="306">

        <?php include(ASP_PATH."backend/tabs/instance/frontend/post_tags.php"); ?>

    </div>
    <div tabid="312">

        <?php include(ASP_PATH."backend/tabs/instance/frontend/search_button.php"); ?>

    </div>
    <div tabid="313">

        <?php include(ASP_PATH."backend/tabs/instance/frontend/reset_button.php"); ?>

    </div>
    <div tabid="307">

        <?php include(ASP_PATH."backend/tabs/instance/frontend/taxonomy_terms.php"); ?>

    </div>
</div>
<div class="item">
    <input name="reset_<?php echo $search['id']; ?>" class="asp_submit asp_submit_transparent asp_submit_reset" type="button" value="<?php echo esc_attr__('Restore defaults', 'ajax-search-pro'); ?>">
    <input name="submit_<?php echo $search['id']; ?>" type="submit" value="<?php echo esc_attr__('Save all tabs!', 'ajax-search-pro'); ?>" />
</div>