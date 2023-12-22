<?php

use WPDRMS\ASP\Query\SearchQuery;
use WPDRMS\ASP\Utils\Ajax;

if (!class_exists("wd_CPTSelect")) {
    /**
     * Class wd_CPTSelect
     *
     * Post/page/cpt select and search class.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2016, Ernest Marcinko
     */
    class wd_CPTSelect extends wpdreamsType {
        private $args = array(
            'show_parent_checkbox' => 1
        );
        private $e_data;

        public function getType() {
            parent::getType();
            $this->processData();
            ?>
            <div class='wd_cpt_select' id='wd_cpt_select-<?php echo self::$_instancenumber; ?>'>
                <fieldset>
                    <div style='margin:15px 30px;text-align: left; line-height: 45px;'>
                        <label>
                            <?php echo __('Search posts/pages/cpt:', 'ajax-search-pro'); ?>
                            <input type="hidden" class="wd_cpt_select_search_nonce" value="<?php echo wp_create_nonce( 'wd_cpt_select_search_nonce' ); ?>">
                            <input type="text" class="wd_cpt_search" placeholder="<?php echo __('Type title or ID here..', 'ajax-search-pro'); ?>"/>
                        </label>
                    </div>
                    <legend><?php echo $this->label; ?></legend>
                    <div class="draggablecontainer" id="sortablecontainer<?php echo self::$_instancenumber; ?>">
                        <div class="dragLoader hiddend"></div>
                        <p><?php echo __('Results', 'ajax-search-pro'); ?></p>
                        <ul id="sortable<?php echo self::$_instancenumber; ?>" class="connectedSortable wd_csortable<?php echo self::$_instancenumber; ?>">
                            <?php echo __('Use the search to look for posts', 'ajax-search-pro'); ?> :)
                        </ul>
                    </div>
                    <div class="sortablecontainer"><p><?php echo __('Drag here the ones you want to', 'ajax-search-pro'); ?> <span style="font-weight: bold;" class="tts_type"><?php echo $this->e_data['op_type']; ?></span>!</p>
                        <ul id="sortable_conn<?php echo self::$_instancenumber; ?>" class="connectedSortable wd_csortable<?php echo self::$_instancenumber; ?>">
                            <?php $this->printSelectedPosts(); ?>
                        </ul>
                    </div>
                    
                    <input type='hidden' value="<?php echo base64_encode(json_encode($this->args)); ?>" class="wd_args">
                    <input isparam=1 type='hidden' value="<?php echo (is_array($this->data) && isset($this->data['value'])) ? $this->data['value'] : $this->data; ?>" name='<?php echo $this->name; ?>'>
                </fieldset>
            </div>
            <?php
        }

        private function printSelectedPosts() {
            if ( count($this->e_data['ids']) > 0 ) {
                $ptypes = get_post_types(array(
                    "public" => true,
                    "_builtin" => false
                ), "names", "OR");
                $ptypes = array_diff($ptypes, array(
                        "revision", "nav_menu_item", "attachment", "acf", "wpcf7_contact_form", "dslc_templates",
                        "acf-field", "acf-group", "acf-groups"
                ));

                $items = get_posts(array(
                    'posts_per_page' => count($this->e_data['ids']),
                    'post_type' => $ptypes,
                    'post__in' => $this->e_data['ids'],
                    'post_status' => 'any'
                ));
                foreach ($items as $p) {
                    if (empty($p) || is_wp_error($p))
                        continue;
                    $checkbox = "";
                    if ($this->args['show_parent_checkbox'] == 1 && $p->post_type == 'page')
                        $checkbox = '<div class="exclude_child">' . __('Exclude direct children too?', 'ajax-search-pro') . ' <input type="checkbox" value="' . $p->ID . '"
                ' . (in_array($p->ID, $this->e_data['parent_ids']) ? ' checked="checked"' : '') . '/></div>';
                    echo '
                <li class="ui-state-default" post_id="' . $p->ID . '">' . $p->post_title . '
                    <span class="extra_info">[id: '.$p->ID.'] [' . $p->post_type . '] [' . $p->post_status . ']</span>
                    ' . $checkbox . '
                <a class="deleteIcon"></a></li>
                ';
                }
            }
        }

        public static function searchPosts() {
            if ( 
                isset($_POST['wd_phrase'], $_POST['wd_cpt_select_search_nonce']) &&
                current_user_can('administrator') && 
                wp_verify_nonce( $_POST["wd_cpt_select_search_nonce"], 'wd_cpt_select_search_nonce' ) 
            ) {
                $phrase = trim($_POST['wd_phrase']);
                $data = json_decode(base64_decode($_POST['wd_args']), true);

                $ptypes = get_post_types(array(
                    "public" => true,
                    "_builtin" => false
                ), "names", "OR");

                $exclude = array("revision", "nav_menu_item", 'peepso-post', 'peepso-comment', "acf",
                    "oembed_cache", "user_request", "wp_block", "shop_coupon", "avada_page_options",
                    "_pods_template", "_pods_pod", "_pods_field", "bp-email",
                    "lbmn_archive", "lbmn_footer", "mc4wp-form",
                    "elementor-front", "elementor-icon",
                    "fusion_template", "fusion_element", "wc_product_tab", "customize_changeset",
                    "wpcf7_contact_form", "dslc_templates", "acf-field", "acf-group", "acf-groups", "acf-field-group", "custom_css");

                $ptypes = array_diff($ptypes, $exclude);

                $asp_query = new SearchQuery(array(
                    "s" => $phrase,
                    "_ajax_search" => false,
                    'keyword_logic' => 'AND',
                    'secondary_logic' => 'OR',
                    "posts_per_page" => 20,
                    'post_type' => $ptypes,
                    'post_status' => array('publish', 'future', 'pending', 'private'),
                    'post_fields' => array(
                        'title', 'ids'
                    )
                ));

                $results = $asp_query->posts;
                Ajax::prepareHeaders();
                if ( ! empty( $results ) ) {
                    echo "Results (".count($results)."): ";
                    foreach ( $results as $p ) {
                        $checkbox = "";
                        if ($data['show_parent_checkbox'] == 1 && $p->post_type == 'page')
                            $checkbox = '<div class="exclude_child">' . __('Exclude direct children too?', 'ajax-search-pro') . ' <input type="checkbox" value="' . $p->ID . '"/></div>';
                        echo '
                        <li class="ui-state-default" post_id="' . $p->ID . '">'. $p->post_title . '
                            <span class="extra_info">[id: '.$p->ID.'] ['.$p->post_type.'] ['.$p->post_status.']</span>
                            ' . $checkbox . '
                        <a class="deleteIcon"></a></li>
                        ';
                    }
                } else {
                    echo __('No items found for term:', 'ajax-search-pro') . ' <b>' . esc_html($phrase) .'<b>';
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

if ( !has_action('wp_ajax_wd_search_cpt') )
    add_action('wp_ajax_wd_search_cpt', 'wd_CPTSelect::searchPosts');