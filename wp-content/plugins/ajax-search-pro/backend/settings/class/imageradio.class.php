<?php
if (!class_exists("wpdreamsImageRadio")) {
    /**
     * Class wpdreamsImageRadio
     *
     * Displays selectable images like radio buttons.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsImageRadio extends wpdreamsType {
        private $selects, $selected;

        function getType() {
            parent::getType();
            $this->processData();
            echo "<div class='wpdreamsImageRadio'>";
            echo "<label class='radioimage'>" . $this->label . "</label>";

            $i = 1;
            foreach ($this->selects as $radio) {
                $radio = trim($radio);
                $selected = !(strpos($radio, $this->selected) === false);
                echo "
          <img num='" . $i . "' sel = '".$radio."' src='" . plugins_url() . $radio . "' class='radioimage" . (($selected) ? ' selected' : '') . "'/>
        ";
                $i++;
            }
            echo "<input isparam=1 type='hidden' class='realvalue' value='" . $this->selected . "' name='" . $this->name . "'>";
            //echo "<input type='hidden' value='wpdreamsImageRadio' name='classname-" . $this->name . "'>";
            echo "<div class='triggerer'></div>
      </div>";
        }

        function processData() {
            $this->selects = $this->defaultData['images'];
            $this->selected = $this->data['value'];
        }

        final function getData() {
            return $this->data;
        }

        final function getSelected() {
            return $this->selected;
        }
    }
}