<?php
if ( isset($_POST['priority_groups']) ) {
    wd_asp()->priority_groups->setEncoded($_POST['priority_groups'], true);
}
//wd_asp()->priority_groups->debug();
?>

<?php if (ASP_DEMO): ?>
    <p class="infoMsg">DEMO MODE ENABLED - Please note, that these options are read-only</p>
<?php endif; ?>
<p id="pg_no_pg"><?php echo __('There are no priority groups yet. Click the <strong>Add New</strong> button to create one!', 'ajax-search-pro'); ?></p>
<div id="pg_container">
    <div class="pg_rule_group" data-groupid="-1">
        <img title="<?php echo esc_attr__('Click on this icon for rule group settings!', 'ajax-search-pro'); ?>"
             class="pg_rg_edit"
             src="<?php echo plugins_url('/backend/settings/assets/icons/settings.png', ASP_FILE) ?>"/>
        <img title="<?php echo esc_attr__('Click here if you want to delete this rule group!', 'ajax-search-pro'); ?>"
             class="pg_rg_delete"
             src="<?php echo plugins_url('/backend/settings/assets/icons/delete.png', ASP_FILE) ?>"/>
        <span class="pg_name"><?php echo __('Rule Group #1', 'ajax-search-pro'); ?></span>
        <span class="pg_info"></span>
    </div>
