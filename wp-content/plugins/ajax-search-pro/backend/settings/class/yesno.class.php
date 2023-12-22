<?php
if (!class_exists("wpdreamsYesNo")) {
    /**
     * Class wpdreamsYesNo
     *
     * Displays an ON-OFF switch UI element
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsYesNo extends wpdreamsType {
        function getType() {
            parent::getType();
            echo "<div class='wpdreamsYesNo" . (($this->data == 1) ? " active" : "") . "'>";
            echo "<label for='wpdreamstext_" . self::$_instancenumber . "'>" . $this->label . "</label>";
            echo "<input isparam=1 type='hidden' value='" . $this->data . "' name='" . $this->name . "'>";
            echo "<div class='wpdreamsYesNoInner'></div>";
            echo "<div class='triggerer'></div>";
            echo "</div>";
        }
    }
}