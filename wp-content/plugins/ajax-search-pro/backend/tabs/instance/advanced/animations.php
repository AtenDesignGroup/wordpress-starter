<fieldset>
    <legend><?php echo __('Advanced Visual Options', 'ajax-search-pro'); ?></legend>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("visual_detect_visbility", __('Hide the search box if it gets invisible?', 'ajax-search-pro'),  $sd['visual_detect_visbility']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('In case the search is placed into an interactive element, which hides on certain events, enable this option.
            The plugin will try to detect it\'s visibility, and hide the settings and the results container if needed.', 'ajax-search-pro'); ?>
        </p>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo __('Other options', 'ajax-search-pro'); ?></legend>
    <div class="item">
        <?php
        $o = new wpdreamsText("jquery_select2_nores", __('\'No matches\' text for searchable select and multiselect filters', 'ajax-search-pro'),  $sd['jquery_select2_nores']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __("When using the 'searchable select' and 'searchable multiselect' fields in category, taxonomy, tag or custom field filters - this text is used
            when no results match the searched value.", 'ajax-search-pro'); ?>
        </p>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo __('Desktop browsers', 'ajax-search-pro'); ?></legend>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsCustomSelect("sett_box_animation", __('Settings drop-down box animation', 'ajax-search-pro'),  array(
            'selects'=>array(
                array('option' => 'None', 'value' => 'none'),
                array('option' => 'Fade', 'value' => 'fade'),
                array('option' => 'Fade and Drop', 'value' => 'fadedrop')
            ),
            'value'=>$sd['sett_box_animation']) );
        $params[$o->getName()] = $o->getData();
        ?>
        <?php
        $o = new wpdreamsTextSmall("sett_box_animation_duration", __('.. animation duration (ms)', 'ajax-search-pro'),
            $sd['sett_box_animation_duration']);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo __('The animation of the appearing settings box when clicking on the settings icon.', 'ajax-search-pro'); ?>
        </div>
    </div>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsCustomSelect("res_box_animation", __('Results container box animation', 'ajax-search-pro'),  array(
            'selects'=>array(
                array('option' => 'None', 'value' => 'none'),
                array('option' => 'Fade', 'value' => 'fade'),
                array('option' => 'Fade and Drop', 'value' => 'fadedrop')
            ),
            'value'=>$sd['res_box_animation']) );
        $params[$o->getName()] = $o->getData();
        ?>
        <?php
        $o = new wpdreamsTextSmall("res_box_animation_duration", __('.. animation duration (ms)', 'ajax-search-pro'),
            $sd['res_box_animation_duration']);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo __('The animation of the appearing results box when finishing the search.', 'ajax-search-pro'); ?>
        </div>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsAnimations("res_items_animation", __('Result items animation', 'ajax-search-pro'),  $sd['res_items_animation']);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg">
            <?php echo __('The animation of each result when the results box is opening.', 'ajax-search-pro'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo __('Mobile browsers', 'ajax-search-pro'); ?></legend>
    <div class="item item-flex-nogrow item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsCustomSelect("sett_box_animation_m", __('Settings drop-down box animation', 'ajax-search-pro'),  array(
            'selects'=>array(
                array('option' => 'None', 'value' => 'none'),
                array('option' => 'Fade', 'value' => 'fade'),
                array('option' => 'Fade and Drop', 'value' => 'fadedrop')
            ),
            'value'=>$sd['sett_box_animation_m']) );
        $params[$o->getName()] = $o->getData();
        ?>
        <?php
        $o = new wpdreamsTextSmall("sett_box_animation_duration_m", __('.. animation duration (ms)', 'ajax-search-pro'),
            $sd['sett_box_animation_duration_m']);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo __('The animation of the appearing settings box when clicking on the settings icon.', 'ajax-search-pro'); ?>
        </div>
    </div>
    <div class="item item-flex-nogrow item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsCustomSelect("res_box_animation_m", __('Results container box animation', 'ajax-search-pro'),  array(
            'selects'=>array(
                array('option' => 'None', 'value' => 'none'),
                array('option' => 'Fade', 'value' => 'fade'),
                array('option' => 'Fade and Drop', 'value' => 'fadedrop')
            ),
            'value'=>$sd['res_box_animation_m']) );
        $params[$o->getName()] = $o->getData();
        ?>
        <?php
        $o = new wpdreamsTextSmall("res_box_animation_duration_m", __('.. animation duration (ms)', 'ajax-search-pro'),
            $sd['res_box_animation_duration_m']);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo __('The animation of the appearing results box when finishing the search.', 'ajax-search-pro'); ?>
        </div>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsAnimations("res_items_animation_m", __('Result items animation', 'ajax-search-pro'),  $sd['res_items_animation_m']);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg">
            <?php echo __('The animation of each result when the results box is opening.', 'ajax-search-pro'); ?>
        </div>
    </div>
</fieldset>