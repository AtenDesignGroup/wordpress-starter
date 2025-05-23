<?php

/**
 * @file
 * All Services Block Template.
 *
 * @param array $block The block settings and attributes.
 * @param string $content The block inner HTML (empty).
 * @param bool $is_preview True during backend preview render.
 * @param int $post_id The post ID the block is rendering content against.
 *          This is either the post ID currently being displayed inside a query loop,
 *          or the post ID of the post hosting this block.
 * @param array $context The context provided to the block by the post or it's parent block.
 */

if (isset($block['data']['preview_image'])) :    /* rendering in inserter preview  */
echo '<img src="' . esc_url( get_site_url() . $block['data']['preview_image'] ) . '" style="width:100%; height:auto;">';
else :

// Support custom "anchor" values.
$anchor = '';
if (!empty($block['anchor'])) {
  $anchor = 'id="' . esc_attr($block['anchor']) . '" ';
}

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'all-services-block includes-jump-links';
if (!empty($block['className'])) {
  $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
  $class_name .= ' align' . $block['align'];
}

// Get the services field.
$services = get_field('services');

// Loop through services sections if there are any.
if (have_rows('services_sections')) : ?>

        <div <?php echo $anchor; ?>class="<?php echo esc_attr($class_name); ?> l-gutter">
            <a id="all-services-top-link"></a>
            <h2>All Services</h2>
            <h3><span class="services-icon jump-link-icon notranslate" aria-hidden="true">arrow_circle_down</span>Jump to a section of services:</h3>
            <div class="services-jump-link-wrapper"></div>

            <ul class="section-list">

                <?php
                // Counter to determine section card colors.
                $color_counter = 1;
                while (have_rows('services_sections')):
                the_row();
                // Get the section title.
                $section_title = get_sub_field('section_title');
                ?>
                    <li class="services-section">
                        <h4 class="service-section-heading"><?php echo $section_title; ?></h4>
                        <a href="#all-services-top-link" class="a11y-visible skip-link">Scroll back to Services List</a>

                        <?php if (have_rows('services')) : ?>
                            <ul class="service-cards">
                                <?php while (have_rows('services')) :
                                  the_row();
                                  // Individual service field vars.
                                  $title = get_sub_field('service_title');
                                  $link = get_sub_field('service_link');
                                  $link_target = $link['target'] ?? '_self';
                                  $icon = get_sub_field('service_icon');
                                  // Optional.
                                  $description = get_sub_field('service_description');
                                  ?>
                                    <li class="service-card">
                                        <div class="icon-container color-<?php echo $color_counter; ?>"">
                                            <span class="service-icon notranslate" aria-hidden="true"><?php echo $icon; ?></span>
                                        </div>
                                        <div class="service-details">
                                            <p class="service-title">
                                                <a href="<?php echo $link['url']; ?>" title="<?php echo $link['title']; ?>" target="<?php echo $link_target; ?>" class="service-card-link">
                                                    <?php echo $title; ?>
                                                </a>
                                            </p>
                                            <?php if ($description) : ?>
                                                <p class="service-description">
                                              <?php echo $description; ?>
                                                </p>
                                              <?php
                                              // Description endif;.
                                              ?>
                                        </div>
                                    </li>
                                              <?php
                                              // Looping services endwhile;.
                                              ?>
                            </ul>
                                              <?php
                                              // Services endif;.
                                              ?>

                    </li>
                                              <?php
                                              // Reset the color pattern after 4 sections.
                                              if ($color_counter < 4) {
                                                $color_counter++;
                                              }
                                              else {
                                                $color_counter = 1;
                                              }
                                              // While Services sections             endwhile;.
                                              ?>

            </ul>

            <a class="back-to-top-btn" href="#all-services-top-link">
                <span class="arrow-icon notranslate" aria-hidden="true">arrow_upward</span>Back to Top
            </a>
        </div>

                                              <?php
                                              // Services sections.
                                            endif;

                        endif;
