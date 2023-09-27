<?php

use WPDRMS\ASP\Query\SearchQuery;
use WPDRMS\ASP\Utils\Ajax;

if (!class_exists("wd_CPTSearchCallBack")) {
    /**
     * Class wd_CPTSearchCallBack
     *
     * Custom post type search for both post meta and user meta tables, which passes the results to a JS callback method
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2016, Ernest Marcinko
     */
    class wd_CPTSearchCallBack extends wpdreamsType {
        private static $delimiter = '!!!CPTRES!!!';
        private $post_title = '';
        private $args = array(
            'callback' => '',       // javacsript function name in the windows scope | if empty, shows results
            'placeholder' => 'Search in post types..',
            'search_values' => 0,
            'limit' => 10,
            'delimiter' => '!!!CPTRES!!!',
            'controls_position' => 'right',
            'class' => ''
        );

        public function getType() {
            parent::getType();
            $this->processData();
            $this->args['delimiter'] = self::$delimiter;
            ?>
            <div class='wd_cpt_search<?php echo $this->args['class'] != '' ? ' '.$this->args['class'] : "";?>'
                 id='wd_cpt_search-<?php echo self::$_instancenumber; ?>'>
                <label for='wd_cpt_search-input-<?php echo self::$_instancenumber; ?>'><?php echo $this->label; ?></label>
                <?php if ($this->args['controls_position'] == 'left') $this->printControls(); ?>
                <input type="search"
                                                   class="hiddend wd_cpt_search"
                                                   value=""
                                                   id='wd_cpt_search-input-<?php echo self::$_instancenumber; ?>'
                                                   placeholder="<?php echo $this->args['placeholder']; ?>"/>
                <input type='hidden'
                       name="<?php echo $this->name; ?>"
                       isparam="1"
                       value="<?php echo (is_array($this->data) && isset($this->data['value'])) ? $this->data['value'] : $this->data; ?>">
                <input type="hidden" class="wd_cpt_search_nonce" value="<?php echo wp_create_nonce( 'wd_cpt_search_nonce' ); ?>">
                <input type='hidden' value="<?php echo base64_encode(json_encode($this->args)); ?>" class="wd_args">
                <?php if ($this->args['controls_position'] != 'left') $this->printControls(); ?>
                <div class="wd_cpt_search_res"></div>
                <span class="wp_cpt_search_selected "><span class="fa fa-ban"></span><span><?php echo esc_html($this->post_title); ?></span></span>
            </div>
            <?php
        }

        private function printControls() {
            ?>
            <span class="loading-small hiddend"></span>
            <div class="wd_ts_close hiddend">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="24px" viewBox="0 0 512 512" enable-background="new 0 0 512 512" xml:space="preserve">
                        <polygon id="x-mark-icon" points="438.393,374.595 319.757,255.977 438.378,137.348 374.595,73.607 255.995,192.225 137.375,73.622 73.607,137.352 192.246,255.983 73.622,374.625 137.352,438.393 256.002,319.734 374.652,438.378 "></polygon>
                    </svg>
            </div>
            <?php
        }

        public static function searchCPT() {
            global $wpdb;
            if ( 
                isset($_POST['wd_phrase'], $_POST['wd_cpt_search_nonce']) &&
                current_user_can('administrator') && 
                wp_verify_nonce( $_POST["wd_cpt_search_nonce"], 'wd_cpt_search_nonce' ) 
            ) {
                $phrase = trim($_POST['wd_phrase']);
                $data = json_decode(base64_decode($_POST['wd_args']), true);

                $ptypes = get_post_types(array(
                    "public" => true,
                    "_builtin" => false
                ), "names", "OR");

                $exclude = array("revision", "nav_menu_item", "attachment", 'peepso-post', 'peepso-comment', "acf",
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
                    'post_status' => array(),
                    'post_fields' => array(
                        'title', 'ids'
                    )
                ));

                $results = $asp_query->posts;
                Ajax::prepareHeaders();
                print_r(self::$delimiter . json_encode($results) . self::$delimiter);
            }
            die();
        }

        public function processData() {
            // Get the args first if exists
            if ( is_array($this->data) && isset($this->data['args']) )
                $this->args = array_merge($this->args, $this->data['args']);

            if ( is_array($this->data) && isset($this->data['value']) ) {
                // If called from back-end non-post context
                $this->data = $this->data['value'];
                $this->post_title = get_the_title($this->data) . " (" . get_post_type($this->data) . ")";
            }
        }

        public final function getData() {
            return $this->data;
        }
    }
}

if ( !has_action('wp_ajax_wd_search_cb_cpt') )
    add_action('wp_ajax_wd_search_cb_cpt', 'wd_CPTSearchCallBack::searchCPT');