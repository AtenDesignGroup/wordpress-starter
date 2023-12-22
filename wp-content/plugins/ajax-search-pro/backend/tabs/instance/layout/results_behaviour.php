<div class="item">
    <?php
    $o = new wpdreamsYesNo("results_click_blank", __('When clicking on a result, open it in a new window?', 'ajax-search-pro'), $sd['results_click_blank']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item item-flex-nogrow item-flex-wrap">
    <?php
    $o = new wpdreamsYesNo("scroll_to_results", __('Scroll the browser window to the result list, when the search starts?', 'ajax-search-pro'), $sd['scroll_to_results']);

    $o = new wpdreamsTextSmall("scroll_to_results_offset", __('scroll offset (px)', 'ajax-search-pro'), $sd['scroll_to_results_offset']);
    ?>
    <div class="descMsg item-flex-grow item-flex-100">
        <?php echo __('A negative offset will move the window upwards, a positive downwards. Default: 0', 'ajax-search-pro'); ?>
    </div>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("resultareaclickable", __('Make the whole result area clickable?', 'ajax-search-pro'), $sd['resultareaclickable']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("close_on_document_click", __('Close results when the search loses focus?', 'ajax-search-pro'), $sd['close_on_document_click']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Closes the results list when clicking outside the search elements.', 'ajax-search-pro'); ?>
    </p>
</div>
