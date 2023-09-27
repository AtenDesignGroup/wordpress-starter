<div class="item">
    <?php
    $option_name = "show_images";
    $option_desc = __('Show images in results?', 'ajax-search-pro');
    $o = new wpdreamsYesNo($option_name, $option_desc,
        $sd[$option_name]);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $option_name = "image_transparency";
    $option_desc = __('Preserve image transparency?', 'ajax-search-pro');
    $o = new wpdreamsYesNo($option_name, $option_desc,
        $sd[$option_name]);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $option_name = "image_bg_color";
    $option_desc = __('Image background color?', 'ajax-search-pro');
    $o = new wpdreamsColorPicker($option_name, $option_desc,
        $sd[$option_name]);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo sprintf( __('Only works if NOT the BFI Thumb library is used. You can change it on the <a href="%s">Cache Settings</a> submenu.', 'ajax-search-pro'), 'admin.php?page=asp_cache_settings' ); ?>
    </p>
</div>
<div class="item">
    <?php
    $option_name = "image_display_mode";
    $option_desc = __('Image display mode', 'ajax-search-pro');
    $o = new wpdreamsCustomSelect($option_name, $option_desc, array(
        'selects'=>array(
            array("option" => "Cover the space", "value" => "cover"),
            array("option" => "Contain the image", "value" => "contain")
        ),
        'value'=>$sd[$option_name]
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $option_name = "image_apply_content_filter";
    $option_desc = __('Execute shortcodes when looking for images in content?', 'ajax-search-pro');
    $o = new wpdreamsYesNo($option_name, $option_desc,
        $sd[$option_name]);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Will execute shortcodes and apply the content filter before looking for images in the post content.', 'ajax-search-pro'); ?><br>
        <?php echo __('If you have <strong>missing images in results</strong>, try turning ON this option. <strong>Can cause lower performance!</strong>', 'ajax-search-pro'); ?>
    </p>
</div>
<fieldset>
    <legend><?php echo __('Post Type image source options', 'ajax-search-pro'); ?></legend>
    <div class="item">
        <?php
        $option_name = "image_source1";
        $option_desc = __('Primary image source', 'ajax-search-pro');
        $o = new wpdreamsCustomSelect($option_name, $option_desc, array(
            'selects'=>$sd['image_sources'],
            'value'=>$sd[$option_name]
        ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $option_name = "image_source2";
        $option_desc = __('Alternative image source 1', 'ajax-search-pro');
        $o = new wpdreamsCustomSelect($option_name, $option_desc, array(
            'selects'=>$sd['image_sources'],
            'value'=>$sd[$option_name]
        ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $option_name = "image_source3";
        $option_desc = __('Alternative image source 2', 'ajax-search-pro');
        $o = new wpdreamsCustomSelect($option_name, $option_desc, array(
            'selects'=>$sd['image_sources'],
            'value'=>$sd[$option_name]
        ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $option_name = "image_source4";
        $option_desc = __('Alternative image source 3', 'ajax-search-pro');
        $o = new wpdreamsCustomSelect($option_name, $option_desc, array(
            'selects'=>$sd['image_sources'],
            'value'=>$sd[$option_name]
        ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $option_name = "image_source5";
        $option_desc = __('Alternative image source 4', 'ajax-search-pro');
        $o = new wpdreamsCustomSelect($option_name, $option_desc, array(
            'selects'=>$sd['image_sources'],
            'value'=>$sd[$option_name]
        ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $option_name = "image_source_featured";
        $option_desc = __('Featured image size source', 'ajax-search-pro');
        $_feat_image_sizes = get_intermediate_image_sizes();
        $feat_image_sizes = array(
            array(
                "option" => "Original size",
                'value' => "original"
            )
        );
        foreach ($_feat_image_sizes as $k => $v)
            $feat_image_sizes[] = array(
                "option" => $v,
                "value"  => $v
            );
        $o = new wpdreamsCustomSelect($option_name, $option_desc, array(
            'selects'=>$feat_image_sizes,
            'value'=>$sd[$option_name]
        ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $option_name = "image_default";
        $option_desc = __('Default image url', 'ajax-search-pro');
        $o = new wpdreamsUpload($option_name, $option_desc,
            $sd[$option_name]);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $option_name = "image_custom_field";
        $option_desc = __('Custom field containing the image', 'ajax-search-pro');
        $o = new wpdreamsText($option_name, $option_desc,
            $sd[$option_name]);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo __('Media/Attachment image options', 'ajax-search-pro'); ?></legend>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsYesNo("attachment_pdf_image", __('Generate thumbnails for PDF files?', 'ajax-search-pro'),
            $sd['attachment_pdf_image']);
        ?>
		<div class="errorMsg"><?php
			echo sprintf(__(
				'WARNING: Make sure that the Imagick library installed and configured. Please check <a href="%s">this documentation</a> for more details.',
				'ajax-search-pro'),
				"https://knowledgebase.ajaxsearchpro.com/miscellaneous/tutorials/pdf-results-thumbnails"
			);
			?></div>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo __('Taxonomy term image options', 'ajax-search-pro'); ?></legend>
    <div class="item">
        <?php
        $o = new wpdreamsText("tax_image_custom_field", __('Custom field containing the image (term meta)', 'ajax-search-pro'),
            $sd["tax_image_custom_field"]);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg"><?php echo __('This is only used, when no other image is found for the given taxonomy term.', 'ajax-search-pro') ?></div>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsUpload('tax_image_default', __('Default taxonomy image', 'ajax-search-pro'),
            $sd['tax_image_default']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo __('User image options', 'ajax-search-pro'); ?></legend>
    <div class="item item-flex-nogrow item-flex-wrap">
        <?php
        $o = new wpdreamsYesNo("user_search_display_images", __('Display user images?', 'ajax-search-pro'),
            $sd['user_search_display_images']);
        $params[$o->getName()] = $o->getData();
        $o = new wpdreamsCustomSelect("user_search_image_source", __('Image source', 'ajax-search-pro'),
            array(
                'selects' => array(
                    array('option' => __('Default', 'ajax-search-pro'), 'value' => 'default'),
                    array('option' => __('BuddyPress avatar', 'ajax-search-pro'), 'value' => 'buddypress')
                ),
                'value' => $sd['user_search_image_source']
            ));
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsUpload('user_image_default', __('Default user image', 'ajax-search-pro'),
            $sd['user_image_default']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo __('Advanced image options', 'ajax-search-pro'); ?></legend>
    <div class="item">
        <?php
        $o = new wpdreamsTextSmall('image_parser_image_number', 'Image number the parser should get from the fields',
            $sd['image_parser_image_number']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="descMsg"><?php echo __('If the image parser finds multiple images, then the image from this position is returned', 'ajax-search-pro') ?></div>
    <div class="item">
        <?php
        $o = new wpdreamsTextarea('image_parser_exclude_filenames', __('Exclude images by file names (comma separated)', 'ajax-search-pro'),
            $sd['image_parser_exclude_filenames']);
        $params[$o->getName()] = $o->getData();
        ?>
        <div class="descMsg"><?php echo __('If any part of the image filename or path contains any of the above strings, it is excluded.', 'ajax-search-pro') ?></div>
    </div>
</fieldset>