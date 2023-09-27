<?php

use WPDRMS\ASP\Utils\Ajax;

if (!class_exists("wd_UserSelect")) {
    /**
     * Class wd_UserSelect
     *
     * A new multi-purpose, flexible user-select class that includes built-in types as well.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2016, Ernest Marcinko
     */
    class wd_UserSelect extends wpdreamsType {
        private $args = array(
            "show_type" => 0,
            "show_checkboxes" => 0,
            "show_all_users_option" => 1
        );
        private $e_data;

        public function getType() {
            parent::getType();
            $this->processData();
            ?>
            <div class='wd_userselect' id='wd_userselect-<?php echo self::$_instancenumber; ?>'>
                <fieldset>
                    <div style='margin:15px 30px;text-align: left; line-height: 45px;'>
                        <label>
                            <?php echo __('Search users:', 'ajax-search-pro'); ?>
                            <input type="text" class="wd_user_search" placeholder="<?php echo __('Type here..', 'ajax-search-pro'); ?>"/>
                        </label>
                        <label<?php echo ($this->args["show_type"] == 1) ? '' :  ' class="hiddend"'; ?>>
                            <?php echo __('Operation:', 'ajax-search-pro'); ?>
                            <select class="tts_operation">
                                <option value="include"<?php echo $this->e_data['op_type'] == "include" ? ' selected="selected"' : ''; ?>><?php echo __('Include', 'ajax-search-pro'); ?></option>
                                <option value="exclude"<?php echo $this->e_data['op_type'] == "exclude" ? ' selected="selected"' : ''; ?>><?php echo __('Exclude', 'ajax-search-pro'); ?></option>
                            </select>
                        </label>
                    </div>
                    <legend><?php echo $this->label; ?></legend>
                    <div class="draggablecontainer" id="sortablecontainer<?php echo self::$_instancenumber; ?>">
                        <div class="dragLoader hiddend"></div>
                        <p><?php echo __('User Results', 'ajax-search-pro'); ?></p>
                        <ul id="sortable<?php echo self::$_instancenumber; ?>" class="connectedSortable wd_csortable<?php echo self::$_instancenumber; ?>">
                            <?php if ($this->args['show_all_users_option'] == 1): ?>
                            <li class="ui-state-default"  user_id="-1"><?php echo __('All users', 'ajax-search-pro'); ?><a class="deleteIcon"></a></li>
                            <?php endif; ?>
                            <li class="ui-state-default"  user_id="0"><?php echo __('Anonymous user (no user)', 'ajax-search-pro'); ?><a class="deleteIcon"></a></li>
                            <li class="ui-state-default"  user_id="-2"><?php echo __('Current logged in user', 'ajax-search-pro'); ?><a class="deleteIcon"></a></li>
                            <?php echo __('Use the search to look for users :)', 'ajax-search-pro'); ?>
                        </ul>
                    </div>
                    <div class="sortablecontainer"><p><?php echo __('Drag here the ones you want to', 'ajax-search-pro'); ?> <span style="font-weight: bold;" class="tts_type"><?php echo $this->e_data['op_type']; ?></span>!</p>
                        <ul id="sortable_conn<?php echo self::$_instancenumber; ?>" class="connectedSortable wd_csortable<?php echo self::$_instancenumber; ?>">
                            <?php $this->printSelectedUsers(); ?>
                        </ul>
                    </div>
                    <input type="hidden" class="wd_user_select_nonce" value="<?php echo wp_create_nonce( 'wd_user_select_nonce' ); ?>">
                    <input type='hidden' value="<?php echo base64_encode(json_encode($this->args)); ?>" class="wd_args">
                    <input isparam=1 type='hidden' value="<?php echo (is_array($this->data) && isset($this->data['value'])) ? $this->data['value'] : $this->data; ?>" name='<?php echo $this->name; ?>'>
                </fieldset>
            </div>
            <?php
        }

        private function printSelectedUsers() {
            foreach($this->e_data['users'] as $u) {
                switch ($u) {
                    case -1:
                        echo '<li class="ui-state-default termlevel-0"  user_id="-1">' . __('All users', 'ajax-search-pro') . '</b><a class="deleteIcon"></a></li>';
                        break;
                    case 0:
                        echo '<li class="ui-state-default"  user_id="0">' . __('Anonymous user (no user)', 'ajax-search-pro') . '</b><a class="deleteIcon"></a></li>';
                        break;
                    case -2:
                        echo '<li class="ui-state-default"  user_id="-2">' . __('Current logged in user', 'ajax-search-pro') . '</b><a class="deleteIcon"></a></li>';
                        break;
                    default:
                        $user = get_user_by("ID", $u);
                        if (empty($user) || is_wp_error($user))
                            break;
                        $checkbox = "";
                        if ($this->args['show_checkboxes'] == 1)
                            $checkbox = '<input style="float:left;" type="checkbox" value="' . $user->ID . '"
                        ' . (!in_array($user->ID, $this->e_data['un_checked']) ? ' checked="checked"' : '') . '/>';
                        echo '
                        <li class="ui-state-default" user_id="' . $user->ID . '">' . $user->user_login . ' ('.$user->display_name.')
                            ' . $checkbox . '
                        <a class="deleteIcon"></a></li>';
                        break;
                }
            }
        }

        public static function searchUsers() {
            if ( 
                isset($_POST['wd_phrase'], $_POST['wd_user_select_nonce']) &&
                current_user_can('administrator') && 
                wp_verify_nonce( $_POST["wd_user_select_nonce"], 'wd_user_select_nonce' ) 
            ) {
                $phrase = trim($_POST['wd_phrase']);
                $data = json_decode(base64_decode($_POST['wd_args']), true);
                $user_query = new WP_User_Query( array( 'search' => "*" . $phrase . "*", "number" => 100 ) );

                Ajax::prepareHeaders();
                if ( $data['show_all_users_option'] == 1 )
                    echo '<li class="ui-state-default termlevel-0"  user_id="-1">' . __('All users', 'ajax-search-pro') . '</b><a class="deleteIcon"></a></li>';
                echo '<li class="ui-state-default"  user_id="0">' . __('Anonymous user (no user)', 'ajax-search-pro') . '</b><a class="deleteIcon"></a></li>
                    <li class="ui-state-default"  user_id="-2">' . __('Current logged in user', 'ajax-search-pro') . '</b><a class="deleteIcon"></a></li>';

                // User Loop
                $user_results = $user_query->get_results();
                if ( ! empty( $user_results ) ) {
                    echo "Or select users:";
                    foreach ( $user_results as $user ) {
                        $checkbox = "";
                        if ($data['show_checkboxes'] == 1)
                            $checkbox = '<input style="float:left;" type="checkbox" value="' . $user->ID . '" checked="checked"/>';
                        echo '
                        <li class="ui-state-default" user_id="' . $user->ID . '">' . $user->user_login . ' ('.$user->display_name.')
                            '.$checkbox.'
                        <a class="deleteIcon"></a></li>
                    ';
                    }
                } else {
                    echo __('No users found for term:', 'ajax-search-pro') . ' <b>' . esc_html($phrase) .'</b>';
                }
            }
            die();
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

if ( !has_action('wp_ajax_wd_search_users') )
    add_action('wp_ajax_wd_search_users', 'wd_UserSelect::searchUsers');