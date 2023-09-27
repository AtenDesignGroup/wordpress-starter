<?php
if (!class_exists("wpdreamsCustomFields")) {
    /**
     * Class wpdreamsCustomFields
     *
     * A custom field selector UI element.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsCustomFields extends wpdreamsType {
        private $selected;

        private $args = array(
            "show_pods" => false
        );

        function getType() {
            parent::getType();
            $this->processData();
            $inst = self::$_instancenumber; // Need this, as the static variable is overwritten when the callback is created

            echo "
      <div class='wpdreamsCustomFields' id='wpdreamsCustomFields-" . $inst . "'>
        <fieldset>
          <legend>" . $this->label . "</legend>";
            echo '<div class="draggablecontainer" id="draggablecontainer' . $inst . '">
            <div class="arrow-all-left"></div>
            <div class="arrow-all-right"></div><div style="margin: -3px 0 5px -5px;">';
            new wd_CFSearchCallBack('wdcfs_' . $inst, '',
                array(
                    'value' => '',
                    'args' => array(
                        'callback' => 'wd_cf_ajax_callback',
                        'show_pods' => $this->args['show_pods'],
                        'limit' => 40
                    )
                )
            );
            echo '</div><ul id="sortable' . $inst . '" class="connectedSortable">
                ' . __('Use the search bar above to look for custom fields', 'ajax-search-pro') . '
                </ul></div>
                <div class="sortablecontainer"><p>' . __('Drag here the custom fields you want to use!', 'ajax-search-pro') . '</p><ul id="sortable_conn' . $inst . '" class="connectedSortable">';
            if ($this->selected != null && is_array($this->selected)) {
                foreach ($this->selected as $k => $v) {
                    echo '<li class="ui-state-default" cf_name="' . $v . '">' . str_replace('__pods__', '[PODs] ', $v) . '<a class="deleteIcon"></a></li>';
                }
            }
            echo "</ul></div>
                  <input isparam=1 type='hidden' value='" . $this->data . "' name='" . $this->name . "'>
                  <input type='hidden' value='wpdreamsCustomFields' name='classname-" . $this->name . "'>
                </fieldset>
              </div>";
        }

        function processData() {
            if ( is_array($this->data) ) {
                $this->args = array_merge($this->args, $this->data);

                if ( isset($this->data['value']) ) {
                    // If called from back-end non-post context
                    $this->data = $this->data['value'];
                }
            }

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