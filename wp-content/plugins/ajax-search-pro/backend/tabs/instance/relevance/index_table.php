<fieldset>
    <legend>
        <?php echo __('Matches weight', 'ajax-search-pro'); ?>
        <span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/relevance-options"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
    </legend>
    <p class='infoMsg'>
        <?php echo __('Please use numbers between <b>0 - 500</b>', 'ajax-search-pro'); ?>
    </p>
    <div class="item">
        <?php
        $o = new wpdreamsTextSmall("it_title_weight", __('Title weight', 'ajax-search-pro'), $sd['it_title_weight']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsTextSmall("it_content_weight", __('Content weight', 'ajax-search-pro'), $sd['it_content_weight']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsTextSmall("it_excerpt_weight", __('Excerpt weight', 'ajax-search-pro'), $sd['it_excerpt_weight']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsTextSmall("it_terms_weight", __('Terms weight', 'ajax-search-pro'), $sd['it_terms_weight']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsTextSmall("it_cf_weight", __('Custom fields weight', 'ajax-search-pro'), $sd['it_cf_weight']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
    <div class="item">
        <?php
        $o = new wpdreamsTextSmall("it_author_weight", __('Author weight', 'ajax-search-pro'), $sd['it_author_weight']);
        $params[$o->getName()] = $o->getData();
        ?>
    </div>
</fieldset>