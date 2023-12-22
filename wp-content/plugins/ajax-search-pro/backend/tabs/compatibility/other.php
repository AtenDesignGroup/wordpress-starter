<div class="item">
	<?php $o = new wpdreamsYesNo("rest_api_enabled", __('Enable the REST API?', 'ajax-search-pro'),
		$com_options['rest_api_enabled']
	); ?>
	<p class='descMsg'>
		<?php echo sprintf( __('Check the <a target="_blank" href="%s">REST API</a> section of the knowledge base for more info.'),
			'https://knowledgebase.ajaxsearchpro.com/other/rest-api' ); ?>
	</p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsCustomPostTypesAll("meta_box_post_types", __('Display the Ajax Search Pro meta box on these post types', 'ajax-search-pro'),
        $com_options['meta_box_post_types']);
    ?>
</div>