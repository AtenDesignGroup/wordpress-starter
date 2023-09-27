<?php
if (!class_exists("wpdreamsFontComplete")) {
    /**
     * Class wpdreamsFontComplete
     *
     * A more advanced font selector UI element with font-shadow included.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2018, Ernest Marcinko
     */
    class wpdreamsFontComplete extends wpdreamsType {

        public static $loadedFonts = array();

        private $font, $weight, $size, $lineheight, $color, $vlength, $hlength, $blurradius, $tsColor;

        private $fonts = array(
            'classic' => array("Arial, Helvetica, sans-serif","Arial Black, Gadget, sans-serif","Comic Sans MS, cursive","Courier New, Courier, monospace","Georgia, serif","Impact, Charcoal, sans-serif","Lucida Console, Monaco, monospace","Lucida Sans Unicode, Lucida Grande, sans-serif","Palatino Linotype, Book Antiqua, Palatino, serif","Tahoma, Geneva, sans-serif","Times New Roman, Times, serif","Trebuchet MS, Helvetica, sans-serif","Verdana, Geneva, sans-serif","Symbol","Webdings","Wingdings, Zapf Dingbats","MS Sans Serif, Geneva, sans-serif","MS Serif, New York, serif"),
            'google'  => array("Allan","Allerta","Allerta Stencil","Anonymous Pro","Arimo","Arvo","Bentham","Buda","Cabin","Calligraffitti","Cantarell","Cardo","Cherry Cream Soda","Chewy","Coda","Coming Soon","Copse","Corben","Cousine","Covered By Your Grace","Crafty Girls","Crimson Text","Crushed","Cuprum","Droid Sans","Droid Sans Mono","Droid Serif","Fontdiner Swanky","GFS Didot","GFS Neohellenic","Geo","Gruppo","Hanuman","Homemade Apple","IM Fell DW Pica","IM Fell DW Pica SC","IM Fell Double Pica","IM Fell Double Pica SC","IM Fell English","IM Fell English SC","IM Fell French Canon","IM Fell French Canon SC","IM Fell Great Primer","IM Fell Great Primer SC","Inconsolata","Irish Growler","Josefin Sans","Josefin Slab","Just Another Hand","Just Me Again Down Here","Kenia","Kranky","Kristi","Lato","Lekton","Lobster","Luckiest Guy","Merriweather","Molengo","Mountains of Christmas","Neucha","Neuton","Nobile","OFL Sorts Mill Goudy TT","Old Standard TT","Orbitron","Open Sans","PT Sans","PT Sans Caption","PT Sans Narrow","Permanent Marker","Philosopher","Puritan","Raleway","Reenie Beanie","Rock Salt","Schoolbell","Slackey","Sniglet","Sunshiney","Syncopate","Tangerine","Tinos","Ubuntu","UnifrakturCook","UnifrakturMaguntia","Unkempt","Vibur","Vollkorn","Walter Turncoat","Yanone Kaffeesatz")
        );


        /**
         * Helper method to be used before printing the font styles. Converts font families to apostrophed versions.
         *
         * @param $font
         * @return mixed
         */
        public static function font($font) {
            preg_match("/family:(.*?);/", $font, $fonts);
            if ( isset($fonts[1]) ) {
                $f = explode(',', str_replace(array('"', "'"), '', $fonts[1]));
                foreach ($f as &$_f) {
                    if ( trim($_f) != 'inherit' )
                        $_f = '"' . trim($_f) . '"';
                    else
                        $_f = trim($_f);
                }
                $f = implode(',', $f);
                return preg_replace("/family:(.*?);/", 'family:'.$f.';', $font);
            } else {
                return $font;
            }
        }

        function getType() {
            parent::getType();
            $this->processData();
            $inherit = $this->font == 'inherit';
            $custom_font = !$inherit && !in_array($this->font, $this->fonts['classic']) && !in_array($this->font, $this->fonts['google']) ? $this->font : '';
            ?>
            <div class='wpdreamsFontComplete'>
                <fieldset>
                    <legend><?php echo $this->label; ?></legend>
                    <div class="item-flex">
                        <div class="wd_fonts_type">
                            <?php new wpdreamsColorPickerDummy("", "", (isset($this->color) ? $this->color : "#000000")); ?>
                            <label>
                                <select class="wd_fonts_select">
                                    <option value="inherit"<?php echo $inherit ? ' selected="selected"' : ''; ?>><?php _e('inherit', 'ajax-search-pro'); ?></option>
                                    <option value="custom"<?php echo $custom_font == '' ? '' : ' selected="selected"'; ?>><?php _e('Custom Font', 'ajax-search-pro'); ?></option>
                                    <option disabled="disabled">----------- <?php _e('Classic Fonts', 'ajax-search-pro'); ?> ---------</option>
                                    <?php foreach($this->fonts['classic'] as $font): ?>
                                        <option value="<?php echo $font; ?>"
                                            <?php echo $this->font == $font ? ' selected="selected"' : ''; ?>
                                                style="font-family: <?php echo $font; ?>">
                                            <?php echo $font; ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option disabled="disabled">----------- <?php _e('Google Fonts', 'ajax-search-pro'); ?> ---------</option>
                                    <?php foreach($this->fonts['google'] as $font): ?>
                                        <option value="<?php echo $font; ?>"
                                            <?php echo $this->font == $font ? ' selected="selected"' : ''; ?>
                                                style="font-family: <?php echo $font; ?>">
                                            <?php echo $font; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <select class="wd_font_weight">
                                <option value="normal"<?php echo $this->weight == 'normal' ? ' selected="selected"' : ''; ?>>Normal</option>
                                <option value="bold"<?php echo $this->weight == 'bold' ? ' selected="selected"' : ''; ?>>Bold</option>
                            </select>
                            <label class="wd_fonts_custom hiddend">
								<?php _e('Custom font name:', 'ajax-search-pro'); ?>
                                <input type='text' class="wd_fonts_custom" value="<?php echo $custom_font; ?>">
                            </label>
                            <div class="wd_fonts_dimensions">
                                <label>
									<?php _e('Size', 'ajax-search-pro'); ?>
                                    <input type='text' class="wd_fonts_size fourdigit" value="<?php echo $this->size; ?>">
                                </label>
                                <label>
									<?php _e('Line height', 'ajax-search-pro'); ?>
                                    <input type='text' class="wd_fonts_line fourdigit" value="<?php echo $this->lineheight; ?>">
                                </label>
                                <div class="descMsg"><?php _e('With dimensions, ex.:', 'ajax-search-pro'); ?> 10em, 10px or 110%</div>
                            </div>
                        </div>
                        <fieldset class="wpd_font_shadow">
                            <legend><?php _e('Font shadow', 'ajax-search-pro'); ?></legend>
                            <label><?php _e('Vertical offset', 'ajax-search-pro'); ?><input type='text' class='twodigit _xx_hlength_xx_' value="<?php echo $this->hlength; ?>"/>px</label>
                            <label><?php _e('Horizontal offset', 'ajax-search-pro'); ?><input type='text' class='twodigit _xx_vlength_xx_' value="<?php echo $this->vlength; ?>"/>px</label><br>
                            <label><?php _e('Blur radius', 'ajax-search-pro'); ?><input type='text' class='twodigit _xx_blurradius_xx_' value="<?php echo $this->blurradius; ?>"/>px</label>
                            <?php new wpdreamsColorPickerDummy('', __('Shadow color', 'ajax-search-pro'), (isset($this->tsColor) ? $this->tsColor : "#000000")); ?>
                        </fieldset>
                    </div>
                </fieldset>
                <input type='hidden' value='wpdreamsFontComplete' name="wpdfont-<?php echo $this->name; ?>">
                <input isparam=1 type='hidden' value="<?php echo $this->data; ?>" name="<?php echo $this->name; ?>">
                <div class='triggerer'></div>
            </div>
            <?php
        }

        function processData() {
            $this->data = str_replace('\\', "", stripcslashes($this->data));
            preg_match("/family:(.*?);/", $this->data, $_fonts);
            $this->font = str_replace(array('--g--', "'", '"'), '', $_fonts[1]);
            preg_match("/weight:(.*?);/", $this->data, $_weight);
            $this->weight = $_weight[1];
            preg_match("/color:(.*?);/", $this->data, $_color);
            $this->color = $_color[1];
            preg_match("/size:(.*?);/", $this->data, $_size);
            $this->size = $_size[1];
            preg_match("/height:(.*?);/", $this->data, $_lineheight);
            $this->lineheight = $_lineheight[1];
            preg_match("/text-shadow:(.*?)px (.*?)px (.*?)px (.*?);/", $this->data, $matches);

            // Backwards compatibility
            if (is_array($matches) && isset($matches[1])) {
                $this->hlength = $matches[1];
                $this->vlength = $matches[2];
                $this->blurradius = $matches[3];
                $this->tsColor = $matches[4];
            } else {
                $this->hlength = '0';
                $this->vlength = '0';
                $this->blurradius = '0';
                $this->tsColor = 'rgba(255, 255, 255, 0)';
            }
        }

        final function getData() {
            return $this->data;
        }

        final function getImport() {
            $font = str_replace("--g--", "", trim($this->font));
            if ( $font != '' && in_array($font, $this->fonts['google']) ) {
                $font = str_replace(" ", "+", $font);
                if ( isset(self::$loadedFonts[$font]) )
                    return '';
                self::$loadedFonts[$font] = true;
                ob_start();
                ?>
                @import url(https://fonts.googleapis.com/css?family=<?php echo $font; ?>:300|<?php echo $font; ?>:400|<?php echo $font; ?>:700);
                <?php
                $out = ob_get_contents();
                ob_end_clean();
                return $out;
            } else {
                return '';
            }
        }
    }
}