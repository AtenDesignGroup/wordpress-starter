<?php
if (!class_exists("wd_UserMeta")) {
    /**
     * Class wd_UserMeta
     *
     * User meta search and select element
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/wpdreams/portfolio
     * @copyright Copyright (c) 2017, Ernest Marcinko
     */
    class wd_UserMeta extends wpdreamsType {
        private $args = array();
        private $e_data;

        function getType()
        {
            parent::getType();
            $this->processData();
            $inst = self::$_instancenumber; // Need this, as the static variable is overwritten when the callback is created

            ?>
            <div class='wd_UserMeta' id="wd_UserMeta-<?php echo $inst; ?>">
                <fieldset>
                <legend><?php echo $this->label; ?></legend>
                <div class="draggablecontainer" id="draggablecontainer<?php echo $inst; ?>">
                <div class="arrow-all-left"></div>
                <div class="arrow-all-right"></div><div style="margin: -3px 0 5px -5px;">
            <?php
            new wd_CFSearchCallBack('wdcfs_' . $inst, '',
                array(
                    'value' => '',
                    'args' => array(
                        'callback' => 'wd_um_ajax_callback',
                        'limit' => 20,
                        'usermeta' => 1
                    )
                )
            );
            ?>
            </div><ul id="sortable<?php echo $inst; ?>" class="connectedSortable">
                <?php echo __('Use the search bar above to look for user meta fields', 'ajax-search-pro'); ?> :)
                </ul></div>
                <div class="sortablecontainer">
                    <p><?php echo __('Drag here the user meta fields you want to use!', 'ajax-search-pro'); ?></p>
                    <ul id="sortable_conn<?php echo $inst; ?>" class="connectedSortable">
                <?php
                if ($this->e_data != null && is_array($this->e_data)) {
                    foreach ($this->e_data as $k => $v) {
                        echo '<li class="ui-state-default" cf_name="' . $v . '">' . $v . '<a class="deleteIcon"></a></li>';
                    }
                }
                ?>
                    </ul></div>
                    <input type='hidden' value="<?php echo base64_encode(json_encode($this->args)); ?>" class="wd_args">
                    <input isparam=1 type='hidden' value="<?php echo (is_array($this->data) && isset($this->data['value'])) ? $this->data['value'] : $this->data; ?>" name="<?php echo $this->name; ?>">
                </fieldset>
              </div>
            <?php
        }

        public function processData() {
            // Get the args first if exists
            if ( is_array($this->data) && isset($this->data['args']) )
                $this->args = array_merge($this->args, $this->data['args']);

            if ( is_array($this->data) && isset($this->data['value']) ) {
                // If called from back-end non-post context
                $this->e_data = $this->decode_param($this->data['value']);
                $this->data = $this->encode_param($this->data['value']);
            } else {
                // POST method or something else
                $this->e_data = $this->decode_param($this->data);
                $this->data = $this->encode_param($this->data);
            }
            /**
             * At this point the  this->data variable surely contains the encoded data, no matter what.
             */
        }

        public final function getData() {
            return $this->data;
        }

        public final function getSelected() {
            return $this->e_data;
        }
    }
}