<?php
/**
 * Event Countdown Elementor Widget.
 *
 * @since   5.4.0
 *
 * @package Tribe\Events\Pro\Integrations\Elementor\Widgets
 */

namespace Tribe\Events\Pro\Integrations\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Tribe\Events\Views\V2\Assets;

class Widget_Countdown extends Widget_Abstract {
	use Traits\Event_Query;

	/**
	 * {@inheritdoc}
	 */
	protected static $widget_slug = 'countdown';

	/**
	 * {@inheritdoc}
	 */
	protected $widget_icon = 'eicon-countdown';

	/**
	 * @var string
	 */
	protected $shortcode = 'tribe_event_countdown';

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		$this->widget_title = __( 'Event Countdown', 'tribe-events-calendar-pro' );
	}

	/**
	 * Render widget output.
	 *
	 * @since 5.4.0
	 */
	protected function render() {
		$settings             = $this->get_settings_for_display();
		$event_query_settings = $this->get_event_query_settings( $settings );
		$event_query_settings = $this->set_id_from_repository_if_unset( $event_query_settings );
		$settings             = wp_parse_args( $settings, $event_query_settings );
		$settings_string      = $this->get_shortcode_attribute_string( $settings, [
			'id',
			'slug',
			'show_seconds',
			'complete',
		] );

		echo do_shortcode( '[tribe_event_countdown ' . $settings_string . ']' );
	}

	/**
	 * Register widget controls.
	 *
	 * @since 5.4.0
	 */
	protected function register_controls() {
		$this->add_event_query_section();

		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'tribe-events-calendar-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_seconds',
			[
				'label'        => __( 'Display Seconds', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'complete',
			[
				'label'       => __( 'Completion Message', 'tribe-events-calendar-pro' ),
				'description' => __( 'Message to display when the countdown is complete.', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::TEXTAREA,
				'label_block' => true,
				'default'     => 'Hooray!',
			]
		);

		$this->end_controls_section();

		// Start style tab

		$this->start_controls_section(
			'event_countdown',
			[
				'label' => esc_html__( 'Event Countdown', 'tribe-events-calendar-pro' ),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'style_warning',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => esc_html__(
					'The style of this widget is often affected by your theme and plugins. If you experience an issue, try switching to a basic WordPress theme and deactivate related plugins.',
					'tribe-events-calendar-pro'
				),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->add_control(
			'heading_event_title_style',
			[
				'label' => esc_html__( 'Event Title', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'event_title_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-countdown__event-title-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_title_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget-countdown__event-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'event_title_text_shadow',
				'selector' => '{{WRAPPER}} .tribe-events-widget-countdown__event-title',
			]
		);

		$this->add_control(
			'heading_countdown_number_style',
			[
				'label' => esc_html__( 'Countdown Number', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'countdown_number_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget .tribe-events-widget-countdown__number' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'countdown_number_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget .tribe-events-widget-countdown__number',
			]
		);

		$this->add_control(
			'heading_countdown_text_style',
			[
				'label' => esc_html__( 'Countdown Text', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'countdown_text_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget .tribe-events-widget-countdown__under' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'countdown_text_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget .tribe-events-widget-countdown__under',
			]
		);

		$this->add_control(
			'heading_completion_message_style',
			[
				'label' => esc_html__( 'Completion Message', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'completion_message_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-countdown__complete' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'completion_message_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget-countdown__complete',
			]
		);

		$this->end_controls_section();

		// End style tab
	}

	/**
	 * Enqueues assets for this widget.
	 *
	 * @since 5.4.0
	 */
	public function enqueue_editor_assets() {
		tribe_asset_enqueue( 'tribe-events-views-v2-manager' );
		tribe_asset_enqueue( 'tribe-events-pro-widgets-v2-countdown' );
		tribe_asset_enqueue( 'tribe-events-pro-widgets-v2-countdown-skeleton' );
		tribe_asset_enqueue( 'tribe-events-virtual-skeleton' );

		if ( tribe( Assets::class )->should_enqueue_full_styles() ) {
			tribe_asset_enqueue( 'tribe-events-pro-widgets-v2-countdown-full' );
			tribe_asset_enqueue( 'tribe-events-virtual-full' );
		}
	}
}
