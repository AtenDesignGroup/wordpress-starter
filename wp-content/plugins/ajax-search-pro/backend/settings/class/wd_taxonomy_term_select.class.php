<?php

use WPDRMS\ASP\Utils\Ajax;

if (!class_exists("wd_TaxonomyTermSelect")) {
    /**
     * Class wd_TaxonomyTermSelect
     *
     * A new multi-purpose, flexible taxonomy-term selector class that includes built-in types as well.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2016, Ernest Marcinko
     */
    class wd_TaxonomyTermSelect extends wpdreamsType {
        private $args = array(
            "show_type" => 0,
            "show_checkboxes" => 0,            // Checkboxes for default states
            "show_display_mode" => 0,          // Display mode option
            "show_more_options" => 0,          // More options for exclusions, default states
            'show_taxonomy_all' => 1,          // Display the 'Use all from taxonomy' when searching
            "built_in" => true,  // display only built in taxonomy terms
            "exclude_taxonomies" => array()
        );

        private $default_options = array(
            "op_type"   => "include", // include|exclude
            /**
             * 0 => array(
             *              "id" => 1,      // -1 if whole taxonomy
             *              "level" => 0,
             *              "taxonomy" => "product_cat",
             *   (optional) "ex_ids"   => array(1, 2, 3,) // array of excluded terms, if id=-1
             * ), ...
             */
            "terms" => array(),
            "un_checked" => array(),        // ids of unchecked terms
            /**
             * "taxonomy_name" => array(
             *      "type"    => "checkbox",
             *      "default" => "checked",
             *      "select_all" => 0,
             *      "select_all_text" => "Choose One",
             *      "box_header_text" => "Filter by Taxonomy"
             * ), ...
             */
            "display_mode" => array()
        );

        private $labels = array(
            'product_visibility' => 'Product visibility',
            'product_type' => 'Product type'
        );

        private $term_data = array();   // Temporary storage for term data

        private $types, $e_data;

        public function getType() {
            parent::getType();
            $this->processData();
            $this->types = $this->getAllTaxonomies();
            $types_copy = $this->types;

            if (!isset($this->e_data['display_mode']))
                $this->e_data['display_mode'] = array();
            ?>
            <div class='wd_TaxonomyTermSelect' id='wd_TaxonomyTermSelect-<?php echo self::$_instancenumber; ?>'>
                <fieldset>
                    <div style='margin:15px 30px;text-align: left; line-height: 45px;'>
                        <label><?php echo __('Select the taxonomy:', 'ajax-search-pro'); ?> </label>
                        <select class='wd_tts_ajax_selector' id='tax_ajax_selector_<?php echo self::$_instancenumber; ?>'>
                            <option name="_select_one_yo" selected="selected" disabled><?php echo __('Select a taxonomy', 'ajax-search-pro'); ?></option>
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

                        <label<?php echo ($this->args["show_type"] == 1) ? '' :  ' class="hiddend"'; ?>><?php echo __('Operation:', 'ajax-search-pro'); ?>
                            <select class="tts_operation">
                                <option value="include"<?php echo $this->e_data['op_type'] == "include" ? ' selected="selected"' : ''; ?>>
                                    <?php echo __('Include the selected', 'ajax-search-pro'); ?>
                                </option>
                                <option value="exclude"<?php echo $this->e_data['op_type'] == "exclude" ? ' selected="selected"' : ''; ?>>
                                    <?php echo __('Exclude the selected', 'ajax-search-pro'); ?>
                                </option>
                            </select>
                        </label>
                        <?php if ($this->args["show_display_mode"] == 1): ?>
                            <input type="button" class="wd_tts_display_mode" value="<?php echo __('Change display mode', 'ajax-search-pro'); ?>"><br>
                            <div class="wd_tts_disp_m_popup hiddend">
                                <div class="wd_tts_m_popup_content">
                                <?php foreach ($types_copy as $tax): ?>
                                    <?php
                                    $custom_post_type = "";
                                    if ( isset($tax->object_type[0]) )
                                        $custom_post_type = $tax->object_type[0] . " - ";
                                    if ( isset($this->labels[$tax->name]) )
                                        $label = $custom_post_type . $this->labels[$tax->name];
                                    else
                                        $label = $custom_post_type . $tax->labels->name;
                                    ?>
                                    <fieldset taxonomy="<?php echo $tax->name; ?>">
                                        <legend><?php echo __('Terms from', 'ajax-search-pro'); ?> <b><?php echo $label; ?></b> (<?php echo $tax->name; ?>)</legend>
                                        <?php echo __('Box header:', 'ajax-search-pro'); ?> <input type="text" class="wd_tts_box_header_text" value="<?php echo ($tax->name != "all") ? __('Filter by ', 'ajax-search-pro').$tax->labels->name : __('Filter by terms', 'ajax-search-pro'); ?>"><br>
                                        <?php echo __('Display as', 'ajax-search-pro'); ?> <select class="tts_display_as">
                                            <option value="checkboxes"><?php echo __('Checkboxes', 'ajax-search-pro'); ?></option>
                                            <option value="dropdown"><?php echo __('Drop-down', 'ajax-search-pro'); ?></option>
                                            <option value="dropdownsearch"><?php echo __('Drop-down with search', 'ajax-search-pro'); ?></option>
                                            <option value="multisearch"><?php echo __('Multiselect with search', 'ajax-search-pro'); ?></option>
                                            <option value="radio"><?php echo __('Radio', 'ajax-search-pro'); ?></option>
                                        </select><span class="tts_d_defaults"> <?php echo __('default', 'ajax-search-pro'); ?> <select class="tts_d_checkboxes">
                                            <option value="checked"><?php echo __('Checked', 'ajax-search-pro'); ?></option>
                                            <option value="unchecked"><?php echo __('Un-checked', 'ajax-search-pro'); ?></option>
                                        </select>
                                        <select class="tts_d_dropdown">
                                            <option value='all'><?php echo __('"Choose one/Any" option', 'ajax-search-pro'); ?></option>
                                            <option value='first'><?php echo __('First item', 'ajax-search-pro'); ?></option>
                                            <option value='last'><?php echo __('Last item', 'ajax-search-pro'); ?></option>
                                            <?php if ($tax->name != "all"): ?>
                                            <option value='0'><?php echo __('As defined:', 'ajax-search-pro'); ?></option>
                                            <?php endif; ?>
                                        </select></span>
                                        <div class="wd_tts_as_defined">
                                            <span class="loading-small hiddend"></span>
                                            <div class="wd_ts_close hiddend">
                                                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="24px" viewBox="0 0 512 512" enable-background="new 0 0 512 512" xml:space="preserve">
                                                    <polygon id="x-mark-icon" points="438.393,374.595 319.757,255.977 438.378,137.348 374.595,73.607 255.995,192.225 137.375,73.622 73.607,137.352 192.246,255.983 73.622,374.625 137.352,438.393 256.002,319.734 374.652,438.378 "></polygon>
                                                </svg>
                                            </div>
                                            <input type="text" class="wd_tts_search" placeholder="<?php echo __('Search terms..', 'ajax-search-pro'); ?>">
                                            <div class="wd_tts_defined"><?php
                                                if (
                                                    isset($this->e_data['display_mode'][$tax->name]['default']) &&
                                                    (int)$this->e_data['display_mode'][$tax->name]['default'] > 0
                                                ) {
                                                    $tt = get_term($this->e_data['display_mode'][$tax->name]['default'], $tax->name);
                                                    if ( !is_wp_error($tt) && isset($tt->term_id) )
                                                        echo "<span term_id='".$tt->term_id."'>".$tt->name."</span>";
                                                    else
                                                        echo __('No selected term.', 'ajax-search-pro');
                                                } else {
                                                    echo __('No selected term.', 'ajax-search-pro');
                                                }
                                                ?></div>
                                            <div class="wd_tts_res"></div>
                                        </div>
                                        <div class="wd_tts_selectall">
                                            <input type="checkbox" class="wd_tts_select_all" value="checked"><?php echo __('Display the', 'ajax-search-pro'); ?><span class="wd_tts_select_all_label"><?php echo __('"Select all option"?', 'ajax-search-pro'); ?></span><br>
                                            <?php echo __('Text:', 'ajax-search-pro'); ?> <input type="text" value="Select all" class="wd_tts_select_all_text">
                                        </div>
                                        <div class="wd_tts_placeholder">
                                            <?php echo __('Placeholder:', 'ajax-search-pro'); ?> <input type="text" value="<?php echo __('Select options..', 'ajax-search-pro'); ?>" class="wd_tts_placeholder_text">
                                        </div>
                                        <div class="wd_tts_required">
                                            <input type="checkbox" class="wd_tts_required" value="checked"><span class="wd_tts_required_label"><?php echo __('Required?', 'ajax-search-pro'); ?></span><br>
                                            <?php echo __('Required popup text:', 'ajax-search-pro'); ?> <input type="text" value="This field is required!" class="wd_tts_required_text">
                                        </div>
                                    </fieldset>
                                <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <label><?php echo __('Show parent categories only', 'ajax-search-pro'); ?> <input class='hide-children' type='checkbox'></label>
                    </div>
                    <legend><?php echo $this->label; ?></legend>
                    <div class="draggablecontainer" id="sortablecontainer<?php echo self::$_instancenumber; ?>">
                        <div class="dragLoader hiddend"></div>
                        <p><?php echo __('Available terms for the selected taxonomy', 'ajax-search-pro'); ?></p>
                        <ul id="sortable<?php echo self::$_instancenumber; ?>" class="connectedSortable wd_csortable<?php echo self::$_instancenumber; ?>">
                        </ul>
                    </div>
                    <div class="sortablecontainer">
						<p>
							<?php _e('Drag here the terms you want to', 'ajax-search-pro'); ?><span style="font-weight: bold;" class="tts_type">
							<?php if ( $this->e_data['op_type'] == 'include' ): ?>
								<?php echo __('include', 'ajax-search-pro'); ?>
							<?php else: ?>
								<?php echo __('exclude', 'ajax-search-pro'); ?>
							<?php endif; ?>
							</span>!
						</p>
                        <ul id="sortable_conn<?php echo self::$_instancenumber; ?>" class="connectedSortable wd_csortable<?php echo self::$_instancenumber; ?>">
                            <?php $this->printSelectedTerms(); ?>
                        </ul>
                        <div class="wd_tts_ex_container hiddend">
                            <h3>Title</h3>
                            <input type="text" class="wd_tts_all_text" placeholder="<?php echo __('Search terms..', 'ajax-search-pro'); ?>">
                            <span class="loading-small hiddend"></span>
                            <div class="wd_ts_close hiddend">
                                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="24px" viewBox="0 0 512 512" enable-background="new 0 0 512 512" xml:space="preserve">
                                    <polygon id="x-mark-icon" points="438.393,374.595 319.757,255.977 438.378,137.348 374.595,73.607 255.995,192.225 137.375,73.622 73.607,137.352 192.246,255.983 73.622,374.625 137.352,438.393 256.002,319.734 374.652,438.378 "></polygon>
                                </svg>
                            </div>
                            <div class="wd_tts_res"></div>
                            <h3><?php echo __('Excluded terms from displaying:', 'ajax-search-pro'); ?></h3>
                            <div class="wd_tts_excluded_t"></div>
                        </div>
                    </div>

                    <input type='hidden' value="<?php echo base64_encode(json_encode($this->term_data)); ?>" class="wd_term_data">
                    <input type='hidden' value="<?php echo base64_encode(json_encode($this->args)); ?>" class="wd_args">
                    <input type="hidden" class="wd_taxonomy_search_nonce" value="<?php echo wp_create_nonce( 'wd_taxonomy_search_nonce' ); ?>">
                    <input isparam=1 type='hidden' value="<?php echo (is_array($this->data) && isset($this->data['value'])) ? $this->data['value'] : $this->data; ?>" name='<?php echo $this->name; ?>'>
            </fieldset>
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

        private function printSelectedTerms() {
            foreach($this->e_data['terms'] as $t) {
                if ( $t['id'] == -1 ) {
                    // Excluded ids for this taxonomy to temporary storage to use in JS
                    if ( taxonomy_exists($t['taxonomy']) ) {
                        if (!empty($t['ex_ids'])) {
                            $this->term_data[$t['taxonomy']] = wpd_get_terms(array(
                                'taxonomy'   => $t['taxonomy'],
                                'include'    => $t['ex_ids'],
                                'fields'     => 'id=>name',
                                'hide_empty' => false
                            ));
                            if (is_wp_error($this->term_data))
                                $this->term_data = array();
                        }
                        $showmore = ($this->args['show_more_options']) ? "<br><a class='wd_tts_showmore'>+ " . __('Show more options', 'ajax-search-pro') . "</a>" : "";
                        echo '<li class="ui-state-default termlevel-0"  term_id="-1" taxonomy="' . $t['taxonomy'] . '">' . __('Use all from', 'ajax-search-pro')  . ' <b>' . $t['taxonomy'] . '</b><a class="deleteIcon"></a>' . $showmore . '</li>';
                    }
                } else if ( $t['id'] == -200 && $t['taxonomy'] == 'post_format' ) {
                    $term = new stdClass();
                    $term->term_id = -200;
                    $term->taxonomy = 'post_format';
                    $term->children = array();
                    $term->name = 'Standard';
                    $term->label = 'Standard';
                    $term->parent = 0;
                    $term = apply_filters('asp_post_format_standard', $term);

                    $checkbox = "";
                    if ($this->args['show_checkboxes'] == 1)
                        $checkbox = '<input style="float:left;" type="checkbox" value="' . $term->term_id . '"
                    ' . (!in_array($term->term_id, $this->e_data['un_checked']) ? ' checked="checked"' : '') . '/>';
                    echo '
                    <li class="ui-state-default termlevel-' . $t['level'] . '" term_level="' . $t['level'] . '" term_id="' . $term->term_id . '" taxonomy="' . $term->taxonomy . '">' . $term->name . '
                        ' . $checkbox . '
                    <a class="deleteIcon"></a></li>
                ';
                } else {
                    $term = get_term($t['id'], $t['taxonomy']);
                    if (empty($term) || is_wp_error($term))
                        continue;
                    $checkbox = "";
                    if ($this->args['show_checkboxes'] == 1)
                        $checkbox = '<input style="float:left;" type="checkbox" value="' . $term->term_id . '"
                    ' . (!in_array($term->term_id, $this->e_data['un_checked']) ? ' checked="checked"' : '') . '/>';

                    // WPML
                    $language_code = apply_filters( 'wpml_element_language_code', null, array( 'element_id'=> (int)$term->term_id, 'element_type'=> $term->taxonomy ) );
                    if ( defined('ICL_SITEPRESS_VERSION') && !empty($language_code) ) {
                      $term->name .= ' [' . $language_code . ']';
                    }

                    echo '
                    <li class="ui-state-default termlevel-' . $t['level'] . '" term_level="' . $t['level'] . '" term_id="' . $term->term_id . '" taxonomy="' . $term->taxonomy . '">' . $term->name . '
                        ' . $checkbox . '
                    <a class="deleteIcon"></a></li>
                ';
                }
            }
        }

        public static function searchTerms() {
            if ( 
                isset($_POST['wd_taxonomy'], $_POST['wd_taxonomy_search_nonce']) &&
                current_user_can('administrator') && 
                wp_verify_nonce( $_POST["wd_taxonomy_search_nonce"], 'wd_taxonomy_search_nonce' ) 
            ) {
                $taxonomy = $_POST['wd_taxonomy'];
                $terms = get_terms($taxonomy, array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                    'fields' => 'id=>name',
                    'search' => trim($_POST['wd_s']),
                    'number' => 15
                ));
                Ajax::prepareHeaders();
                if ( !is_wp_error($terms) && count($terms) > 0) {
                    foreach ($terms as $k => $term)
                        print "<span term_id='".$k."'>".$term."</span><br>";
                } else {
                    print "No results for <b>" .$_POST['wd_s'] . "</b>";
                }
            }
            die();
        }

        public static function printTerms() {
            if ( 
                isset($_POST['wd_taxonomy'], $_POST['wd_taxonomy_search_nonce']) &&
                current_user_can('administrator') && 
                wp_verify_nonce( $_POST["wd_taxonomy_search_nonce"], 'wd_taxonomy_search_nonce' ) 
            ) {
                $taxonomy = $_POST['wd_taxonomy'];
                $data = json_decode(base64_decode($_POST['wd_args']), true);

                // WPMl?
                $languages = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' );
                $terms = array();

                if (  defined('ICL_SITEPRESS_VERSION') && !empty( $languages ) ) {
                    foreach( $languages as $l ) {
                        if ( isset($l['language_code']) ) {
                        do_action( 'wpml_switch_language', $l['language_code'] );
                        $gterms = get_terms($taxonomy, array(
                            'taxonomy' => $taxonomy,
                            'hide_empty' => false
                        ));
                        if ( !is_wp_error($gterms) && count($gterms) > 0 ) {
                            $terms = array_merge( $terms, $gterms );
                            foreach ( $gterms as &$term ) {
                            $term->name = $term->name . ' [' . $l['language_code'] . ']';
                            }
                        }
                        }
                    }
                } else {
                    $terms = get_terms($taxonomy, array(
                        'taxonomy' => $taxonomy,
                        'hide_empty' => false
                    ));
                }

                if ( $taxonomy == 'post_format' && !is_wp_error($terms) && !empty($terms) ) {
                    $std_term = new stdClass();
                    $std_term->term_id = -200;
                    $std_term->taxonomy = 'post_format';
                    $std_term->children = array();
                    $std_term->name = 'Standard';
                    $std_term->label = 'Standard';
                    $std_term->parent = 0;
                    $std_term = apply_filters('asp_post_format_standard', $std_term);
                    array_unshift($terms, $std_term);
                }
                Ajax::prepareHeaders();
                $showmore = ( $data['show_more_options'] ) ? "<br><a class='wd_tts_showmore'>+ Show more options</a>" : "";
                if ( $data['show_taxonomy_all'] )
                    echo '
                        <li class="ui-state-default termlevel-0"  term_id="-1" taxonomy="' . esc_attr($taxonomy) . '">' . __('Use all from', 'ajax-search-pro')  . ' <b>'.esc_attr($taxonomy).'</b><a class="deleteIcon"></a>'.$showmore.'</li>
                        ..or select terms..
                    ';
                if (!empty($terms) && is_array($terms)) {
                    $termsHierarchical = array();
                    wd_sort_terms_hierarchicaly($terms, $termsHierarchical);
                    self::printTermsRecursive($termsHierarchical, 0, $data);
                } else {
                    print "No terms to display in this taxonomy.";
                }
            }
            die();
        }

        private static function printTermsRecursive ($terms, $level, $data) {
            foreach ($terms as $term) {
                $checkbox = "";
                if ($data['show_checkboxes'] == 1)
                    $checkbox = '<input style="float:left;" type="checkbox" value="' . $term->term_id . '" checked="checked"/>';
                echo '
                    <li class="ui-state-default termlevel-'.$level.'" term_level="'.$level.'" term_id="' . $term->term_id . '" taxonomy="' . $term->taxonomy . '">' . $term->name . '
                        '.$checkbox.'
                    <a class="deleteIcon"></a></li>
                ';
                if (is_array($term->children) && count($term->children) >0 )
                    self::printTermsRecursive($term->children, ($level + 1), $data);
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
            // All keys are srings in array, merge it with defaults to override
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

// Ajax action for the back-end
if ( !has_action('wp_ajax_wd_print_taxonomy_terms') )
    add_action('wp_ajax_wd_print_taxonomy_terms', 'wd_TaxonomyTermSelect::printTerms');
if ( !has_action('wp_ajax_wd_search_taxonomy_terms') )
    add_action('wp_ajax_wd_search_taxonomy_terms', 'wd_TaxonomyTermSelect::searchTerms');