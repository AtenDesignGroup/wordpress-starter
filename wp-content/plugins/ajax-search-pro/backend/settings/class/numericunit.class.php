<?php
if (!class_exists("wpdreamsNumericUnit")) {
    /**
     * Class wpdreamsNumericUnit
     *
     * Displays a numeric input box with up-down arrows.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsNumericUnit extends wpdreamsType {
        private $units, $numeric, $selected;

        function getType() {
            parent::getType();
            $this->processData();
            echo "
      <div class='wpdreamsNumericUnit'>";
            echo "<label>" . $this->label . "</label><input type='text' class='twodigit' name='numeric' value='" . $this->numeric . "' />";
            echo "<div class='wpdreams-updown'><div class='wpdreams-uparrow'></div><div class='wpdreams-downarrow'></div></div>";
            echo "<select name='units'>";
            foreach ($this->units as $key => $value) {
                echo "<option value='" . $key . "' " . (($key == $this->selected) ? 'selected=selected' : '') . ">" . $value . "</option>";
            }
            echo "</select>";

            echo "
         <input isparam=1 type='hidden' value='" . $this->data['value'] . "' name='" . $this->name . "'>
         <div class='triggerer'></div>
      </div>";
        }

        function processData() {
            $this->units = $this->data['units'];
            $this->data['value'] = str_replace("\n", "", $this->data['value']);
            preg_match("/([0-9]+)(.*)/", $this->data['value'], $matches);
            $this->numeric = $matches[1];
            $this->selected = $matches[2];
        }

        final function getData() {
            return $this->data;
        }
    }
}