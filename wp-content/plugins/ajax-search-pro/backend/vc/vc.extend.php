<?php
if (!defined('ABSPATH')) die('-1');

/**
 * Class VCASPAddonClass
 *
 * Visual Composer integration class
 *
 * @class         VCASPAddonClass
 * @version       1.0
 * @package       AjaxSearchPro/Classes/BackEnd
 * @category      Class
 * @author        Ernest Marcinko
 */
class VCASPAddonClass {

    function __construct() {
        // We safely integrate with VC with this hook
        add_action( 'vc_before_init', array( $this, 'integrateWithVC' ) );

        add_shortcode( 'vc_asp_search', array( $this, 'renderVcAspSearch' ) );
        add_shortcode( 'vc_asp_results', array( $this, 'renderVcAspResults' ) );
        add_shortcode( 'vc_asp_settings', array( $this, 'renderVcAspSettings' ) );
        add_shortcode( 'vc_asp_twocolumn', array( $this, 'renderVcAspTwoColumn' ) );
    }

    /**
     * Integrates the Ajax Search Pro modules with Visual Composer
     *
     * @return bool|void
     */
    public function integrateWithVC() {
        // Check if Visual Composer is installed
        if ( ! defined( 'WPB_VC_VERSION' ) ) {
            return false;
        }

        $instances = wd_asp()->instances->getWithoutData();
        $vc_asp_instances = array();
        foreach ($instances as $k=>$v) {
            $vc_asp_instances[] = "(" . $v['id'] . ") " . $v['name'];
        }

        /* Return if there are no search instances created */
        if (count($vc_asp_instances) < 1) return false;

        /* The search bar shortcode */
        vc_map( array(
            "name" => "Search shortcode",
            "description" => "Adds a search bar.",
            "base" => "vc_asp_search",
            "class" => "",
            "controls" => "full",
            "icon" => plugins_url('assets/magnifier.png', __FILE__),
            "category" => __('Ajax Search Pro', 'js_composer'),
            "params" => array(
                array(
                    "type" => "dropdown",
                    "holder" => "",
                    "class" => "",
                    "heading" => __("Search instance", 'vc_extend'),
                    "param_name" => "id",
                    "value" => $vc_asp_instances,
                    "description" => __("Select the search instance to show", 'vc_extend')
                ),
                array(
                    "type" => "textfield",
                    "holder" => "",
                    "class" => "",
                    "heading" => __("Extra class names", 'vc_extend'),
                    "param_name" => "extra_class",
                    "value" => "",
                    "description" => __("An extra class names for the search field", 'vc_extend')
                )
            )
        ) );

        /* Results shortcode */
        vc_map( array(
            "name" => "Results shortcode",
            "description" => "Adds a results list.",
            "base" => "vc_asp_results",
            "class" => "",
            "controls" => "full",
            "icon" => plugins_url('assets/results.png', __FILE__),
            "category" => __('Ajax Search Pro', 'js_composer'),
            "params" => array(
                array(
                    "type" => "dropdown",
                    "holder" => "",
                    "class" => "",
                    "heading" => __("Search instance", 'vc_extend'),
                    "param_name" => "id",
                    "value" => $vc_asp_instances,
                    "description" => __("Select the search instance to show", 'vc_extend')
                ),
                array(
                    "type" => "textfield",
                    "holder" => "",
                    "class" => "",
                    "heading" => __("Extra class names", 'vc_extend'),
                    "param_name" => "extra_class",
                    "value" => "",
                    "description" => __("An extra class names for the search field", 'vc_extend')
                )
            )
        ) );

        /* Settings shortcode */
        vc_map( array(
            "name" => "Settings shortcode",
            "description" => "Adds a settings list.",
            "base" => "vc_asp_settings",
            "class" => "",
            "controls" => "full",
            "icon" => plugins_url('assets/settings.png', __FILE__),
            "category" => __('Ajax Search Pro', 'js_composer'),
            "params" => array(
                array(
                    "type" => "dropdown",
                    "holder" => "",
                    "class" => "",
                    "heading" => __("Search instance", 'vc_extend'),
                    "param_name" => "id",
                    "value" => $vc_asp_instances,
                    "description" => __("Select the search instance to show", 'vc_extend')
                ),
                array(
                    "type" => "textfield",
                    "holder" => "",
                    "class" => "",
                    "heading" => __("Extra class names", 'vc_extend'),
                    "param_name" => "extra_class",
                    "value" => "",
                    "description" => __("An extra class names for the search field", 'vc_extend')
                )
            )
        ) );

        /* Settings shortcode */
        vc_map( array(
            "name" => "Two column shortcode",
            "description" => "Adds a search box and a result list next to it.",
            "base" => "vc_asp_twocolumn",
            "class" => "",
            "controls" => "full",
            "icon" => plugins_url('assets/twocolumn.png', __FILE__),
            "category" => __('Ajax Search Pro', 'js_composer'),
            "params" => array(
                array(
                    "type" => "dropdown",
                    "holder" => "",
                    "class" => "",
                    "heading" => __("Search instance", 'vc_extend'),
                    "param_name" => "id",
                    "value" => $vc_asp_instances,
                    "description" => __("Select the search instance to show", 'vc_extend')
                ),
                array(
                    "type" => "textfield",
                    "holder" => "",
                    "class" => "",
                    "heading" => __("Search width (%)", 'vc_extend'),
                    "param_name" => "search_width",
                    "value" => 50,
                    "description" => __("The search field width in %", 'vc_extend')
                ),
                array(
                    "type" => "textfield",
                    "holder" => "",
                    "class" => "",
                    "heading" => __("Result list width (%)", 'vc_extend'),
                    "param_name" => "results_width",
                    "value" => 50,
                    "description" => __("Result list width in %", 'vc_extend')
                ),
                array(
                    "type" => "dropdown",
                    "holder" => "",
                    "class" => "",
                    "heading" => __("Layout", 'vc_extend'),
                    "param_name" => "invert",
                    "value" => array(
                        "Search left, results right",
                        "Results left, search right"
                    ),
                    "description" => __("Select the search instance to show", 'vc_extend')
                ),
                array(
                    "type" => "textfield",
                    "holder" => "",
                    "class" => "",
                    "heading" => __("Extra class names", 'vc_extend'),
                    "param_name" => "extra_class",
                    "value" => "",
                    "description" => __("An extra class names for the search field", 'vc_extend')
                )
            )
        ) );

    }

