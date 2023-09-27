<?php
if (!class_exists("wd_CPT_Editable")) {
    /**
     * Class wd_CPT_Editable
     *
     * A new custom post types selector UI element with editable titles, supporting built in post types
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2016, Ernest Marcinko
     */
    class wd_CPT_Editable extends wpdreamsType {
        private $types, $selected;

        public function getType() {
            parent::getType();
            $this->processData();
            echo "
      <div class='wd_cpt_editable' id='wd_cpt_editable-" . self::$_instancenumber . "'>
        <fieldset>
          <legend>" . $this->label . "</legend>";
            echo '<div class="sortablecontainer" id="sortablecontainer' . self::$_instancenumber . '">
            <p>' . __('Available post types', 'ajax-search-pro') . '</p><ul id="sortable' . self::$_instancenumber . '" class="connectedSortable">';
            if ($this->types != null && is_array($this->types)) {
                foreach ($this->types as $k => $v) {
                    if ($this->selected == null || !wd_in_array_r($k, $this->selected)) {
                        echo '<li class="ui-state-default ui-left" style="background: #ddd;">
              <label>' . $k . '</label>
              <input type="text" value="' . $v->labels->name . '"/>
              </li>';
                    }
                }
            }
            echo "</ul></div>";
            echo '<div class="sortablecontainer"><p>' . __('Drag here the post types you want to use!', 'ajax-search-pro') . '</p><ul id="sortable_conn' . self::$_instancenumber . '" class="connectedSortable">';
            if ($this->selected != null && is_array($this->selected)) {
                foreach ($this->selected as $k => $v) {
                    echo '<li class="ui-state-default ui-left" style="background: #ddd;">
                    <label>' . $v['post_type'] . '</label>
                    <input type="text" value="' . $v['name'] . '"/>
                    </li>';
                }
            }
            echo "</ul></div>";
            echo "
         <input isparam=1 type='hidden' value='" . $this->data . "' name='" . $this->name . "'>";
            echo "
        </fieldset>
      </div>";
        }

        public function processData() {
            // Make sure that the correct variables are used
            $this->selected = $this->decode_param($this->data);
            $this->data = $this->encode_param($this->data);

            $this->types = get_post_types(array(
                "public" => true,
                "_builtin" => false
            ), "objects", "OR");
            foreach ($this->types as $k => $v) {
                if (in_array($k, array(
                        "revision", "nav_menu_item", "attachment", "acf", "wpcf7_contact_form", "dslc_templates",
                        "acf-field", "acf-group", "acf-groups"
                ))) {
                    unset($this->types[$k]);
                    continue;
                }
            }
        }

        public final function getData() {
            return $this->data;
        }

        public final function getSelected() {
            return $this->selected;
        }
    }
}