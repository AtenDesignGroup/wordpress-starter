<?php
if (!class_exists("wd_DateInterval")) {
    /**
     * Class wd_DateInterval
     *
     * Displays a tag selector element.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2016, Ernest Marcinko
     */
    class wd_DateInterval extends wpdreamsType {
        private $raw, $selected;

        function getType() {
            parent::getType();
            $this->processData();
            ?>
            <div class="wd_DateInterval" id="wd_DateInterval-<?php echo self::$_instancenumber; ?>">
                <div class="wd_col_20"></div>
                <div class="wd_col_40" style="line-height: 33px; vertical-align: top;">
                    <select aria-label="<?php echo __('Mode', 'ajax-search-pro'); ?>" class="wd_di_mode">
                        <option value="exclude"><?php echo __('Exclude', 'ajax-search-pro'); ?></option>
                        <option value="include"><?php echo __('Include', 'ajax-search-pro'); ?></option>
                    </select>&nbsp;from:
                    <select aria-label="<?php echo __('Type', 'ajax-search-pro'); ?>" class="wd_di_from">
                        <option value="date"><?php echo __('Date', 'ajax-search-pro'); ?></option>
                        <option value="rel_date"><?php echo __('Relative Date', 'ajax-search-pro'); ?></option>
                        <option value="disabled"><?php echo __('Disabled', 'ajax-search-pro'); ?></option>
                    </select>
                    <input type='hidden' class="wd_di_fromDate"/>
                    <div class="wd_di_fromreld" style="display: inline;">
                        <input aria-label="<?php echo __('Years', 'ajax-search-pro'); ?>"
                               class="wd_di_fromyy twodigit" value=""/>&nbsp;<?php echo __('years', 'ajax-search-pro'); ?>
                        <input aria-label="<?php echo __('Months', 'ajax-search-pro'); ?>"
                               class="wd_di_frommm twodigit" value=""/>&nbsp;<?php echo __('months', 'ajax-search-pro'); ?>
                        <input aria-label="<?php echo __('Days before current date', 'ajax-search-pro'); ?>"
                               class="wd_di_fromdd twodigit" value=""/>&nbsp;<?php echo __('days before current date', 'ajax-search-pro'); ?>
                    </div>
                </div>
                <div class="wd_col_40" style="line-height: 33px; vertical-align: top;">
                    <?php echo __('to:', 'ajax-search-pro'); ?>
                    <select aria-label="<?php echo __('Type', 'ajax-search-pro'); ?>" class="wd_di_to">
                        <option value="date"><?php echo __('Date', 'ajax-search-pro'); ?></option>
                        <option value="rel_date"><?php echo __('Relative Date', 'ajax-search-pro'); ?></option>
                        <option value="disabled"><?php echo __('Disabled', 'ajax-search-pro'); ?></option>
                    </select><br>
                    <input class="wd_di_toDate"/>
                    <div class="wd_di_toreld" style="display: inline;">
                        <input aria-label="<?php echo __('Years', 'ajax-search-pro'); ?>"
                               class="wd_di_toyy twodigit" value=""/>&nbsp;<?php echo __('years', 'ajax-search-pro'); ?>
                        <input aria-label="<?php echo __('Months', 'ajax-search-pro'); ?>"
                               class="wd_di_tomm twodigit" value=""/>&nbsp;<?php echo __('months', 'ajax-search-pro'); ?>
                        <input aria-label="<?php echo __('Days before current date', 'ajax-search-pro'); ?>"
                               class="wd_di_todd twodigit" value=""/>&nbsp;<?php echo __('days before current date', 'ajax-search-pro'); ?>
                    </div>
                </div>
                <div style="clear:both;"></div>
                <input isparam=1 type="hidden" value='<?php echo $this->data; ?>' name="<?php echo $this->name; ?>">
                <input type='hidden' value='wd_DateInterval' name='classname-<?php echo $this->name; ?>'>
                <div style="clear:both;"></div>
            </div>
        <?php
        }

        function processData() {

            /**
             * Expected raw format
             *
             * [0] mode:     exclude|include
             * [1] from:     disabled|date|interval
             * [2] to:       disabled|date|interval
             * [3] fromDate: yyyy-mm-dd
             * [4] toDate:   yyyy-mm-dd
             * [5] fromInt:  0,0,0
             * [6] toInt:    0,0,0
             */
            $this->raw = explode("|", $this->data);

            if ( $this->raw[5] != "" )
                $this->raw[5] = explode(",", $this->raw[5]);
            if ( $this->raw[6] != "" )
                $this->raw[6] = explode(",", $this->raw[6]);

            $this->selected = array(
                "mode" => $this->raw[0],
                "from"  => $this->raw[1],
                "to" => $this->raw[2],
                "fromDate" => $this->raw[3],
                "toDate"=> $this->raw[4],
                "fromInt"=> $this->raw[5],
                "toInt" => $this->raw[6]
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