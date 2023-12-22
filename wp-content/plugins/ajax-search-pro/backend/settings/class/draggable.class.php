<?php
if (!class_exists("wpdreamsDraggable")) {
    /**
     * Class wpdreamsDraggable
     *
     * A draggable selector UI element with custom values.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsDraggable extends wpdreamsType {
        private $selects, $selected;

        function getType() {
            $verified_sel = array();

            parent::getType();
            $this->processData();
            echo "
      <div class='wpdreamsDraggable' id='wpdreamsDraggable-" . self::$_instancenumber . "'>
        <fieldset>
          <legend>" . $this->label . "</legend>";
            if (isset($this->data['description']))
                echo "<p class='descMsg'>" . $this->data['description'] . "</p>";
            echo '<div class="sortablecontainer" id="sortablecontainer' . self::$_instancenumber . '">
            <ul id="sortable' . self::$_instancenumber . '" class="connectedSortable">';
            foreach ($this->selects as $k => $v) {
                if (!in_array($k, $this->selected))
                    echo '<li class="ui-state-default" key="'.$k.'">' . $v . '</li>';
            }
            echo "</ul></div>";
            echo '<div class="sortablecontainer"><ul id="sortable_conn' . self::$_instancenumber . '" class="connectedSortable">';
            if ($this->selected != null && is_array($this->selected)) {
                foreach ($this->selected as $k => $v) {
                    if ( isset($this->selects[$v]) ) {
                        echo '<li class="ui-state-default" key="'.$v.'">' . $this->selects[$v] . '</li>';
                        $verified_sel[] = $v;
                    }
                }
            }
            echo "</ul></div>";
            echo "
         <input isparam=1 type='hidden' value='" . implode("|", $verified_sel) . "' name='" . $this->name . "'>";
            echo "
         <input type='hidden' value='wpdreamsDraggable' name='classname-" . $this->name . "'>";
            echo "
        </fieldset>
      </div>";
        }

        function processData() {
            if (is_array($this->data)) {
                if (isset($this->data['selects']) && is_array($this->data['selects']))
                    $this->selects = $this->data['selects'];
                else
                    $this->selects = array();

                if ( is_array($this->data) && isset($this->data['value']) )
                    $this->selected = explode("|", $this->data['value']);
                else
                    $this->selected = array();
            } else {
                $this->selects = array();
                $this->selected = explode("|", $this->data);
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