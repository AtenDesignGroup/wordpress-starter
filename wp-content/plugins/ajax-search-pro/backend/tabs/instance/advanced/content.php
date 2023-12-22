<style>
	.asp-adv-fields .wd_textarea_expandable {
		min-width: 85%;
		max-height: 480px !important;
	}
</style>
<fieldset>
    <legend><?php echo __('Content & Language', 'ajax-search-pro'); ?></legend>
    <div class="item<?php echo class_exists('SitePress') ? "" : " hiddend"; ?>">
        <?php
        $o = new wpdreamsYesNo("wpml_compatibility", __('WPML compatibility', 'ajax-search-pro'),  $sd['wpml_compatibility']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('If turned <strong>ON</strong>: return results from current language. If turned <strong>OFF</strong>: return results from any language.', 'ajax-search-pro'); ?>
        </p>
    </div>
    <div class="item<?php echo function_exists("pll_current_language") ? "" : " hiddend"; ?>">
        <?php
        $o = new wpdreamsYesNo("polylang_compatibility", __('Polylang compatibility', 'ajax-search-pro'),  $sd['polylang_compatibility']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('If turned <strong>ON</strong>: return results from current language. If turned <strong>OFF</strong>: return results from any language.', 'ajax-search-pro'); ?>
        </p>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsCustomSelect("shortcode_op", __('What to do with shortcodes in results content?', 'ajax-search-pro'),  array(
            'selects'=>array(
                array("option"=>__('Remove them, keep the content', 'ajax-search-pro'), "value" => "remove"),
                array("option"=>__('Execute them (can by really slow)', 'ajax-search-pro'), "value" => "execute")
            ),
            'value'=>$sd['shortcode_op']
        ));
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('Removing shortcode is usually <strong>much faster</strong>, especially if you have many of them within posts.', 'ajax-search-pro'); ?>
        </p>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsText("striptagsexclude", __('HTML Tags exclude from stripping content', 'ajax-search-pro'),  $sd['striptagsexclude']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
</fieldset>
<fieldset class="asp-adv-fields">
    <legend>
        <?php echo __('Post Type Result Fields', 'ajax-search-pro'); ?>
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/advanced-options/advanced-title-and-description-fields"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
    </legend>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsCustomSelect("primary_titlefield", __('Primary Title Field for Posts/Pages/CPT', 'ajax-search-pro'),  array(
            'selects'=>array(
                array('option' => __('Post Title', 'ajax-search-pro'), 'value' => 0),
                array('option' => __('Post Excerpt', 'ajax-search-pro'), 'value' => 1),
                array('option' => __('Custom Field', 'ajax-search-pro'), 'value' => 'c__f')
            ),
            'value'=>$sd['primary_titlefield']
        ));
        $params[$o->getName()] = $o->getData();
        $o = new wd_CFSearchCallBack('primary_titlefield_cf', '', array(
                'value'=>$sd['primary_titlefield_cf'],
                'args'=> array(
                        'controls_position' => 'left',
                        'class'=>'wpd-text-right'
                )
        ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsCustomSelect("secondary_titlefield", __('Secondary Title Field for Posts/Pages/CPT', 'ajax-search-pro'),  array(
            'selects'=>array(
                array('option' => __('Disabled', 'ajax-search-pro'), 'value' => -1),
                array('option' => __('Post Title', 'ajax-search-pro'), 'value' => 0),
                array('option' => __('Post Excerpt', 'ajax-search-pro'), 'value' => 1),
                array('option' => __('Custom Field', 'ajax-search-pro'), 'value' => 'c__f')
            ),
            'value'=>$sd['secondary_titlefield']
        ));
        $params[$o->getName()] = $o->getData();
        $o = new wd_CFSearchCallBack('secondary_titlefield_cf', '', array(
                'value'=>$sd['secondary_titlefield_cf'],
                'args'=> array(
                        'controls_position' => 'left',
                        'class'=>'wpd-text-right'
                )
        ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsCustomSelect("primary_descriptionfield", __('Primary Description Field for Posts/Pages/CPT', 'ajax-search-pro'),  array(
            'selects'=>array(
                array('option' => __('Post Content', 'ajax-search-pro'), 'value' => 0),
                array('option' => __('Post Excerpt', 'ajax-search-pro'), 'value' => 1),
                array('option' => __('Post Title', 'ajax-search-pro'), 'value' => 2),
                array('option' => __('Custom Field', 'ajax-search-pro'), 'value' => 'c__f')
            ),
            'value'=>$sd['primary_descriptionfield']
        ));
        $params[$o->getName()] = $o->getData();
        $o = new wd_CFSearchCallBack('primary_descriptionfield_cf', '', array(
                'value'=>$sd['primary_descriptionfield_cf'],
                'args'=> array(
                        'controls_position' => 'left',
                        'class'=>'wpd-text-right'
                )
        ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsCustomSelect("secondary_descriptionfield", __('Secondary Description Field for Posts/Pages/CPT', 'ajax-search-pro'),  array(
            'selects'=>array(
                array('option' => __('Disabled', 'ajax-search-pro'), 'value' => -1),
                array('option' => __('Post Content', 'ajax-search-pro'), 'value' => 0),
                array('option' => __('Post Excerpt', 'ajax-search-pro'), 'value' => 1),
                array('option' => __('Post Title', 'ajax-search-pro'), 'value' => 2),
                array('option' => __('Custom Field', 'ajax-search-pro'), 'value' => 'c__f')
            ),
            'value'=>$sd['secondary_descriptionfield']
        ));
        $params[$o->getName()] = $o->getData();
        $o = new wd_CFSearchCallBack('secondary_descriptionfield_cf', '', array(
                'value'=>$sd['secondary_descriptionfield_cf'],
                'args'=> array(
                        'controls_position' => 'left',
                        'class'=>'wpd-text-right'
                )
        ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <p class='infoMsg'>
        <?php echo __('Example: <b>{titlefield} - {_price}</b> will show the title and price for a woocommerce product.', 'ajax-search-pro'); ?>&nbsp;
        <?php echo sprintf( __('For more info and more advanced uses please <a href="%s" target="_blank">check this documentation chapter</a>.', 'ajax-search-pro'), 'https://wp-dreams.com/go/?to=asp-doc-advanced-title-content' ); ?>
    </p>
    <div class="item">
        <?php
        $o = new wd_TextareaExpandable("advtitlefield", __('Advanced Title Field (default: {titlefield})', 'ajax-search-pro'),  $sd['advtitlefield']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('HTML is supported! Use {custom_field_name} format to have custom field values.', 'ajax-search-pro'); ?>&nbsp;
            <a href="https://wp-dreams.com/go/?to=asp-doc-advanced-title-content" target="_blank">
                <?php echo __('More possibilities explained here!', 'ajax-search-pro'); ?>
            </a>
        </p>
    </div>
    <div class="item">
        <?php
        $o = new wd_TextareaExpandable("advdescriptionfield", __('Advanced Description Field (default: {descriptionfield})', 'ajax-search-pro'),  $sd['advdescriptionfield']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('HTML is supported! Use {custom_field_name} format to have custom field values.', 'ajax-search-pro'); ?>&nbsp;
            <a href="https://wp-dreams.com/go/?to=asp-doc-advanced-title-content" target="_blank">
                <?php echo __('More possibilities explained here!', 'ajax-search-pro'); ?>
            </a>
        </p>
    </div>
</fieldset>
<fieldset class="asp-adv-fields">
    <legend>
        <?php echo __('User Result Fields & URL', 'ajax-search-pro'); ?>
    </legend>
    <div class="item">
        <?php
        $o = new wpdreamsCustomSelect("user_search_title_field", __('Title field for <strong>User</strong> results', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Login Name', 'ajax-search-pro'), 'value' => 'login'),
                    array('option' => __('Display Name', 'ajax-search-pro'), 'value' => 'display_name')
                ),
                'value' => $sd['user_search_title_field']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsCustomSelect("user_search_description_field", __('Description field for <strong>User</strong> results', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Biography', 'ajax-search-pro'), 'value' => 'bio'),
                    array('option' => __('BuddyPress Last Activity', 'ajax-search-pro'), 'value' => 'buddypress_last_activity'),
                    array('option' => __('Nothing', 'ajax-search-pro'), 'value' => 'nothing')
                ),
                'value' => $sd['user_search_description_field']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wd_TextareaExpandable("user_search_advanced_title_field", __('Advanced title field for <strong>User</strong> results', 'ajax-search-pro'),
            $sd['user_search_advanced_title_field']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('Variable {titlefield} will be replaced with the Title field value. Use the format {meta_field} to get user meta.', 'ajax-search-pro'); ?><br>
            <a href="https://wp-dreams.com/go/?to=asp-doc-advanced-title-content" target="_blank"><?php echo __('More possibilities explained here!', 'ajax-search-pro'); ?></a>
        </p>
    </div>
    <div class="item">
        <?php
        $o = new wd_TextareaExpandable("user_search_advanced_description_field", __('Advanced description field for <strong>User</strong> results', 'ajax-search-pro'),
            $sd['user_search_advanced_description_field']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('Variable {descriptionfield} will be replaced with the Description field value. Use the format {meta_field} to get user meta.', 'ajax-search-pro'); ?><br>
            <a href="https://wp-dreams.com/go/?to=asp-doc-advanced-title-content" target="_blank"><?php echo __('More possibilities explained here!', 'ajax-search-pro'); ?></a>
        </p>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsCustomSelect("user_search_url_source", __('<strong>User</strong> results url source', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Default', 'ajax-search-pro'), 'value' => 'default'),
                    array('option' => __('BuddyPress profile', 'ajax-search-pro'), 'value' => 'bp_profile'),
                    array('option' => __('Custom scheme', 'ajax-search-pro'), 'value' => 'custom')
                ),
                'value' => $sd['user_search_url_source']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('This is the result URL destination. By default it\'s the author profile link.', 'ajax-search-pro'); ?>
        </p>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsText("user_search_custom_url", __('Custom url scheme for <strong>User</strong> results', 'ajax-search-pro'),
            $sd['user_search_custom_url']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('You can use these variables: {USER_ID}, {USER_LOGIN}, {USER_NICENAME}, {USER_DISPLAYNAME}, {USER_NICKNAME}', 'ajax-search-pro'); ?>
        </p>
    </div>
</fieldset>