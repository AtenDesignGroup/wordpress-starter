<p class="infoMsg"><?php echo __('Keyword exceptions will be replaced with an empty string "" in the search phrase.', 'ajax-search-pro'); ?></p>
<div class="item">
    <?php
    $o = new wd_TextareaExpandable("kw_exceptions", __('Keyword exceptions - replace anywhere', 'ajax-search-pro'), $sd['kw_exceptions']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg"><?php echo __('<strong>Comma separated list</strong> of keywords you want to remove or ban. Matching anything, even partial words.', 'ajax-search-pro'); ?></p>
</div>
<div class="item">
    <?php
    $o = new wd_TextareaExpandable("kw_exceptions_e", __('Keyword exceptions - replace whole words only', 'ajax-search-pro'), $sd['kw_exceptions_e']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg"><?php echo __('<strong>Comma separated list</strong> of keywords you want to remove or ban. Only matching whole words between word boundaries.', 'ajax-search-pro'); ?></p>
</div>