<?php

use WPDRMS\ASP\Utils\Ajax;

if (!class_exists("wd_TaxTermSearchCallBack")) {
    /**
     * Class wd_TaxTermSearchCallBack
     *
     * Custom field search for both post meta and user meta tables, which passes the results to a JS callback method
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2018, Ernest Marcinko
     */
    class wd_TaxTermSearchCallBack extends wpdreamsType {
        private static $delimiter = '!!!TTRES!!!';
        private $args = array(
            'callback' => '',       // javacsript function name in the windows scope | if empty, shows results
            'placeholder' => 'Search terms..',
            'built_in'  => true,
            'search_values' => 0,
            'limit' => 10,
            'delimiter' => '!!!TTRES!!!',
            'controls_position' => 'right',
            'class' => '',
            'usermeta' => 0
        );

        private $types;
        private $labels = array(
            'product_visibility' => 'Product visibility',
            'product_type' => 'Product type'
        );

        public function getType() {
            parent::getType();
            $this->processData();
            $this->args['delimiter'] = self::$delimiter;
            $this->types = $this->getAllTaxonomies();
            ?>
            <div class='wd_taxterm_search<?php echo $this->args['class'] != '' ? ' '.$this->args['class'] : "";?>'
                 id='wd_taxterm_search-<?php echo self::$_instancenumber; ?>'>
                <?php if ( $this->label != '' ): ?>
                    <label class="wd_taxterm_label"><?php echo $this->label; ?></label>
                <?php endif; ?>
                <select class='wd_taxterm_tax' id='tax_ajax_selector_<?php echo self::$_instancenumber; ?>'>
                    <?php
                    foreach ($this->types as $taxonomy) {
                        $custom_post_type = "";
                        if ( isset($taxonomy->object_type[0]) )
                            $custom_post_type = $taxonomy->object_type[0] . " - ";
                        if ( isset($this->labels[$taxonomy->name]) )
                            $label = $this->labels[$taxonomy->name];
                        else
                            $label = $taxonomy->labels->name;
                        echo "<option  value='" . $taxonomy->name . "' taxonomy='" . $taxonomy->name . "'>" . $custom_post_type .''. $taxonomy->name .' ('.$label. ")</option>";
                    }
                    ?>
                </select>
                <?php if ($this->args['controls_position'] == 'left') $this->printControls(); ?>
                <input type="hidden" class="wd_taxonomy_search_cb_nonce" value="<?php echo wp_create_nonce( 'wd_taxonomy_search_cb_nonce' ); ?>">
                <input type="search" name="<?php echo $this->name; ?>"
                                                   class="wd_taxterm_search"
                                                   value="<?php echo (is_array($this->data) && isset($this->data['value'])) ? $this->data['value'] : $this->data; ?>"
                                                   placeholder="<?php echo $this->args['placeholder']; ?>"/>
                <input type='hidden' value="<?php echo base64_encode(json_encode($this->args)); ?>" class="wd_args">
                <?php if ($this->args['controls_position'] != 'left') $this->printControls(); ?>
                <div class="wd_taxterm_search_res"></div>
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

        public function getAllTaxonomies() {
            if ( $this->args['built_in'] == false ) {
                $args = array('_builtin' => false);
            } else {
                $args = array();
            }
            $taxonomies = get_taxonomies( $args, 'objects' );
            if ( !empty($this->args["exclude_taxonomies"]) ) {
                foreach ($taxonomies as $k => $v) {
                    if ( in_array($v->name, $this->args["exclude_taxonomies"]) )
                        unset($taxonomies[$k]);
                }
            }
            return $taxonomies;
        }

        public static function searchTaxTerm() {
            if ( 
                isset($_POST['wd_taxonomy'], $_POST['wd_taxonomy_search_cb_nonce']) &&
                current_user_can('administrator') && 
                wp_verify_nonce( $_POST["wd_taxonomy_search_cb_nonce"], 'wd_taxonomy_search_cb_nonce' ) 
            ) {
                $taxonomy = $_POST['wd_taxonomy'];
                $data = json_decode(base64_decode($_POST['wd_args']), true);
                $terms = get_terms($taxonomy, array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                    'fields' => 'all',
                    'search' => trim($_POST['wd_phrase']),
                    'number' => $data['limit']
                ));
                Ajax::prepareHeaders();
                if ( !is_wp_error($terms) ) {
                    print_r(self::$delimiter . json_encode($terms) . self::$delimiter);;
                } else {
                    print 1;
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
                $this->data = $this->data['value'];
            }
        }

        public final function getData() {
            return $this->data;
        }
    }
}

if ( !has_action('wp_ajax_wd_search_taxterm') )
    add_action('wp_ajax_wd_search_taxterm', 'wd_TaxTermSearchCallBack::searchTaxTerm');