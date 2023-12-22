<style>
    .wd-primary-order input,
    .wd-secondary-order input {
        width: 120px !important;
    }
</style>
<fieldset>
    <legend>
        <?php echo __('Ordering', 'ajax-search-pro'); ?>
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/general-settings/result-ordering"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
    </legend>
    <div class="item wd-primary-order item-flex-nogrow item-flex-wrap"><?php
        $o = new wpdreamsCustomSelect("orderby_primary", __('Primary ordering', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Relevance', 'ajax-search-pro'), 'value' => 'relevance DESC'),
                    array('option' => __('Title descending', 'ajax-search-pro'), 'value' => 'post_title DESC'),
                    array('option' => __('Title ascending', 'ajax-search-pro'), 'value' => 'post_title ASC'),
                    array('option' => __('Date descending', 'ajax-search-pro'), 'value' => 'post_date DESC'),
                    array('option' => __('Date ascending', 'ajax-search-pro'), 'value' => 'post_date ASC'),
					array('option' => __('ID descending', 'ajax-search-pro'), 'value' => 'id DESC'),
					array('option' => __('ID ascending', 'ajax-search-pro'), 'value' => 'id ASC'),
                    array('option' => __('Menu order descending', 'ajax-search-pro'), 'value' => 'menu_order DESC'),
                    array('option' => __('Menu order ascending', 'ajax-search-pro'), 'value' => 'menu_order ASC'),
                    array('option' => __('Random', 'ajax-search-pro'), 'value' => 'RAND()'),
                    array('option' => __('Custom Field descending', 'ajax-search-pro'), 'value' => 'customfp DESC'),
                    array('option' => __('Custom Field  ascending', 'ajax-search-pro'), 'value' => 'customfp ASC')
                ),
                'value' => $sd['orderby_primary']
            ));
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsText("orderby_primary_cf", __('custom field name', 'ajax-search-pro'), $sd['orderby_primary_cf']);
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsCustomSelect("orderby_primary_cf_type", __('type', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('numeric', 'ajax-search-pro'), 'value' => 'numeric'),
                    array('option' => __('string or date', 'ajax-search-pro'), 'value' => 'string')
                ),
                'value' => $sd['orderby_primary_cf_type']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item wd-secondary-order item-flex-nogrow item-flex-wrap"><?php
        $o = new wpdreamsCustomSelect("orderby", __('Secondary ordering', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Relevance', 'ajax-search-pro'), 'value' => 'relevance DESC'),
                    array('option' => __('Title descending', 'ajax-search-pro'), 'value' => 'post_title DESC'),
                    array('option' => __('Title ascending', 'ajax-search-pro'), 'value' => 'post_title ASC'),
                    array('option' => __('Date descending', 'ajax-search-pro'), 'value' => 'post_date DESC'),
                    array('option' => __('Date ascending', 'ajax-search-pro'), 'value' => 'post_date ASC'),
					array('option' => __('ID descending', 'ajax-search-pro'), 'value' => 'id DESC'),
					array('option' => __('ID ascending', 'ajax-search-pro'), 'value' => 'id ASC'),
                    array('option' => __('Random', 'ajax-search-pro'), 'value' => 'RAND()'),
                    array('option' => __('Custom Field descending', 'ajax-search-pro'), 'value' => 'customfs DESC'),
                    array('option' => __('Custom Field ascending', 'ajax-search-pro'), 'value' => 'customfs ASC')
                ),
                'value' => $sd['orderby']
            ));
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsText("orderby_secondary_cf", __('custom field name', 'ajax-search-pro'), $sd['orderby_secondary_cf']);
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsCustomSelect("orderby_secondary_cf_type", __('type', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('numeric', 'ajax-search-pro'), 'value' => 'numeric'),
                    array('option' => __('string or date', 'ajax-search-pro'), 'value' => 'string')
                ),
                'value' => $sd['orderby_secondary_cf_type']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo __('If two elements match the primary ordering criteria, the <b>Secondary ordering</b> is used.', 'ajax-search-pro'); ?>
        </div>
    </div>
    <div class="item">
        <p class="infoMsg">
            <?php echo __('Separate ordering options are available for <strong>User results</strong>, under the <a class="asp_to_tab" href="#108" tabid="108" data-asp-os-highlight="user_orderby_primary">User Search panel</a>.', 'ajax-search-pro'); ?>
        </p>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("use_post_type_order", __('Use separate ordering for each post type group?', 'ajax-search-pro'), $sd['use_post_type_order']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wd_Post_Type_Sortalbe("post_type_order", __('Post type results ordering', 'ajax-search-pro'), $sd['post_type_order']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $fields = $sd['results_order'];

        // For updating to 4.5
        if (strpos($fields, "attachments") === false) $fields = $fields . "|attachments";

        $o = new wpdreamsSortable("results_order", __('Mixed results order', 'ajax-search-pro'), $fields);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
</fieldset>