<fieldset>
    <legend>Logic Options</legend>
    <div class="item"><?php
        $o = new wpdreamsCustomSelect("term_logic", __('Taxonomy terms checkbox/multiselect connection logic', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('At least one selected terms should match', 'ajax-search-pro'), 'value' => 'or'),
                    array('option' => __('All of the selected terms must match, exclude unselected (default)', 'ajax-search-pro'), 'value' => 'and'),
                    array('option' => __('All of the selected terms must match EXACTLY, but unselected ones are not excluded.', 'ajax-search-pro'), 'value' => 'andex')
                ),
                'value' => $sd['term_logic']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
        <div id='term_logic_MSG' class="errorMsg hiddend">
            <?php echo __("<strong>WARNING:</strong> This is a very strict configuration - only results <strong>matching exactly ALL</strong>
            of the selected terms will show up. If you don't get any results, it is probably because of this option.<br>
            This logic works best, if you start with all checkboxes <strong>unchecked</strong>.", 'ajax-search-pro'); ?>
        </div>
        <p class="descMsg">
            <?php echo __('This determines the rule how the individual checkbox/multiselect selections should be treated within each taxonomy group.', 'ajax-search-pro'); ?>
        </p>
    </div>
    <div class="item"><?php
        $o = new wpdreamsCustomSelect("taxonomy_logic", __('Logic between taxonomy groups', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => 'AND (default)', 'value' => 'and'),
                    array('option' => 'OR', 'value' => 'or')
                ),
                'value' => $sd['taxonomy_logic']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('This determines the connection between each taxonomy term filter group.', 'ajax-search-pro'); ?>
        </p>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("frontend_terms_empty", __('Show posts/CPM with empty (missing) taxonomy terms?', 'ajax-search-pro'), $sd['frontend_terms_empty']);
        ?>
        <p class="descMsg">
            <?php echo __('This decides what happens if the posts does not have any terms from the selected taxonomies. For example posts with no categories, when using a category filter.', 'ajax-search-pro'); ?>
        </p>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("frontend_terms_ignore_empty", __('Ignore checkbox filters that have nothing selected?', 'ajax-search-pro'), $sd['frontend_terms_ignore_empty']);
        ?>
        <p class="descMsg">
            <?php echo __('When turned <strong>ON</strong> and nothing is checked within a checkbox filter - then the search will ignore it completely - instead of excluding everything unchecked.', 'ajax-search-pro'); ?>
        </p>
    </div>
    <div class="item"><?php
        $o = new wpdreamsCustomSelect("cf_logic", __('Custom Fields connection Logic', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => 'AND', 'value' => 'AND'),
                    array('option' => 'OR', 'value' => 'OR')
                ),
                'value' => $sd['cf_logic']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("cf_allow_null", __('Allow results with missing custom fields, when using custom field selectors?', 'ajax-search-pro'), $sd['cf_allow_null']);
        ?>
        <p class="descMsg">
            <?php echo __('When using custom field selectors (filters), this option will allow displaying posts/pages/cpm where the given custom field is not defined.
            <br>For example: You have a custom field filter on "location" custom field, but some posts does not have the "location" custom field defined. This option
            will allow displaying them as results regardless.', 'ajax-search-pro'); ?>
        </p>
    </div>
</fieldset>
<div class="item">
    <?php
    $fields = $sd['field_order'];

    if (strpos($fields, "general") === false) $fields = "general|" . $fields;
    if (strpos($fields, "post_tags") === false) $fields .= "|post_tags";
    if (strpos($fields, "date_filters") === false) $fields .= "|date_filters";
    if (strpos($fields, "content_type_filters") === false) $fields .= "|content_type_filters";
    if (strpos($fields, "search_button") === false) $fields .= "|search_button";

    $o = new wpdreamsSortable("field_order", __('Field order', 'ajax-search-pro'),
        $fields);
    $params[$o->getName()] = $o->getData();
    ?>
</div>