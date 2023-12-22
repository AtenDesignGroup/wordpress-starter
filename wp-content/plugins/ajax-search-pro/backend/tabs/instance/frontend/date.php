<span class="asp_legend_docs">
    <a target="_blank" href="https://documentation.ajaxsearchpro.com/frontend-search-settings/date-selectors"><span class="fa fa-book"></span>
        <?php echo __('Documentation', 'ajax-search-pro'); ?>
    </a>
</span>
<div class="item">
    <?php
    $o = new wd_DateFilterPost("date_filter_from", __('Display \'Posts from date\' filter as', 'ajax-search-pro'), $sd['date_filter_from']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item item-flex-nogrow item-flex-wrap">
    <?php
    $o = new wpdreamsText("date_filter_from_t", __('Filter header text', 'ajax-search-pro'), $sd['date_filter_from_t']);
    $params[$o->getName()] = $o->getData();

    $o = new wpdreamsText("date_filter_from_placeholder", __('..and placeholder text', 'ajax-search-pro'), $sd['date_filter_from_placeholder']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item" style="border-bottom: 1px dashed #E5E5E5;padding-bottom: 26px;">
    <?php
    $o = new wpdreamsText("date_filter_from_format", __('Date format', 'ajax-search-pro'), $sd['date_filter_from_format']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo sprintf( __('dd/mm/yy is the most popular format, <a href="%s" target="_blank">list of accepted params</a>', 'ajax-search-pro'), 'http://api.jqueryui.com/datepicker/#utility-formatDate' ); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wd_DateFilterPost("date_filter_to", __('Display \'Posts to date\' filter', 'ajax-search-pro'), $sd['date_filter_to']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item item-flex-nogrow item-flex-wrap">
    <?php
    $o = new wpdreamsText("date_filter_to_t", __('Filter header text', 'ajax-search-pro'), $sd['date_filter_to_t']);
    $params[$o->getName()] = $o->getData();

    $o = new wpdreamsText("date_filter_to_placeholder", __('..and placeholder text', 'ajax-search-pro'), $sd['date_filter_to_placeholder']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item" style="border-bottom: 1px dashed #E5E5E5;padding-bottom: 26px;">
    <?php
    $o = new wpdreamsText("date_filter_to_format", __('Date format', 'ajax-search-pro'), $sd['date_filter_to_format']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo sprintf( __('dd/mm/yy is the most popular format, <a href="%s" target="_blank">list of accepted params</a>', 'ajax-search-pro'), 'http://api.jqueryui.com/datepicker/#utility-formatDate' ); ?>
    </p>
</div>
<div class="item item-flex-nogrow">
    <?php
    $o = new wpdreamsYesNo("date_filter_required", __('Required fields?', 'ajax-search-pro'), $sd['date_filter_required']);
    $params[$o->getName()] = $o->getData();

    $o = new wpdreamsText("date_filter_invalid_input_text", __('required popup text', 'ajax-search-pro'), $sd['date_filter_invalid_input_text']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>