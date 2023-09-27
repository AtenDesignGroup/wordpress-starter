<?php
if (!class_exists("wpdreamsLoaderSelect")) {
    /**
     * Class wpdreamsLoaderSelect
     *
     * Displays CSS loaders to select
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsLoaderSelect extends wpdreamsType {

        public static $loaders = array(
            "ball-pulse" => 3,
            "ball-grid-pulse" => 9,
            "simple-circle" => 0,
            "ball-clip-rotate" => 1,
            "ball-clip-rotate-simple" => 2,
            "ball-clip-rotate-multiple" => 2,
            "ball-rotate" => 1,
            "cube-transition" => 2,
            "ball-scale" => 1,
            "line-scale" => 5,
            "line-scale-party" => 4,
            "ball-scale-multiple" => 3,
            "ball-pulse-sync" => 3,
            "ball-beat" => 3,
            "line-scale-pulse-out" => 5,
            "line-scale-pulse-out-rapid" => 5,
            "ball-scale-ripple" => 1,
            "ball-scale-ripple-multiple" => 3,
            "ball-spin-fade-loader" => 8,
            "line-spin-fade-loader" => 8,
            "ball-grid-beat" => 9
        );
        private $selected;

        function getType() {
            parent::getType();
            $this->processData();

            wp_enqueue_style("wpdreams_loaders", ASP_URL_NP . 'css/style.loaders.css', array(), ASP_CURR_VER_STRING);

            echo "<div class='wpdreamsLoaderSelect'>";
            echo "<label class='loaderselect'>" . $this->label . "</label>";

            foreach (wpdreamsLoaderSelect::$loaders as $loader => $div_count) {
                $loader = trim($loader);
                $selected = $this->selected == $loader;
                echo "<div sel = '" . $loader . "' class='proloading asp-select-loader" . (($selected) ? '-selected' : '') . "'>
                <div class='asp_loader'>
                    <div class='asp_loader-inner asp_".$loader."'>
                ";
                    for($i=0;$i<$div_count;$i++) {
                        echo "
                            <div></div>
                        ";
                    }
                echo "</div>
                </div>
                </div>";
            }
            echo "<input isparam=1 type='hidden' class='realvalue' value='" . $this->selected . "' name='" . $this->name . "'>";
            echo "<div class='triggerer'></div>
      </div>";
        }

        function processData() {
            $this->selected = $this->data;
        }

        final function getData() {
            return $this->data;
        }

        final function getSelected() {
            return $this->selected;
        }
    }
}