<?php
/**
 * Image Gallery Block Template.
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
    echo '<img src="'. $block['data']['preview_image'] .'" style="width:100%; height:auto;">';
else :

	// Support custom "anchor" values.
	$anchor = '';
	if ( ! empty( $block['anchor'] ) ) {
		$anchor = 'id="' . esc_attr( $block['anchor'] ) . '" ';
	}

	// Create class attribute allowing for custom "className" and "align" values.
	$class_name = 'image-gallery-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	} 

	$images = get_field('image_gallery');
	$size = 'full';
	if( $images ): ?>
	
		<div <?php echo $anchor; ?> class="<?php echo esc_attr( $class_name ); ?> alignfull">
			<div class="masonry-grid">
				<ul>
					<?php foreach( $images as $image ): 
						$image_id = $image['ID'];
						$image_alt = ($image['alt']) ? $image['alt'] : "";
						$image_meta = [ 'class' => 'grid-img', 'alt' => $image_alt, 'title' => $image['title'] ];
						$image_srcset = wp_get_attachment_image_srcset($image_id);

						// Generate a unique ID for each video frame
						$image_id_suffix = substr( md5( serialize( $image_id ) ), 0, 8 );
						
						$file_size = filesize( get_attached_file( $image_id ) ); 
						// Convert file size to kilobytes (KB)
						$file_size = round($file_size / 1024, 2);
						$file_size_suffix = 'KB';
						
						// Convert to megabytes (MB) as necessary
						if($file_size > 1000) {
							$file_size = round($file_size / 1000, 2);
							$file_size_suffix = 'MB';
						} 
						?>
						<li>
							<figure class="masonry-grid-img">
								<a href="#gallery-image-<?php echo $image_id_suffix; ?>" data-type="html" class="image-lightbox-trigger lando" data-caption="<?php echo wp_get_attachment_caption( $image_id ); ?>" data-group="masonry-grid-lightbox">
									<?php echo wp_get_attachment_image( $image_id, $size, false, $image_meta ); ?>
								</a>
								<div class="caption-wrapper">
									<figcaption class="img-caption"><?php echo wp_get_attachment_caption( $image_id ); ?></figcaption>
									<a href="<?php echo wp_get_attachment_url( $image_id ); ?>" class="img-download-link" download>Download (<?php echo $file_size . ' ' . $file_size_suffix; ?>)</a>
								</div>
							</figure>

							<div class="image-lightbox-content" id="gallery-image-<?php echo $image_id_suffix; ?>">
								<figure class="lightbox-image">
									<?php echo wp_get_attachment_image( $image_id, $size, false, $image_meta ); ?>
									<div class="caption-wrapper">
										<figcaption class="img-caption"><?php echo wp_get_attachment_caption( $image_id ); ?></figcaption>
										<a href="<?php echo wp_get_attachment_url( $image_id ); ?>" class="img-download-link" download>Download (<?php echo $file_size . ' ' . $file_size_suffix; ?>)</a>
									</div>
								</figure>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
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
			// Masonry grid 
			var macyInstance = Macy({
				container: '.masonry-grid ul',
				columns: 1,
				mobileFirst: true,
				trueOrder: true,
				margin: {
					x: 0,
					y: 20,
				},
				breakAt: {
					768: {
						margin: {
							x: 20
						},
						columns: 2
					},
					1024: {
						margin: {
							x: 40,
							y: 40,
						}
					},
					1400: {
						columns: 3
					}
				}
			});

			// Lightbox 
			const imageLightBox = new Tobii({
				selector: '.image-lightbox-trigger',
				zoom: false,
				nav: true,
				captionsSelector: "self",
				captionAttribute: "data-caption"
			});

			jQuery( document ).ready(function($) {
				macyInstance.reInit();

				// Creating custom container for lightbox styling and moving lightbox controls into it
				$('<div class="lightbox-controls" />').insertAfter('.tobii__slider');
				$('.tobii__counter').detach().appendTo('.lightbox-controls');
				$('.tobii__btn--previous').detach().appendTo('.lightbox-controls').html('<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="0.5" y="0.5" width="35" height="35" rx="17.5" stroke="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M15.3101 12.4531L10.1673 17.596V18.4041L15.3101 23.547L16.1182 22.7388L11.9509 18.5715H25.4285V17.4286H11.9509L16.1182 13.2612L15.3101 12.4531Z" fill="white"/></svg>');
				$('.tobii__btn--next').detach().appendTo('.lightbox-controls').html('<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="0.5" y="0.5" width="35" height="35" rx="17.5" stroke="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M20.6899 12.4531L25.8327 17.596V18.4041L20.6899 23.547L19.8818 22.7388L24.0491 18.5715H10.5715V17.4286H24.0491L19.8818 13.2612L20.6899 12.4531Z" fill="white"/></svg>');
				$('.tobii__btn--close').html('<svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="1" width="50" height="50" rx="25" stroke="white" stroke-width="2"/><g clip-path="url(#clip0_476_6964)"><path fill-rule="evenodd" clip-rule="evenodd" d="M26 27.4142L34.4853 35.8995L35.8995 34.4853L27.4142 26L35.8995 17.5147L34.4853 16.1005L26 24.5858L17.5147 16.1005L16.1005 17.5147L24.5858 26L16.1005 34.4853L17.5147 35.8995L26 27.4142Z" fill="white"/></g><defs><clipPath id="clip0_476_6964"><rect width="28" height="28" fill="white" transform="translate(12 12)"/></clipPath></defs></svg>');

				$('.tobii').addClass('transparent image-gallery-lightbox');
				// Open/Close events
				imageLightBox.on('open', function(){
					$('.tobii.transparent').removeClass('transparent');
				});

				imageLightBox.on('close', function(){
					$('.tobii').addClass('transparent');
				});

				$('.tobii__btn--close').click(function(e){
					e.stopPropagation();
					$('.tobii').addClass('transparent');
					setTimeout(() => {
						imageLightBox.close();
					}, "500"); 
				});
			});
		</script>
	<?php endif; ?>

<?php endif; ?>



