<?php

use TEC\Events\Custom_Tables\V1\Models\Event;
use TEC\Events\Custom_Tables\V1\Models\Occurrence;

if ( empty( $events ) ) :
	_e( 'No events available to attach to a series.', 'tribe-events-calendar-pro' );
else : ?>
	<div class="tec-events-pro-series">
		<label class="tec-events-pro-series__label screen-reader-text" for="tec-events-pro-series__dropdown">
			<?php esc_html_e( 'Events: ', 'tribe-events-calendar-pro' ); ?>
		</label>
		<select
			tabindex="-1"
			name="<?php echo esc_attr( $field_name ); ?>[]"
			class="tribe-dropdown tec-events-pro-series__dropdown"
			id="<?php echo esc_attr( $field_name ); ?>"
			aria-hidden="true"
			data-dropdown-css-width="0"
			data-maximum-selection-size="3"
			data-prevent-clear
			data-attach-container
			data-placeholder="<?php esc_attr_e( 'Search events', 'tribe-events-calendar-pro' ); ?>"
			style="width: 100%;"
			multiple
		>
			<?php foreach ( $events as list( $post, $selected ) ): ?>
				<option
					class="tec-events-pro-series__dropdown-option"
					value="<?php echo esc_attr( $post->ID ); ?>"
					data-status="<?php echo esc_attr( $post->post_status ); ?>"
					data-status-label="<?php echo esc_attr( get_post_statuses()[ $post->post_status ] ); ?>"
					data-recurring="<?php echo esc_attr( $post->recurring ? 1 : 0 ); ?>"
					<?php
					$format = tribe_get_date_format( true );
					if ( $post->recurring ) :
						$recurrence_count        = Occurrence::where( 'post_id', $post->ID )->count();
						$recurrence_count_events = sprintf( _n( '(%s event)', '(%s events)', $recurrence_count, 'tribe-events-calendar-pro' ), $recurrence_count );
						$event_obj               = Event::find( $post->ID, 'post_id' );
						$first_start_date        = tribe_format_date( $event_obj->start_date, false, $format );
						$last_end_date           = tribe_format_date( $event_obj->end_date, false, $format );
						?>
						data-recurrence-first-start-date="<?php echo esc_attr( $first_start_date ); ?>"
						data-recurrence-last-end-date="<?php echo esc_attr( $last_end_date ); ?>"
						data-recurrence-count="<?php echo esc_attr( $recurrence_count_events ); ?>"
						<?php
					else :
						if ( $post->dates->start_display instanceof DateTimeInterface ) {
							$start_date = $post->dates->start_display->format( $format );
						} else {
							$start_date = tribe_format_date( $post->dates->start_display, false, $format );
						}
						?>
						data-start-date="<?php echo esc_attr( $start_date ); ?>"
						<?php
					endif;
					?>
					<?php selected( (bool) $selected ) ?>
				>
					<?php echo esc_html( $post->post_title ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<div class="tec-events-pro-series__selections">
			<span class="tec-events-pro-series__selections-label hidden">
				<?php esc_html_e( 'Update this Series to add the selected events:', 'tribe-events-calendar-pro' ); ?>
			</span>
			<ul class="tec-events-pro-series__selections-list"></ul>
		</div>
		<script class="tec-events-pro-series__result-template" id="tec-events-pro-series__result-template" type="text/template">
			<div class="tec-events-pro-series__result">
				<div class="tec-events-pro-series__result-label">
					<span class="tec-events-pro-series__result-label-title"></span>
					<svg viewBox="0 0 12 12" width="12" height="12"><title><?php echo __( 'Recurring', 'tribe-events-calendar-pro' ); ?></title><use xlink:href="#recurring" /></svg>
					<span class="tec-events-pro-series__result-label-count-events"></span>
					<span class="tec-events-pro-series__result-label-status"></span>
				</div>
				<div class="tec-events-pro-series__result-date"></div>
			</div>
		</script>
		<script class="tec-events-pro-series__selection-template" id="tec-events-pro-series__selection-template" type="text/template">
			<div class="tec-events-pro-series__selection">
				<span class="tec-events-pro-series__selection-title"></span>
				<svg viewBox="0 0 12 12" width="12" height="12"><title><?php echo __( 'Recurring', 'tribe-events-calendar-pro' ); ?></title><use xlink:href="#recurring" /></svg>
				<span class="tec-events-pro-series__selection-count-events"></span>
			</div>
		</script>
	</div>
<?php endif; ?>
