<?php
if (!class_exists("wpdreamsFour")) {
    /**
     * Class wpdreamsFour
     *
     * Holds four separate values in four input boxes labeled: Top Bottom Right Left.
     * Good input choice for CSS padding, margin, border-width etc..
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsFour extends wpdreamsType {
        private $top, $bottom, $right, $left, $desc;

        function getType() {
            parent::getType();
            $this->processData();
            echo "
      <div class='wpdreamsFour'>
        <fieldset>
          <legend>" . $this->label . "</legend>";
            echo "
         <label>" . __('Top', 'ajax-search-pro') . "</label><input type='text' class='threedigit' name='topleft' value='" . $this->top . "' />
         <label>" . __('Bottom', 'ajax-search-pro') . "</label><input type='text' class='threedigit' name='bottomright' value='" . $this->bottom . "' />
         <label>" . __('Right', 'ajax-search-pro') . "</label><input type='text' class='threedigit' name='topright' value='" . $this->right . "' />
         <label>" . __('Left', 'ajax-search-pro') . "</label><input type='text' class='threedigit' name='bottomleft' value='" . $this->left . "' />
         <input isparam=1 type='hidden' value='" . $this->data . "' name='" . $this->name . "'>
         <div class='triggerer'></div>
         <p class='descMsg'>$this->desc</p>
        </fieldset>
      </div>";
        }

        function processData() {
            $this->desc = $this->data['desc'];
            $this->data = str_replace("\n", "", $this->data['value']);
            preg_match("/\|\|(.*?)\|\|(.*?)\|\|(.*?)\|\|(.*?)\|\|/", $this->data, $matches);
            $this->top = $matches[1];
            $this->bottom = $matches[2];
            $this->right = $matches[3];
            $this->left = $matches[4];
        }

        final function getData() {
            return $this->data;
        }

        final function getSelected() {
            return array(
                "top" => $this->top,
                "bottom" => $this->bottom,
                "right" => $this->right,
                "left" => $this->left
            );
        }
    }
}