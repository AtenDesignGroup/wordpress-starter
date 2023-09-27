<?php
/**
 * Countdown View Elementor Widget.
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
use Tribe\Events\Pro\Integrations\Elementor\Traits;

class Widget_Event_List extends Widget_Abstract {
	use Traits\Tags;

	/**
	 * {@inheritdoc}
	 */
	protected static $widget_slug = 'events_list_widget';

	/**
	 * {@inheritdoc}
	 */
	protected $widget_icon = 'eicon-post-list';

	/**
	 * @var string
	 */
	protected $shortcode = 'tribe_events_list';

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		$this->widget_title = __( 'Events List', 'tribe-events-calendar-pro' );
	}

	/**
	 * Render widget output.
	 *
	 * @since 5.4.0
	 */
	protected function render() {
		$settings        = $this->get_settings_for_display();
		$settings_string = $this->get_shortcode_attribute_string(
			$settings,
			[
				'category',
				'city',
				'cost',
				'country',
				'limit',
				'organizer',
				'phone',
				'region',
				'street',
				'tag',
				'venue',
				'zip',
				'website',
			]
		);

		echo do_shortcode( '[tribe_events_list ' . $settings_string . ']' );
	}

	/**
	 * Register widget controls.
	 *
	 * @since 5.4.0
	 */
	protected function register_controls() {
		// Content Tab
		$this->do_content_panel();

		// Style tab
		$this->do_style_panel();
	}

	protected function do_content_panel() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'tribe-events-calendar-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

			$this->add_control(
				'limit',
				[
					'label'        => __( 'Maximum Events', 'tribe-events-calendar-pro' ),
					'description'  => __( 'The maximum number of events this widget should show.', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::TEXT,
					'label_block'  => true,
					'default'      => '',
				]
			);

			$this->start_controls_tabs( 'options_tabs' );

			$this->do_option_view_tab();

			$this->do_option_event_tab();

			$this->end_controls_tabs();

		$this->end_controls_section();
	}

	protected function do_option_view_tab() {
		$this->start_controls_tab(
			'option_view_tab',
			[
				'label' => __( 'View Options', 'tribe-events-calendar-pro' ),
			]
		);

			$this->add_control(
				'cost',
				[
					'label'        => __( 'Cost', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'no',
				]
			);

			$this->add_control(
				'organizer',
				[
					'label'        => __( 'Organizer', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'no',
				]
			);

			$this->add_control(
				'venue',
				[
					'label'        => __( 'Venue', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'no',
				]
			);

			$this->add_control(
				'street',
				[
					'label'        => __( 'Street', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'no',
					'separator'    => 'before',
				]
			);

			$this->add_control(
				'country',
				[
					'label'        => __( 'Country', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'no',
				]
			);

			$this->add_control(
				'city',
				[
					'label'        => __( 'City', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'no',
				]
			);

			$this->add_control(
				'region',
				[
					'label'        => __( 'Region', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'no',
				]
			);

			$this->add_control(
				'zip',
				[
					'label'        => __( 'Zip', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'no',
				]
			);

			$this->add_control(
				'phone',
				[
					'label'        => __( 'Phone', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'no',
					'separator'    => 'before',
				]
			);

			$this->add_control(
				'website',
				[
					'label'        => __( 'Website', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Yes', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'No', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'no',
				]
			);

		$this->end_controls_tab();
	}

	protected function do_option_event_tab() {
		$this->start_controls_tab(
			'option_event_tab',
			[
				'label' => __( 'Event Options', 'tribe-events-calendar-pro' ),
			]
		);

			$this->add_control(
				'category',
				[
					'label'       => __( 'Category', 'tribe-events-calendar-pro' ),
					'type'        => Controls_Manager::SELECT2,
					'options'     => $this->get_event_categories(),
					'label_block' => true,
					'multiple'    => true,
				]
			);

			$this->add_control(
				'tag',
				[
					'label'       => __( 'Tag', 'tribe-events-calendar-pro' ),
					'type'        => Controls_Manager::SELECT2,
					'options'     => $this->get_event_tags(),
					'label_block' => true,
					'multiple'    => true,
				]
			);

		$this->end_controls_tab();
	}

	protected function do_style_panel() {

		// Events section (warning)
		$this->do_events_styles();

		// Date section
		$this->do_date_section();

		// Featured event section
		$this->do_featured_event_section();

		// Time section
		$this->do_time_section();

		// Title section
		$this->do_title_section();

		// Calendar link section
		$this->do_calendar_link_section();

		// Optional style sections based on widget view settings.
		$this->do_view_based_style_sections();
	}

	protected function do_view_based_style_sections() {
		// Cost section
		$this->do_cost_section();

		// Organizer section
		$this->do_organizer_section();

		// Venue section
		$this->do_venue_section();

		// Street section
		$this->do_street_section();

		// Country section
		$this->do_country_section();

		// City section
		$this->do_city_section();

		// Region section
		$this->do_region_section();

		// Zip section
		$this->do_zip_section();

		// Start phone section
		$this->do_phone_section();

		// Website section
		$this->do_website_section();
	}

	protected function do_events_styles() {
		$this->start_controls_section(
			'events',
			[
				'label' => esc_html__( 'Notes On Event Styling', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
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

		$this->end_controls_section();
	}

	protected function do_title_section() {
		$this->start_controls_section(
			'event_title_tab',
			[
				'label' => esc_html__( 'Event Title', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'event_title_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-events-list__event-title-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_title_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'event_title_text_shadow',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-title',
			]
		);

		$this->end_controls_section();
	}

	protected function do_date_section() {
		$this->start_controls_section(
			'date_tab',
			[
				'label' => esc_html__( 'Date', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_event_date_month_style',
			[
				'label' => esc_html__( 'Month', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'event_date_tag_month_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-events-list__event-date-tag-month' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_date_tag_month_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-date-tag-month',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'event_date_tag_month_text_shadow',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-date-tag-month',
			]
		);

		$this->add_control(
			'heading_event_date_daynum_style',
			[
				'label' => esc_html__( 'Date', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'event_date_daynum_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-events-list__event-date-tag-daynum' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_date_tag_daynum_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-date-tag-daynum',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'event_date_tag_daynum_text_shadow',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-date-tag-daynum',
			]
		);

		$this->end_controls_section();
	}

	protected function do_time_section() {
		$this->start_controls_section(
			'time_tab',
			[
				'label' => esc_html__( 'Time', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'event_time_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-events-list__event-datetime, {{WRAPPER}} .tribe-common-c-svgicon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_time_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-datetime',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'event_time_text_shadow',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-datetime',
			]
		);

		$this->end_controls_section();
	}

	protected function do_featured_event_section() {
		$this->start_controls_section(
			'featured_event_section',
			[
				'label' => esc_html__( 'Featured Events', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'featured_event_border_color',
			[
				'label' => esc_html__( 'Border Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-events-list__event-date-tag-datetime:after' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'featured_event_icon_color',
			[
				'label' => esc_html__( 'Icon Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-common-c-svgicon' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function do_calendar_link_section() {
		$this->start_controls_section(
			'calendar_link',
			[
				'label' => esc_html__( 'Calendar Link', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'view_calendar_link_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-events-list__view-more-link' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'view_calendar_link_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__view-more',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'view_calendar_text_shadow',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__view-more',
			]
		);

		$this->end_controls_section();
	}

	protected function do_cost_section() {
		$this->start_controls_section(
			'event_cost',
			[
				'label' => esc_html__( 'Cost', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'cost' => 'yes',
				],
			]
		);

		$this->add_control(
			'event_cost_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-events-list__event-cost-price' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_cost_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-cost-price',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'event_cost_text_shadow',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-cost-price',
			]
		);

		$this->end_controls_section();
	}

	protected function do_organizer_section() {
		$this->start_controls_section(
			'event_organizer',
			[
				'label' => esc_html__( 'Organizer', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'organizer' => 'yes',
				],
			]
		);

		$this->add_control(
			'event_organizer_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-events-list__event-organizer-label, {{WRAPPER}} .tribe-events-widget-events-list__event-organizer-title-link, {{WRAPPER}} .tribe-events-widget-events-list__event-organizer-phone' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_organizer_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-organizer-label, {{WRAPPER}} .tribe-events-widget-events-list__event-organizer-title-link, {{WRAPPER}} .tribe-events-widget-events-list__event-organizer-phone',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'event_organizer_text_shadow',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-organizer-label, {{WRAPPER}} .tribe-events-widget-events-list__event-organizer-title-link, {{WRAPPER}} .tribe-events-widget-events-list__event-organizer-phone',
			]
		);

		$this->end_controls_section();
	}

	protected function do_venue_section() {
		$this->start_controls_section(
			'event_venue',
			[
				'label' => esc_html__( 'Venue', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_event_venue_title',
			[
				'label' => esc_html__( 'Venue Title', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'event_venue_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-events-list__event-venue-name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_venue_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-venue-name',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'event_venue_text_shadow',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-venue-name',
			]
		);

		$this->add_control(
			'heading_event_venue_details',
			[
				'label' => esc_html__( 'Venue Details', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'event_venue_details_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-street, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-country, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-city, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-region, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-zip, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-phone' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_venue_details_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-street, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-country, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-city, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-region, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-zip, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-phone',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'event_venue_details_text_shadow',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-street, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-country, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-city, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-region, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-zip, {{WRAPPER}} .tribe-events-widget-events-list__event-venue-phone',
			]
		);

		$this->end_controls_section();
	}


	protected function do_street_section() {
		$this->start_controls_section(
			'event_street',
			[
				'label' => esc_html__( 'Street', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'street' => 'yes',
				],
			]
		);

		$this->add_control(
			'event_street_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-street' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_street_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-street',
			]
		);

		$this->end_controls_section();
	}
	protected function do_country_section() {
		$this->start_controls_section(
			'event_country',
			[
				'label' => esc_html__( 'Country', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'country' => 'yes',
				],
			]
		);

			$this->add_control(
				'event_country_color',
				[
					'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-country' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'event_country_typography',
					'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-country',
				]
			);

		$this->end_controls_section();
	}

	protected function do_city_section() {
		$this->start_controls_section(
			'event_city',
			[
				'label' => esc_html__( 'City', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'city' => 'yes',
				],
			]
		);

			$this->add_control(
				'event_city_color',
				[
					'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-city' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'event_city_typography',
					'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-city',
				]
			);

		$this->end_controls_section();
	}

	protected function do_region_section() {
		$this->start_controls_section(
			'event_region',
			[
				'label' => esc_html__( 'Region', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'region' => 'yes',
				],
			]
		);

			$this->add_control(
				'event_region_color',
				[
					'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-region' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'event_region_typography',
					'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-region',
				]
			);

		$this->end_controls_section();
	}

	protected function do_zip_section() {
		$this->start_controls_section(
			'event_zip',
			[
				'label' => esc_html__( 'Zip', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'zip' => 'yes',
				],
			]
		);

			$this->add_control(
				'event_zip_color',
				[
					'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
					'type' => Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-zip' => 'color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Typography::get_type(),
				[
					'name' => 'event_zip_typography',
					'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-venue-address-zip',
				]
			);

		$this->end_controls_section();
	}

	protected function do_phone_section() {
		$this->start_controls_section(
			'event_phone',
			[
				'label' => esc_html__( 'Phone', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'phone' => 'yes',
				],
			]
		);

		$this->add_control(
			'event_phone_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-events-list__event-venue-phone' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_phone_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-venue-phone',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the Website settings in the styling panel.
	 *
	 * @since 6.0.12
	 */
	protected function do_website_section() {
		$this->start_controls_section(
			'event_website',
			[
				'label' => esc_html__( 'Website', 'tribe-events-calendar-pro' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'website' => 'yes',
				],
			]
		);

		$this->add_control(
			'event_website_color',
			[
				'label' => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-widget-events-list__event-website a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'event_website_typography',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-website',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'event_website_text_shadow',
				'selector' => '{{WRAPPER}} .tribe-events-widget-events-list__event-website',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Enqueues assets for this widget.
	 *
	 * @since 5.4.0
	 */
	public function enqueue_editor_assets() {
		tribe_asset_enqueue( 'tribe-events-views-v2-manager' );
		tribe_asset_enqueue( 'tribe-events-widgets-v2-events-list-skeleton' );
		tribe_asset_enqueue( 'tribe-events-virtual-widgets-v2-events-list-skeleton' );

		if ( tribe( Assets::class )->should_enqueue_full_styles() ) {
			tribe_asset_enqueue( 'tribe-events-widgets-v2-events-list-full' );
			tribe_asset_enqueue( 'tribe-events-virtual-widgets-v2-events-list-full' );
		}

	}
}
