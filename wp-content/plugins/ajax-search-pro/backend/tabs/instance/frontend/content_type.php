<span class="asp_legend_docs">
    <a target="_blank" href="https://documentation.ajaxsearchpro.com/frontend-search-settings/content-type-filters"><span class="fa fa-book"></span>
        <?php echo __('Documentation', 'ajax-search-pro'); ?>
    </a>
</span>
<div class="item">
    <?php
    $o = new wpdreamsText("content_type_filter_label", __('Content type filter label text', 'ajax-search-pro'), $sd['content_type_filter_label']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wd_DraggableFields("content_type_filter", __('Content Type filter', 'ajax-search-pro'), array(
        "value"=>$sd['content_type_filter'],
        "args" => array(
            "show_checkboxes" => 1,
            "show_display_mode" => 1,
            "show_labels" => 1,
            "show_required" => 1,
            'fields' => array(
                'any'           => __('Choose One/Select all', 'ajax-search-pro'),
                'cpt'           => __('Custom post types', 'ajax-search-pro'),
                'comments'      => __('Comments', 'ajax-search-pro'),
                'taxonomies'    => __('Taxonomy terms', 'ajax-search-pro'),
                'users'         => __('Users', 'ajax-search-pro'),
                'blogs'         => __('Multisite blogs', 'ajax-search-pro'),
                'buddypress'    => __('BuddyPress content', 'ajax-search-pro'),
                'attachments'   => __('Attachments', 'ajax-search-pro')
            ),
            'checked' => array()
        )
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>