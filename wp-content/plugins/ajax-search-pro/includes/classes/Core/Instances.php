<?php
namespace WPDRMS\ASP\Core;

use WP_Error;
use WPDRMS\ASP\Misc\Themes;
use WPDRMS\ASP\Patterns\SingletonTrait;

defined('ABSPATH') or die("You can't access this file directly.");

/**
 * Class WD_ASP_Instances
 *
 * This class handles the data transfer between code and instance data
 *
 * @class         WD_ASP_Instances
 * @version       1.0
 * @package       AjaxSearchPro/Classes/Core
 * @category      Class
 * @author        Ernest Marcinko
 */
class Instances {

    use SingletonTrait;

    /**
     * This holds the search instances
     *
     * @var array()
     */
    private $search_instances;

    /**
     * This holds the search instances without data
     *
     * @var array()
     */
    private $instancesNoData;

    /**
     * When updating or first demand, this variable sets to true, telling that instances need re-parsing
     *
     * @var bool
     */
    private $refresh = true;

	/**
	 * The search instances init script JSON data
	 * @var string[]
	 */
	private $script_data = array();

    /**
     * Gets the search instance if exists
     *
     * @param int $id
     * @param bool $force_refresh
     * @param bool|int $check_ownership
     * @return bool|array
     */
    public function get( $id = -1, $force_refresh = false, $check_ownership = false ) {
        if ($this->refresh || $force_refresh) {
            $this->init();
            $this->refresh = false;
        }

        if ( $check_ownership !== false && !is_super_admin() ) {
            if ( is_int($check_ownership) ) {
                $user_id = $check_ownership;
            } else  {
                $user_id = intval( get_current_user_id() );
            }
            foreach ($this->search_instances as $key => $inst) {
                if ( $inst['data']['owner'] != 0 && $inst['data']['owner'] != $user_id )
                    unset($this->search_instances[$key]);
            }
        }

        if ($id > -1)
            return isset($this->search_instances[$id]) ? $this->search_instances[$id] : array();

        return $this->search_instances;
    }

	/**
	 * Get an option value for a search instance ID
	 *
	 * @param $instance
	 * @param $option_name
	 * @return false|mixed
	 */
	public function getOption($instance, $option_name) {
		if ( $this->exists($instance) ) {
			$instance = $this->get($instance);
			return $instance['data'][$option_name] ?? false;
		}

		return false;
	}

    /**
     * Temporary changes the search instance data within the cache variable (not permanent)
     *
     * @param int $id
     * @param array $data
     * @return bool|array
     */
    public function set( $id = 0, $data = array() ) {
        if ($this->refresh) {
            $this->init();
            $this->refresh = false;
        }
        if ( isset($this->search_instances[$id]) ) {
            $this->search_instances[$id]['data'] = array_merge($this->search_instances[$id]['data'], $data);
            return true;
        }
        return false;
    }

    /**
     * Gets the search instance if exists, without data
     *
     * @param int $id
     * @param bool $force_refresh
     * @return bool|array
     */
    public function getWithoutData( $id = -1, $force_refresh = false ) {
        if ($this->refresh || $force_refresh) {
            $this->init();
            $this->refresh = false;
        }
        if ($id > -1)
            return isset($this->instancesNoData[$id]) ? $this->instancesNoData[$id] : array();

        return $this->instancesNoData;
    }

    /**
     * Checks if the given search instance exists
     *
     * @param $id
     * @return bool
     */
    public function exists( $id = false ) {
        if ($this->refresh) {
            $this->init();
            $this->refresh = false;
        }
        if ( $id === false ) {
            return count($this->search_instances) > 0;
        } else {
            return isset($this->search_instances[$id]);
        }
    }

    /**
     * Create a new search instance with the default options set
     *
     * @param $name
     * @param int $owner User ID of the owner
     * @return bool|int
     */
    public function add( $name, $owner = 0 ) {
        global $wpdb;

        $this->refresh = true;
        $data = wd_asp()->options['asp_defaults'];
        $data['owner'] = intval($owner);

        if (
            $wpdb->query(
                "INSERT INTO " . wd_asp()->db->table('main') . "
                            (name, data) VALUES
                            ('" . esc_sql($name) . "', '" . wd_mysql_escape_mimic(json_encode($data)) . "')"
            ) !== false
        ) return $wpdb->insert_id;

        return false;
    }

