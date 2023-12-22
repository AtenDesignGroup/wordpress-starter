<?php
/**
 * Events View Elementor Widget.
 *
 * @since   5.4.0
 *
 * @package Tribe\Events\Pro\Integrations\Elementor\Widgets
 */

namespace Tribe\Events\Pro\Integrations\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Box_Shadow;
use Tribe\Events\Pro\Integrations\Elementor\Traits;
use Tribe\Events\Views\V2\Assets;
use Tribe\Events\Views\V2\Manager;
use Tribe\Events\Views\V2\Views\Day_View;
use Tribe\Events\Views\V2\Views\List_View;
use Tribe\Events\Views\V2\Views\Month_View;

class Widget_Events_View extends Widget_Abstract {
	use Traits\Categories;
	use Traits\Tags;

	/**
	 * {@inheritdoc}
	 */
	protected static $widget_slug = 'events_view';

	/**
	 * {@inheritdoc}
	 */
	protected $widget_icon = 'eicon-calendar';

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $data = [], $args = null ) {
		parent::__construct( $data, $args );

		$this->widget_title = __( 'Events View', 'tribe-events-calendar-pro' );
	}

	/**
	 * Render widget output.
	 *
	 * @since 5.4.0
	 */
	protected function render() {
		add_action( 'tribe_events_views_v2_view_template_vars', [ $this, 'filter_template_vars_to_override_is_initial_load' ], 15 );

		$settings = $this->get_settings_for_display();

		if ( isset( $settings['events_per_page_setting'] ) && 'custom' === $settings['events_per_page_setting'] ) {
			$settings['events_per_page'] = $settings['events_per_page_custom'];
		}

		if ( isset( $settings['month_events_per_day_setting'] ) && 'custom' === $settings['month_events_per_day_setting'] ) {
			$settings['month_events_per_day'] = $settings['month_events_per_day_custom'];
		}

		if ( isset( $settings['featured'] ) ) {
			switch ( $settings['featured'] ) {
				case 'exclude':
					$settings['featured'] = false;
					break;
				case 'only':
					$settings['featured'] = true;
					break;
				case 'include':
				default:
					unset( $settings['featured'] );
					break;
			}
		}

		$settings_string = $this->get_shortcode_attribute_string(
			$settings,
			[
				'view',
				'category',
				'exclude-category',
				'featured',
				'date',
				'tribe-bar',
				'filter-bar',
				'events_per_page',
				'month_events_per_day',
				'keyword',
				'organizer',
				'venue',
				'author',
				'tag',
				'exclude-tag',
			]
		);

		echo do_shortcode( '[tribe_events ' . $settings_string . ']' );
	}

	/**
	 * Register widget controls.
	 *
	 * @since 5.4.0
	 * @since 5.14.5 modularized.
	 */
	protected function register_controls() {
		// Content panel
		$this->do_content_panel();

		// Style panel
		$this->do_style_panel();
	}

	/**
	 * Assembles the content panel.
	 *
	 * @since 5.14.5
	 */
	protected function do_content_panel() {
		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Content', 'tribe-events-calendar-pro' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$views = tribe( Manager::class )->get_registered_views();
		$views = array_filter(
			$views,
			static function ( $view_class, $slug ) {
				return (bool) call_user_func( [ $view_class, 'is_publicly_visible' ] );
			},
			ARRAY_FILTER_USE_BOTH
		);
		$views = array_map( static function ( $value ) {
			return tribe( Manager::class )->get_view_label_by_class( $value );
		}, $views );

		asort( $views );

		$view_selector = [ '' => __( 'Default', 'tribe-events-calendar-pro' ) ];
		$view_selector = array_merge( $view_selector, $views );

		$this->add_control(
			'tribe-bar',
			[
				'label'        => __( 'Events Bar', 'tribe-events-calendar-pro' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_block'  => false,
				'label_on'     => __( 'Show', 'tribe-events-calendar-pro' ),
				'label_off'    => __( 'Hide', 'tribe-events-calendar-pro' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		if ( class_exists( 'Tribe\Events\Filterbar\Views\V2_1\Service_Provider' ) ) {
			$this->add_control(
				'filter-bar',
				[
					'label'        => __( 'Filter Bar', 'tribe-events-calendar-pro' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_block'  => false,
					'label_on'     => __( 'Show', 'tribe-events-calendar-pro' ),
					'label_off'    => __( 'Hide', 'tribe-events-calendar-pro' ),
					'return_value' => 'yes',
					'default'      => 'no',
				]
			);
		}

		$this->add_control(
			'view',
			[
				'label'       => __( 'View', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => false,
				'options'     => $view_selector,
			]
		);

		$this->start_controls_tabs(
			'options_tabs'
		);

		$this->start_controls_tab(
			'option_event_tab',
			[
				'label' => __( 'Event Options', 'tribe-events-calendar-pro' ),
			]
		);

		$this->add_control(
			'featured',
			[
				'label'       => __( 'Featured Events', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::CHOOSE,
				'label_block' => false,
				'options'     => [
					'include' => [
						'title' => __( 'Include', 'tribe-events-calendar-pro' ),
						'icon'  => 'fa fa-plus',
					],
					'exclude' => [
						'title' => __( 'Exclude', 'tribe-events-calendar-pro' ),
						'icon'  => 'fa fa-minus',
					],
					'only'    => [
						'title' => __( 'Only Featured Events', 'tribe-events-calendar-pro' ),
						'icon'  => 'fa fa-check',
					],
				],
				'default'     => 'include',
				'toggle'      => false,
			]
		);

		$this->add_control(
			'organizer',
			[
				'label'       => __( 'Organizer', 'tribe-events-calendar-pro' ),
				'placeholder' => __( 'Enter an organizer name or ID.', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => '',
			]
		);

		$this->add_control(
			'venue',
			[
				'label'       => __( 'Venue', 'tribe-events-calendar-pro' ),
				'placeholder' => __( 'Enter a venue name or ID.', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => '',
			]
		);

		$this->add_control(
			'author',
			[
				'label'       => __( 'Author', 'tribe-events-calendar-pro' ),
				'placeholder' => __( 'Enter an author login or ID.', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => '',
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
			'exclude-category',
			[
				'label'       => __( 'Category Exclusion', 'tribe-events-calendar-pro' ),
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

		$this->add_control(
			'exclude-tag',
			[
				'label'       => __( 'Tag Exclusion', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->get_event_tags(),
				'label_block' => true,
				'multiple'    => true,
			]
		);

		$this->add_control(
			'keyword',
			[
				'label'       => __( 'Keyword', 'tribe-events-calendar-pro' ),
				'placeholder' => __( 'Enter a search keyword.', 'tribe-events-calendar-pro' ),
				'description' => __( 'Use a search keyword to only show matching events.', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => '',
			]
		);
		$this->end_controls_tab();

		$this->start_controls_tab(
			'option_view_tab',
			[
				'label' => __( 'View Options', 'tribe-events-calendar-pro' ),
			]
		);

		$this->add_control(
			'date',
			[
				'label'       => __( 'View Start Date', 'tribe-events-calendar-pro' ),
				'placeholder' => __( 'Date in YYY-MM-DD or YYYY-MM format', 'tribe-events-calendar-pro' ),
				'description' => __( 'Note: the Day View only supports YYYY-MM-DD date formats as well as relative date formats like "yesterday", "today", "tomorrow", "+3 days", etc.', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::TEXT,
				'label_block' => true,
				'default'     => '',
			]
		);

		$this->add_control(
			'events_per_page_setting',
			[
				'label'       => __( 'Events Per Page', 'tribe-events-calendar-pro' ),
				'description' => __( 'The number of events to display per page in List, Map, Photo, and Summary View.', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => true,
				'separator'   => 'before',
				'default'     => 'default',
				'options'     => [
					'default' => __( 'Default', 'tribe-events-calendar-pro' ),
					'custom'  => __( 'Custom', 'tribe-events-calendar-pro' ),
				],
			]
		);

		$this->add_control(
			'events_per_page_custom',
			[
				'label'       => __( 'Event Count', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::NUMBER,
				'label_block' => true,
				'default'     => (int) tribe_get_option( 'postsPerPage', 10 ),
				'condition'   => [
					'events_per_page_setting' => 'custom',
				],
			]
		);

		$this->add_control(
			'month_events_per_day_setting',
			[
				'label'       => __( 'Month View Events Per Day', 'tribe-events-calendar-pro' ),
				'description' => __( 'The number of events to display per page day in month view. Defaults to the value set in Events > Settings.', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::SELECT,
				'label_block' => true,
				'separator'   => 'before',
				'default'     => 'default',
				'options'     => [
					'default' => __( 'Default', 'tribe-events-calendar-pro' ),
					'custom'  => __( 'Custom', 'tribe-events-calendar-pro' ),
				],
			]
		);

		$this->add_control(
			'month_events_per_day_custom',
			[
				'label'       => __( 'Event Count', 'tribe-events-calendar-pro' ),
				'type'        => Controls_Manager::NUMBER,
				'label_block' => true,
				'default'     => (int) tribe_get_option( 'monthEventAmount', 3 ),
				'condition'   => [
					'month_events_per_day_setting' => 'custom',
				],
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Assembles the style panel.
	 *
	 * @since 5.14.5
	 */
	protected function do_style_panel() {
		$this->do_events_view_styling_notice();

		$this->do_style_events_bar();

		$this->do_style_events_month_view();

		$this->do_style_events_list_view();

		$this->do_style_events_summary_view();

		$this->do_style_events_day_view();

		$this->do_style_events_photo_view();

		$this->do_style_events_map_view();

		$this->do_style_events_week_view();

		$this->do_style_events_subscribe_to_calendar();

		$this->do_style_events_navigation();
	}

	/**
	 * Displays the events view styling notes.
	 *
	 * @since 5.14.5
	 */
	protected function do_events_view_styling_notice() {
		$this->start_controls_section(
			'events_view_styling_notes',
			[
				'label' => esc_html__( 'Notes On Event View Styling', 'tribe-events-calendar-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'style_warning',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__(
					'The style of this widget is often affected by your theme and plugins. If you experience an issue, try switching to a basic WordPress theme and deactivate related plugins.',
					'tribe-events-calendar-pro'
				),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the events bar settings in the styling panel.
	 *
	 * @since 5.14.5
	 */
	protected function do_style_events_bar() {
		$this->start_controls_section(
			'events_bar',
			[
				'label'     => esc_html__( 'Events Bar', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'tribe-bar' => 'yes',
				],
			]
		);

		$this->add_control(
			'events_bar_view_color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-view-selector--labels .tribe-events-c-view-selector__button-text,
					 {{WRAPPER}} .tribe-events-c-view-selector__list-item-text,
					 {{WRAPPER}} .tribe-events-c-events-bar .tribe-common-form-control-text__input'
					=> '--tec-color-text-events-bar-input: {{VALUE}}; --tec-color-text-view-selector-list-item: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_bar_view_typography',
				'selector' => '{{WRAPPER}} .tribe-events-c-search__button,
							   {{WRAPPER}} .tribe-events-c-view-selector--labels .tribe-events-c-view-selector__button-text,
							   {{WRAPPER}} .tribe-events-c-view-selector__list-item-text,
							   {{WRAPPER}} .tribe-events-c-events-bar .tribe-common-form-control-text__input',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'events_bar_view_text_shadow',
				'selector' => '{{WRAPPER}} .tribe-events-c-search__button,
							   {{WRAPPER}} .tribe-events-c-view-selector--labels .tribe-events-c-view-selector__button-text,
							   {{WRAPPER}} .tribe-events-c-view-selector__list-item-text,
							   {{WRAPPER}} .tribe-events-c-events-bar .tribe-common-form-control-text__input',
			]
		);

		$this->add_control(
			'events_bar_view_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-search__input-control-icon-svg,
					 {{WRAPPER}} .tribe-events-c-search__input-control-icon-svg path'
					=> '--tec-color-icon-events-bar: {{VALUE}}; fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'events_bar_view_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-events-bar--border' => '--tec-color-border-events-bar: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_events_bar_button_style',
			[
				'label'     => esc_html__( 'Find Events Button', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_bar_input_color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-search__button:not(:hover):not(:active),
					 {{WRAPPER}} .tribe-events-c-search__button:focus,
					 {{WRAPPER}} .tribe-events .tribe-events-c-search__button:hover'
					=> '--tec-color-text-events-bar-submit-button: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'events_bar_button_accent_color',
			[
				'label'     => esc_html__( 'Background Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-search__button:not(:hover):not(:active),
					{{WRAPPER}} .tribe-events-c-search__button:focus,
					{{WRAPPER}} .tribe-events .tribe-events-c-search__button:hover'
					=> '--tec-color-background-events-bar-submit-button: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_events_bar_view_selector',
			[
				'label'     => esc_html__( 'View Selector', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_bar_view_selector_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-view-selector__content' => '--tec-color-background-view-selector: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the navigation settings in the styling panel.
	 *
	 * @since 5.14.5
	 */
	protected function do_style_events_navigation() {
		$this->start_controls_section(
			'events_navigation',
			[
				'label' => esc_html__( 'Navigation', 'tribe-events-calendar-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'events_navigation_arrows_color',
			[
				'label'     => esc_html__( 'Navigation Arrows Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-common-c-btn-icon--caret-left .tribe-common-c-btn-icon__icon-svg path,
					 {{WRAPPER}} .tribe-common-c-btn-icon--caret-right .tribe-common-c-btn-icon__icon-svg path'
					=> '--tec-color-icon-secondary: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_events_navigation_button',
			[
				'label'     => esc_html__( 'Today Button', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_navigation_button_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-common-c-btn-border-small' => '--tec-color-background: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'events_navigation_button_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-common-c-btn-border-small,
					 {{WRAPPER}} .tribe-common-c-btn-border-small:hover'
					=> '--tec-color-text-secondary: {{VALUE}}; --tec-color-text-primary: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_navigation_button_typography',
				'selector' => '{{WRAPPER}} .tribe-common-c-btn-border-small',
			]
		);

		$this->add_control(
			'heading_events_navigation_date_selector',
			[
				'label'     => esc_html__( 'Date Selector', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_navigation_date_selector_text_color',
			[
				'label'     => esc_html__( 'Label Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-top-bar__datepicker-button,
					 {{WRAPPER}} .tribe-events-c-top-bar__datepicker-button-icon-svg .tribe-common-c-svgicon__svg-fill'
					=> '--tec-color-text-primary: {{VALUE}}; --tec-color-icon-active: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_navigation_date_selector_typography',
				'label'    => esc_html__( 'Label Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-c-top-bar__datepicker-button',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_navigation_date_selector_popup_switch_typography',
				'label'    => esc_html__( 'Popup Date Switch Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .datepicker-switch',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_navigation_date_selector_popup_typography',
				'label'    => esc_html__( 'Popup Date Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .dow, {{WRAPPER}} .day, {{WRAPPER}} .month, {{WRAPPER}} .year',
			]
		);

		$this->add_control(
			'heading_events_navigation_prev_next_links',
			[
				'label'     => esc_html__( 'Previous/Next Events', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_navigation_prev_next_links_text_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-nav__prev,
					 {{WRAPPER}} .tribe-events-c-nav__next,
					 {{WRAPPER}} .tribe-events-c-nav__prev-icon-svg path,
					 {{WRAPPER}} .tribe-events-c-nav__next-icon-svg path'
					=> '--tec-color-text-secondary: {{VALUE}}; --tec-color-icon-primary: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_navigation_prev_next_links_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-c-nav__prev, {{WRAPPER}} .tribe-events-c-nav__next',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the Month view settings in the styling panel.
	 *
	 * @since 5.14.5
	 */
	protected function do_style_events_month_view() {
		$this->start_controls_section(
			'events_month_view',
			[
				'label'     => esc_html__( 'Event Details', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'view' => Month_View::get_view_slug(),
				],
			]
		);

		$this->add_control(
			'events_month_view_grid_color',
			[
				'label'     => esc_html__( 'Grid Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-month__day,
					 {{WRAPPER}} .tribe-events-calendar-month__body'
					=> '--tec-color-border-secondary-month-grid: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'events_month_view_day_hover_color',
			[
				'label'     => esc_html__( 'Day Hover Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-month__day:hover:after' => '--tec-color-border-active-month-grid-hover: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_events_month_view_day',
			[
				'label'     => esc_html__( 'Day of week', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_month_view_day_text_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-month__day-date-daynum,
					 {{WRAPPER}} .tribe-events-calendar-month__day-date-link,
					 {{WRAPPER}} .tribe-events-calendar-month__header-column-title'
					=> '--tec-color-day-marker-month: {{VALUE}};
						--tec-color-day-marker-past-month: {{VALUE}};
						--tec-color-text-day-of-week-month: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_month_view_day_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-month__day-date-daynum,
							   {{WRAPPER}} .tribe-events-calendar-month__day-date-link,
							   {{WRAPPER}} .tribe-events-calendar-month__header-column-title',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name'     => 'events_month_view_day_text_shadow',
				'selector' => '{{WRAPPER}} .tribe-events-calendar-month__day-date-daynum, {{WRAPPER}} .tribe-events-calendar-month__day-date-link',
			]
		);

		$this->add_control(
			'heading_events_month_view_events_title',
			[
				'label'     => esc_html__( 'Event Title', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_month_view_events_title_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-month__calendar-event-title-link' => '--tec-color-text-events-title: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_month_view_events_title_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-month__calendar-event-title'
			]
		);

		$this->add_control(
			'heading_events_month_view_events_time',
			[
				'label'     => esc_html__( 'Event Time', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_month_view_events_time_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-month__calendar-event-datetime' => '--tec-color-text-event-date: {{VALUE}}; --tec-color-text-secondary-event-date: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_month_view_events_time_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-month__calendar-event-datetime'
			]
		);

		$this->add_control(
			'heading_events_month_view_featured_events',
			[
				'label'     => esc_html__( 'Featured Events', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_month_view_featured_event_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-month__calendar-event--featured:before,
					 {{WRAPPER}} .tribe-events-calendar-month__calendar-event-datetime-featured-icon-svg'
					=> '--tec-color-accent-primary: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the List view settings in the styling panel.
	 *
	 * @since 5.14.5
	 */
	protected function do_style_events_list_view() {
		$this->start_controls_section(
			'events_list_view',
			[
				'label'     => esc_html__( 'Event Details', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'view' => List_View::get_view_slug(),
				],
			]
		);

		$this->add_control(
			'heading_events_list_view_events_title',
			[
				'label'     => esc_html__( 'Event Title', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_list_view_event_title_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-list__event-title-link' => '--tec-color-text-events-title: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_list_view_event_title_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-list__event-title'
			]
		);

		$this->add_control(
			'heading_events_list_view_event_time',
			[
				'label'     => esc_html__( 'Event Time', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_list_view_event_time_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-list__event-datetime' => '--tec-color-text-event-date: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_list_view_event_time_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-list__event-datetime'
			]
		);

		$this->add_control(
			'heading_events_list_view_event_description',
			[
				'label'     => esc_html__( 'Event Description', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_list_view_event_description_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-list__event-description' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_list_view_event_description_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-list__event-description p'
			]
		);

		$this->add_control(
			'heading_events_list_view_event_venue',
			[
				'label'     => esc_html__( 'Event Venue', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_list_view_event_venue_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-list__event-venue' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_list_view_event_venue_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-list__event-venue'
			]
		);

		$this->add_control(
			'heading_events_list_view_event_cost',
			[
				'label'     => esc_html__( 'Event Cost', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_list_view_event_cost_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-list__event-cost' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_list_view_event_cost_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-list__event-cost'
			]
		);

		$this->add_control(
			'heading_events_list_view_featured_events',
			[
				'label'     => esc_html__( 'Featured Events', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_list_view_featured_event_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-list__event-date-tag-datetime:after,
					 {{WRAPPER}} .tribe-events-calendar-list__event-datetime-featured-text,
					 {{WRAPPER}} .tribe-events-calendar-list__event-datetime-featured-icon-svg'
					=> '--tec-color-accent-primary: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_events_list_view_event_date_tag',
			[
				'label'     => esc_html__( 'Date Tag', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_list_view_event_date_tag_color',
			[
				'label'     => esc_html__( 'Day of Week Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-list__event-date-tag-weekday' => '--tec-color-text-secondary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_list_view_event_date_tag_typography',
				'label'    => esc_html__( 'Day of Week Typography', 'tribe-events-calendar-pro' ),
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-list__event-date-tag-weekday'
			]
		);

		$this->add_control(
			'events_list_view_event_daynum_color',
			[
				'label'     => esc_html__( 'Day Number Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-list__event-date-tag-daynum' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_list_view_event_daynum_typography',
				'label'    => esc_html__( 'Day Number Typography', 'tribe-events-calendar-pro' ),
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-list__event-date-tag-daynum'
			]
		);

		$this->add_control(
			'heading_events_list_view_event_month_separator',
			[
				'label'     => esc_html__( 'Month Separator', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_list_view_event_month_separator_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-list__month-separator-text' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_list_view_event_month_separator_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-list__month-separator-text'
			]
		);

		$this->add_control(
			'events_list_view_event_month_border_separator_color',
			[
				'label'     => esc_html__( 'Border Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-list__month-separator:after' => '--tec-color-border-default: {{VALUE}};',
				],
			]
		);

		// Start featured image settings
		$this->add_control(
			'heading_events_list_view_featured_image',
			[
				'label'     => esc_html__( 'Featured Image', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'events_list_view_featured_image_width',
			[
				'label'          => esc_html__( 'Width', 'tribe-events-calendar-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ '%', 'px', 'vw' ],
				'range'          => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .tribe-events-calendar-list__event-featured-image' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'events_list_view_featured_image_space',
			[
				'label'          => esc_html__( 'Max Width', 'tribe-events-calendar-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ '%', 'px', 'vw' ],
				'range'          => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .tribe-events-calendar-list__event-featured-image' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'events_list_view_featured_image_height',
			[
				'label'          => esc_html__( 'Height', 'tribe-events-calendar-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => 'px',
				],
				'tablet_default' => [
					'unit' => 'px',
				],
				'mobile_default' => [
					'unit' => 'px',
				],
				'size_units'     => [ 'px', 'vh' ],
				'range'          => [
					'px' => [
						'min' => 1,
						'max' => 500,
					],
					'vh' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .tribe-events-calendar-list__event-featured-image' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'events_list_view_featured_image_object_fit',
			[
				'label'     => esc_html__( 'Object Fit', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'condition' => [
					'height[size]!' => '',
				],
				'options'   => [
					''        => esc_html__( 'Default', 'tribe-events-calendar-pro' ),
					'fill'    => esc_html__( 'Fill', 'tribe-events-calendar-pro' ),
					'cover'   => esc_html__( 'Cover', 'tribe-events-calendar-pro' ),
					'contain' => esc_html__( 'Contain', 'tribe-events-calendar-pro' ),
				],
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-list__event-featured-image' => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'events_list_view_featured_image_separator_panel',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->start_controls_tabs( 'events_list_view_featured_image_effects' );

		$this->add_control(
			'events_list_view_featured_image_opacity',
			[
				'label'     => esc_html__( 'Opacity', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-list__event-featured-image' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'events_list_view_featured_image_css_filters',
				'selector' => '{{WRAPPER}} img',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'events_list_view_featured_image_image_border',
				'selector'  => '{{WRAPPER}} img',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'events_list_view_featured_image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'tribe-events-calendar-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .tribe-events-calendar-list__event-featured-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'events_list_view_featured_image_box_shadow',
				'exclude'  => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .tribe-events-calendar-list__event-featured-image',
			]
		);
		// End featured image settings

		$this->end_controls_section();
	}

	/**
	 * Assembles the Summary view settings in the styling panel.
	 *
	 * @since 5.14.5
	 */
	protected function do_style_events_summary_view() {
		$this->start_controls_section(
			'events_summary_view',
			[
				'label'     => esc_html__( 'Event Details', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'view' => \Tribe\Events\Pro\Views\V2\Views\Summary_View::get_view_slug(),
				],
			]
		);

		$this->add_control(
			'heading_events_summary_view_events_title',
			[
				'label'     => esc_html__( 'Event Title', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_summary_view_event_title_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-summary__event-title-link' => '--tec-color-text-events-title: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_summary_view_event_title_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-summary__event-title'
			]
		);

		$this->add_control(
			'heading_events_summary_view_event_time',
			[
				'label'     => esc_html__( 'Event Time', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_summary_view_event_time_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-summary__event-datetime' => '--tec-color-text-event-date: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_summary_view_event_time_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-summary__event-datetime'
			]
		);

		$this->add_control(
			'heading_events_summary_view_event_cost',
			[
				'label'     => esc_html__( 'Event Cost', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_summary_view_event_cost_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-summary__event-cost' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_summary_view_event_cost_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-summary__event-cost'
			]
		);

		$this->add_control(
			'heading_events_summary_view_featured_events',
			[
				'label'     => esc_html__( 'Featured Events', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_summary_view_featured_event_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-summary__event-title-featured-icon-svg' => '--tec-color-accent-primary: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_events_summary_view_event_date_tag',
			[
				'label'     => esc_html__( 'Date Tag', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_summary_view_event_date_tag_color',
			[
				'label'     => esc_html__( 'Day of Week Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-summary__event-date-tag-weekday' => '--tec-color-text-secondary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_summary_view_event_date_tag_typography',
				'label'    => esc_html__( 'Day of Week Typography', 'tribe-events-calendar-pro' ),
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-summary__event-date-tag-weekday'
			]
		);

		$this->add_control(
			'events_summary_view_event_daynum_color',
			[
				'label'     => esc_html__( 'Day Number Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-summary__event-date-tag-daynum' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'heading_events_summary_view_event_daynum_typography',
				'label'    => esc_html__( 'Day Number Typography', 'tribe-events-calendar-pro' ),
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-summary__event-date-tag-daynum'
			]
		);

		$this->add_control(
			'heading_events_summary_view_event_month_separator',
			[
				'label'     => esc_html__( 'Month Separator', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_summary_view_event_month_separator_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-summary__month-separator-text' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_summary_view_event_month_separator_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-summary__month-separator-text'
			]
		);

		$this->add_control(
			'events_summary_view_event_month_border_separator_color',
			[
				'label'     => esc_html__( 'Border Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-summary__month-separator:after' => '--tec-color-border-default: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the Day view settings in the styling panel.
	 *
	 * @since 5.14.5
	 */
	protected function do_style_events_day_view() {
		$this->start_controls_section(
			'events_day_view',
			[
				'label'     => esc_html__( 'Event Details', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'view' => Day_View::get_view_slug(),
				],
			]
		);

		$this->add_control(
			'heading_events_day_view_events_title',
			[
				'label'     => esc_html__( 'Event Title', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_day_view_event_title_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-day__event-title-link' => '--tec-color-text-events-title: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_day_view_event_title_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-day__event-title'
			]
		);

		$this->add_control(
			'heading_events_day_view_event_time',
			[
				'label'     => esc_html__( 'Event Time', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_day_view_event_time_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-day__event-datetime' => '--tec-color-text-event-date: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_day_view_event_time_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-day__event-datetime'
			]
		);

		$this->add_control(
			'heading_events_day_view_event_description',
			[
				'label'     => esc_html__( 'Event Description', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_day_view_event_description_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-day__event-description' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_day_view_event_description_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-day__event-description p'
			]
		);

		$this->add_control(
			'heading_events_day_view_event_venue',
			[
				'label'     => esc_html__( 'Event Venue', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_day_view_event_venue_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-day__event-venue' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_day_view_event_venue_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-day__event-venue'
			]
		);

		$this->add_control(
			'heading_events_day_view_event_cost',
			[
				'label'     => esc_html__( 'Event Cost', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_day_view_event_cost_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-day__event-cost' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_day_view_event_cost_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-day__event-cost'
			]
		);

		$this->add_control(
			'heading_events_day_view_featured_events',
			[
				'label'     => esc_html__( 'Featured Events', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_day_view_featured_event_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-day__event--featured:after,
					 {{WRAPPER}} .tribe-events-calendar-day__event-datetime-featured-text,
					 {{WRAPPER}} .tribe-events-calendar-day__event-datetime-featured-icon-svg'
					=> '--tec-color-accent-primary: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_events_day_view_event_date_tag',
			[
				'label'     => esc_html__( 'Date Tag', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_day_view_event_date_tag_color',
			[
				'label'     => esc_html__( 'Day of Week Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-day__event-date-tag-weekday' => '--tec-color-text-secondary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_day_view_event_date_tag_typography',
				'label'    => esc_html__( 'Day of Week Typography', 'tribe-events-calendar-pro' ),
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-day__event-date-tag-weekday'
			]
		);

		$this->add_control(
			'heading_events_day_view_event_month_separator',
			[
				'label'     => esc_html__( 'Time Separator', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_day_view_event_month_separator_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-day__time-separator-text' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_day_view_event_month_separator_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-calendar-day__time-separator-text'
			]
		);

		$this->add_control(
			'events_day_view_event_month_border_separator_color',
			[
				'label'     => esc_html__( 'Border Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-day__time-separator:after,
					 {{WRAPPER}} .tribe-events-calendar-day-nav'
					=> '--tec-color-border-default: {{VALUE}};',
				],
			]
		);

		// Start featured image settings
		$this->add_control(
			'heading_events_day_view_featured_image',
			[
				'label'     => esc_html__( 'Featured Image', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'events_day_view_featured_image_width',
			[
				'label'          => esc_html__( 'Width', 'tribe-events-calendar-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ '%', 'px', 'vw' ],
				'range'          => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .tribe-events-calendar-day__event-featured-image' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'events_day_view_featured_image_space',
			[
				'label'          => esc_html__( 'Max Width', 'tribe-events-calendar-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ '%', 'px', 'vw' ],
				'range'          => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .tribe-events-calendar-day__event-featured-image' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'events_day_view_featured_image_height',
			[
				'label'          => esc_html__( 'Height', 'tribe-events-calendar-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => 'px',
				],
				'tablet_default' => [
					'unit' => 'px',
				],
				'mobile_default' => [
					'unit' => 'px',
				],
				'size_units'     => [ 'px', 'vh' ],
				'range'          => [
					'px' => [
						'min' => 1,
						'max' => 500,
					],
					'vh' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .tribe-events-calendar-day__event-featured-image' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'events_day_view_featured_image_object_fit',
			[
				'label'     => esc_html__( 'Object Fit', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'condition' => [
					'height[size]!' => '',
				],
				'options'   => [
					''        => esc_html__( 'Default', 'tribe-events-calendar-pro' ),
					'fill'    => esc_html__( 'Fill', 'tribe-events-calendar-pro' ),
					'cover'   => esc_html__( 'Cover', 'tribe-events-calendar-pro' ),
					'contain' => esc_html__( 'Contain', 'tribe-events-calendar-pro' ),
				],
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-day__event-featured-image' => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'events_day_view_featured_image_separator_panel',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->start_controls_tabs( 'events_day_view_featured_image_effects' );

		$this->add_control(
			'events_day_view_featured_image_opacity',
			[
				'label'     => esc_html__( 'Opacity', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .tribe-events-calendar-day__event-featured-image' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'events_day_view_featured_image_css_filters',
				'selector' => '{{WRAPPER}} img',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'events_day_view_featured_image_image_border',
				'selector'  => '{{WRAPPER}} img',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'events_day_view_featured_image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'tribe-events-calendar-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .tribe-events-calendar-day__event-featured-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'events_day_view_featured_image_box_shadow',
				'exclude'  => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .tribe-events-calendar-day__event-featured-image',
			]
		);
		// End featured image settings

		$this->end_controls_section();
	}

	/**
	 * Assembles the Photo view settings in the styling panel.
	 *
	 * @since 5.14.5
	 */
	protected function do_style_events_photo_view() {
		$this->start_controls_section(
			'events_photo_view',
			[
				'label'     => esc_html__( 'Event Details', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'view' => \Tribe\Events\Pro\Views\V2\Views\Photo_View::get_view_slug(),
				],
			]
		);

		$this->add_control(
			'heading_events_photo_view_events_title',
			[
				'label'     => esc_html__( 'Event Title', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_photo_view_event_title_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-photo__event-title-link' => '--tec-color-text-events-title: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_photo_view_event_title_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-photo__event-title'
			]
		);

		$this->add_control(
			'heading_events_photo_view_event_time',
			[
				'label'     => esc_html__( 'Event Time', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_photo_view_event_time_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-photo__event-datetime' => '--tec-color-text-event-date: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_photo_view_event_time_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-photo__event-datetime'
			]
		);

		$this->add_control(
			'heading_events_photo_view_event_cost',
			[
				'label'     => esc_html__( 'Event Cost', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_photo_view_event_cost_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-photo__event-cost' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_photo_view_event_cost_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-photo__event-cost'
			]
		);

		$this->add_control(
			'heading_events_photo_view_featured_events',
			[
				'label'     => esc_html__( 'Featured Events', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_photo_view_featured_event_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-photo__event-datetime-featured-text,
					 {{WRAPPER}} .tribe-events-pro-photo__event-datetime-featured-icon-svg'
					=> '--tec-color-accent-primary: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_events_photo_view_event_date_tag',
			[
				'label'     => esc_html__( 'Date Tag', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_photo_view_event_date_tag_color',
			[
				'label'     => esc_html__( 'Month Label Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-photo__event-date-tag-month' => '--tec-color-text-secondary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_photo_view_event_date_tag_typography',
				'label'    => esc_html__( 'Month Label Typography', 'tribe-events-calendar-pro' ),
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-photo__event-date-tag-month'
			]
		);

		$this->add_control(
			'events_photo_view_event_daynum_color',
			[
				'label'     => esc_html__( 'Day Number Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-photo__event-date-tag-daynum' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_photo_view_event_daynum_typography',
				'label'    => esc_html__( 'Day Number Typography', 'tribe-events-calendar-pro' ),
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-photo__event-date-tag-daynum'
			]
		);

		// Start featured image settings
		$this->add_control(
			'heading_events_photo_view_featured_image',
			[
				'label'     => esc_html__( 'Featured Image', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'events_photo_view_featured_image_width',
			[
				'label'          => esc_html__( 'Width', 'tribe-events-calendar-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ '%', 'px', 'vw' ],
				'range'          => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .tribe-events-pro-photo__event-featured-image' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'events_photo_view_featured_image_space',
			[
				'label'          => esc_html__( 'Max Width', 'tribe-events-calendar-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ '%', 'px', 'vw' ],
				'range'          => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .tribe-events-pro-photo__event-featured-image' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'events_photo_view_featured_image_height',
			[
				'label'          => esc_html__( 'Height', 'tribe-events-calendar-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => 'px',
				],
				'tablet_default' => [
					'unit' => 'px',
				],
				'mobile_default' => [
					'unit' => 'px',
				],
				'size_units'     => [ 'px', 'vh' ],
				'range'          => [
					'px' => [
						'min' => 1,
						'max' => 500,
					],
					'vh' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .tribe-events-pro-photo__event-featured-image' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'events_photo_view_featured_image_object_fit',
			[
				'label'     => esc_html__( 'Object Fit', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'condition' => [
					'height[size]!' => '',
				],
				'options'   => [
					''        => esc_html__( 'Default', 'tribe-events-calendar-pro' ),
					'fill'    => esc_html__( 'Fill', 'tribe-events-calendar-pro' ),
					'cover'   => esc_html__( 'Cover', 'tribe-events-calendar-pro' ),
					'contain' => esc_html__( 'Contain', 'tribe-events-calendar-pro' ),
				],
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-photo__event-featured-image' => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'events_photo_view_featured_image_separator_panel',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->start_controls_tabs( 'events_photo_view_featured_image_effects' );

		$this->add_control(
			'events_photo_view_featured_image_opacity',
			[
				'label'     => esc_html__( 'Opacity', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-photo__event-featured-image' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'events_photo_view_featured_image_css_filters',
				'selector' => '{{WRAPPER}} img',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'events_photo_view_featured_image_image_border',
				'selector'  => '{{WRAPPER}} img',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'events_photo_view_featured_image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'tribe-events-calendar-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .tribe-events-pro-photo__event-featured-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'events_photo_view_featured_image_box_shadow',
				'exclude'  => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .tribe-events-pro-photo__event-featured-image',
			]
		);
		// End featured image settings

		$this->end_controls_section();
	}

	/**
	 * Assembles the Map view settings in the styling panel.
	 *
	 * @since 5.14.5
	 */
	protected function do_style_events_map_view() {
		$this->start_controls_section(
			'events_map_view',
			[
				'label'     => esc_html__( 'Event Details', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'view' => \Tribe\Events\Pro\Views\V2\Views\Map_View::get_view_slug(),
				],
			]
		);

		$this->add_control(
			'events_map_view_event_grid_color',
			[
				'label'     => esc_html__( 'Grid Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-map__event-card-wrapper--active' => '--tec-color-accent-primary: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_events_map_view_events_title',
			[
				'label'     => esc_html__( 'Event Title', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_map_view_event_title_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-map__event-title' => '--tec-color-text-events-title: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_map_view_event_title_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-map__event-title'
			]
		);

		$this->add_control(
			'heading_events_map_view_event_time',
			[
				'label'     => esc_html__( 'Event Time', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_map_view_event_time_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-map__event-datetime-wrapper' => '--tec-color-text-event-date: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_map_view_event_time_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-map__event-datetime-wrapper'
			]
		);

		$this->add_control(
			'heading_events_map_view_event_venue',
			[
				'label'     => esc_html__( 'Event Venue', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_map_view_event_venue_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-map__event-venue' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_map_view_event_venue_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-map__event-venue'
			]
		);

		$this->add_control(
			'heading_events_map_view_featured_events',
			[
				'label'     => esc_html__( 'Featured Events', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_map_view_featured_event_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-map__event-datetime-featured-text,
					 {{WRAPPER}} .tribe-events-pro-map__event-datetime-featured-icon-svg'
					=> '--tec-color-accent-primary: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_events_map_view_event_date_tag',
			[
				'label'     => esc_html__( 'Date Tag', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_map_view_event_date_tag_color',
			[
				'label'     => esc_html__( 'Month Label Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-map__event-date-tag-month' => '--tec-color-text-secondary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_map_view_event_date_tag_typography',
				'label'    => esc_html__( 'Month Label Typography', 'tribe-events-calendar-pro' ),
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-map__event-date-tag-month'
			]
		);

		$this->add_control(
			'events_map_view_event_daynum_color',
			[
				'label'     => esc_html__( 'Day Number Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-map__event-date-tag-daynum' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_map_view_event_daynum_typography',
				'label'    => esc_html__( 'Day Number Typography', 'tribe-events-calendar-pro' ),
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-map__event-date-tag-daynum'
			]
		);

		// Start featured image settings
		$this->add_control(
			'heading_events_map_view_featured_image',
			[
				'label'     => esc_html__( 'Featured Image', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'events_map_view_featured_image_width',
			[
				'label'          => esc_html__( 'Width', 'tribe-events-calendar-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ '%', 'px', 'vw' ],
				'range'          => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .tribe-events-pro-map__event-featured-image' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'events_map_view_featured_image_space',
			[
				'label'          => esc_html__( 'Max Width', 'tribe-events-calendar-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => '%',
				],
				'tablet_default' => [
					'unit' => '%',
				],
				'mobile_default' => [
					'unit' => '%',
				],
				'size_units'     => [ '%', 'px', 'vw' ],
				'range'          => [
					'%'  => [
						'min' => 1,
						'max' => 100,
					],
					'px' => [
						'min' => 1,
						'max' => 1000,
					],
					'vw' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .tribe-events-pro-map__event-featured-image' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'events_map_view_featured_image_height',
			[
				'label'          => esc_html__( 'Height', 'tribe-events-calendar-pro' ),
				'type'           => Controls_Manager::SLIDER,
				'default'        => [
					'unit' => 'px',
				],
				'tablet_default' => [
					'unit' => 'px',
				],
				'mobile_default' => [
					'unit' => 'px',
				],
				'size_units'     => [ 'px', 'vh' ],
				'range'          => [
					'px' => [
						'min' => 1,
						'max' => 500,
					],
					'vh' => [
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'      => [
					'{{WRAPPER}} .tribe-events-pro-map__event-featured-image' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'events_map_view_featured_image_object_fit',
			[
				'label'     => esc_html__( 'Object Fit', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SELECT,
				'condition' => [
					'height[size]!' => '',
				],
				'options'   => [
					''        => esc_html__( 'Default', 'tribe-events-calendar-pro' ),
					'fill'    => esc_html__( 'Fill', 'tribe-events-calendar-pro' ),
					'cover'   => esc_html__( 'Cover', 'tribe-events-calendar-pro' ),
					'contain' => esc_html__( 'Contain', 'tribe-events-calendar-pro' ),
				],
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-map__event-featured-image' => 'object-fit: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'events_map_view_featured_image_separator_panel',
			[
				'type'  => Controls_Manager::DIVIDER,
				'style' => 'thick',
			]
		);

		$this->start_controls_tabs( 'events_map_view_featured_image_effects' );

		$this->add_control(
			'events_map_view_featured_image_opacity',
			[
				'label'     => esc_html__( 'Opacity', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [
					'px' => [
						'max'  => 1,
						'min'  => 0.10,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-map__event-featured-image' => 'opacity: {{SIZE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			[
				'name'     => 'events_map_view_featured_image_css_filters',
				'selector' => '{{WRAPPER}} img',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'events_map_view_featured_image_image_border',
				'selector'  => '{{WRAPPER}} img',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'events_map_view_featured_image_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'tribe-events-calendar-pro' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .tribe-events-pro-map__event-featured-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'events_map_view_featured_image_box_shadow',
				'exclude'  => [
					'box_shadow_position',
				],
				'selector' => '{{WRAPPER}} .tribe-events-pro-map__event-featured-image',
			]
		);
		// End featured image settings

		$this->end_controls_section();
	}

	/**
	 * Assembles the Week view settings in the styling panel.
	 *
	 * @since 5.14.5
	 */
	protected function do_style_events_week_view() {
		$this->start_controls_section(
			'events_week_view',
			[
				'label'     => esc_html__( 'Event Details', 'tribe-events-calendar-pro' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'view' => \Tribe\Events\Pro\Views\V2\Views\Week_View::get_view_slug(),
				],
			]
		);

		$this->add_control(
			'events_week_view_event_grid_color',
			[
				'label'     => esc_html__( 'Grid Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-week-grid__events-day,
					 {{WRAPPER}} .tribe-events-pro-week-grid__events-row-outer-wrapper'
					=> '--tec-color-border-secondary: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'events_week_view_past_event_background_color',
			[
				'label'     => esc_html__( 'Past Event Background Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-week-grid__event--past .tribe-events-pro-week-grid__event-link-inner' => '--tec-color-background-secondary: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'events_week_view_future_event_background_color',
			[
				'label'     => esc_html__( 'Future Event Background Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-week-grid__event-link-inner' => '--tec-color-accent-primary-week-event: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_events_week_view_events_title',
			[
				'label'     => esc_html__( 'Event Title', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_week_view_event_title_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-week-grid__event-title' => '--tec-color-text-events-title: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_week_view_event_title_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-week-grid__event-title'
			]
		);

		$this->add_control(
			'heading_events_week_view_event_time',
			[
				'label'     => esc_html__( 'Event Time', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_week_view_event_time_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-week-grid__event-datetime' => '--tec-color-text-event-date: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_week_view_event_time_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-week-grid__event-datetime'
			]
		);

		$this->add_control(
			'heading_events_week_view_featured_events',
			[
				'label'     => esc_html__( 'Featured Events', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_week_view_featured_event_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-week-grid__event-link-inner:before,
					 {{WRAPPER}} .tribe-events-pro-week-grid__event-datetime-featured-icon-svg'
					=> '--tec-color-accent-primary: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_events_week_view_event_time_tag',
			[
				'label'     => esc_html__( 'Time Tag', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_week_view_event_time_tag_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-week-grid__events-time-tag' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_week_view_event_time_tag_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-week-grid__events-time-tag'
			]
		);

		$this->add_control(
			'heading_events_week_view_event_date_tag',
			[
				'label'     => esc_html__( 'Date Tag', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_week_view_event_date_tag_color',
			[
				'label'     => esc_html__( 'Day of Week Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-week-grid__header-column-weekday' => '--tec-color-text-secondary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_week_view_event_date_tag_typography',
				'label'    => esc_html__( 'Day of Week Typography', 'tribe-events-calendar-pro' ),
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-week-grid__header-column-weekday'
			]
		);

		$this->add_control(
			'events_week_view_event_daynum_color',
			[
				'label'     => esc_html__( 'Day Number Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-pro-week-grid__header-column-daynum' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_week_view_event_daynum_typography',
				'label'    => esc_html__( 'Day Number Typography', 'tribe-events-calendar-pro' ),
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-pro-week-grid__header-column-daynum'
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Assembles the Subscribe to Calendar settings in the styling panel.
	 *
	 * @since 5.14.5
	 */
	protected function do_style_events_subscribe_to_calendar() {
		$this->start_controls_section(
			'events_subscribe_to_calendar',
			[
				'label' => esc_html__( 'Subscribe to Calendar', 'tribe-events-calendar-pro' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'events_subscribe_to_calendar_inactive_settings',
			[
				'label'     => esc_html__( 'Inactive State', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_subscribe_to_calendar_inactive_background',
			[
				'label'     => esc_html__( 'Background Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-subscribe-dropdown__button,
					 {{WRAPPER}} .tribe-events-c-subscribe-dropdown__button-text'
					=> '--tec-color-background: {{VALUE}}; background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'events_subscribe_to_calendar_inactive_color',
			[
				'label'     => esc_html__( 'Label Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-subscribe-dropdown__button-text' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'events_subscribe_to_calendar_hover_settings',
			[
				'label'     => esc_html__( 'On hover', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_subscribe_to_calendar_hover_background',
			[
				'label'     => esc_html__( 'Background Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-subscribe-dropdown__button:hover' => '--tec-color-accent-primary: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'events_subscribe_to_calendar_hover_color',
			[
				'label'     => esc_html__( 'Label Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-subscribe-dropdown__button:hover,
					 {{WRAPPER}} .tribe-events-c-subscribe-dropdown__button-text:hover'
					=> 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'events_subscribe_to_calendar_dropdown_settings',
			[
				'label'     => esc_html__( 'Dropdown', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'events_subscribe_to_calendar_dropdown_color',
			[
				'label'     => esc_html__( 'Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-subscribe-dropdown__list-item a' => '--tec-color-text-primary: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'events_subscribe_to_calendar_dropdown_typography',
				'label'    => esc_html__( 'Typography', 'tribe-events-calendar-pro' ),
				'selector' => '{{WRAPPER}} .tribe-events-c-subscribe-dropdown__list-item'
			]
		);

		$this->add_control(
			'events_subscribe_to_calendar_dropdown_background',
			[
				'label'     => esc_html__( 'Background Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-subscribe-dropdown__list,
					 {{WRAPPER}} .tribe-events-c-subscribe-dropdown__list-item:hover'
					=> '--tec-color-background: {{VALUE}};
						--tec-color-background-subscribe-list-item-hover: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'events_subscribe_to_calendar_dropdown_border',
			[
				'label'     => esc_html__( 'Border Color', 'tribe-events-calendar-pro' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .tribe-events-c-subscribe-dropdown__list' => '--tec-color-border-secondary: {{VALUE}};',
				],
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
		tribe_asset_enqueue( 'tribe-events-views-v2-breakpoints' );
		tribe_asset_enqueue( 'tribe-events-views-v2-manager' );
		tribe_asset_enqueue( 'tribe-events-virtual-skeleton' );
		tribe_asset_enqueue( 'tribe-events-filterbar-views-v2-1-filter-bar-skeleton' );

		if ( tribe( Assets::class )->should_enqueue_full_styles() ) {
			tribe_asset_enqueue( 'tribe-events-virtual-full' );
			tribe_asset_enqueue( 'tribe-events-filterbar-views-v2-1-filter-bar-full' );
		}
	}

	/**
	 * Overrides the is_initial_load variable on render within the preview.
	 *
	 * @since 5.4.0
	 *
	 * @param array<string,mixed> $template_vars Template variables.
	 *
	 * @return array<string,mixed>
	 */
	public function filter_template_vars_to_override_is_initial_load( $template_vars ) {
		if (
			! empty( $_POST['action'] )
			&& 'elementor_ajax' === $_POST['action']
		) {
			$template_vars['is_initial_load'] = true;
		}

		remove_action( 'tribe_events_views_v2_view_template_vars', [ $this, 'filter_template_vars_to_override_is_initial_load' ], 15 );

		return $template_vars;
	}
}
