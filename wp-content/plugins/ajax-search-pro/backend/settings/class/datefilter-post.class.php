<?php
if (!class_exists("wd_DateFilterPost")) {
	/**
	 * Class wd_DateFilter
	 *
	 * Displays a date filter configuration element.
	 *
	 * @package  WPDreams/OptionsFramework/Classes
	 * @category Class
	 * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
	 * @link http://wp-dreams.com, http://codecanyon.net/user/wpdreams/portfolio
	 * @copyright Copyright (c) 2016, Ernest Marcinko
	 */
	class wd_DateFilterPost extends wpdreamsType {
		protected $raw, $selected;

		function getType() {
			parent::getType();
			$this->processData();
			?>
			<div class="wd_DateFilter" id="wd_DateFilter-<?php echo self::$_instancenumber; ?>">
				<div class="wd_col_40"></div>
				<div class="wd_col_60" style="line-height: 33px; vertical-align: top;">
					<label><?php echo $this->label; ?>aa</label>
					<select aria-label="<?php echo __('Date type', 'ajax-search-pro'); ?>" class="wd_di_state">
						<option value="date"><?php echo __('Date', 'ajax-search-pro'); ?></option>
						<option value="rel_date"><?php echo __('Relative Date', 'ajax-search-pro'); ?></option>
						<option value="earliest_date"><?php echo __('Earliest Post Date', 'ajax-search-pro'); ?></option>
						<option value="latest_date"><?php echo __('Latest Post Date', 'ajax-search-pro'); ?></option>
						<option value="disabled"><?php echo __('Disabled', 'ajax-search-pro'); ?></option>
					</select><br>
					<div class="wd_di_date" style="display: inline;">
						<input type="text" class="wd_di_date"/>
						<p class="descMsg">
							<?php echo __('Empty value can be used, in that case the placeholder text will appear.', 'ajax-search-pro'); ?>
						</p>
					</div>
					<div class="wd_di_rel_date" style="display: inline;">
						<input aria-label="<?php echo __('Years', 'ajax-search-pro'); ?>"
							   class="wd_di_yy twodigit" value=""/>&nbsp;<?php echo __('years', 'ajax-search-pro'); ?>
						<input aria-label="<?php echo __('Months', 'ajax-search-pro'); ?>"
							   class="wd_di_mm twodigit" value=""/>&nbsp;<?php echo __('months', 'ajax-search-pro'); ?>
						<input aria-label="<?php echo __('Days before current date', 'ajax-search-pro'); ?>"
							   class="wd_di_dd twodigit" value=""/>&nbsp;<?php echo __('days before current date', 'ajax-search-pro'); ?>
						<p class="descMsg">
							<?php echo __('It is possible to use negative values as well.', 'ajax-search-pro'); ?>
						</p>
					</div>
				</div>
				<div style="clear:both;"></div>
				<input isparam=1 type="hidden" value='<?php echo $this->data; ?>' name="<?php echo $this->name; ?>">
				<input type='hidden' value='wd_DateFilter' name='classname-<?php echo $this->name; ?>'>
				<div style="clear:both;"></div>
			</div>
			<?php
		}

		function processData() {

			/**
			 * Expected raw format
			 *
			 * [0] state:       date|rel_date|disabled
			 * [1] date:        yyyy-mm-dd
			 * [2] rel_date:    0,0,0
			 */
			$this->raw = explode("|", $this->data);

			if ( $this->raw[2] != "" )
				$this->raw[2] = explode(",", $this->raw[2]);

			$this->selected = array(
				"state" => $this->raw[0],
				"date"  => $this->raw[1],
				"rel_date" => $this->raw[2]
			);

		}

		final function getData() {
			return $this->data;
		}

		final function getSelected() {
			return $this->selected;
		}
	}
}