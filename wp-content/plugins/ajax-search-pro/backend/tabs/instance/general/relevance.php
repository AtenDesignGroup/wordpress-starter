<fieldset>
	<legend>
		<?php echo __('Relevance Options', 'ajax-search-pro'); ?>
		<span class="asp_legend_docs">
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/relevance-options"><span class="fa fa-book"></span>
                <?php echo __('Documentation', 'ajax-search-pro'); ?>
            </a>
        </span>
	</legend>
	<p class='infoMsg'>
		<?php echo __('Every result gets a relevance value based on the weight numbers set below. The weight is the measure of importance.', 'ajax-search-pro'); ?><br/>
		<?php echo __('If you wish to change the the results basic ordering, then you can do it under the <a href="#107">General Options -> Ordering</a> panel.', 'ajax-search-pro'); ?>
	</p>
	<div wd-show-on="search_engine:regular">
		<?php
		$sd_wht_def = array(
			array('option' => '10 - Highest weight', 'value' => 10),
			array('option' => '9', 'value' => 9),
			array('option' => '8', 'value' => 8),
			array('option' => '7', 'value' => 7),
			array('option' => '6', 'value' => 6),
			array('option' => '5', 'value' => 5),
			array('option' => '4', 'value' => 4),
			array('option' => '3', 'value' => 3),
			array('option' => '2', 'value' => 2),
			array('option' => '1 - Lowest weight', 'value' => 1)
		);
		?>
		<div class="item">
			<?php
			$o = new wpdreamsYesNo("userelevance", __('Use relevance?', 'ajax-search-pro'), $sd['userelevance']);
			$params[$o->getName()] = $o->getData();
			?>
		</div>
		<fieldset>
			<legend><?php echo __('Exact matches weight', 'ajax-search-pro'); ?></legend>
			<div class="item">
				<?php
				$o = new wpdreamsCustomSelect("etitleweight", __('Title weight', 'ajax-search-pro'), array('selects' => $sd_wht_def, 'value' => $sd['etitleweight']));
				$params[$o->getName()] = $o->getData();
				?>
			</div>
			<div class="item">
				<?php
				$o = new wpdreamsCustomSelect("econtentweight", __('Content weight', 'ajax-search-pro'), array('selects' => $sd_wht_def, 'value' => $sd['econtentweight']));
				$params[$o->getName()] = $o->getData();
				?>
			</div>
			<div class="item">
				<?php
				$o = new wpdreamsCustomSelect("eexcerptweight", __('Excerpt weight', 'ajax-search-pro'), array('selects' => $sd_wht_def, 'value' => $sd['eexcerptweight']));
				$params[$o->getName()] = $o->getData();
				?>
			</div>
			<div class="item">
				<?php
				$o = new wpdreamsCustomSelect("etermsweight", __('Terms weight', 'ajax-search-pro'), array('selects' => $sd_wht_def, 'value' => $sd['etermsweight']));
				$params[$o->getName()] = $o->getData();
				?>
			</div>
		</fieldset>
		<fieldset>
			<legend><?php echo __('Random matches weight', 'ajax-search-pro'); ?></legend>
			<div class="item">
				<?php
				$o = new wpdreamsCustomSelect("titleweight", __('Title weight', 'ajax-search-pro'), array('selects' => $sd_wht_def, 'value' => $sd['titleweight']));
				$params[$o->getName()] = $o->getData();
				?>
			</div>
			<div class="item">
				<?php
				$o = new wpdreamsCustomSelect("contentweight", __('Content weight', 'ajax-search-pro'), array('selects' => $sd_wht_def, 'value' => $sd['contentweight']));
				$params[$o->getName()] = $o->getData();
				?>
			</div>
			<div class="item">
				<?php
				$o = new wpdreamsCustomSelect("excerptweight", __('Excerpt weight', 'ajax-search-pro'), array('selects' => $sd_wht_def, 'value' => $sd['excerptweight']));
				$params[$o->getName()] = $o->getData();
				?>
			</div>
			<div class="item">
				<?php
				$o = new wpdreamsCustomSelect("termsweight", __('Terms weight', 'ajax-search-pro'), array('selects' => $sd_wht_def, 'value' => $sd['termsweight']));
				$params[$o->getName()] = $o->getData();
				?>
			</div>
		</fieldset>
	</div>
	<div wd-show-on="search_engine:index">
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
	</div>
</fieldset>