<fieldset>
    <legend><?php echo __('Peepso Groups', 'ajax-search-pro'); ?></legend>
    <div class="item item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsYesNo("peep_gs_public", __('Search Public:', 'ajax-search-pro'), $sd['peep_gs_public']);
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsYesNo("peep_gs_closed", __(' ..Closed:', 'ajax-search-pro'), $sd['peep_gs_closed']);
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsYesNo("peep_gs_secret", __(' ..Secret:', 'ajax-search-pro'), $sd['peep_gs_secret']);
        $params[$o->getName()] = $o->getData();
        ?><div>&nbsp;&nbsp;&nbsp;PeepSo groups.</div>
    </div>
    <div class="item item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsYesNo("peep_gs_title", __('Search within group titles:', 'ajax-search-pro'), $sd['peep_gs_title']);
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsYesNo("peep_gs_content", __(' ..and descriptions:', 'ajax-search-pro'), $sd['peep_gs_content']);
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsYesNo("peep_gs_categories", __(' .. and categories:', 'ajax-search-pro'), $sd['peep_gs_categories']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wd_TextareaExpandable("peep_gs_exclude", __('Exclude Groups by ID', 'ajax-search-pro'), $sd['peep_gs_exclude']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">Comma separated list.</p>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo __('Peepso Group Activities - Posts and Comments', 'ajax-search-pro'); ?></legend>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsYesNo("peep_s_posts", __('Search Posts:', 'ajax-search-pro'), $sd['peep_s_posts']);
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsYesNo("peep_s_comments", __(' and Comments:', 'ajax-search-pro'), $sd['peep_s_comments']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("peep_pc_follow", __('Search activities only within groups, which the user follows?', 'ajax-search-pro'), $sd['peep_pc_follow']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsYesNo("peep_pc_public", __('Include activities only from public', 'ajax-search-pro'), $sd['peep_pc_public']);
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsYesNo("peep_pc_closed", __(' ..Closed:', 'ajax-search-pro'), $sd['peep_pc_closed']);
        $params[$o->getName()] = $o->getData();

        $o = new wpdreamsYesNo("peep_pc_secret", __(' ..Secret:', 'ajax-search-pro'), $sd['peep_pc_secret']);
        $params[$o->getName()] = $o->getData();
        ?><div>&nbsp;&nbsp;&nbsp;PeepSo groups.</div>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo __('When none selected, all activities are searched, including non-group related.', 'ajax-search-pro') ?>
        </div>
    </div>
</fieldset>