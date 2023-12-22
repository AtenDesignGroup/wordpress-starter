<?php
if (!class_exists("wpdreamsTaxonomySelect")) {
    /**
     * Class wpdreamsTaxonomySelect
     *
     * A taxonomy drag and drop UI element.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsTaxonomySelect extends wpdreamsType {
        private $types, $selected, $otype, $v;
        private $exclude = array(
            'product_visibility', 'product_type'
        );

        function getType() {
            parent::getType();
            $this->processData();
            $this->types = $this->getAllTaxonomies();
            echo "
      <div class='wpdreamsTaxonomySelect' id='wpdreamsTaxonomySelect-" . self::$_instancenumber . "'>
        <fieldset>

          <legend>" . $this->label . "</legend>";
            echo '<div class="sortablecontainer" id="sortablecontainer' . self::$_instancenumber . '">
                  <div class="arrow-all-left"></div>
                  <div class="arrow-all-right"></div>
            <p>Available taxonomies</p>
            <ul id="sortable' . self::$_instancenumber . '" class="connectedSortable">';
            if ($this->types != null && is_array($this->types)) {
                foreach ($this->types as $tax) {
                    $custom_post_type = "";
                    if ( isset($tax->object_type, $tax->object_type[0]) )
                        $custom_post_type = $tax->object_type[0] . " - ";
                    if ( in_array($tax->name, $this->exclude) )
                        continue;
                    if ($this->selected == null || !wd_in_array_r($tax->name, $this->selected)) {
                        echo '<li class="ui-state-default" taxonomy="' . $tax->name . '">' . $custom_post_type . $tax->labels->name . '<span class="extra_info">[' . $tax->name . ']</span></li>';
                    }

                }
            }
            echo "</ul></div>";
            echo '<div class="sortablecontainer">' . __('Drag here the taxonomies you want to ', 'ajax-search-pro') . '<b>' . $this->otype . '</b>!</p><ul id="sortable_conn' . self::$_instancenumber . '" class="connectedSortable">';
            if ($this->selected != null && is_array($this->selected)) {
                foreach ($this->selected as $_tax) {
                    $tax = get_taxonomy( $_tax );
                    $custom_post_type = "";
                    if (isset($tax->object_type) && $tax->object_type[0] != null)
                        $custom_post_type = $tax->object_type[0] . " - ";
                    if (isset($tax->name))
                        echo '<li class="ui-state-default" taxonomy="' . $tax->name . '">' . $custom_post_type . $tax->labels->name . '<span class="extra_info">[' . $tax->name . ']</span></li>';
                }
            }
            echo "</ul></div>";
            echo "
         <input isparam=1 type='hidden' value='" . $this->data . "' name='" . $this->name . "'>
         <input type='hidden' value='wpdreamsTaxonomySelect' name='classname-" . $this->name . "'>";
            echo "
        </fieldset>
      </div>";
        }

        function getAllTaxonomies() {
            $args = array(
                '_builtin' => false
            );
            $output = 'objects'; // or objects
            $operator = 'and'; // 'and' or 'or'
            $taxonomies = get_taxonomies($args, $output, $operator);
            return $taxonomies;
        }

        function processData() {
            if (is_array($this->data) && isset($this->data['type']) && isset($this->data['value'])) {
                $this->otype = $this->data['type'];
                $this->v = str_replace("\n", "", $this->data["value"]);
                $this->data = $this->data["value"];
            } else {
                $this->otype = "include";
                $this->v = str_replace("\n", "", $this->data);
            }

            $this->selected = array();
            if ($this->v != "") {
                $this->selected = explode("|", $this->v);
            } else {
                $this->selected = null;
            }

        }

        final function getData() {
            return $this->data;
        }

        final function getSelected() {
            return $this->selected;
        }
    }
}