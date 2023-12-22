<?php
/**
 * This is the dynamic stylesheet file of the plugin. Don't edit unless necessary.
 *
 * If you are wondering, why so many weird rules? Well, some themes appear to use
 * IDs in selectors instead of sticking only with classes,
 * thus making it inevitable to use stronger CSS specificities.
 */

// Since version 3.0 it is possible to use multiple instances, we need at least 2 IDs to be safe

defined('ABSPATH') or die("You can't access this file directly.");

$use_compatibility = true;
$use_strong_compatibility = true;
$base_css = "";

$asp_comp = get_option('asp_compatibility');
$comp_level = w_isset_def($asp_comp["css_compatibility_level"], "medium");

if ($comp_level == "medium") {
    $use_compatibility = true;
    $use_strong_compatibility = false;
} else if ($comp_level == "maximum") {
    $use_compatibility = true;
    $use_strong_compatibility = true;
} else {
    $use_compatibility = false;
    $use_strong_compatibility = false;
}

$asp_container_class = '.asp_w_container.asp_w_container_'.$id;

$asp_div_ids1 = '#ajaxsearchpro'.$id.'_1';
$asp_div_ids2 = '#ajaxsearchpro'.$id.'_2';

$asp_res_ids1 = '#ajaxsearchprores'.$id.'_1';
$asp_res_ids2 = '#ajaxsearchprores'.$id.'_2';

$asp_set_ids1 = '#ajaxsearchprosettings'.$id.'_1';
$asp_set_ids2 = '#ajaxsearchprosettings'.$id.'_2';

$asp_bset_ids1 = '#ajaxsearchprobsettings'.$id.'_1';
$asp_bset_ids2 = '#ajaxsearchprobsettings'.$id.'_2';

$asp_div_ids = 'div.asp_m.asp_m_'.$id;
$asp_res_ids = 'div.asp_r.asp_r_'.$id;
$asp_set_ids = 'div.asp_s.asp_s_'.$id;
$asp_bset_ids = 'div.asp_sb.asp_sb_'.$id;


if (!class_exists('aspCSSCompatibilityReplace')) {
    class aspCSSCompatibilityReplace {
        private $id;
        private $end;
        private $prefix;

        function __construct($id, $end = false, $prefix = '') {
            $this->id = $id;
            $this->end = $end;
            $this->prefix = $prefix;
        }

        function setPrefix($prefix) {
            $this->prefix = $prefix;
        }

        function callback($matches) {
            $id = $this->id;
            if (!isset($matches[1])) return "";
            if (strpos($matches[1], "*=") < 0) return $matches[1];
            $instance1 = str_replace(
                array(
                    "*='ajaxsearchpro']",
                    "*='ajaxsearchprosettings']",
                    "*='ajaxsearchprobsettings']",
                    "*='ajaxsearchprores']"
                ),
                array(
                    '#ajaxsearchpro'.$id.'_1',
                    '#ajaxsearchprosettings'.$id.'_1',
                    '#ajaxsearchprobsettings'.$id.'_1',
                    '#ajaxsearchprores'.$id.'_1'
                ),
                $matches[1]);
            $instance2 = str_replace(
                array(
                    "*='ajaxsearchpro']",
                    "*='ajaxsearchprosettings']",
                    "*='ajaxsearchprobsettings']",
                    "*='ajaxsearchprores']"
                ),
                array(
                    '#ajaxsearchpro'.$id.'_2',
                    '#ajaxsearchprosettings'.$id.'_2',
                    '#ajaxsearchprobsettings'.$id.'_2',
                    '#ajaxsearchprores'.$id.'_2'
                ),
                $matches[1]);
            if ($this->end)
                return $instance1 . ", " . $instance2 . " {";
            else
                return $instance1 . " " . $instance2;
        }

        function callback2($matches) {
            $id = $this->id;
            if (!isset($matches[1])) return "";
            if ($this->end)
                return $this->prefix.$id.'_1'. $matches[1] . ", " . $this->prefix.$id.'_2'. $matches[1] . " {";
            else
                return $this->prefix.$id.'_1'. $matches[1] . " " . $this->prefix.$id.'_2'. $matches[1];
        }
    }
}

// Load the corresponding CSS3 loader
$asp_loader_css_full = file_get_contents(ASP_CSS_PATH . "style.loaders.css");
$asp_loader_css = asp_get_inner_substring( $asp_loader_css_full, "/*[general]*/");
$asp_loader_css .= " " . asp_get_inner_substring( $asp_loader_css_full, "/*[" . w_isset_def($style['loader_image'], "simple-circle") . "]*/");
$asp_loader_css = str_replace("#fff", $style['loadingimage_color'], $asp_loader_css);

