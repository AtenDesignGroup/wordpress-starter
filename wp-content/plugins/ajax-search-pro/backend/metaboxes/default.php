<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * Calls the class on the post edit screen.
 */
function call_WD_ASP_DefaultMetaBox() {
    new WD_ASP_DefaultMetaBox();
}

if ( is_admin() ) {
    add_action( 'load-post.php', 'call_WD_ASP_DefaultMetaBox' );
    add_action( 'load-post-new.php', 'call_WD_ASP_DefaultMetaBox' );
}

/**
 * The Class.
 */
class WD_ASP_DefaultMetaBox {

    private $asp_default_metadata = array(
        "asp_suggested_phrases" =>"",
        "asp_suggested_instances" => 0
    );

    /**
     * Hook into the appropriate actions when the class is constructed.
     */
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post', array( $this, 'save' ) );
        add_action( 'edit_attachment', array( $this, 'save' ) );
    }

    /**
     * Adds the meta box container.
     */
    public function add_meta_box( $post_type ) {
        $post_types = wd_asp()->o['asp_compatibility']['meta_box_post_types']; //Allow only for selected post types
        $post_types = explode('|', $post_types);

        if ( count($post_types) > 0 && in_array( $post_type, $post_types )) {
            wp_register_style('wpdreams-style', ASP_URL_NP . 'backend/settings/assets/style.css', array(), ASP_CURR_VER_STRING);
            wp_enqueue_style('wpdreams-style');

            add_meta_box(
                'asp_metadata'
                ,__( 'Ajax Search Pro settings', 'ajax-search-pro' )
                ,array( $this, 'render_meta_box_content' )
                ,$post_type
                ,'advanced'
                ,'high'
            );
        }
    }

    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     * @return int $post_id
     */
    public function save( $post_id ) {

        /*
         * We need to verify this came from the our screen and with proper authorization,
         * because save_post can be triggered at other times.
         */
        // Check if our nonce is set.
        if ( ! isset( $_POST['asp_meta_custom_box_nonce'] ) )
            return $post_id;

        $nonce = $_POST['asp_meta_custom_box_nonce'];

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'asp_meta_custom_box' ) )
            return $post_id;

        // If this is an autosave, our form has not been submitted,
        //     so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {

            if ( ! current_user_can( 'edit_page', $post_id ) )
                return $post_id;

        } else {

            if ( ! current_user_can( 'edit_post', $post_id ) )
                return $post_id;
        }

        $posted = array();
        // Gather the posted data, but only the ones related to ASP
        foreach ( $this->asp_default_metadata as $k => $v) {
            if ( isset($_POST[$k]) )
                $posted[$k] = $_POST[$k];
        }

        // If it is the defaults, no need to store it at all.
        if ($posted == $this->asp_default_metadata) {
            delete_post_meta($post_id, '_asp_metadata');
        } else {
            update_post_meta( $post_id, '_asp_metadata', $posted );
        }

        if ( isset($_POST['asp_additional_tags']) ) {
            $blog_id = "x1";
            if ( is_multisite() )
                $blog_id = "x" . get_current_blog_id();

			$add_tags = trim( preg_replace('/\s+/', ' ',$_POST['asp_additional_tags']) );
            $add_tags = str_replace(array(',,'), ',', $add_tags);
			$add_tags = str_replace(array(', ,'), ',', $add_tags);
            if ( empty($add_tags) ) {
                delete_post_meta($post_id, '_asp_additional_tags');
                foreach (array_keys(wd_asp()->o['asp_glob']['additional_tag_posts'], $post_id.$blog_id, true) as $key) {
                    unset(wd_asp()->o['asp_glob']['additional_tag_posts'][$key]);
                }
            } else {
                update_post_meta($post_id, '_asp_additional_tags', $add_tags);
                if ( !in_array($post_id.$blog_id, wd_asp()->o['asp_glob']['additional_tag_posts']))
                    wd_asp()->o['asp_glob']['additional_tag_posts'][] = $post_id.$blog_id;
            }
            asp_save_option('asp_glob', true);
        }

        if ( isset($_POST['asp_negative_keywords']) ) {
            $negative_keywords = str_replace(array(', ', ','), ' ', $_POST['asp_negative_keywords']);
            $negative_keywords = trim( preg_replace('/\s+/', ' ',$negative_keywords) );
            if ( empty($negative_keywords) ) {
                delete_post_meta($post_id, '_asp_negative_keywords');
            } else {
                update_post_meta($post_id, '_asp_negative_keywords', $negative_keywords);
            }
        }

        return $post_id;
    }


    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function render_meta_box_content( $post ) {

        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'asp_meta_custom_box', 'asp_meta_custom_box_nonce' );

        // Use get_post_meta to retrieve an existing value from the database.
        $asp_metadata = get_post_meta( $post->ID, '_asp_metadata', true );

        if ( !is_array($asp_metadata) )
            $asp_metadata = array();

        $asp_metadata = array_merge($this->asp_default_metadata, $asp_metadata);

        ?>
        <style>
        #wpdreams .asp_option_box {
            border-bottom: 1px dashed #cacaca;
            margin: 15px auto 0;
            padding: 0 0 15px 0 !important;
        }
        #wpdreams .asp_option_box:first-child {
            margin: 0 auto;
        }
        #wpdreams .asp_option_box:last-child {
            border-bottom: none;
        }
        #wpdreams .asp_sugg_meta {
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
        }
        #wpdreams .asp_sugg_meta>textarea {
            height: 50px;
        }
        </style>
        <div id='wpdreams' class='asp-be wpdreams wrap'>
            <div class='wpdreams-box'>
                <div class='asp_option_box'>
                    <div class="item asp_option_meta" style="vertical-align: top;">
                        <label style="vertical-align: top;">
                            <?php echo __('Additional search tags for this post (comma separated)', 'ajax-search-pro'); ?>
                        </label>
                        <textarea placeholder="<?php echo __('Enter phrases here, separated by comma', 'ajax-search-pro'); ?>"
                                  style="background-image: none;background-position: 0% 0%;background-repeat: repeat;" name="asp_additional_tags"><?php echo get_post_meta( $post->ID, '_asp_additional_tags', true ); ?></textarea>
                    </div>
                    <p class="descMsg">
                        <?php echo __('Enter additional words here, which you also want to be able to search for.', 'ajax-search-pro'); ?>
                    </p>
                </div>
                <div class='asp_option_box'>
                    <div class="item asp_option_meta" style="vertical-align: top;">
                        <label style="vertical-align: top;">
                            <?php echo __('Negative keywords (space separated)', 'ajax-search-pro'); ?>
                        </label>
                        <textarea placeholder="<?php echo __('Enter keywords here, separated by space', 'ajax-search-pro'); ?>"
                                  style="background-image: none;background-position: 0% 0%;background-repeat: repeat;" name="asp_negative_keywords"><?php echo get_post_meta( $post->ID, '_asp_negative_keywords', true ); ?></textarea>
                    </div>
                    <p class="descMsg">
                        <?php echo sprintf( __('This only works with the <a href="%s" target="_blank">index table engine!</a>', 'ajax-search-pro'),
                            'https://documentation.ajaxsearchpro.com/index-table'); ?>
                        &nbsp;<?php __('Negative keywords are excluded from indexing, within all fields of this post.', 'ajax-search-pro'); ?>
                    </p>
                </div>
                <div class='asp_option_box'>
                    <div class="item asp_sugg_meta" style="vertical-align: top;">
                        <label style="vertical-align: top;">
                            <?php echo __('"Try these" - custom phrase suggestions for this post (comma separated)', 'ajax-search-pro'); ?>
                        </label>
                        <textarea style="    background-image: none;background-position: 0% 0%;background-repeat: repeat;" name="asp_suggested_phrases"><?php echo $asp_metadata['asp_suggested_phrases']; ?></textarea>
                        <label style="vertical-align: top;"><?php echo __('for', 'ajax-search-pro'); ?></label>
                        <select name="asp_suggested_instances" style="vertical-align: top;">
                            <option value="0"<?php echo $asp_metadata['asp_suggested_instances'] == 0 ? " selected" : ""; ?>><?php echo __('All search instances', 'ajax-search-pro'); ?></option>
                            <?php foreach( wd_asp()->instances->getWithoutData() as $id=>$data ): ?>
                                <option value="<?php echo $id; ?>"<?php echo $asp_metadata['asp_suggested_instances'] == $id ? " selected" : ""; ?>><?php echo esc_html($data['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <p class="descMsg">
                        <?php echo __('These are the tags displaying under the search bar as suggestions if enabled.', 'ajax-search-pro'); ?>
                        <br><img src="<?php echo ASP_URL . "img/editor/kw_suggestions.png"; ?>">
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
}