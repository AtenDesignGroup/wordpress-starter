<div class="item"><?php
	$_it_engine_val = isset($_POST['search_engine']) ? $_POST['search_engine'] : $sd['search_engine'];
	$o = new wpdreamsCustomSelect("search_engine", __('Search engine', 'ajax-search-pro'),
		array(
			'selects' => array(
				array('option' => 'Regular engine', 'value' => 'regular'),
				array('option' => 'Index table engine', 'value' => 'index')
			),
			'value' => $sd['search_engine']
		));
	$params[$o->getName()] = $o->getData();
	?>
	<p class="descMsg">
        <?php echo sprintf( __('Index table engine will only work if you have the <a href="%s">index table</a> generated.', 'ajax-search-pro'), get_admin_url() . 'admin.php?page=asp_index_table' ); ?>&nbsp;
        <?php echo sprintf( __('To learn more about the pros. and cons. of the index table read the <a href="%s" target="_blank">documentation about the index table</a>.', 'ajax-search-pro'), 'https://documentation.ajaxsearchpro.com/index_table.html' ); ?>
	</p>
</div>
<?php
	$it_options_visibility = $_it_engine_val == 'index' ? ' hiddend' : '';
?>
<div class="item it_engine_index_d" style="text-align: center;">
	<?php echo sprintf( __('Since you have the Index table engine selected, some options here are disabled,<br> because they are available
	on the <a href="%s" target="_blank">index table</a> options page.', 'ajax-search-pro'), get_admin_url() . "admin.php?page=asp_index_table" ); ?>
</div>
<div class="item"><?php
    $o = new wpdreamsCustomPostTypes("customtypes", __('Search in custom post types', 'ajax-search-pro'),
        $sd['customtypes']);
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("searchincomments", __('Return comments as results?', 'ajax-search-pro'),
        $sd['searchincomments']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item it_engine_index">
    <?php
    $o = new wpdreamsYesNo("searchintitle", __('Search in title?', 'ajax-search-pro'),
        $sd['searchintitle']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item it_engine_index">
    <?php
    $o = new wpdreamsYesNo("searchincontent", __('Search in content?', 'ajax-search-pro'),
        $sd['searchincontent']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item it_engine_index">
    <?php
    $o = new wpdreamsYesNo("searchinexcerpt", __('Search in post excerpts?', 'ajax-search-pro'),
        $sd['searchinexcerpt']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item it_engine_index">
    <?php
    $o = new wpdreamsYesNo("search_in_permalinks", __('Search in post permalinks?', 'ajax-search-pro'),
        $sd['search_in_permalinks']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Might not work correctly in some cases unfortunately.', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("search_in_ids", __('Search in post (and CPT) IDs?', 'ajax-search-pro'),
        $sd['search_in_ids']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item it_engine_index" style="position:relative;">
    <div class='asp-setting-search-all-cf' style="position: absolute; right: 253px; top: 18px; z-index: 1000000;">
    <?php
    $o = new wpdreamsYesNo("search_all_cf", __('Search all custom fields?', 'ajax-search-pro'),
        $sd['search_all_cf']);
    $params[$o->getName()] = $o->getData();
    ?></div>
	<div wd-enable-on="search_all_cf:0">
	<?php
    $o = new wpdreamsCustomFields("customfields", __('..or search in selected custom fields?', 'ajax-search-pro'),
        $sd['customfields']);
    $params[$o->getName()] = $o->getData();
    $params['selected-'.$o->getName()] = $o->getSelected();
    ?>
	</div>
</div>
<div class="item it_engine_index">
    <?php $o = new wpdreamsText("post_status", __('Post statuses to search', 'ajax-search-pro'), $sd['post_status']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Comma separated list. WP Defaults: publish, future, draft, pending, private, trash, auto-draft', 'ajax-search-pro'); ?>
    </p>
</div>
<div class="item it_engine_index">
    <?php $o = new wpdreamsYesNo("post_password_protected", __('Search and return password protected posts?', 'ajax-search-pro'), $sd['post_password_protected']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item it_engine_index">
    <?php
    $o = new wpdreamsYesNo("searchinterms", __('Search in terms? (categories, tags)', 'ajax-search-pro'),
        $sd['searchinterms']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Will search in terms (categories, tags) related to posts.', 'ajax-search-pro'); ?>
    </p>
    <p class="errorMsg">
        <?php echo __('WARNING: <strong>Search in terms</strong> can be database heavy operation. Not recommended for big databases.', 'ajax-search-pro'); ?>
    </p>
</div>
<script>
jQuery(function($) {
	$('select[name="search_engine"]').change(function() {
		if ($(this).val() == 'index') {
			$("#wpdreams .item.it_engine_index").css('display', 'none');
			$("#wpdreams .item.it_engine_index_d").css('display', 'block');
		} else {
			$("#wpdreams .item.it_engine_index").css('display', 'block');
			$("#wpdreams .item.it_engine_index_d").css('display', 'none');
		}
	});
	$('select[name="search_engine"]').change();
});
</script>