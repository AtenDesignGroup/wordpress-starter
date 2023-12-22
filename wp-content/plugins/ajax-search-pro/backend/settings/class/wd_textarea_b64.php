<?php
if (!class_exists("wd_Textarea_B64")) {
    /**
     * Class wd_Textarea_B64
     *
     * A simple textarea field.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wd_Textarea_B64 extends wpdreamsType {
        private $ddata, $pdata;

        public function getType() {
            parent::getType();
            $this->pdata = "";
            $this->processData();
            echo "<label style='vertical-align: top;' for='wpdreamstextarea_" . self::$_instancenumber . "'>" . $this->label . "</label>";
            echo "<input type='hidden' name='" . $this->name . "' value='".$this->data."' />";
            echo "<textarea class='wd_textarea_b64' id='wd_textarea_b64_" . self::$_instancenumber . "'>" . stripcslashes($this->ddata) . "</textarea>";
        }
        
        public function processData() {
            $this->ddata = base64_decode($this->data);
        }
    }
}