<?php
/**
 * The template for the recurrence not supported with tickets message.
 */
?>
<tr class="recurrence-row tribe-recurrence-not-supported tec-events-pro-recurrence-not-supported">
	<td>
		<label>
			<?php
			echo esc_html(
				sprintf(
					/* Translators: %1$s - single event term. */
					__( 'Recurring %1$s.', 'tribe-events-calendar-pro' ),
					tribe_get_event_label_singular()
				)
			);
			?>
		</label>
	</td>
	<td>
	<p class="tec-events-pro-recurrence-not-supported__text">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %1$s: event (plural), %2$s: ticket (plural), %3$s: RSVP (plural). */
				__( '%2$s and %3$s are not yet supported on recurring %1$s.', 'tribe-events-calendar-pro' ),
				tribe_get_event_label_plural_lowercase(),
				tribe_get_ticket_label_plural_lowercase(),
				tribe_get_rsvp_label_plural()
			)
		);
		?>
		<br />
		<a href="https://evnt.is/1b7a" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Read about our plans for future features.', 'tribe-events-calendar-pro' ); ?>
		</a>
	</p>
	</td>
</tr>
