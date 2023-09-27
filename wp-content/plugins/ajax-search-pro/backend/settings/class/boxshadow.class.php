<?php
if (!class_exists("wpdreamsBoxShadow")) {
    /**
     * Class wpdreamsBoxShadow
     *
     * Creates a CSS box-shadow defining element.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2018, Ernest Marcinko
     */
    class wpdreamsBoxShadow extends wpdreamsType {
        private $color;
        private $inset;
        private $hlength;
        private $vlength;
        private $blurradius;
        private $spread;

        private $shadow_styles = array(
            array('inset', 'Inset'),
            array('', 'None')
        );

        function getType() {
            parent::getType();
            $this->processData();
            ?>
            <div class='wpdreamsBoxShadow'>
                <fieldset>
                    <legend><?php echo $this->label; ?></legend>
                    <div class="item-flex">
                        <div>
                            <label><?php echo __('Style', 'ajax-search-pro'); ?><select class='smaller _xx_inset_xx_'>
                                <?php foreach($this->shadow_styles as $option): ?>
                                    <option value="<?php echo $option[0]; ?>"<?php echo $this->inset == $option[0] ? ' selected="selected"' : ''; ?>><?php echo $option[1]; ?></option>
                                <?php endforeach; ?>
                            </select></label>
                            <?php new wpdreamsColorPickerDummy("", "Color", (isset($this->color) ? $this->color : "#000000")); ?>
                        </div>
                        <fieldset class="wpd_shadow_size">
                            <legend><?php echo __('Shadow size', 'ajax-search-pro'); ?></legend>
                            <label><?php echo __('Horizontal offset', 'ajax-search-pro'); ?><input type='text' class='twodigit _xx_hlength_xx_' value="<?php echo $this->hlength; ?>" />px</label>
                            <label><?php echo __('Vertical offset', 'ajax-search-pro'); ?><input type='text' class='twodigit _xx_vlength_xx_' value="<?php echo $this->vlength; ?>" />px</label><br>
                            <label><?php echo __('Blur radius', 'ajax-search-pro'); ?><input type='text' class='twodigit _xx_blurradius_xx_' value="<?php echo $this->blurradius; ?>" />px</label>
                            <label><?php echo __('Spread', 'ajax-search-pro'); ?><input type='text' class='twodigit _xx_spread_xx_' value="<?php echo $this->spread; ?>" />px</label>
                        </fieldset>
                    </div>
                </fieldset>
                <input isparam=1 type='hidden' value="<?php echo $this->data; ?>" name="<?php echo $this->name; ?>">
                <div class='triggerer'></div>
            </div>
            <?php
        }

        function processData() {
            $this->data = str_replace("\n", "", $this->data);
            preg_match("/box-shadow:(.*?)px (.*?)px (.*?)px (.*?)px (.*?);/", $this->data, $matches);
            $ci = $matches[5];
            preg_match("/(.*?) inset/", $ci, $_matches);
            if ($_matches != null && isset($_matches[1])) {
                $this->color = $_matches[1];
                $this->inset = "inset";
            } else {
                $this->color = $ci;
                $this->inset = "";
            }
            $this->hlength = $matches[1];
            $this->vlength = $matches[2];
            $this->blurradius = $matches[3];
            $this->spread = $matches[4];

        }

        final function getData() {
            return $this->data;
        }
    }
}