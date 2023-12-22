<?php /** @noinspection PhpInconsistentReturnPointsInspection */
if (!class_exists("wpdreamsTermMeta")) {
    /**
     * Class wpdreamsTermMeta
     *
     * A term meta selector UI element.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsTermMeta extends wpdreamsType {
        private $types, $selected;

        function getType() {
            parent::getType();
            global $wpdb;
            $this->processData();

            // Bail if term meta table is not installed.
            if ( get_option( 'db_version' ) < 34370 ) {
                echo "<input isparam=1 type='hidden' value='' name='" . $this->name . "'>";
                return false;
            }

            $this->types = $wpdb->get_results("SELECT DISTINCT(meta_key) FROM " . $wpdb->termmeta . " LIMIT 1500", ARRAY_A);
            echo "
      <div class='wpdreamsTermMeta' id='wpdreamsTermMeta-" . self::$_instancenumber . "'>
        <fieldset>
          <legend>" . $this->label . "</legend>";
            echo '<div class="sortablecontainer" id="sortablecontainer' . self::$_instancenumber . '">
            <div class="arrow-all-left"></div>
            <div class="arrow-all-right"></div>
            <p>' . __('Available term meta keys', 'ajax-search-pro') . '</p><ul id="sortable' . self::$_instancenumber . '" class="connectedSortable">';
            if ($this->types != null && is_array($this->types)) {
                foreach ($this->types as $k => $v) {
                    if ($this->selected == null || !in_array($v['meta_key'], $this->selected)) {
                        echo '<li class="ui-state-default">' . $v['meta_key'] . '</li>';
                    }
                }
            }
            echo "</ul></div>";
            echo '<div class="sortablecontainer"><p>' . __('Drag here the meta keys you want to use!', 'ajax-search-pro') . '</p><ul id="sortable_conn' . self::$_instancenumber . '" class="connectedSortable">';
            if ($this->selected != null && is_array($this->selected)) {
                foreach ($this->selected as $k => $v) {
                    echo '<li class="ui-state-default">' . $v . '</li>';
                }
            }
            echo "</ul></div>";
            echo "
         <input isparam=1 type='hidden' value='" . $this->data . "' name='" . $this->name . "'>";
            echo "
         <input type='hidden' value='wpdreamsTermMeta' name='classname-" . $this->name . "'>";
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
    }
}