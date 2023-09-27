<fieldset>
	<legend>
		<?php echo __('Grouping', 'ajax-search-pro'); ?>
		<span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/general-settings/grouping-title-duplicates"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
	</legend>
    <div class="errorMsg">
        <?php echo __(
            'Grouping by <strong>title</strong> means, that duplicate results matching titles are removed, and only the first match is left. This option should be used where multiple items are reffering to the same post and are not needed, such as with some <strong>Event calendar</strong> plugins, or similar.', 'ajax-search-pro'); ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("groupby_cpt_title", __('Group post type results by title instead of IDs?', 'ajax-search-pro'), $sd['groupby_cpt_title']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('This will <strong>not</strong> work with the Index Table engine.', 'ajax-search-pro'); ?>
        </p>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("groupby_term_title", __('Group term results by title instead of IDs?', 'ajax-search-pro'), $sd['groupby_term_title']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("groupby_user_title", __('Group user results by title instead of IDs?', 'ajax-search-pro'), $sd['groupby_user_title']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsYesNo("groupby_attachment_title", __('Group attachment results by title instead of IDs?', 'ajax-search-pro'), $sd['groupby_attachment_title']);
        $params[$o->getName()] = $o->getData();
        ?>
        <p class="descMsg">
            <?php echo __('This will <strong>not</strong> work with the Index Table engine.', 'ajax-search-pro'); ?>
        </p>
    </div>
</fieldset>
