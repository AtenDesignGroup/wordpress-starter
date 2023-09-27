<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_search", __('Enable search in users?', 'ajax-search-pro'),
        $sd['user_search']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div wd-disable-on="user_search:0">
<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_login_search", __('Search in user login names?', 'ajax-search-pro'),
        $sd['user_login_search']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_display_name_search", __('Search in user display names?', 'ajax-search-pro'),
        $sd['user_display_name_search']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_first_name_search", __('Search in user first names?', 'ajax-search-pro'),
        $sd['user_first_name_search']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_last_name_search", __('Search in user last names?', 'ajax-search-pro'),
        $sd['user_last_name_search']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_bio_search", __('Search in user bio?', 'ajax-search-pro'),
        $sd['user_bio_search']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("user_email_search", __('Search in user email addresses?', 'ajax-search-pro'),
        $sd['user_email_search']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item wd-primary-order item-flex-nogrow item-flex-wrap"><?php
    $o = new wpdreamsCustomSelect("user_orderby_primary", __('Primary ordering', 'ajax-search-pro'),
        array(
            'selects' => array(
                array('option' => __('Relevance', 'ajax-search-pro'), 'value' => 'relevance DESC'),
                array('option' => __('Title descending', 'ajax-search-pro'), 'value' => 'title DESC'),
                array('option' => __('Title ascending', 'ajax-search-pro'), 'value' => 'title ASC'),
                array('option' => __('Date descending', 'ajax-search-pro'), 'value' => 'date DESC'),
                array('option' => __('Date ascending', 'ajax-search-pro'), 'value' => 'date ASC'),
                array('option' => __('ID descending', 'ajax-search-pro'), 'value' => 'id DESC'),
                array('option' => __('ID ascending', 'ajax-search-pro'), 'value' => 'id ASC'),
                array('option' => __('Random', 'ajax-search-pro'), 'value' => 'RAND()'),
                array('option' => __('Custom Field descending', 'ajax-search-pro'), 'value' => 'customfp DESC'),
                array('option' => __('Custom Field  ascending', 'ajax-search-pro'), 'value' => 'customfp ASC')
            ),
            'value' => $sd['user_orderby_primary']
        ));
    $params[$o->getName()] = $o->getData();
	?>
	<div wd-show-on="user_orderby_primary:customfp DESC,customfp ASC">
	<?php
    $o = new wpdreamsText("user_orderby_primary_cf", __('custom field', 'ajax-search-pro'), $sd['orderby_primary_cf']);
    $params[$o->getName()] = $o->getData();
	?>
	</div>
	<div wd-show-on="user_orderby_primary:customfp DESC,customfp ASC">
	<?php
    $o = new wpdreamsCustomSelect("user_orderby_primary_cf_type", __('type', 'ajax-search-pro'),
        array(
            'selects' => array(
                array('option' => __('numeric', 'ajax-search-pro'), 'value' => 'numeric'),
                array('option' => __('string or date', 'ajax-search-pro'), 'value' => 'string')
            ),
            'value' => $sd['user_orderby_primary_cf_type']
        ));
    $params[$o->getName()] = $o->getData();
    ?>
	</div>
</div>
    <div class="item wd-secondary-order item-flex-nogrow item-flex-wrap"><?php
        $o = new wpdreamsCustomSelect("user_orderby_secondary", __('Secondary result ordering', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Relevance', 'ajax-search-pro'), 'value' => 'relevance DESC'),
                    array('option' => __('Title descending', 'ajax-search-pro'), 'value' => 'title DESC'),
                    array('option' => __('Title ascending', 'ajax-search-pro'), 'value' => 'title ASC'),
                    array('option' => __('Date descending', 'ajax-search-pro'), 'value' => 'date DESC'),
                    array('option' => __('Date ascending', 'ajax-search-pro'), 'value' => 'date ASC'),
					array('option' => __('ID descending', 'ajax-search-pro'), 'value' => 'id DESC'),
					array('option' => __('ID ascending', 'ajax-search-pro'), 'value' => 'id ASC'),
                    array('option' => __('Random', 'ajax-search-pro'), 'value' => 'RAND()'),
                    array('option' => __('Custom Field descending', 'ajax-search-pro'), 'value' => 'customfs DESC'),
                    array('option' => __('Custom Field ascending', 'ajax-search-pro'), 'value' => 'customfs ASC')
                ),
                'value' => $sd['user_orderby_secondary']
            ));
        $params[$o->getName()] = $o->getData();
		?>
		<div wd-show-on="user_orderby_secondary:customfs DESC,customfs ASC">
		<?php
        $o = new wpdreamsText("user_orderby_secondary_cf", __('custom field', 'ajax-search-pro'), $sd['orderby_secondary_cf']);
        $params[$o->getName()] = $o->getData();
		?>
		</div>
		<div wd-show-on="user_orderby_secondary:customfs DESC,customfs ASC">
		<?php
        $o = new wpdreamsCustomSelect("user_orderby_secondary_cf_type", __('type', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('numeric', 'ajax-search-pro'), 'value' => 'numeric'),
                    array('option' => __('string or date', 'ajax-search-pro'), 'value' => 'string')
                ),
                'value' => $sd['user_orderby_secondary_cf_type']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
		</div>
        <div class="descMsg item-flex-grow item-flex-100">
            <?php echo __('If two elements match the primary ordering criteria, the <b>Secondary ordering</b> is used.', 'ajax-search-pro'); ?>
        </div>
    </div>
<div class="item">
    <?php
    $o = new wpdreamsUserRoleSelect("user_search_exclude_roles", __('User roles exclude', 'ajax-search-pro'),
        $sd['user_search_exclude_roles']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wd_UserSelect("user_search_exclude_users", __('Exclude or Include users from results', 'ajax-search-pro'), array(
        "value"=>$sd['user_search_exclude_users'],
        'args'=> array(
            'show_type' => 1,
            'show_all_users_option' => 0
        )
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wd_UserMeta("user_search_meta_fields", __('Search in following user meta fields', 'ajax-search-pro'), array(
        "value"=>$sd['user_search_meta_fields'],
        'args'=> array()
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsBP_XProfileFields("user_bp_fields", __('Search in these BP Xprofile fields', 'ajax-search-pro'),
        $sd['user_bp_fields']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
	<p>
	<?php echo sprintf( __('To change the user result URL or Title, Content fields, please go to <a class="asp_to_tab" href="%s" tabid="%s">Advanced Options -> Content</a> panel.', 'ajax-search-pro'), '#701', '701' ); ?>
    </p>
</div>
</div>