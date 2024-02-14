<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_One
 * @since Twenty Twenty-One 1.0
 */

get_header();

// Check if user is an approved member or site admin
$is_user_member = check_user_authentication();
$site_url = get_site_url();

// Get page heading fields from the 404 options page
$heading_text = (get_field('page_title_text', 'option')) ? get_field('page_title_text', 'option') : 'Page Not Found';
$subheading_text = (get_field('page_heading_description', 'option')) ? get_field('page_heading_description', 'option') : '';

// Get the field groups from the 404 options page
$card_1_field_group = get_field('card_1', 'option');
$card_2_field_group = get_field('card_2', 'option');
$card_3_field_group = get_field('card_3', 'option');

// Get archive slugs
$message_archive_slug = get_post_type_object( 'message' )->has_archive;
$research_archive_slug = get_post_type_object( 'research' )->has_archive;

if($is_user_member) { // If user is an approved member, look for the member fields
	// Card 1, defaults to Social Dashboard
	$card_1_member_fields = $card_1_field_group['member_fields'];
	$card_1_title = (isset($card_1_member_fields['card_heading_text'])) ? $card_1_member_fields['card_heading_text'] : 'View the social dashboard for up-to-date conversations about healthcare equity.';
	$card_1_url = (isset($card_1_member_fields['card_link']) && isset($card_1_member_fields['card_link']['url'])) ? $card_1_member_fields['card_link']['url'] : $site_url . '/social-listening/';
	$card_1_url_title = (isset($card_1_member_fields['card_link']) && isset($card_1_member_fields['card_link']['title'])) ? $card_1_member_fields['card_link']['title'] : 'Social Listening Map';
	$card_1_button_text = (isset($card_1_member_fields['button_text'])) ? $card_1_member_fields['button_text'] : 'View the Social Dashboard';

	// Card 2, defaults to Research archive
	$card_2_member_fields = $card_2_field_group['member_fields'];
	$card_2_title = (isset($card_2_member_fields['card_heading_text'])) ? $card_2_member_fields['card_heading_text'] : 'Explore our archive of available research and learning resources.';
	$card_2_url = (isset($card_2_member_fields['card_link']) && isset($card_2_member_fields['card_link']['url'])) ? $card_2_member_fields['card_link']['url'] : $site_url . '/' . $research_archive_slug . '/';
	$card_2_url_title = (isset($card_2_member_fields['card_link']) && isset($card_2_member_fields['card_link']['title'])) ? $card_2_member_fields['card_link']['title'] : 'Research Archive';
	$card_2_button_text = (isset($card_2_member_fields['button_text'])) ? $card_2_member_fields['button_text'] : 'Explore Research Articles';

	// Card 3, defaults to Message archive
	$card_3_member_fields = $card_3_field_group['member_fields'];
	$card_3_title = (isset($card_3_member_fields['card_heading_text'])) ? $card_3_member_fields['card_heading_text'] : 'Visit the message lab for messaging templates, infographs, and more.';
	$card_3_url = (isset($card_3_member_fields['card_link']) && isset($card_3_member_fields['card_link']['url'])) ? $card_3_member_fields['card_link']['url'] : $site_url . '/' . $message_archive_slug . '/';
	$card_3_url_title = (isset($card_3_member_fields['card_link']) && isset($card_3_member_fields['card_link']['title'])) ? $card_3_member_fields['card_link']['title'] : 'Message Lab';
	$card_3_button_text = (isset($card_3_member_fields['button_text'])) ? $card_3_member_fields['button_text'] : 'Visit the Message Lab';
} else { // If user is not an approved member, look for the non-member fields
	// Card 1, defaults to Homepage
	$card_1_non_member_fields = $card_1_field_group['non_member_fields'];
	$card_1_title = (isset($card_1_non_member_fields['card_heading_text'])) ? $card_1_non_member_fields['card_heading_text'] : 'Return home to learn more about the collaborative.';
	$card_1_url = (isset($card_1_non_member_fields['card_link']) && isset($card_1_non_member_fields['card_link']['url'])) ? $card_1_non_member_fields['card_link']['url'] : $site_url;
	$card_1_url_title = (isset($card_1_non_member_fields['card_link']) && isset($card_1_non_member_fields['card_link']['title'])) ? $card_1_non_member_fields['card_link']['title'] : 'Cost & Care Collaborative';
	$card_1_button_text = (isset($card_1_non_member_fields['button_text'])) ? $card_1_non_member_fields['button_text'] : 'Return to Home';

	// Card 2, defaults to Request Access
	$card_2_non_member_fields = $card_2_field_group['non_member_fields'];
	$card_2_title = (isset($card_2_non_member_fields['card_heading_text'])) ? $card_2_non_member_fields['card_heading_text'] : 'Request access to our library of resources and social dashboards.';
	$card_2_url = (isset($card_2_non_member_fields['card_link']) && isset($card_2_non_member_fields['card_link']['url'])) ? $card_2_non_member_fields['card_link']['url'] : $site_url . '/register/';
	$card_2_url_title = (isset($card_2_non_member_fields['card_link']) && isset($card_2_non_member_fields['card_link']['title'])) ? $card_2_non_member_fields['card_link']['title'] : 'Request Access';
	$card_2_button_text = (isset($card_2_non_member_fields['button_text'])) ? $card_2_non_member_fields['button_text'] : 'Request Access';

	// Card 3, defaults to Log In
	$card_3_non_member_fields = $card_3_field_group['non_member_fields'];
	$card_3_title = (isset($card_3_non_member_fields['card_heading_text'])) ? $card_3_non_member_fields['card_heading_text'] : 'Already a member? Sign in to view resources and manage your account.';
	$card_3_url = (isset($card_3_non_member_fields['card_link']) && isset($card_3_non_member_fields['card_link']['url'])) ? $card_3_non_member_fields['card_link']['url'] : $site_url . '/login/';
	$card_3_url_title = (isset($card_3_non_member_fields['card_link']) && isset($card_3_non_member_fields['card_link']['title'])) ? $card_3_non_member_fields['card_link']['title'] : 'Log In';
	$card_3_button_text = (isset($card_3_non_member_fields['button_text'])) ? $card_3_non_member_fields['button_text'] : 'Log In';
}