    /**
     * Import the search from the Lite version, as a new search instance
     *
     * @param $name
     * @param int $owner User ID of the owner
     * @return bool|int
     */
    public function importFromLite( $name, $owner = 0 ) {
        global $wpdb;

        $this->refresh = true;
        $data = wd_asp()->options['asp_defaults'];
        $data['owner'] = intval($owner);

        $lite = get_option('asl_options', array());
        if ( count($lite) > 0 && get_option('asl_version', 0) > 4732 ) {
            // --- Resolve the options from the lite version ---
            // 1. Resolve these as-is, no change required
            $as_is = array(
                'keyword_logic', 'triggerontype', 'customtypes', 'searchintitle', 'searchincontent', 'searchinexcerpt',
                'search_in_permalinks', 'search_in_ids', 'search_all_cf', 'customfields', 'post_status', 'override_default_results',
                'override_method', 'exactonly', 'exact_match_location', 'searchinterms', 'charcount', 'itemscount', 'orderby_primary', 'orderby_secondary',
                'show_images', 'image_width', 'image_height', 'image_source1', 'image_source2', 'image_source3', 'image_source4', 'image_source5',
                'image_source_featured', 'image_custom_field', 'show_frontend_search_settings', 'box_width', 'resultsposition', 'defaultsearchtext',
                'showmoreresults', 'showmoreresultstext', 'v_res_max_height', 'results_click_blank', 'scroll_to_results', 'resultareaclickable',
                'close_on_document_click', 'show_close_icon', 'showauthor', 'showdate', 'showdescription', 'descriptionlength', 'description_context',
                'noresultstext', 'didyoumeantext', 'autocomplete', 'shortcode_op', 'striptagsexclude', 'excludeposts', 'wpml_compatibility', 'polylang_compatibility',
                'click_action_location', 'return_action_location', 'showcustomtypes',
				// 4.9
				'primary_titlefield', 'primary_descriptionfield', 'primary_titlefield_cf', 'primary_descriptionfield_cf', 'advtitlefield', 'advdescriptionfield',
				'kw_exceptions', 'kw_exceptions_e', 'single_highlight', 'single_highlightwholewords', 'single_highlightcolor', 'single_highlightbgcolor',
				'single_highlight_scroll', 'single_highlight_offset', 'single_highlight_selector', 'image_display_mode'
            );
            foreach ( $as_is as $key ) {
                if ( isset($lite[$key]) )
                    $data[$key] = $lite[$key];
            }
            // 2. Resolve by difference in keys only
            $resolve_keys = array(
                'trigger_on_facet_change' => 'trigger_on_facet',
                'titlefield' => 'primary_titlefield',
                'descriptionfield' => 'primary_descriptionfield',
                'titlefield_cf' => 'primary_titlefield_cf',
                'descriptionfield_cf' => 'primary_descriptionfield_cf',
                'kw_suggestions' => 'keywordsuggestions',
                'kw_length'  => 'keyword_suggestion_length',
                'kw_count' => 'keyword_suggestion_count',
                'kw_google_lang' => 'keywordsuggestionslang',
                'kw_exceptions' => 'autocompleteexceptions',
                'kw_highlight' => 'highlight',
                'kw_highlight_whole_words' => 'highlightwholewords',
                'highlight_color' => 'highlightcolor',
                'highlight_bg_color' => 'highlightbgcolor',
                'redirect_click_to' => 'click_action',
                'redirect_enter_to' => 'return_action',
                'custom_redirect_url' => 'redirect_url',
                'maxresults' => 'posts_limit',

            );
            foreach ( $resolve_keys as $lkey => $pkey ) {
                if ( isset($lite[$lkey]) ) {
                    $data[$pkey] = $lite[$lkey];
                }
            }
            // 3. Manually Resolve
            // -- Default image
            if ( !empty($lite['image_default']) && strpos($lite['image_default'], 'img/default.jpg') === false ) {
                $data['image_default'] = $lite['image_default'];
            }
            // -- Generic filters
            $data['frontend_fields']['selected'] = array();
            $data['frontend_fields']['unselected'][] = 'exact';
            if ( $lite['showexactmatches'] == 1 ) {
                $data['frontend_fields']['selected'][] = 'exact';
                $data['frontend_fields']['labels']['exact'] = $lite['exactmatchestext'];
            }
            if ( $lite['showsearchintitle'] == 1 ) {
                $data['frontend_fields']['selected'][] = 'title';
                $data['frontend_fields']['labels']['title'] = $lite['searchintitletext'];
            }
            if ( $lite['showsearchincontent'] == 1 ) {
                $data['frontend_fields']['selected'][] = 'content';
                $data['frontend_fields']['labels']['content'] = $lite['searchincontenttext'];
            }
            if ( $lite['showsearchinexcerpt'] == 1 ) {
                $data['frontend_fields']['selected'][] = 'excerpt';
                $data['frontend_fields']['labels']['excerpt'] = $lite['searchinexcerpttext'];
            }
            if ( count($data['frontend_fields']['selected']) > 0 ) {
                $data['frontend_fields']['unselected'] = array_diff(
                    $data['frontend_fields']['unselected'],
                    $data['frontend_fields']['selected']
                );
            }
            // -- Old version Post type filters
            if ( isset($lite['showsearchinposts']) && $lite['showsearchinposts'] == 1 ) {
                if ( $data['showcustomtypes'] == '' ) {
                    $data['showcustomtypes'] = 'post;' . $lite['searchinpoststext'];
                } else {
                    $data['showcustomtypes'] = 'post;' . $lite['searchinpoststext'] . '|' . $data['showcustomtypes'];
                }
            }
            if ( isset($lite['showsearchinpages']) && $lite['showsearchinpages'] == 1 ) {
                if ( $data['showcustomtypes'] == '' ) {
                    $data['showcustomtypes'] = 'page;' . $lite['searchinpagestext'];
                } else {
                    $data['showcustomtypes'] = 'page;' . $lite['searchinpagestext'] . '|' . $data['showcustomtypes'];
                }
            }
            // -- Category filters
            if ( $lite['showsearchincategories'] == 1 && $lite['exsearchincategories'] != '' ) {
                $categories = explode('|', $lite['exsearchincategories']);
                foreach ( $categories as $ck => $cc ) {
                    if ( $cc == '' )
                        unset($categories[$ck]);
                }
                if ( count($categories) > 0 ) {
                    // Set the display mode
                    $data['show_terms']['display_mode']['category'] = array (
                        'type' => 'checkboxes',
                        'select_all' => false,
                        'box_header_text' => $lite['exsearchincategoriestext'],
                        'select_all_text' => '',
                        'default' => 'checked'
                    );
                    $categories = get_terms(array(
                        'taxonomy' => 'category',
                        'include'  => $categories,
                        'orderby'  => 'include'
                    ));
                    if ( !is_wp_error($categories) && count($categories) > 0 ) {
                        $categories_sorted = array();
                        wd_sort_terms_hierarchicaly($categories, $categories_sorted);
                        wd_flatten_hierarchical_terms($categories_sorted, $categories);
                        foreach ($categories as $category) {
                            $data['show_terms']['terms'][] = array(
                                'taxonomy' => 'category',
                                'id' => $category->term_id,
                                'level' => $category->level,
                                'ex_ids' => array()
                            );
                        }
                    }
                }
            }
            // -- Themes & layout
            $themes = array(
                'simple-red' => 'Simple Red vertical (default)',
                'simple-blue' => 'Simple Blue vertical',
                'simple-grey' => 'Simple Grey vertical',
                'classic-blue' => 'Classic Blue vertical',
                'curvy-black' => 'Curvy Black vertical',
                'curvy-red' => 'Curvy Red vertical',
                'curvy-blue' => 'Curvy Blue vertical',
                'underline' => 'Underline Grey vertical'
            );
            if ( isset($themes[$lite['theme']]) ) {
                $data['themes'] = $themes[$lite['theme']];
                if ( $lite['theme'] != 'simple-red' ) {
                    $theme = Themes::get('search', $themes[$lite['theme']]);
                    foreach ( $theme as $tkey => $tval ) {
                        $data[$tkey] = $tval;
                    }
                }
            }
            if ( $lite['override_bg'] == 1 ) {
                $data['boxbackground'] = $lite['override_bg_color'];
                // Simple themes, input background
                if ( strpos($lite['theme'], 'simple-') === 0 ) {
                    $data['inputbackground'] = $lite['override_bg_color'];
                }
            }
            if ( $lite['override_icon'] == 1 ) {
                $data['settingsbackground'] = $lite['override_icon_bg_color'];
                $data['magnifierbackground'] = $lite['override_icon_bg_color'];
                $data['settingsimage_color'] = $lite['override_icon_color'];
                $data['magnifierimage_color'] = $lite['override_icon_color'];
            }
            if ( $lite['override_border'] == 1 ) {
                $data['boxborder'] = $lite['override_border_style'];
            }
			if ( $lite['results_bg_override'] == 1 ) {
				$data['resultsbackground'] = $lite['results_bg_override_color'];
			}
			if ( $lite['results_item_bg_override'] == 1 ) {
				$data['resultscontainerbackground'] = $lite['results_item_bg_override_color'];
			}
			if ( $lite['results_override_border'] == 1 ) {
				$data['resultsborder'] = $lite['results_override_border_style'];
			}
			if ( $lite['settings_bg_override'] == 1 ) {
				$data['settingsdropbackground'] = $lite['settings_bg_override_color'];
			}

            // -- Category based exclusions
            if ( $lite['excludecategories'] != '' ) {
                $terms = explode('|', $lite['excludecategories']);
                foreach ( $terms as $tk => $tt ) {
                    if ( $tt == '' )
                        unset($terms[$tk]);
                }
                if ( count($terms) > 0 ) {
                    foreach ( $terms as $term ) {
                        $data['exclude_by_terms']['terms'][] = array(
                            'taxonomy' => 'category',
                            'id' => $term,
                            'level' => 0,
                            'ex_ids' => array()
                        );
                    }
                }
            }
        } else {
            return false;
        }

        // Resume the import
        if (
            $wpdb->query(
                "INSERT INTO " . wd_asp()->db->table('main') . "
                            (name, data) VALUES
                            ('" . esc_sql($name) . "', '" . wd_mysql_escape_mimic(json_encode($data)) . "')"
            ) !== false
        ) {
            // Final import options, the form override
            if ( $lite['override_search_form'] == 1 ) {
                update_option("asp_st_override", $wpdb->insert_id);
            }
            if ( $lite['override_woo_search_form'] == 1 ) {
                update_option("asp_woo_override", $wpdb->insert_id);
            }
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Update the search data
     *
     * @param $id
     * @param $data
     * @param $update_owner bool|int
     * @return false|int
     */
    public function update( $id, $data = array(), $update_owner = false ) {
        global $wpdb;

        $this->refresh = true;

        if ( isset($this->search_instances[$id], $this->search_instances[$id]['data']) )
            $data = array_merge($this->search_instances[$id]['data'], $data);
        $data = $this->clearData($data);

        if ( $update_owner === true ) {
            $data['owner'] = intval( get_current_user_id() );
        } else if ( $update_owner !== false ) {
            $update_owner = intval($update_owner);
            if ( $update_owner >= 0 )
                $data['owner'] = $update_owner;
        }

        return $wpdb->query("
            UPDATE " . wd_asp()->db->table('main') . "
            SET data = '" . wd_mysql_escape_mimic(json_encode($data)) . "'
            WHERE id = " . intval($id) . "
        ");
    }

    /**
     * Renames a search instance
     *
     * @param $new_name string
     * @param $id int
     * @return bool|int
     */
    public function rename( $new_name, $id ) {
        global $wpdb;

        $this->refresh = true;

        return $wpdb->query(
            $wpdb->prepare("UPDATE " . wd_asp()->db->table('main') . " SET name = '%s' WHERE id = %d", $new_name, $id)
        );
    }

    /**
     * Resets the search instance to the default options.
     *
     * @param int $id Search instance ID
     */
    public function reset($id) {
        global $wpdb;
        $this->refresh = true;

        $query = "UPDATE " . wd_asp()->db->table('main') . "
             SET
                data='" . wd_mysql_escape_mimic(json_encode(wd_asp()->options['asp_defaults'])) . "'
             WHERE id=" . intval($id);
        $wpdb->query($query);
    }

    /**
     * Duplicates a search instance
     *
     * @param $id int
     * @return bool|int
     */
    public function duplicate( $id ) {
        global $wpdb;

        $this->refresh = true;

        return $wpdb->query(
            $wpdb->prepare("
            INSERT INTO " . wd_asp()->db->table('main') . "( name, data )
            SELECT CONCAT(name, ' duplicate'), data FROM " . wd_asp()->db->table('main') . "
            WHERE id=%d;"
                , $id)
        );
    }

    /**
     * Deletes a search instance
     *
     * @param $id int
     * @return bool|int
     */
    public function delete( $id ) {
        global $wpdb;

        $this->refresh = true;

        return $wpdb->query(
            $wpdb->prepare("DELETE FROM " . wd_asp()->db->table('main') . " WHERE id=%d", $id)
        );
    }

	/**
	 * @param int $id
	 * @return string
	 */
	public function export($id = 0 ) {
    	if ( $this->exists($id) ) {
			return base64_encode( json_encode( $this->get($id) ) );
		}
		return '';
	}

	/**
	 * @param array|string $data json encoded array of base 64 encoded instance objects (or only one base 64 encoded instance)
	 * @return int|string|WP_Error number of instances (int) or an error message from the Exception
	 */
	public function import($data ) {
		$imported = 0;
		$instances = array();
		if ( is_array($data) ) {
			foreach ( $data as $d ) {
				$instances[] = json_decode(base64_decode($d), true);
				if ( json_last_error() != 0 ) {
					break;
				}
			}
		} else if ( is_string($data) ) {
			$instances[] = json_decode(base64_decode($data), true);
		} else {
			return new WP_Error('data', __('Invalid data', 'ajax-search-pro'));
		}

		if ( json_last_error() != 0 ) {
			return new WP_Error( 'json', __('Invalid JSON data', 'ajax-search-pro'));
		}

		foreach ( $instances as $instance ) {
			if ( isset($instance['name']) ) {
				$id = $this->add($instance['name'] . ' imported');
				$this->update($id, $instance['data']);
				$imported++;
			}
		}
		return $imported;
	}

    /**
     * This method is intended to use on params AFTER parsed from the database
     *
     * @param $params
     * @return mixed
     */
    public function decode_params( $params ) {
        /**
         * New method for future use.
         * Detects if there is a _decode_ prefixed input for the current field.
         * If so, then decodes and overrides the posted value.
         */
        foreach ($params as $k=>$v) {
            if (gettype($v) === "string" && substr($v, 0, strlen('_decode_')) == '_decode_') {
                $real_v = substr($v, strlen('_decode_'));
                $params[$k] = json_decode(base64_decode($real_v), true);
            }
        }
        return $params;
    }

	public function add_script_data( $id, $data ) {
		$this->script_data[$id] = $data;
		update_site_option('_asp_script_data', $this->script_data);
	}

	public function get_script_data( ) {
		return get_option('_asp_script_data', array());
	}

	public function getInstancesPrinted(): array {
		return array_keys($this->script_data);
	}

	public function areInstancesOnCurrentPage(): bool {
		return count($this->script_data) > 0;
	}

    // ------------------------------------------------------------
    //       ---------------- PRIVATE --------------------
    // ------------------------------------------------------------

    /**
     * Just calls init
     */
    private function __construct() {}

    /**
     * Clears unwanted keys from the search instance data array
     *
     * @param $data array
     * @return array
     */
    private function clearData($data ) {
        $remove_keys = array('asp_options_serialized');

        if ( is_array($data) ) {
            foreach ($remove_keys as $key) {
                if ( isset($data[$key]) )
                    unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Fetches the search instances from the DB and stores them internally for future use
     */
    private function init() {
        global $wpdb;

        // Reset both variables, so in case of deleting no remains are left
        $this->search_instances = array();
        $this->instancesNoData = array();

        if ( !wd_asp()->db->exists('main') )
            return;

        $instances = $wpdb->get_results("SELECT * FROM ". wd_asp()->db->table('main') . " ORDER BY id ASC", ARRAY_A);

        foreach ($instances as $k => $instance) {
            $this->instancesNoData[$instance['id']] = array(
                "name" => $instance['name'],
                "id" => $instance['id']
            );

            $this->search_instances[$instance['id']] = $instance;
             /**
             * Explanation:
             *  1. json_decode(..) -> converts the params from the database to PHP format
             *  2. $this->decode_params(..) -> decodes params that are stored in base64 and prefixed to be decoded
             *
             * This is not equivalent with wd_parse_params(..) as that runs before inserting to the DB as well,
             * and $this->decode_params(..) runs after getting the data from the database, so it stays redundant.
              *
              * NOTE:
              *  raw_data is used in backwards compatibility checks, to see if a certain option exists before merging
              *           with the defaults
             */
            $this->search_instances[$instance['id']]['raw_data'] = $this->decode_params(json_decode($instance['data'], true));
            $this->search_instances[$instance['id']]['data'] = array_merge(
                wd_asp()->options['asp_defaults'],
                $this->search_instances[$instance['id']]['raw_data']
            );

            $this->search_instances[$instance['id']]['data'] = apply_filters("asp_instance_options", $this->search_instances[$instance['id']]['data'], $instance['id']);
        }
    }
}