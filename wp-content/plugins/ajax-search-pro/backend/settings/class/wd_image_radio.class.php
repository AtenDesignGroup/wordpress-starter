<?php
if (!class_exists("wd_imageRadio")) {
    /**
     * Class wpdreamsImageRadio
     *
     * Displays selectable images like radio buttons.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2016, Ernest Marcinko
     */
    class wd_imageRadio extends wpdreamsType {
        private $selects, $selected;

        public function getType() {
            parent::getType();
            $this->processData();
            echo "<div class='wd_imageRadio'>";
            echo "<label class='image_radio'>" . $this->label . "</label>";

            $i = 1;
            foreach ($this->selects as $k => $image) {
                $image = trim($image);
                $selected = !(strpos($k, $this->selected) === false);
                echo "
          <img num='" . $i . "' sel = '".$k."' src='" . plugins_url() . $image . "' class='image_radio" . (($selected) ? ' selected' : '') . "'/>
        ";
                $i++;
            }
            echo "<input isparam=1 type='hidden' class='realvalue' value='" . $this->selected . "' name='" . $this->name . "'>";
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