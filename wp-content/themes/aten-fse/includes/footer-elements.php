<?php

/**
 * @file
 * Footer Elements
 * Add widgets for Upper and Lower Footer sections
 */

/**
 *
 */
function aten_fse_widgets_init() {
  register_sidebar([
    'name'          => esc_html__('Upper Footer Menu', 'aten-fse'),
    'id'            => 'upper-footer-menu-1',
    'description'   => esc_html__('Add widgets here.', 'aten-fse'),
    'before_widget' => '',
    'after_widget'  => '',
  ]);
  register_sidebar([
    'name'          => esc_html__('Upper Footer Location', 'aten-fse'),
    'id'            => 'upper-footer-location-1',
    'description'   => esc_html__('Add widgets here.', 'aten-fse'),
    'before_widget' => '',
    'after_widget'  => '',
  ]);
  register_sidebar([
    'name'          => esc_html__('Upper Footer Services', 'textdomain'),
    'id'            => 'upper-footer-services-1',
    'description'   => esc_html__('Add widgets here.', 'aten-fse'),
    'before_widget' => '',
    'after_widget'  => '',
  ]);
  register_sidebar([
    'name'          => esc_html__('Lower Footer Menu', 'aten-fse'),
    'id'            => 'lower-footer-menu-1',
    'description'   => esc_html__('Add widgets here.', 'aten-fse'),
    'before_widget' => '',
    'after_widget'  => '',
  ]);
}

add_action('widgets_init', 'aten_fse_widgets_init');
