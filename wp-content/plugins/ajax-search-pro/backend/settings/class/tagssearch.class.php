<?php

use WPDRMS\ASP\Utils\Ajax;

if (!class_exists("wpdreamsSearchTags")) {
    /**
     * Class wpdreamsSearchTags
     *
     * Displays a tag selector element.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2015, Ernest Marcinko
     */
    class wpdreamsSearchTags extends wpdreamsType {
        private $selected;

        function getType() {
            parent::getType();
            $this->processData();
            ?>
            <div class="wpdreamsSearchTags" id="wpdreamsSearchTags-<?php echo self::$_instancenumber; ?>">
                <fieldset>
                <legend><?php echo $this->label; ?></legend>

                <div class="wd_tagSelSearch">
                    <span class="loading-small hiddend"></span>
                    <div class="wd_ts_close hiddend">
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="24px" viewBox="0 0 512 512" enable-background="new 0 0 512 512" xml:space="preserve">
                            <polygon id="x-mark-icon" points="438.393,374.595 319.757,255.977 438.378,137.348 374.595,73.607 255.995,192.225 137.375,73.622 73.607,137.352 192.246,255.983 73.622,374.625 137.352,438.393 256.002,319.734 374.652,438.378 "></polygon>
                        </svg>
                    </div>
                    <input type="hidden" class="wd_tag_search_nonce" value="<?php echo wp_create_nonce( 'wd_tag_search_nonce' ); ?>">
                    <input type="text" value="" placeholder="<?php esc_attr_e('Search for tags', 'ajax-search-pro'); ?>" class="wd_tagSelectSearch">
                    <div class="wd_tagSearchResults"></div>
                </div>

                <div class="wd_tagSelectContent">
                    <?php
                    $tags = array();
                    if ( count($this->selected) > 0 )
                        $tags = get_terms("post_tag", array("include" => $this->selected));

                    foreach ($tags as $tag) {
                        echo "<span class='wd_tag' tagid='".$tag->term_id."'>
                              <a class='wd_tag_remove'></a>
                              ".$tag->name."</span>";
                    }
                    ?>
                </div>

                <input isparam=1 type="hidden" value='<?php echo $this->data; ?>' name="<?php echo $this->name; ?>">
                <input type='hidden' value='wpdreamsSearchTags' name='classname-<?php echo $this->name; ?>'>
                </fieldset>
            </div>
        <?php
        }

        function processData() {

            $this->data = trim($this->data);
            if ( $this->data!= "")
                $this->selected = explode("|", $this->data);
            else
                $this->selected = array();

        }

        final function getData() {
            return $this->data;
        }

        final function getSelected() {
            return $this->selected;
        }

        static function searchTag() {
            if ( 
                isset($_POST['wd_tag_phrase'], $_POST['wd_tag_search_nonce']) &&
                current_user_can('administrator') && 
                wp_verify_nonce( $_POST["wd_tag_search_nonce"], 'wd_tag_search_nonce' ) 
            ) {
                $phrase = $_POST["wd_tag_phrase"];
                $tags = get_terms(array("post_tag"), array('search' => $phrase, 'number' => 50));
                $ret = "";
                if ( count($tags) > 0 )
                    foreach ($tags as $tag) {
                        $ret .= "<p>".$tag->name."<span termid='".$tag->term_id."'>>>" . __('ADD', 'ajax-search-pro') . "</span></p>";
                    }
                else
                    $ret = __('No tags found for this phrase', 'ajax-search-pro');
                Ajax::prepareHeaders();
                print "!!WDSTART!!" . $ret . "!!WDEND!!";
            }
            die();
        }
    }
}

if ( !has_action('wp_ajax_wd_search_tags') )
    add_action('wp_ajax_wd_search_tags', 'wpdreamsSearchTags::searchTag');