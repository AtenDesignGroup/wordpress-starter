<?php
/**
 * Video Gallery Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $post_id The post ID the block is rendering content against.
 *          This is either the post ID currently being displayed inside a query loop,
 *          or the post ID of the post hosting this block.
 * @param   array $context The context provided to the block by the post or it's parent block.
 */

if( isset( $block['data']['preview_image'] )  ) :    /* rendering in inserter preview  */
    echo '<img src="' . esc_url( get_site_url() . $block['data']['preview_image'] ) . '" style="width:100%; height:auto;">';
else :

	// Support custom "anchor" values.
	$anchor = '';
	if ( ! empty( $block['anchor'] ) ) {
		$anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
	}

	// Create class attribute allowing for custom "className" and "align" values.
	$class_name = 'video-gallery-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	} 

	$videos = get_field('videos');
	if(is_array($videos)) : ?>
		<div <?php echo $anchor; ?>class="<?php echo esc_attr( $class_name ); ?> alignfull">
			<ul class="video-grid">
				<?php foreach($videos as $video) : 
					$video_title = ($video['video_title']) ? $video['video_title'] : '';
					$cover_image = ($video['cover_image']) ? $video['cover_image'] : '';
					$video_publication_date = ($video['video_publication_date']) ? $video['video_publication_date'] : '';
					$video_source = ($video['video_source']) ? $video['video_source'] : '';
					$video_link = $file_size = '';
					if($video_source === 'external') :
						$video_link = ($video['video_link']) ? $video['video_link'] : '';
					elseif($video_source === 'internal') :
						$video_id = $video['video_file'];
						if($video_id) {
							$video_link = wp_get_attachment_url($video_id);
							$file_size = filesize(get_attached_file($video_id)); 
							// Convert file size to kilobytes (KB)
							$file_size = round($file_size / 1024, 2);
							$file_size_suffix = 'KB';
							
							// Convert to megabytes (MB) as necessary
							if($file_size > 1000) {
								$file_size = round($file_size / 1000, 2);
								$file_size_suffix = 'MB';
							}
						}
					endif;

					if($video_title && $cover_image && $video_link) :
						// Generate a unique ID for each video frame
						$video_frame_id = substr( md5( serialize( $video_title ) ), 0, 8 );
				?>
					<li class="single-video-item">
						<div class="single-video-wrapper">
							<a href="#gallery-video-<?php echo $video_frame_id; ?>" data-type="html" class="video-lightbox-trigger" data-caption="<?php echo $video_title; ?>">
								<div class="video-thumbnail-wrapper" style="background-image: url('<?php echo $cover_image; ?>');">
									<img src="<?php echo get_stylesheet_directory_uri();?>/assets/icons/play.svg" alt="Play the video <?php echo $video_title; ?>" class="video-gallery-icon" />
								</div>
							</a>

							<div class="video-lightbox-content" id="gallery-video-<?php echo $video_frame_id; ?>">
								<iframe src="<?php echo $video_link; ?>" title="Video Player" frameborder="0" width="100%" height="auto" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
								<div class="lightbox-video-meta">
									<p class="lightbox-video-title">
										<?php if($video_source === 'external') : ?>
											<a href="<?php echo $video_link; ?>" target="_blank" class="external-link">
										<?php endif; 

										echo $video_title; 
										
										if($video_source === 'external') : ?>
											</a>
										<?php endif; ?>
									</p>

									<?php if($video_source === 'internal' && $file_size) : ?>
										<p class="lightbox-video-download">
											<a class="video-download" href="<?php echo $video_link; ?>" download>Download (<?php echo $file_size . ' ' . $file_size_suffix; ?>)</a>
										</p>
									<?php endif; ?>
								</div>
							</div>

							<div class="video-meta">
								<p class="video-title">
									<?php if($video_source === 'external') : ?>
										<a href="<?php echo $video_link; ?>" target="_blank" class="external-link">
									<?php endif; 

									echo $video_title; 
									
									if($video_source === 'external') : ?>
										</a>
									<?php endif; ?>
								</p>

								<?php if($video_source === 'internal' && $file_size) : ?>
									<p class="video-download">
										<a class="video-download" href="<?php echo $video_link; ?>" download>Download (<?php echo $file_size . ' ' . $file_size_suffix; ?>)</a>
									</p>
								<?php endif; ?>
							</div>
						</div>
					</li>
				<?php endif; endforeach; ?>
			</ul>
		</div>
		<style>
			.tobii {
				transition: opacity .5s ease;
			}

			.transparent {
				opacity: 0!important;
			}
		</style>
		<script src="https://cdn.jsdelivr.net/npm/macy@2"></script>
		<script src="<?php echo get_stylesheet_directory_uri(); ?>/tobii/tobii.min.js"></script>
		<link href="<?php echo get_stylesheet_directory_uri(); ?>/tobii/tobii.min.css" rel="stylesheet">
		<script type="text/javascript">
			// Lightbox 
			const videoLightBox = new Tobii({
				selector: ".video-lightbox-trigger",
				zoom: false,
				captions: true,
				captionsSelector: "self",
				captionAttribute: "data-caption",
				counter: false,
				nav: false
			});

			jQuery( document ).ready(function($) {
				$('.tobii').addClass('transparent video-gallery-lightbox');
				$('.tobii__btn--close').html('<svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="50" height="50" rx="25" stroke="white" stroke-width="2"/><g clip-path="url(#clip0_476_6964)"><path fill-rule="evenodd" clip-rule="evenodd" d="M26 27.4142L34.4853 35.8995L35.8995 34.4853L27.4142 26L35.8995 17.5147L34.4853 16.1005L26 24.5858L17.5147 16.1005L16.1005 17.5147L24.5858 26L16.1005 34.4853L17.5147 35.8995L26 27.4142Z" fill="white"/></g><defs><clipPath id="clip0_476_6964"><rect width="28" height="28" fill="white" transform="translate(12 12)"/></clipPath></defs></svg>');

				// Open/Close events
				videoLightBox.on('open', function(){
					$('.tobii.transparent').removeClass('transparent');
				});

				videoLightBox.on('close', function(){
					$('.tobii').addClass('transparent');
				});

				$('.tobii__btn--close').click(function(e){
					e.stopPropagation();
					$('.tobii').addClass('transparent');
					setTimeout(() => {
						videoLightBox.close();
					}, "500"); 
				});
			});
		</script>
	<?php endif; 
 endif; ?>



