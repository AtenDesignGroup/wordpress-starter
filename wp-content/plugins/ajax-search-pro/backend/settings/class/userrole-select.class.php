<?php
if (!class_exists("wpdreamsUserRoleSelect")) {
    /**
     * Class wpdreamsUsermetaSelect
     *
     * A user meta selector UI element.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/wpdreams/portfolio
     * @copyright Copyright (c) 2015, Ernest Marcinko
     */
    class wpdreamsUserRoleSelect extends wpdreamsType {
        private $selected, $types;

        function getType() {
            parent::getType();
            $this->processData();
            $this->types = $this->getEditableRoles();
            echo "
      <div class='wpdreamsUserRoleSelect' id='wpdreamsUserRoleSelect-" . self::$_instancenumber . "'>
        <fieldset>
          <legend>" . $this->label . "</legend>";
            echo '<div class="sortablecontainer" id="sortablecontainer' . self::$_instancenumber . '">
            <div class="arrow-all-left"></div>
            <div class="arrow-all-right"></div>
            <p>' . __('Available user roles', 'ajax-search-pro') . '</p><ul id="sortable' . self::$_instancenumber . '" class="connectedSortable">';
            if ($this->types != null && is_array($this->types)) {
                foreach ($this->types as $_role => $_data) {
                    if ($this->selected == null || !in_array($_role, $this->selected)) {
                        echo '<li class="ui-state-default">' . $_role . '</li>';
                    }
                }
            }
            echo "</ul></div>";
            echo '<div class="sortablecontainer"><p>' . __('Drag here the user roles you want to exclude!', 'ajax-search-pro') . '</p><ul id="sortable_conn' . self::$_instancenumber . '" class="connectedSortable">';
            if ($this->selected != null && is_array($this->selected)) {
                foreach ($this->selected as $k => $v) {
                    echo '<li class="ui-state-default">' . $v . '</li>';
                }
            }
            echo "</ul></div>";
            echo "
         <input isparam=1 type='hidden' value='" . $this->data . "' name='" . $this->name . "'>";
            echo "
         <input type='hidden' value='wpdreamsUserRoleSelect' name='classname-" . $this->name . "'>";
            echo "
        </fieldset>
      </div>";
        }

        function processData() {
            $this->data = str_replace("\n", "", $this->data);
            if ($this->data != "")
                $this->selected = explode("|", $this->data);
            else
                $this->selected = null;
        }

        final function getData() {
            return $this->data;
        }

        final function getSelected() {
            return $this->selected;
        }

        function getEditableRoles() {
            global $wp_roles;
            return $wp_roles->roles;
        }
    }
}