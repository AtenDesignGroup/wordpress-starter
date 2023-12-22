<?php
if (!class_exists("wpdreamsUpload")) {
    /**
     * Class wpdreamsUpload
     *
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsUpload extends wpdreamsType {
        function getType() {
            parent::getType();
            $this->processData();
            ?>
            <div class='wpdreamsUpload' id='wpdreamsUpload<?php echo self::$_instancenumber; ?>'>
                <label for='wpdreamsUpload_input<?php echo self::$_instancenumber; ?>'>
                    <?php echo $this->label; ?>
                </label>
                <input id="wpdreamsUpload_input<?php echo self::$_instancenumber; ?>" type="text"
                       class="wdUploadText"
                       size="36" name="<?php echo $this->name; ?>"
                       value="<?php echo $this->data; ?>"/>
                <input id="wpdreamsUpload_button<?php echo self::$_instancenumber; ?>"
                       class="wdUploadButton button" type="button"
                       value="Upload"/>
            </div>
            <?php
        }


        function processData() {}

        final function getData() {
            return $this->data;
        }

        final function getSelected() {}
        final function getItems() {}
    }
}