// Print the loader CSS, without the changes (so same instances above 2 will work)
echo str_replace("id*='ajaxsearchpro'", "id*='ajaxsearchpro".$id."_'", $asp_loader_css);
echo str_replace("id*='ajaxsearchpro']", "id*='ajaxsearchprores".$id."_'] .asp_res_loader", $asp_loader_css);

// Load the required CSS3 animation
$asp_anim_css_full = file_get_contents(ASP_CSS_PATH . "animations.css");
$asp_anim_css =  asp_get_inner_substring( $asp_anim_css_full, "/*[" . w_isset_def($style['res_items_animation'], "fadeInDown") . "]*/");
$asp_anim_css_mob =  asp_get_inner_substring( $asp_anim_css_full, "/*[" . w_isset_def($style['res_items_animation_m'], "voidanim") . "]*/");

if ( $asp_anim_css != $asp_anim_css_mob )
    $asp_anim_css .= " " . $asp_anim_css_mob;

// Do some stuff with the base CSS if the compatibility level is Maximum
if ($use_strong_compatibility == true) {
    ob_start();
    include(ASP_PATH . "/css/style.basic.css.php");
    $basic_css = ob_get_clean();
    $orinal_base_css = $base_css;
    $css_helper = new aspCSSCompatibilityReplace($id, false, '#ajaxsearchpro');
    $base_css = preg_replace_callback("/^div\.asp_w\.ajaxsearchpro(.*?[ ]*,)/im", array($css_helper, "callback2"), $base_css);
    $css_helper->setPrefix('#ajaxsearchprobsettings');
    $base_css = preg_replace_callback("/^div\.asp_w\.asp_sb(.*?[ ]*,)/im", array($css_helper, "callback2"), $base_css);
    $css_helper->setPrefix('#ajaxsearchprosettings');
    $base_css = preg_replace_callback("/^div\.asp_w\.asp_s(.*?[ ]*,)/im", array($css_helper, "callback2"), $base_css);
    $css_helper->setPrefix('#ajaxsearchprores');
    $base_css = preg_replace_callback("/^div\.asp_w\.asp_r(.*?[ ]*,)/im", array($css_helper, "callback2"), $base_css);
    //$base_css = preg_replace_callback("/^div\.ajaxsearchpro\[id(.*?[ ]*,)/im", array($css_helper, "callback2"), $base_css);

    $css_helper = new aspCSSCompatibilityReplace($id, true, '#ajaxsearchpro');
    $base_css = preg_replace_callback("/^div\.asp_w\.ajaxsearchpro(.*?)[ ]*\{/im", array($css_helper, "callback2"), $base_css);
    $css_helper->setPrefix('#ajaxsearchprobsettings');
    $base_css = preg_replace_callback("/^div\.asp_w\.asp_sb(.*?)[ ]*\{/im", array($css_helper, "callback2"), $base_css);
    $css_helper->setPrefix('#ajaxsearchprosettings');
    $base_css = preg_replace_callback("/^div\.asp_w\.asp_s(.*?)[ ]*\{/im", array($css_helper, "callback2"), $base_css);
    $css_helper->setPrefix('#ajaxsearchprores');
    $base_css = preg_replace_callback("/^div\.asp_w\.asp_r(.*?)[ ]*\{/im", array($css_helper, "callback2"), $base_css);

    $base_css = $orinal_base_css . ' '. $base_css;
}

$css_helper = new aspCSSCompatibilityReplace($id);
$asp_loader_css = preg_replace_callback("/^div\[id(.*?[ ]*,)/im", array($css_helper, "callback"), $asp_loader_css);
$css_helper = new aspCSSCompatibilityReplace($id, true);
$asp_loader_css = preg_replace_callback("/^div\[id(.*?)[ ]*\{/im", array($css_helper, "callback"), $asp_loader_css);

echo $base_css;
echo $asp_loader_css;
echo $asp_anim_css;
include(ASP_PATH . "/css/style.shared.css.php");
include(ASP_PATH . "/css/style.".$style['resultstype'].".css.php");
// Custom CSS
foreach ( array('custom_css', 'custom_css_h') as $_css_key ) {
	if ( $style[$_css_key] != '' ) {
		if ( base64_decode($style[$_css_key], true) == true ) {
			$asp_c_css = stripcslashes( base64_decode($style[$_css_key]) );
		} else {
			$asp_c_css = stripcslashes( $style[$_css_key] );
		}
		echo str_replace( "_aspid", $id, $asp_c_css );
	}
}
echo "/* Generated at: ".date("Y-m-d H:i:s", time())." */";
