<?php
if (!class_exists("wd_Sortable_Editable")) {
    /**
     * Class wd_Sortable_Editable
     *
     * A sortable based editable jQuery UI element
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2016, Ernest Marcinko
     */
    class wd_Sortable_Editable extends wpdreamsType {
        private $e_data = array();
        private $types;

        public function getType() {
            parent::getType();
            $this->processData();
            echo "
        <fieldset>
        <legend>".$this->label."</legend>
        <div class='wd_sortable_editable' id='wd_sortable_editable-" . self::$_instancenumber . "'>";
            echo '<div class="sortablecontainer" style="float:right;"><ul id="sortable' . self::$_instancenumber . '" class="connectedSortable">';
            foreach ($this->e_data as $k => $v) {
                echo '<li class="ui-state-default ui-left">
                        <label>' . $k . '</label><input type="text" value="' . $v . '">
                      </li>';
            }
            echo "
            </ul></div>
         <input isparam=1 type='hidden' value='" . $this->data . "' name='" . $this->name . "'>";
            ?>
         </div>
         <div class="clear"></div>
         </fieldset>
            <?php
        }

        public function processData() {
            $this->e_data = $this->decode_param($this->data);
            $this->data = $this->encode_param($this->data);
        }

        public final function getData() {
            return $this->data;
        }

        public final function getSelected() {
            return $this->types;
        }
    }
}