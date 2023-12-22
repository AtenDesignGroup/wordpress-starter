<?php

use WPDRMS\ASP\Utils\Ajax;

if (!class_exists("wd_CFSearchCallBack")) {
    /**
     * Class wd_CFSearchCallBack
     *
     * Custom field search for both post meta and user meta tables, which passes the results to a JS callback method
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2016, Ernest Marcinko
     */
    class wd_CFSearchCallBack extends wpdreamsType {
        private static $delimiter = '!!!CFRES!!!';
        private $args = array(
            'callback' => '',       // javacsript function name in the windows scope | if empty, shows results
            'search_values' => 0,
            'limit' => 15,
            'controls_position' => 'right',
            'class' => '',
            'usermeta' => 0,
            'show_pods' => false
        );

        public function getType() {
            parent::getType();
            $this->processData();
            $this->args['delimiter'] = self::$delimiter;
            ?>
            <div class='wd_cf_search<?php echo $this->args['class'] != '' ? ' '.$this->args['class'] : "";?>'
                 id='wd_cf_search-<?php echo self::$_instancenumber; ?>'>
                <?php if ($this->args['controls_position'] == 'left') $this->printControls(); ?>
                <?php echo $this->label; ?> <input type="search" name="<?php echo $this->name; ?>"
                                                   class="wd_cf_search"
                                                   value="<?php echo (is_array($this->data) && isset($this->data['value'])) ? $this->data['value'] : $this->data; ?>"
                                                   placeholder="<?php esc_attr_e('Search custom fields..', 'ajax-search-pro'); ?>"/>
                <input type="hidden" class="wd_cf_search_nonce" value="<?php echo wp_create_nonce( 'wd_cf_search_nonce' ); ?>">                                   
                <input type='hidden' value="<?php echo base64_encode(json_encode($this->args)); ?>" class="wd_args">
                <?php if ($this->args['controls_position'] != 'left') $this->printControls(); ?>
                <div class="wd_cf_search_res"></div>
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

        public static function searchCF()
        {
            global $wpdb;
            if ( 
                isset($_POST['wd_phrase'], $_POST['wd_cf_search_nonce']) &&
                current_user_can('administrator') && 
                wp_verify_nonce( $_POST["wd_cf_search_nonce"], 'wd_cf_search_nonce' ) 
            ) {
                // Exact matches
                $phrase = trim($_POST['wd_phrase']) . '%';
                $data = json_decode(base64_decode($_POST['wd_args']), true);
                if ($data['usermeta'])
                    $table = $wpdb->usermeta;
                else
                    $table = $wpdb->postmeta;
                if ($data['search_values'] == 1) {
                    $cf_query = $wpdb->prepare(
                        "SELECT DISTINCT(meta_key) FROM $table WHERE meta_key LIKE '%s' OR meta_value LIKE '%s' ORDER BY meta_key ASC LIMIT %d",
                        $phrase, $phrase, $data['limit']);
                } else {
                    $cf_query = $wpdb->prepare(
                        "SELECT DISTINCT(meta_key) FROM $table WHERE meta_key LIKE '%s' ORDER BY meta_key ASC LIMIT %d",
                        $phrase, $data['limit']);
                }
                $cf_results = $wpdb->get_results( $cf_query, OBJECT );

                $remaining_limit = $data['limit'] - count($cf_results);
                if ( $remaining_limit > 0 ) {
                    // Fuzzy matches
                    $not_in_query = '';
                    $not_in = array();
                    foreach ($cf_results as $r) {
                        $not_in[] = $r->meta_key;
                    }
                    if (count($not_in) > 0) {
                        $not_in_query = " AND meta_key NOT IN ('" . implode("','", $not_in) . "')";
                    }
                    $phrase = '%' . trim($_POST['wd_phrase']) . '%';
                    if ($data['search_values'] == 1) {
                        $cf_query = $wpdb->prepare(
                            "SELECT DISTINCT(meta_key) FROM $table WHERE (meta_key LIKE '%s' OR meta_value LIKE '%s') $not_in_query ORDER BY meta_key ASC LIMIT %d",
                            $phrase, $phrase, $remaining_limit);
                    } else {
                        $cf_query = $wpdb->prepare(
                            "SELECT DISTINCT(meta_key) FROM $table WHERE (meta_key LIKE '%s') $not_in_query ORDER BY meta_key ASC LIMIT %d",
                            $phrase, $remaining_limit);
                    }
                    $cf_results = array_merge($cf_results, $wpdb->get_results($cf_query, OBJECT));
                }

                if ( $data['show_pods'] )
                    $pods_fields = self::searchPods($_POST['wd_phrase']);
                else
                    $pods_fields = array();

                Ajax::prepareHeaders();
                print_r(self::$delimiter . json_encode(array_merge($pods_fields, $cf_results)) . self::$delimiter);
            }
            die();
        }

        private static function searchPods($s) {
            $ret = array();
            if ( function_exists('pods_api') ) {
                // Filter table storage based fields only
                $pods = get_posts(array(
                    'fields'          => 'ids',
                    'posts_per_page'  => -1,
                    'post_type' => '_pods_pod',
                    'meta_query' => array(
                        array(
                            'key' => 'storage',
                            'value' => 'table',
                            'compare' => 'LIKE'
                        )
                    )
                ));
                if ( !is_wp_error($pods) && !empty($pods) ) {
                    $pods_fields = get_posts(array(
                        'fields' => 'post_name',
                        'posts_per_page' => -1,
                        's' => $s,
                        'post_type' => '_pods_field',
                        'post_parent__in' => $pods // Only filtered parents by table storage type
                    ));
                    if ( !is_wp_error($pods_fields) && !empty($pods_fields) ) {
                        $ret = array();
                        foreach ($pods_fields as $f) {
                            $ret[] = array('meta_key' => '__pods__' . $f->post_name);
                        }
                    }
                }
            }

            return $ret;
        }

        public function processData() {
            // Get the args first if exists
            if ( is_array($this->data) && isset($this->data['args']) )
                $this->args = array_merge($this->args, $this->data['args']);

            if ( is_array($this->data) && isset($this->data['value']) ) {
                // If called from back-end non-post context
                $this->data = $this->data['value'];
            }
        }

        public final function getData() {
            return $this->data;
        }
    }
}

if ( !has_action('wp_ajax_wd_search_cf') )
    add_action('wp_ajax_wd_search_cf', 'wd_CFSearchCallBack::searchCF');