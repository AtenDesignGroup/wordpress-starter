<?php
if (!class_exists("wd_Post_Type_Sortalbe")) {
    /**
     * Class wd_Post_Type_Sortalbe
     *
     * A post type sortable based on jQuery UI
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2016, Ernest Marcinko
     */
    class wd_Post_Type_Sortalbe extends wpdreamsType {
        private $types;

        public function getType() {
            parent::getType();
            $this->processData();
            echo "
        <div class='wd_post_type_sortalbe' id='wd_post_type_sortalbe-" . self::$_instancenumber . "'>";
            echo '<div class="sortablecontainer" style="float:right;"><p>'.$this->label.'</p><ul id="sortable' . self::$_instancenumber . '" class="connectedSortable">';
            if (is_array($this->types)) {
                foreach ($this->types as $k => $v) {
                    echo '<li class="ui-state-default">' . $v . '</li>';
                }
            }
            echo "
            </ul></div>
         <input isparam=1 type='hidden' value='" . $this->data . "' name='" . $this->name . "'>";
            ?>
         </div>
         <div class="clear"></div>
            <?php
        }

        public function processData() {
            $this->types = $this->decode_param($this->data);
            $this->data = $this->encode_param($this->data);

            $ptypes = get_post_types(array(
                "public" => false,
                "_builtin" => false
            ), "names", "OR");

            foreach ( $ptypes as $type )
                if ( !in_array($type, $this->types) )
                    $this->types[] = $type;
        }

        public final function getData() {
            return $this->data;
        }

        public final function getSelected() {
            return $this->types;
        }
    }
}