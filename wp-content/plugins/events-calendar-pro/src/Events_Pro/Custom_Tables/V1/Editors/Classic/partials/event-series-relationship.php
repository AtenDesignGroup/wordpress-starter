<?php
/**
 * The template for the metabox field allowing the connection from Events to Series.
 *
 * @var string                        $field_name       The input `name` and `id` attribute..
 * @var array<int,array<string|bool>> $series           A map of the available Series, by post ID.
 * @var bool                          $creation_enabled Whether the current user is allowed to create Series.
 * @var string                        $create_label     The creation label.
 * @var bool                          $clear_enabled    Whether clearing the dropdown selection is allowed or not.
 */
?>
<div class="tec-events-pro-series">
	<label
		class="tec-events-pro-series__label screen-reader-text"
		for="<?php echo esc_attr( $field_name ); ?>"
	>
		<?php esc_html_e( 'Assign event to series', 'tribe-events-calendar-pro' ); ?>
	</label>
	<select
		tabindex="-1"
		id="<?php echo esc_attr( $field_name ); ?>"
		name="<?php echo esc_attr( $field_name ); ?>[]"
		class="tribe-dropdown tec-events-pro-series__dropdown"
		aria-hidden="true"
		data-dropdown-css-width="0"
		style="width: 100%;"
		data-placeholder="<?php echo esc_attr( $create_label ); ?>"
		data-search-placeholder="<?php echo esc_attr( $create_label ); ?>"
		<?php if ( $creation_enabled ) : ?>
			data-freeform
			data-sticky-search
			data-create-choice-template="<?php echo __( 'Create: <%= term %> (Draft)', 'tribe-events-calendar-pro' ); ?>"
			data-allow-html
			data-force-search
		<?php endif; ?>
		<?php if ( ! $clear_enabled ) : ?>
			data-prevent-clear
		<?php endif; ?>
	>
		<option
			class="tec-events-pro-series__dropdown-option"
			value="-1"
			<?php selected( ! $has_selection ); ?>
		>
			<?php echo esc_html( $create_label ); ?>
		</option>

		<?php foreach ( $series as $series_id => list( $series_title, $selected, $edit_link ) ): ?>
			<option
				value="<?php echo esc_attr( json_encode( [ 'id' => $series_id, 'title' => $series_title ] ) ); ?>"
				data-edit-link="<?php echo esc_url( $edit_link ); ?>"
				<?php selected( (bool) $selected ) ?>>
				<?php echo esc_html( $series_title ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<div <?php tribe_classes( [ 'tec-events-pro-series__edit-link-container', 'hidden' => ! $has_selection ] ); ?>>
		<a href="<?php echo esc_url( $edit_series_link ); ?>" class="tec-events-pro-series__edit-link" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Edit series', 'tribe-events-calendar-pro' ); ?>
		</a>
	</div>
</div>
