<?php
$sugg_select_arr = array(
	'google' => __('Google keywords', 'ajax-search-pro'),
    'google_places' => __('Google Places API', 'ajax-search-pro'),
	'statistics' => __('Statistics database', 'ajax-search-pro'),
	'tags' => __('Post tags', 'ajax-search-pro'),
	'xtax_category' => __('Post categories', 'ajax-search-pro'),
	'titles' => __('Post titles', 'ajax-search-pro')
);
$taxonomies_arr = get_taxonomies(array('public' => true, '_builtin' => false), 'names', 'and');
foreach($taxonomies_arr as $taxx) {
	$sugg_select_arr['xtax_'.$taxx] = '[taxonomy] ' . $taxx;
}
?>
<ul id="subtabs"  class='tabs'>
    <li><a tabid="501" class='subtheme current'><?php echo __('Autocomplete', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="502" class='subtheme'><?php echo __('Predictive Results & Keyword suggestions', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="503" class='subtheme'><?php echo __('Suggested search keywords', 'ajax-search-pro'); ?></a></li>
</ul>
<div class='tabscontent'>
    <div tabid="501">
        <fieldset>
            <legend>
                <?php echo __('Autocomplete', 'ajax-search-pro'); ?>
                <span class="asp_legend_docs">
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/autocomplete-and-keyword-suggestions/autocomplete"><span class="fa fa-book"></span>
                        <?php echo __('Documentation', 'ajax-search-pro'); ?>
                    </a>
                </span>
            </legend>
            <p class="infoMsg">
                <?php echo __('Autocomplete feature will try to help the user finish what is being typed into the search box.', 'ajax-search-pro'); ?>
            </p>
            <?php include(ASP_PATH."backend/tabs/instance/suggest/autocomplete.php"); ?>
        </fieldset>
    </div>
    <div tabid="502">
        <fieldset>
            <legend>
                <?php echo __('Predictive Results & Keyword suggestions', 'ajax-search-pro'); ?>
                <span class="asp_legend_docs">
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/autocomplete-and-keyword-suggestions/keyword-suggestions"><span class="fa fa-book"></span>
                        <?php echo __('Documentation', 'ajax-search-pro'); ?>
                    </a>
                </span>
            </legend>
            <?php include(ASP_PATH."backend/tabs/instance/suggest/keywords.php"); ?>
        </fieldset>
    </div>
    <div tabid="503">
        <fieldset>
            <legend>
                <?php echo __('Suggested search keywords', 'ajax-search-pro'); ?>
                <span class="asp_legend_docs">
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/autocomplete-and-keyword-suggestions/try-these-suggested-phrases"><span class="fa fa-book"></span>
                        <?php echo __('Documentation', 'ajax-search-pro'); ?>
                    </a>
                </span>
            </legend>
            <?php include(ASP_PATH."backend/tabs/instance/suggest/suggestions.php"); ?>
        </fieldset>
    </div>
</div>
<div class="item">
    <input name="reset_<?php echo $search['id']; ?>" class="asp_submit asp_submit_transparent asp_submit_reset" type="button" value="<?php echo esc_attr__('Restore defaults', 'ajax-search-pro'); ?>">
    <input name="submit_<?php echo $search['id']; ?>" type="submit" value="<?php echo esc_attr__('Save all tabs!', 'ajax-search-pro'); ?>" />
</div>