?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="page-header-component animate-fade-in-slide-up">
		<div class="page-header-triangle"><img src="<?php echo get_stylesheet_directory_uri();?>/assets/images/page-header-triangle.svg" alt="" /></div>
		<div class="page-header-wrapper">
			<div class="page-header-content">
				<h1><?php echo $heading_text; ?></h1>
				<?php if($subheading_text) { echo '<h2>' . $subheading_text . '</h2>'; } ?>
			</div>
		</div>
	</div>

	<div class="cta-panel-component-404">
		<ul class="cta-cards">
			<li class="cta-card animate-fade-in-slide-up">
				<h3><?php echo $card_1_title; ?></h3>
				<div class="cta-btn-wrap button-with-icon large-button-purple">
					<a href="<?php echo $card_1_url; ?>" title="<?php echo $card_1_url_title; ?>" target="_self">
						<?php echo $card_1_button_text; ?>
					</a>
				</div>
			</li>

			<li class="cta-card animate-fade-in-slide-up">
				<h3><?php echo $card_2_title; ?></h3>
				<div class="cta-btn-wrap button-with-icon large-button-purple">
					<a href="<?php echo $card_2_url; ?>" title="<?php echo $card_2_url_title; ?>" target="_self">
						<?php echo $card_2_button_text; ?>
					</a>
				</div>
			</li>

			<li class="cta-card animate-fade-in-slide-up">
				<h3><?php echo $card_3_title; ?></h3>
				<div class="cta-btn-wrap button-with-icon large-button-purple">
					<a href="<?php echo $card_3_url; ?>" title="<?php echo $card_3_url_title; ?>" target="_self">
						<?php echo $card_3_button_text; ?>
					</a>
				</div>
			</li>
		</ul>
	</div>
</article>

<?php
get_footer();
