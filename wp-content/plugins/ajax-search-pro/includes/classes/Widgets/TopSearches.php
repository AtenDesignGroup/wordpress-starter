<?php
namespace WPDRMS\ASP\Widgets;

use WP_Widget;
use WPDRMS\ASP\Misc\Statistics;

if (!defined('ABSPATH')) die('-1');

class TopSearches extends WP_Widget {
	public static $instancenum;

	public function __construct() {
		$widget_ops = array(
			'classname'   => 'AjaxSearchProTopSearchesWidget',
			'description' => 'Displays the Top searches done by Ajax Search Pro.'
		);
		parent::__construct( 'AjaxSearchProTopSearchesWidget', 'Ajax Search Pro Top Searches', $widget_ops );
		self::$instancenum ++;
	}

	public function form( $instance ) {
		global $wpdb;
		$_prefix = $wpdb->base_prefix ?? $wpdb->prefix;
		$searches = $wpdb->get_results( "SELECT * FROM " . $_prefix . "ajaxsearchpro", ARRAY_A );

		$instance  = wp_parse_args( (array) $instance, array(
			'title' => '',
			'action' => 0,
			'number' => 10,
			'searchid' => 0,
			'targetid' => 0,
			'delimiter' => ','
		) );
		$title     = $instance['title'];
		$action    = $instance['action'];
		$number    = $instance['number'];
		$searchid  = $instance['searchid'];
		$targetid  = $instance['targetid'];
		$delimiter = $instance['delimiter'];
		?>
		<?php if (get_option("asp_stat", 0) == 0): ?>
			<p class="notice notice-error">
				The search statistics is turned OFF! This widget will not work unless you turn it on
				the <a href="<?php echo get_admin_url() . "admin.php?page=asp_statistics"; ?>">Search Statistics</a> submenu first.
			</p>
		<?php endif; ?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>">Title: <input class="widefat"
																					id="<?php echo $this->get_field_id( 'title' ); ?>"
																					name="<?php echo $this->get_field_name( 'title' ); ?>"
																					type="text"
																					value="<?php echo esc_attr( $title ); ?>"/></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'searchid' ); ?>">Source search form: </label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'searchid' ); ?>"
					id="<?php echo $this->get_field_id( 'searchid' ); ?>">
				<option value='0' <?php echo( ( esc_attr( $searchid ) == 0 ) ? "selected='selected'" : "''" ); ?>>All
				</option>
				<?php
				if ( is_array( $searches ) ) {
					foreach ( $searches as $search ) {
						echo "<option value='" . $search['id'] . "' " . ( ( esc_attr( $searchid ) == $search['id'] ) ? "selected='selected'" : "''" ) . ">" . esc_attr( $search['name'] ) . "</option>";
					}
				}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'action' ); ?>">Action on click: </label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'action' ); ?>"
					id="<?php echo $this->get_field_id( 'action' ); ?>">
				<option value='0' <?php echo( ( esc_attr( $action ) == 0 ) ? "selected='selected'" : "''" ); ?>>Do
					Nothing
				</option>
				<option value='1' <?php echo( ( esc_attr( $action ) == 1 ) ? "selected='selected'" : "''" ); ?>>Redirect
					to Default search page
				</option>
				<option value='2' <?php echo( ( esc_attr( $action ) == 2 ) ? "selected='selected'" : "''" ); ?>>Do an
					Ajax search with the target
				</option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>">Number: </label>
			<input type='text' class="widefat" name="<?php echo $this->get_field_name( 'number' ); ?>"
				   id="<?php echo $this->get_field_id( 'number' ); ?>"
				   value='<?php echo($number ?? '10'); ?>'/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'delimiter' ); ?>">Delimiter: </label>
			<input type='text' class="widefat" name="<?php echo $this->get_field_name( 'delimiter' ); ?>"
				   id="<?php echo $this->get_field_id( 'delimiter' ); ?>"
				   value='<?php echo($delimiter ?? ', '); ?>'/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'targetid' ); ?>">Target search form: </label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'targetid' ); ?>"
					id="<?php echo $this->get_field_id( 'targetid' ); ?>">
				<?php
				if ( is_array( $searches ) ) {
					foreach ( $searches as $search ) {
						echo "<option value='" . $search['id'] . "' " . ( ( esc_attr( $targetid ) == $search['id'] ) ? "selected='selected'" : "''" ) . ">" . esc_attr($search['name']) . "</option>";
					}
				}
				?>
			</select>
		</p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance              = $old_instance;
		$instance['title']     = $new_instance['title'];
		$instance['searchid']  = $new_instance['searchid'];
		$instance['targetid']  = $new_instance['targetid'];
		$instance['action']    = $new_instance['action'];
		$instance['number']    = $new_instance['number'];
		$instance['delimiter'] = $new_instance['delimiter'];

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
		$title       = empty( $instance['title'] ) ? ' ' : apply_filters( 'widget_title', $instance['title'] );
		$searchid    = empty( $instance['searchid'] ) ? '' : $instance['searchid'];
		$targetid    = empty( $instance['targetid'] ) ? '' : $instance['targetid'];
		$action      = ! isset( $instance['action'] ) ? '' : $instance['action'];
		$number      = empty( $instance['number'] ) ? '' : $instance['number'];
		$delimiter   = empty( $instance['delimiter'] ) ? '' : $instance['delimiter'];
		$instancenum = self::$instancenum ++;

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		if ( get_option("asp_stat", 0) == 0 ) {
			echo "<p>The search statistics are turned off! <br>To use this widget, please turn them on on the back-end, under the <strong>Ajax Search Pro -> Search Statistics</strong> submenu.</p>";
		} else {

			if ( empty($searchid) )
				$searchid = 0;

			$keywords = Statistics::getTop($number, $searchid, true);

			$i = 1;
			$html_data = json_encode(array(
				'instance' => $instancenum,
				'action' => $action,
				'id' => $targetid
			));

			?>
			<div class='ajaxsearchprotop ajaxsearhcprotop<?php echo $instancenum; ?> keywords'
				 data-aspdata="<?php echo htmlentities($html_data, ENT_QUOTES, 'UTF-8'); ?>">
				<?php
				if (empty($keywords)) {
					echo '<p>No top search phrases yet!</p>';
				} else {
					foreach ($keywords as $keyword) { ?>
						<a
						href='<?php echo get_bloginfo('wpurl') . "?s=" . $keyword['keyword']; ?>'><?php echo $keyword['keyword']; ?></a><?php echo(($i < count($keywords)) ? $delimiter : ""); ?>
						<?php $i++;
					}
				} ?>
			</div>
			<?php
		}
		echo $args['after_widget'];
	}
}