<div class='item'>
    <p class='infoMsg'>
        <?php echo __('If you not choose any site, then the <strong>currently active</strong> blog will be used!<br />
        Also, you can use the same search on multiple blogs!', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("searchinblogtitles", __('Search in blog titles?', 'ajax-search-pro'),
         $sd['searchinblogtitles']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsCustomSelect("blogtitleorderby", __('Result ordering', 'ajax-search-pro'), array(
        'selects'=> array(
            array('option' => __('Blog titles descending', 'ajax-search-pro'), 'value' => 'desc'),
            array('option' => __('Blog titles ascending', 'ajax-search-pro'), 'value' => 'asc')
        ),
        'value'=> $sd['blogtitleorderby']
    ) );
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item">
    <?php
    $o = new wpdreamsText("blogresultstext", __('Blog results group default text', 'ajax-search-pro'),
         $sd['blogresultstext']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsBlogselect("blogs", __('Blogs', 'ajax-search-pro'),
         $sd['blogs']);
    $params[$o->getName()] = $o->getData();
    $params['selected-'.$o->getName()] = $o->getSelected();
    ?>
</div>
<div class="item">
    <input name="reset_<?php echo $search['id']; ?>" class="asp_submit asp_submit_transparent asp_submit_reset" type="button" value="<?php echo esc_attr__('Restore defaults', 'ajax-search-pro'); ?>">
    <input name="submit_<?php echo $search['id']; ?>" type="submit" value="<?php echo esc_attr__('Save all tabs!', 'ajax-search-pro'); ?>" />
</div>