</div>
<p id="pg_information">
    <?php echo sprintf( __('If you don\'t know what priority groups are, check the <a href="%s" target="_blank">Priority</a> and the
    <a href="%s" target="_blank">Priority groups</a> documentations first.', 'ajax-search-pro'),
        'https://wp-dreams.com/go/?to=asp-doc-result-priority',
    'https://wp-dreams.com/go/?to=asp-doc-result-priority-group'
    ); ?>
</p>
<p class="noticeMsg">
    <?php echo __('PLEASE NOTE: Always create <strong>as few rules as possible</strong>, as they may affect the search performance negatively.', 'ajax-search-pro'); ?>
</p>
<form method="POST">
    <p style="text-align: right">
        <input type="button" id="pg_remove_all" value="<?php echo esc_attr__('Remove all', 'ajax-search-pro'); ?>" style="float: left;" class="submit wd_button_opaque">
        <input type="button" id="pg_add_new" value="<?php echo esc_attr__('Add new!', 'ajax-search-pro'); ?>" class="submit wd_button_green">
        <input type="button" id="pg_save" value="<?php echo esc_attr__('Save Groups', 'ajax-search-pro'); ?>" class="submit wd_button wd_button_blue">
    </p>
    <input name="priority_groups" id="priority_groups" type="hidden" value="<?php echo wd_asp()->priority_groups->getForDisplayEncoded(); ?>"/>
</form>

<!-- SAMPLE ITEM STARTS HERE -->
<div class="asp_pg_item asp_pg_item_sample hiddend" id="asp_pg_editor">
    <form name="pg_item_form">
        <div class="pg_rule_info">
            <div class="pg_line">
                <label for="ruleset_name">
                    <?php echo __('Ruleset name', 'ajax-search-pro'); ?>
                </label>
                <input name="ruleset_name" id="ruleset_name" value="Ruleset">
            </div>
            <div class="pg_line">
                <label for="pg_priority">
                    <?php echo __('Set results priority matching the rules to', 'ajax-search-pro'); ?>
                </label>
                <input type="number" name="pg_priority" id="pg_priority" value="100" min="1" max="5000">
            </div>
            <div class="pg_line">
                <label for="pg_instance">
                    <?php echo __('Apply to', 'ajax-search-pro'); ?>
                </label>
                <select name="pg_instance" id="pg_instance">
                    <option value="0"><?php _e('Every search instance', 'ajax-search-pro'); ?></option>
                    <?php foreach( wd_asp()->instances->getWithoutData() as $search_instance ): ?>
                        <option value="<?php echo $search_instance['id']; ?>"><?php echo esc_html($search_instance['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="pg_line">
                <label for="pg_phrase_logic"><?php echo __('Apply on', 'ajax-search-pro'); ?></label>
                <select name="pg_phrase_logic" id="pg_phrase_logic">
                    <option value="disabled"><?php echo __('any search phrase', 'ajax-search-pro'); ?></option>
                    <option value="any"><?php echo __('phrase matching anywhere', 'ajax-search-pro'); ?></option>
                    <option value="exact"><?php echo __('phrase matching exactly', 'ajax-search-pro'); ?></option>
                    <option value="start"><?php echo __('phrase starting with', 'ajax-search-pro'); ?></option>
                    <option value="end"><?php echo __('phrase ending with', 'ajax-search-pro'); ?></option>
                </select>
                <label for="pg_phrase"></label>
                <input type="text" name="pg_phrase" id="pg_phrase" value="" placeholder="<?php echo esc_attr__('Enter search phrase..', 'ajax-search-pro'); ?>">
            </div>
            <div class="pg_line">
                <label for="pg_rule_logic"><?php echo __('Apply if', 'ajax-search-pro'); ?></label>
                <select name="pg_rule_logic" id="pg_rule_logic">
                    <option value="and"><?php echo __('all rules match', 'ajax-search-pro'); ?></option>
                    <option value="or"><?php echo __('any of the rules match', 'ajax-search-pro'); ?></option>
                </select>
            </div>
        </div>
        <div class="pg_rules">
            <label for="pg_add_rule">
                <input type='button' name="pg_add_rule" id="pg_add_rule" value="New rule" class="wd_button wd_button_blue">
            </label>
            <div class="pg_rules_container" id="pg_rules_container">
                <p class="pg_rule" data-id="1">
                    <img title="<?php echo esc_attr__('Click on this icon for rule settings!', 'ajax-search-pro'); ?>"
                         class="pg_edit_rule"
                         src="<?php echo plugins_url('/backend/settings/assets/icons/settings.png', ASP_FILE) ?>"/>
                    <img title="<?php echo esc_attr__('Click here if you want to delete this rule!', 'ajax-search-pro'); ?>"
                         class="pg_delete_rule"
                         src="<?php echo plugins_url('/backend/settings/assets/icons/delete.png', ASP_FILE) ?>"/>
                    <span>Rule #1</span></p>
            </div>
        </div>
        <div class="pg_rule_editor hiddend" id="pg_rule_editor" data-rule-id="1">
            <span class="re_label"><?php echo __('Rule #1', 'ajax-search-pro'); ?></span>
            <div class="re_line">
                <label for="rule_name">
                    <?php echo __('Rule name', 'ajax-search-pro'); ?>
                </label>
                <input type="text" name="rule_name" value="<?php echo esc_attr__('Rule name', 'ajax-search-pro'); ?>">
            </div>
            <div class="re_line">
                <label for="rule_field">
                    <?php echo __('Rule type', 'ajax-search-pro'); ?>
                </label>
                <select name="rule_field">
                    <option value="tax"><?php echo __('Taxonomy term', 'ajax-search-pro'); ?></option>
                    <option value="cf"><?php echo __('Custom field', 'ajax-search-pro'); ?></option>
                    <!-- <option value="title">Post title</option> -->
                </select>
            </div>
            <div class="pg_rule_tax re_line hiddend">
                <label for="term_operator"><?php echo __('Operator', 'ajax-search-pro'); ?></label>
                <select name="term_operator">
                    <option value="in"><?php echo __('IN', 'ajax-search-pro'); ?></option>
                    <option value="not in"><?php echo __('NOT IN', 'ajax-search-pro'); ?></option>
                </select>
            </div>
            <div class="pg_rule_tax re_line hiddend">
                <div style="display:none" id="_tax_search_field"></div>
                <?php
                new wd_TaxTermSearchCallBack('pg_search_taxterms', __('Select taxonomy terms', 'ajax-search-pro'),
                    array(
                        'value' => '',
                        //'args' => array('callback' => 'wd_cf_ajax_callback'),
                        'limit' => 12
                    )
                );
                ?>
                <ul id="pg_selected_tax_terms">
                    <!-- <li data-taxonomy='taxonomy' data-id=1>Term name</li> -->
                </ul>
            </div>
            <div class="pg_rule_cf re_line hiddend">
                <div style="display:none" id="_cf_search_field"></div>
                <label for="pg_search_cf"><?php echo __('Choose custom field', 'ajax-search-pro'); ?></label>
                <?php
                new wd_CFSearchCallBack('pg_search_cf', '',
                    array(
                        'value' => '',
                        //'args' => array('callback' => 'wd_cf_ajax_callback'),
                        'limit' => 12
                    )
                );
                ?>
            </div>
            <div class="pg_rule_cf re_line hiddend">
                <label for="cf_operator">
                    <?php echo __('Operator:', 'ajax-search-pro'); ?>
                </label>
                <select name="cf_operator">
                    <optgroup label="String operators">
                        <option value="like"><?php echo __('CONTAINS', 'ajax-search-pro'); ?></option>
                        <option value="not like"><?php echo __('DOES NOT CONTAIN', 'ajax-search-pro'); ?></option>
                        <option value="elike"><?php echo __('IS EXACTLY', 'ajax-search-pro'); ?></option>
                    </optgroup>
                    <optgroup label="Numeric operators">
                        <option value="=">=</option>
                        <option value="<>"><></option>
                        <option value="<"><</option>
                        <option value="<="><=</option>
                        <option value=">">></option>
                        <option value=">=">>=</option>
                        <option value="between"><?php echo __('Between', 'ajax-search-pro'); ?></option>
                    </optgroup>
                </select>
            </div>
            <div class="pg_rule_cf re_line hiddend">
                <label for="cf_val1">
                    <?php echo __('Value(s)', 'ajax-search-pro'); ?>
                </label>
                <input type="text" name="cf_val1" value="" placeholder="<?php echo esc_attr__('Enter value here..', 'ajax-search-pro'); ?>">
                <input style="display: none;" type="text" name="cf_val2" value="" placeholder="<?php echo esc_attr__('Enter value 2 here..', 'ajax-search-pro'); ?>">
            </div>
            <div class="pg_rule_title re_line hiddend">
                <label for="title_operator">
                    <?php echo __('Operator', 'ajax-search-pro'); ?>
                </label>
                <select name="title_operator">
                    <option value="like"><?php echo __('CONTAINS', 'ajax-search-pro'); ?></option>
                    <option value="not like"><?php echo __('DOES NOT CONTAIN', 'ajax-search-pro'); ?></option>
                    <option value="elike"><?php echo __('IS EXACTLY', 'ajax-search-pro'); ?></option>
                </select>
            </div>
            <div class="pg_rule_title re_line hiddend">
                <label for="title_value">
                    <?php echo __('Text', 'ajax-search-pro'); ?>
                </label>
                <input type="text" name="title_value" value="" placeholder="<?php echo esc_attr__('Keyword..', 'ajax-search-pro'); ?>">
            </div>
            <div class="pg_rule_buttons">
                <input type="button" id="pg_editor_save_rule" value="<?php echo esc_attr__('Save rule', 'ajax-search-pro'); ?>" class="wd_button wd_button_blue">
                <input type="button" id="pg_editor_delete_rule" value="<?php echo esc_attr__('Delete rule', 'ajax-search-pro'); ?>" class="wd_button">
            </div>
        </div>
    </form>
</div>
<!-- SAMPLE ITEM ENDS HERE -->