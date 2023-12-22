<div class="item">
    <?php
    $o = new wpdreamsYesNo("frontend_show_suggestions", __('Show the Suggested phrases?', 'ajax-search-pro'), $sd['frontend_show_suggestions']);
    $params[$o->getName()] = $o->getData();
    ?>
    <p class="descMsg">
        <?php echo __('Will show the "Try these" as seen on the demo.', 'ajax-search-pro'); ?>
    </p>
</div>
<div wd-disable-on="frontend_show_suggestions:0">
	<div class="item item-flex-nogrow item-conditional">
		<?php
		$o = new wpdreamsText("frontend_suggestions_text", __('Suggestion text', 'ajax-search-pro'), $sd['frontend_suggestions_text']);
		$params[$o->getName()] = $o->getData();
		?>
		<?php
		$o = new wpdreamsColorPicker("frontend_suggestions_text_color", __(' color ', 'ajax-search-pro'), $sd['frontend_suggestions_text_color']);
		$params[$o->getName()] = $o->getData();
		?>
	</div>
	<div class="item">
		<?php
		$o = new wd_TextareaExpandable("frontend_suggestions_keywords", __('Keywords', 'ajax-search-pro'), $sd['frontend_suggestions_keywords']);
		$params[$o->getName()] = $o->getData();
		?>
		<p class="descMsg">
			<?php echo __('Comma separated!', 'ajax-search-pro'); ?>
		</p>
	</div>
	<div class="item">
		<?php
		$o = new wpdreamsColorPicker("frontend_suggestions_keywords_color", __('Keywords color ', 'ajax-search-pro'), $sd['frontend_suggestions_keywords_color']);
		$params[$o->getName()] = $o->getData();
		?>
	</div>
</div>