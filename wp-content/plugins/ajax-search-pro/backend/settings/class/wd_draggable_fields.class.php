<?php
if (!class_exists("wd_DraggableFields")) {
    /**
     * Class wd_DraggableFields
     *
     * A new multi-purpose, flexible abstract field editor - witch checkboxes and display mode option
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2016, Ernest Marcinko
     */
    class wd_DraggableFields extends wpdreamsType {
        private $e_data;
        private $args = array(
            "show_checkboxes" => 0,             // Checkboxes for default states
            "show_display_mode" => 0,           // Display mode option
            "show_labels" => 0,                 // Display inputs to edit the labels
            "show_required" => 0,
            /**
             * It's more logical to add the fields via arguments, because it can be extended - as opposing to
             * options, which are stored permanently after saving.
             */
            'fields' => array(
                /*'field1'     => 'Field label 1',
                'field2'     => 'Field label 2'*/
            ),
            'checked' => array()     // true|array() Which ones are checked by default on drag?
        );

        private $default_options = array(
            'display_mode' => 'checkboxes', // checkboxes, dropdown, radio
            'selected' => array('exact', 'title', 'content', 'excerpt', 'comments'),
            'required' => false,
            'invalid_input_text' => 'Please select an option!',
            'unselected' => array(),
            /**
             * This only contains the items from the selected array, for the front-end,
             * because of the labels - as the $args is not available on the front-end,
             * and somehow we need to display the labels, do we?
             * (this is calculated every time, but a default value is needed)
             */
            'labels' => array(
                'field1'     => 'Field label 1',
                'field2'     => 'Field label 2'
            ),
            'checked' => array('exact', 'title', 'content', 'excerpt', 'comments')
        );

        public function getType() {
            parent::getType();
            $this->processData();
            ?>
            <div class='wd_DraggableFields' id='wd_DraggableFields-<?php echo self::$_instancenumber; ?>'>
                <fieldset>
                    <legend><?php echo $this->label; ?></legend>

                    <?php if ($this->args['show_display_mode']): ?>
                        <div class="wd_df_display_mode_container"><label>
                                <?php echo __('Display mode:', 'ajax-search-pro'); ?>
                                <select class="wd_df_display_mode" id="wd_df_display_mode-<?php echo self::$_instancenumber; ?>">
                                <option value="checkboxes"<?php echo $this->e_data['display_mode'] == 'checkboxes' ? ' selected="selected"' : '';?>>
                                    <?php echo __('Checkboxes', 'ajax-search-pro'); ?>
                                </option>
                                <option value="dropdown"<?php echo $this->e_data['display_mode'] == 'dropdown' ? ' selected="selected"' : '';?>>
                                    <?php echo __('Drop-down', 'ajax-search-pro'); ?>
                                </option>
                                <option value="radio"<?php echo $this->e_data['display_mode'] == 'radio' ? ' selected="selected"' : '';?>>
                                    <?php echo __('Radio buttons', 'ajax-search-pro'); ?>
                                </option>
                            </select>
                        </label></div>
                    <?php endif; ?>
                    <?php if ($this->args['show_required']): ?>
                        <div class="wd_df_required_container">
                            <label>
                                <?php echo __('Required?', 'ajax-search-pro'); ?>
                                <input type="checkbox" class="wd_df_required" <?php echo $this->e_data['required'] ? " checked=checked" :''; ?>>
                            </label>
                            <label>
                                <?php echo __('Required popup text:', 'ajax-search-pro'); ?>
                                <input type="text" class="wd_df_invalid_input_text" value="<?php echo esc_attr($this->e_data['invalid_input_text']); ?>">
                            </label>
                        </div>
                    <?php endif; ?>
                    <div class="draggablecontainer" id="sortablecontainer<?php echo self::$_instancenumber; ?>">
                        <p>&nbsp;</p>
                        <ul style='text-align:left;' id="sortable<?php echo self::$_instancenumber; ?>" class="connectedSortable wd_csortable<?php echo self::$_instancenumber; ?>">
                            <?php $this->printAvailableFields(); ?>
                        </ul>
                    </div>
                    <div class="sortablecontainer"><p><?php echo __('Drag here the terms you want to include!', 'ajax-search-pro'); ?></p>
                        <ul style='text-align:left;' id="sortable_conn<?php echo self::$_instancenumber; ?>" class="connectedSortable wd_csortable<?php echo self::$_instancenumber; ?>">
                            <?php $this->printSelectedFields(); ?>
                        </ul>
                    </div>
                    <input type='hidden' value="<?php echo base64_encode(json_encode($this->args)); ?>" class="wd_args">
                    <input isparam=1 type='hidden' value="<?php echo (is_array($this->data) && isset($this->data['value'])) ? $this->data['value'] : $this->data; ?>" name='<?php echo $this->name; ?>'>
                </fieldset>
            </div>
            <?php
        }

        public function printAvailableFields() {
            foreach ($this->args['fields'] as $k=>$field) {
                $checkbox = '';
                if ($this->args['show_checkboxes'] == 1) {
                    if ( $this->args['checked'] === true || in_array($k, $this->args['checked']) )
                        $_c = ' checked="checked"';
                    else
                        $_c = '';
                    $checkbox = '<label class="wd_plain">' . __('Checked?', 'ajax-search-pro') . ' <input style="float:left;" type="checkbox" value="' . $k . '"'.$_c.'/></label>';
                }
                if ($this->args['show_labels'] == 1)
                    $input = '<label>'.$this->args['fields'][$k].'</label><input type="text" value="'.$this->args['fields'][$k].'"/>';
                else
                    $input = $this->args['fields'][$k];
                if ( in_array($k, $this->e_data['selected']) )
                    $disabled = ' ui-state-disabled';
                else
                    $disabled = '';
                echo '<li class="ui-state-default'.$disabled.'" field="'.$k.'">
                        <span class="wd_drag_visible">'.$this->args['fields'][$k].'</span>
                        '.$input.$checkbox.'
                        <a class="deleteIcon wd_icon_absolute"></a>
                      </li>';
            }
        }

        public function printSelectedFields() {
            foreach ($this->e_data['selected'] as $field) {
                $checkbox = '';
                if ($this->args['show_labels'] == 1)
                    $input = '<label>'.$this->args['fields'][$field].'</label><input type="text" value="'.$this->e_data['labels'][$field].'">';
                else
                    $input = $this->args['fields'][$field];
                if ($this->args['show_checkboxes'] == 1)
                    $checkbox = '<label class="wd_plain">' . __('Checked?', 'ajax-search-pro') . ' <input style="float:left;" type="checkbox" value="' . $field . '"
                    ' . (in_array($field, $this->e_data['checked']) ? ' checked="checked"' : '') . '/></label>';
                echo '<li class="ui-state-default" field="'.$field.'">
                        '.$input.$checkbox.'
                        <a class="deleteIcon wd_icon_absolute"></a>
                      </li>';
            }
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
            // All keys are strings in array, merge it with defaults to override
            $this->e_data = array_merge($this->default_options, $this->e_data);
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