    /**
     * Renders the search module
     *
     * @param $atts
     * @param null $content
     * @return string
     */
    public function renderVcAspSearch( $atts, $content = null ) {

        extract( shortcode_atts( array(
            'id' => '',
            'extra_class' => ''
        ), $atts ) );
        if (function_exists('wpb_js_remove_wpautop'))
            $content = wpb_js_remove_wpautop($content, true);

	    // First item (or blank first) is selected, let the shortcode know
	    $id = ($id == "") ? "(99999) blank" : $id;

        preg_match("/^\((\d+)\)/", $id, $matches);

        if (!isset($matches[1]))
            return "";
        else
            $id = $matches[1];

        $output = do_shortcode("[wpdreams_ajaxsearchpro id=".$id." extra_class='$extra_class']");
        return $output;
    }

    /**
     * Renders the search results VC module
     *
     * @param $atts
     * @param null $content
     * @return string
     */
    public function renderVcAspResults( $atts, $content = null ) {
        extract( shortcode_atts( array(
            'id' => '',
            'extra_class' => ''
        ), $atts ) );
        if (function_exists('wpb_js_remove_wpautop'))
            $content = wpb_js_remove_wpautop($content, true);

	    // First item (or blank first) is selected, let the shortcode know
	    $id = ($id == "") ? "(99999) blank" : $id;

        preg_match("/^\((\d+)\)/", $id, $matches);
        if (!isset($matches[1]))
            return "";
        else
            $id = $matches[1];

        $output = do_shortcode("[wpdreams_ajaxsearchpro_results id=".$id." extra_class='$extra_class']");
        return $output;
    }

    /**
     * Renders the search settings VC module
     *
     * @param $atts
     * @param null $content
     * @return string
     */
    public function renderVcAspSettings( $atts, $content = null ) {
        extract( shortcode_atts( array(
            'id' => '',
            'extra_class' => ''
        ), $atts ) );
        if (function_exists('wpb_js_remove_wpautop'))
            $content = wpb_js_remove_wpautop($content, true);

	    // First item (or blank first) is selected, let the shortcode know
	    $id = ($id == "") ? "(99999) blank" : $id;

        preg_match("/^\((\d+)\)/", $id, $matches);
        if (!isset($matches[1]))
            return "";
        else
            $id = $matches[1];

        $output = do_shortcode("[wpdreams_asp_settings id=".$id." extra_class='$extra_class']");
        return $output;
    }

    /**
     * Renders the search TwoColumn VC module
     *
     * @param $atts
     * @param null $content
     * @return string
     */
    public function renderVcAspTwoColumn( $atts, $content = null ) {
        extract( shortcode_atts( array(
            'id' => '',
            'search_width' => 50,
            'results_width' => 50,
            'invert' => "Search left, results right",
            'extra_class' => ''
        ), $atts ) );

        if (function_exists('wpb_js_remove_wpautop'))
            $content = wpb_js_remove_wpautop($content, true);

	    // First item (or blank first) is selected, let the shortcode know
	    $id = ($id == "") ? "(99999) blank" : $id;

        preg_match("/^\((\d+)\)/", $id, $matches);
        if (!isset($matches[1]))
            return "";
        else
            $id = $matches[1];

        if ($invert == "Results left, search right")
            $invert = 1;
        else
            $invert = 0;

        $output = do_shortcode("[wpdreams_ajaxsearchpro_two_column id=".$id." search_width=".$search_width." results_width=".$results_width." invert=".$invert." extra_class='$extra_class']");
        return $output;
    }
}
// Finally initialize code
new VCASPAddonClass();