<?php
if (!class_exists("wpdreamsHidden")) {
    /**
     * Class wpdreamsHidden
     *
     * Just a hidden input field.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsHidden extends wpdreamsType {
        function getType() {
            echo "<input type='hidden' id='wpdreamshidden_" . self::$_instancenumber . "' name='" . $this->name . "' value='" . $this->data . "' />";
        }
    }
}