<div class="descMsg">
    <?php echo __('Define the number of results for each source group with these options.<br>
    The <strong>left</strong> values are for the ajax results, the <strong>right</strong> values are for non-ajax results (aka. results page/override).', 'ajax-search-pro'); ?>
</div>
<div style="border-bottom: 1px dotted #e7e7e7; padding-bottom: 10px;margin-bottom: 10px;"></div>
<div class="item">
    <?php
    $o = new wpdreamsTextSmall("posts_limit", __('Post type (post, page, product..) results limit', 'ajax-search-pro'), $sd['posts_limit']);
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("posts_limit_override", __(' on result page', 'ajax-search-pro'), $sd['posts_limit_override']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("posts_limit_distribute", __('Distribute the posts limit between each post type equally?', 'ajax-search-pro'),
        $sd['posts_limit_distribute']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('For example if you have search in <strong>posts</strong> and <strong>pages</strong>
        enabled and the post limit is 10,<br>then the plugin will try to return <strong>5 posts</strong> and <strong>5 pages.</strong>', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsTextSmall("taxonomies_limit", __('Category/Tag/Term results limit', 'ajax-search-pro'), $sd['taxonomies_limit']);
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("taxonomies_limit_override", __(' on result page', 'ajax-search-pro'), $sd['taxonomies_limit_override']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsTextSmall("users_limit", __('User results limit', 'ajax-search-pro'), $sd['users_limit']);
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("users_limit_override", __(' on result page', 'ajax-search-pro'), $sd['users_limit_override']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsTextSmall("blogs_limit", __('Blog results limit', 'ajax-search-pro'), $sd['blogs_limit']);
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("blogs_limit_override", __(' on result page', 'ajax-search-pro'), $sd['blogs_limit_override']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsTextSmall("buddypress_limit", __('Buddypress results limit', 'ajax-search-pro'), $sd['buddypress_limit']);
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("buddypress_limit_override", __(' on result page', 'ajax-search-pro'), $sd['buddypress_limit_override']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsTextSmall("comments_limit", __('Comments results limit', 'ajax-search-pro'), $sd['comments_limit']);
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("comments_limit_override", __(' on result page', 'ajax-search-pro'), $sd['comments_limit_override']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsTextSmall("attachments_limit", __('Attachments results limit', 'ajax-search-pro'), $sd['attachments_limit']);
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("attachments_limit_override", __(' on result page', 'ajax-search-pro'), $sd['attachments_limit_override']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item<?php echo class_exists('PeepSo') ? '' : ' hiddend'; ?>">
    <?php
    $o = new wpdreamsTextSmall("peepso_groups_limit", __('Peepso Groups results limit', 'ajax-search-pro'), $sd['peepso_groups_limit']);
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("peepso_groups_limit_override", __(' on result page', 'ajax-search-pro'), $sd['peepso_groups_limit_override']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item<?php echo class_exists('PeepSo') ? '' : ' hiddend'; ?>">
    <?php
    $o = new wpdreamsTextSmall("peepso_activities_limit", __('Peepso Activities results limit', 'ajax-search-pro'), $sd['peepso_activities_limit']);
    $params[$o->getName()] = $o->getData();
    $o = new wpdreamsTextSmall("peepso_activities_limit_override", __(' on result page', 'ajax-search-pro'), $sd['peepso_activities_limit_override']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>