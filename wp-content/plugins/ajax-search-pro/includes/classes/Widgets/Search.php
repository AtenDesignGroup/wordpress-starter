<?php
namespace WPDRMS\ASP\Widgets;

use WP_Widget;

if (!defined('ABSPATH')) die('-1');

class Search extends WP_Widget {
	function __construct() {
		$widget_ops = array( 'classname' => 'AjaxSearchProWidget', 'description' => 'Displays an Ajax Search Pro!' );
		parent::__construct( 'AjaxSearchProWidget', 'Ajax Search Pro', $widget_ops );
	}

	function form( $instance ) {

		$searches = wd_asp()->instances->getWithoutData();
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title    = $instance['title'];
		$searchid = $instance['searchid'] ?? '';
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>">Title: <input
					class="widefat"
					id="<?php echo $this->get_field_id( 'title' ); ?>"
					name="<?php echo $this->get_field_name( 'title' ); ?>"
					type="text"
					value="<?php echo esc_attr( $title ); ?>"/></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'searchid' ); ?>">Select the search form: </label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'searchid' ); ?>"
					id="<?php echo $this->get_field_id( 'searchid' ); ?>">
				<?php
				if ( is_array( $searches ) && count($searches) > 0 ) {
					foreach ( $searches as $search ) {
						echo "<option value='" . $search['id'] . "' " . ( ( esc_attr( $searchid ) == $search['id'] ) ? "selected='selected'" : "''" ) . ">" . esc_attr($search['name']) . "</option>";
					}
				} else {
					echo '<option value="0" disabled>There are no search instances created yet!</option>';
				}
				?>
			</select>
		</p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance             = $old_instance;
		$instance['title']    = $new_instance['title'];
		$instance['searchid'] = $new_instance['searchid'];

		return $instance;
	}

	function widget( $args, $instance ) {
		$args = wp_parse_args($args, array(
			'before_title' => '',
			'after_title' => '',
			'before_widget' => '',
			'after_widget' => ''
		));
		echo $args['before_widget'];
		$title    = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );
		$searchid = empty( $instance['searchid'] ) ? '' : $instance['searchid'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		// WIDGET CODE GOES HERE
		if ( ! empty( $searchid ) ) {
			echo do_shortcode( "[wpdreams_ajaxsearchpro id=" . $searchid . "]" );
		}
		echo $args['after_widget'];
	}
}