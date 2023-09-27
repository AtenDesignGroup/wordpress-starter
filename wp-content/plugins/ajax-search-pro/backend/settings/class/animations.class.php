<?php
if (!class_exists("wpdreamsAnimations")) {
    /**
     * Class wpdreamsAnimations
     *
     * Creates an animation selector. The animation declarations can be found in the css/animations.css file.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2012, Ernest Marcinko
     */
    class wpdreamsAnimations extends wpdreamsType {
        private $animations, $selected;

        function getType() {
            parent::getType();
            $this->processData();
            echo "<div class='wpdreamsAnimations'>";
            echo "<label for='wpdreamsAnimations_" . self::$_instancenumber . "'>" . $this->label . "</label>";
            echo "<select isparam=1 class='wpdreamsanimationselect' id='wpdreamsanimationselect_" . self::$_instancenumber . "' name='" . $this->name . "'>";
            foreach ($this->animations as $key => $animation) {
                if (strpos($key, 'groupstart') !== false) {
                    echo "<optgroup label='" . $animation . "'>";
                    continue;
                }
                if (strpos($key, 'groupend') !== false) {
                    echo "</optgroup>";
                    continue;
                }
                if ($animation == $this->selected)
                    echo "<option value='" . $key . "' selected='selected'>" . $animation . "</option>";
                else
                    echo "<option value='" . $key . "'>" . $animation . "</option>";
            }
            echo "</select>";
            echo "<span>" . __('Hi there!', 'ajax-search-pro') . "</span>";
            echo "<div class='triggerer'></div>
      </div>";
        }

        function processData() {
            $this->data = str_replace("\n", "", $this->data);
            $this->selected = $this->data;

            $this->animations = array(
                "voidanim" => "No animation",
                "groupstart 1" => "Simple",
                "flash" => "flash",
                "bounce" => "bounce",
                "shake" => "shake",
                "tada" => "tada",
                "swing" => "swing",
                "wobble" => "wobble",
                "pulse" => "pulse",
                "groupend 1" => "x",
                "groupstart 2" => "Flippin",
                "flip" => "flip",
                "flipInX" => "flipInX",
                "flipOutX" => "flipOutX",
                "flipInY" => "flipInY",
                "flipOutY" => "flipOutY",
                "groupend 2" => "x",
                "groupstart 3" => "Fade in",
                "fadeIn" => "fadeIn",
                "fadeInUp" => "fadeInUp",
                "fadeInDown" => "fadeInDown",
                "fadeInLeft" => "fadeInLeft",
                "fadeInRight" => "fadeInRight",
                "fadeInUpBig" => "fadeInUpBig",
                "fadeInDownBig" => "fadeInDownBig",
                "fadeInLeftBig" => "fadeInLeftBig",
                "fadeInRightBig" => "fadeInRightBig",
                "groupend 3" => "x",
                "groupstart 4" => "Fade Out",
                "fadeOut" => "fadeOut",
                "fadeOutUp" => "fadeOutUp",
                "fadeOutDown" => "fadeOutDown",
                "fadeOutLeft" => "fadeOutLeft",
                "fadeOutRight" => "fadeOutUpBig",
                "fadeOutDownBig" => "fadeOutDownBig",
                "fadeOutLeftBig" => "fadeOutLeftBig",
                "fadeOutRightBig" => "fadeOutRightBig",
                "groupend 4" => "x",
                "groupstart 5" => "Bounce In",
                "bounceIn" => "bounceIn",
                "bounceInDown" => "bounceInDown",
                "bounceInUp" => "bounceInUp",
                "bounceInLeft" => "bounceInLeft",
                "bounceInRight" => "bounceInRight",
                "groupend 5" => "x",
                "groupstart 6" => "Bounce Out",
                "bounceOut" => "bounceOut",
                "bounceOutDown" => "bounceOutDown",
                "bounceOutUp" => "bounceOutUp",
                "bounceOutLeft" => "bounceOutLeft",
                "bounceOutRight" => "bounceOutRight",
                "groupend 6" => "x",
                "groupstart 7" => "Rotate in",
                "rotateIn" => "rotateIn",
                "rotateInDownLeft" => "rotateInDownLeft",
                "rotateInDownRight" => "rotateInDownRight",
                "rotateInUpLeft" => "rotateInUpLeft",
                "rotateInUpRight" => "rotateInUpRight",
                "groupend 7" => "x",
                "groupstart 8" => "Rotate Out",
                "rotateOut" => "rotateOut",
                "rotateOutDownLeft" => "rotateOutDownLeft",
                "rotateOutDownRight" => "rotateOutDownRight",
                "rotateOutUpLeft" => "rotateOutUpLeft",
                "rotateOutUpRight" => "rotateOutUpRight",
                "groupend 8" => "x",
                "groupstart 9" => "LightSpeed",
                "lightSpeedIn" => "lightSpeedIn",
                "lightSpeedOut" => "lightSpeedOut",
                "groupend" => "x",
                "groupstart" => "Special",
                "hinge" => "hinge",
                "rollIn" => "rollIn",
                "rollOut" => "rollOut",
                "groupend 9" => "x"
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