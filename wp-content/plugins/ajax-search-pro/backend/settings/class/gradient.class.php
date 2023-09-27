<?php
if (!class_exists("wpdreamsGradient")) {
    class wpdreamsGradient extends wpdreamsType {
        /**
         * Class wpdreamsGradient
         *
         * A simple gradient selector with two color inputs. Radial and Linear supported.
         *
         * @package  WPDreams/OptionsFramework/Classes
         * @category Class
         * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
         * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
         * @copyright Copyright (c) 2018, Ernest Marcinko
         */

        private $is_gradient, $grad_type, $leftcolor, $rightcolor, $rotation;

        function getType() {
            parent::getType();
            $this->processData();
            ?>
            <div class='wpdreamsGradient'>
                <label><?php echo esc_attr($this->label); ?></label>
                <select class='grad_bg_type'>
                    <option value='0'<?php echo $this->is_gradient == 1 ? 'selected' : ''; ?>>Solid</option>
                    <option value='1'<?php echo $this->is_gradient == 0 ? 'selected' : ''; ?>>Gradient</option>
                </select>
                <?php
                new wpdreamsColorPickerDummy('leftcolor_' . self::$_instancenumber, "", $this->leftcolor);
                new wpdreamsColorPickerDummy('rightcolor_' . self::$_instancenumber, "", $this->rightcolor);
                ?>
                <select class='grad_type'>
                    <option value='1' <?php echo $this->grad_type == 1 ? 'selected' : ''; ?>>Linear</option>
                    <option value='0' <?php echo $this->grad_type == 0 ? 'selected' : ''; ?>>Radial</option>
                </select>
                <div class="grad_ex"></div>
                <div class="grad_slider">
                    <div class="dslider" data-rotation="<?php echo esc_attr($this->rotation); ?>"></div>
                    <div class='ddisplay'>
                        <div class='dbg'></div>
                    </div>
                    <div class="dtxt"><?php echo esc_attr($this->rotation); ?></div>&#176;
                </div>

                <input isparam=1 type='hidden' class='gradient' name="<?php echo esc_attr($this->name); ?>" value="<?php echo esc_attr($this->data); ?>"/>
                <div class='triggerer'></div>
            </div>
            <?php
        }

        function processData() {
            $this->data = str_replace("\n", "", $this->data);
            if ( preg_match("/(.*?)-(.*?)-(.*?)-(.*)/", $this->data, $matches) ) {
                $this->grad_type = $matches[1];
                $this->rotation = $matches[2];
                if ($this->rotation == null || $this->rotation == '') $this->rotation = 0;
                $this->leftcolor = wpdreams_admin_hex2rgb($matches[3]);
                $this->rightcolor = wpdreams_admin_hex2rgb($matches[4]);
            } else {
                $this->grad_type = 0;
                $this->rotation = 180;
                $this->leftcolor = $this->data;
                $this->rightcolor = $this->data;
            }
            $this->data = $this->grad_type . '-' . $this->rotation . '-' . $this->leftcolor . '-' . $this->rightcolor;
        }

        final function getData() {
            return $this->data;
        }

    }
}