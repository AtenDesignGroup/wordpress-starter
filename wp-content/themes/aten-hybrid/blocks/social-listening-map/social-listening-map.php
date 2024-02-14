<?php
/**
 * Social Listening Map Block Template.
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
	$class_name = 'social-listening-map-block';
	if ( ! empty( $block['className'] ) ) {
		$class_name .= ' ' . $block['className'];
	}
	if ( ! empty( $block['align'] ) ) {
		$class_name .= ' align' . $block['align'];
	}

	// Load values and assign defaults.
    $heading_text = (get_field( 'heading_text' )) ? get_field( 'heading_text' ) : 'Social Dashboards';
    $description = (get_field( 'description' )) ? get_field( 'description' ) : '';
    $has_national_dashboard_btn = get_field('display_national_dashboard_btn');
    $national_dashboard_link = (get_field('national_dashboard_link') && is_array(get_field('national_dashboard_link')) && isset(get_field('national_dashboard_link')['url'])) ? get_field('national_dashboard_link')['url'] : '';
    $display_contact_link = get_field('display_a_contact_link');
    $state_link_fields = (get_field('state_dashboard_links')) ? get_field('state_dashboard_links') : '';
    $state_abbreviations = ($state_link_fields && is_array($state_link_fields)) ? array_keys($state_link_fields) : '';

    // Getting state links 
    foreach($state_abbreviations as $state) : 
        if(get_field('state_dashboard_links_' . $state) && is_array(get_field('state_dashboard_links_' . $state)) && isset(get_field('state_dashboard_links_' . $state)['url'])) {
            ${"state_link_" . $state} = get_field('state_dashboard_links_' . $state)['url'];
        } else {
            ${"state_link_" . $state} = '';
        }
    endforeach; 
	?>

    <div class="social-listening-map-block alignfull">
        <div class="social-listening-wrapper">
            <div class="social-listening-heading-section animate-fade-in-slide-up">
                <h1><?php echo $heading_text; ?></h1>
                <?php if($description) { echo '<p>' . $description . '</p>'; } ?>
                <?php if($has_national_dashboard_btn && $national_dashboard_link): ?>
                    <div class="btn-with-divider large-button-white button-with-icon external">
                        <a href="<?php echo $national_dashboard_link; ?>" target="_blank">Go to the national dashboard</a>
                        <hr class="gradient" />
                    </div>
                <?php endif; ?>
            </div>
            <div class="map-skip-link-wrapper">
                <a class="skip-link screen-reader-text" href="#state-selection-form" id="map-skip-link">
                    <?php
                        /* translators: Hidden accessibility text. */
                        esc_html_e( 'Skip to state dashboard selection form', 'twentytwentyone' );
                    ?>
                </a>
            </div>

            <div class="social-listening-map-section animate-fade-in-slide-up">
                <div id="mobile-map-wrapper">
                    <svg id="social-map-mobile" class="social-listening-map" viewbox="0 0 335 1135" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMin slice">
                        <g class="state-box" id="mobile-al">
                            <?php if($state_link_AL) { ?><a href="<?php echo $state_link_AL; ?>" class="state-link" data-selected-state="AL" aria-label="Visit the Alabama Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2 6C2 3.79086 3.79086 2 6 2H71.75C73.9591 2 75.75 3.79086 75.75 6V78H2V6Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2 6C2 3.79086 3.79086 2 6 2H71.75C73.9591 2 75.75 3.79086 75.75 6V78H2V6Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M61.3,10.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V10.3z M55.4,13l0.5-0.5h4.4v1.1h-3.8v7.6 h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V13z"/>
                                <path class="state-abbreviation" d="M37.4606 46L36.3566 43.312H30.9966L29.9566 46H27.5566L32.2766 34.32H35.0766L40.0206 46H37.4606ZM31.7806 41.312H35.5406L33.6526 36.688H33.5726L31.7806 41.312ZM41.3972 46V34.32H43.7972V43.936H50.0052V46H41.3972Z" />
                            <?php if($state_link_AL) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ak">
                            <?php if($state_link_AK) { ?><a href="<?php echo $state_link_AK; ?>" class="state-link" data-selected-state="AK" aria-label="Visit the Alaska Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M87.75 6C87.75 3.79086 89.5409 2 91.75 2H157.5C159.709 2 161.5 3.79086 161.5 6V78H87.75V6Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M87.75 6C87.75 3.79086 89.5409 2 91.75 2H157.5C159.709 2 161.5 3.79086 161.5 6V78H87.75V6Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M147.3,10.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V10.3z M141.3,13l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V13z"/>
                                <path class="state-abbreviation" d="M122.218 46L121.114 43.312H115.754L114.714 46H112.314L117.034 34.32H119.834L124.778 46H122.218ZM116.538 41.312H120.298L118.41 36.688H118.33L116.538 41.312ZM126.155 34.32H128.555V39.536L133.835 34.32H136.731L131.835 39.12L136.987 46H134.011L130.123 40.784L128.555 42.32V46H126.155V34.32Z" />
                            <?php if($state_link_AK) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-az">
                            <?php if($state_link_AZ) { ?><a href="<?php echo $state_link_AZ; ?>" class="state-link" data-selected-state="AZ" aria-label="Visit the Arizona Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M173.5 6C173.5 3.79086 175.291 2 177.5 2H243.25C245.459 2 247.25 3.79086 247.25 6V78H173.5V6Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M173.5 6C173.5 3.79086 175.291 2 177.5 2H243.25C245.459 2 247.25 3.79086 247.25 6V78H173.5V6Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M233,10.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4H233V10.3z M227,13l0.5-0.5h4.4v1.1h-3.8v7.6 h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V13z"/>
                                <path class="state-abbreviation" d="M208.531 46L207.427 43.312H202.067L201.027 46H198.627L203.347 34.32H206.147L211.091 46H208.531ZM202.851 41.312H206.611L204.723 36.688H204.643L202.851 41.312ZM221.604 34.32V36.256L214.836 43.936H221.604V46H211.78V44.128L218.644 36.384H211.924V34.32H221.604Z" />
                            <?php if($state_link_AZ) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ar">
                            <?php if($state_link_AR) { ?><a href="<?php echo $state_link_AR; ?>" class="state-link" data-selected-state="AR" aria-label="Visit the Arkansas Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M259.25 6C259.25 3.79086 261.041 2 263.25 2H329C331.209 2 333 3.79086 333 6V78H259.25V6Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M259.25 6C259.25 3.79086 261.041 2 263.25 2H329C331.209 2 333 3.79086 333 6V78H259.25V6Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M318.8,10.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V10.3z M312.9,13l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V13z"/>
                                <path class="state-abbreviation" d="M293.586 46L292.482 43.312H287.122L286.082 46H283.682L288.402 34.32H291.202L296.146 46H293.586ZM287.906 41.312H291.666L289.778 36.688H289.698L287.906 41.312ZM305.378 46C304.994 45.584 304.85 44.384 304.738 43.424C304.594 42.144 304.226 41.728 302.962 41.728H299.922V46H297.522V34.32H303.81C306.354 34.32 307.65 35.584 307.65 37.68C307.65 40.016 306.066 40.624 304.866 40.704V40.752C305.97 40.848 306.85 41.28 307.154 43.024C307.41 44.48 307.586 45.488 308.146 46H305.378ZM299.922 39.696H302.882C303.778 39.696 305.218 39.504 305.218 38.016C305.218 37.024 304.626 36.384 303.25 36.384H299.922V39.696Z" />
                            <?php if($state_link_AR) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ca">
                            <?php if($state_link_CA) { ?><a href="<?php echo $state_link_CA; ?>" class="state-link" data-selected-state="CA" aria-label="Visit the California Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2 94C2 91.7909 3.79086 90 6 90H71.75C73.9591 90 75.75 91.7909 75.75 94V166H2V94Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2 94C2 91.7909 3.79086 90 6 90H71.75C73.9591 90 75.75 91.7909 75.75 94V166H2V94Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M61.3,98.2h5.5l0.5,0.5v5.5h-1.1V100l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V98.2z M55.4,100.9l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V100.9z"/>
                                <path class="state-abbreviation" d="M38.8048 129.024C38.3568 132.016 36.3568 134.192 32.7088 134.192C28.9648 134.192 26.8208 131.696 26.8208 128.32C26.8208 124.56 29.1888 122.128 32.8688 122.128C36.0688 122.128 38.0368 124.032 38.5968 126.64L36.3568 127.2C35.9088 125.344 34.6928 124.208 32.9008 124.208C30.6608 124.208 29.3008 125.744 29.3008 128.224C29.3008 130.656 30.5808 132.112 32.8368 132.112C34.8688 132.112 36.2128 130.8 36.5008 128.736L38.8048 129.024ZM48.9606 134L47.8566 131.312H42.4966L41.4566 134H39.0566L43.7766 122.32H46.5766L51.5206 134H48.9606ZM43.2806 129.312H47.0406L45.1526 124.688H45.0726L43.2806 129.312Z" />
                            <?php if($state_link_CA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-co">
                            <?php if($state_link_CO) { ?><a href="<?php echo $state_link_CO; ?>" class="state-link" data-selected-state="CO" aria-label="Visit the Colorado Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M87.75 94C87.75 91.7909 89.5409 90 91.75 90H157.5C159.709 90 161.5 91.7909 161.5 94V166H87.75V94Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M87.75 94C87.75 91.7909 89.5409 90 91.75 90H157.5C159.709 90 161.5 91.7909 161.5 94V166H87.75V94Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M147.3,98.2h5.5l0.5,0.5v5.5h-1.1V100l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V98.2z M141.3,100.9l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V100.9z"/>
                                <path class="state-abbreviation" d="M123.719 129.024C123.271 132.016 121.271 134.192 117.623 134.192C113.879 134.192 111.735 131.696 111.735 128.32C111.735 124.56 114.103 122.128 117.783 122.128C120.983 122.128 122.951 124.032 123.511 126.64L121.271 127.2C120.823 125.344 119.607 124.208 117.815 124.208C115.575 124.208 114.215 125.744 114.215 128.224C114.215 130.656 115.495 132.112 117.751 132.112C119.783 132.112 121.127 130.8 121.415 128.736L123.719 129.024ZM131.256 134.192C127.512 134.192 125.016 131.744 125.016 128.16C125.016 124.576 127.512 122.128 131.256 122.128C135.016 122.128 137.512 124.576 137.512 128.16C137.512 131.744 135.016 134.192 131.256 134.192ZM127.496 128.16C127.496 130.496 128.904 132.112 131.256 132.112C133.624 132.112 135.032 130.496 135.032 128.16C135.032 125.824 133.624 124.208 131.256 124.208C128.904 124.208 127.496 125.824 127.496 128.16Z" />
                            <?php if($state_link_CO) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ct">
                            <?php if($state_link_CT) { ?><a href="<?php echo $state_link_CT; ?>" class="state-link" data-selected-state="CT" aria-label="Visit the Connecticut Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M173.5 94C173.5 91.7909 175.291 90 177.5 90H243.25C245.459 90 247.25 91.7909 247.25 94V166H173.5V94Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M173.5 94C173.5 91.7909 175.291 90 177.5 90H243.25C245.459 90 247.25 91.7909 247.25 94V166H173.5V94Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M233,98.2h5.5l0.5,0.5v5.5h-1.1V100l-7.4,7.4l-0.8-0.8l7.4-7.4H233V98.2z M227,100.9l0.5-0.5h4.4v1.1h-3.8v7.6 h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V100.9z"/>
                                <path class="state-abbreviation" d="M210.953 129.024C210.505 132.016 208.505 134.192 204.857 134.192C201.113 134.192 198.969 131.696 198.969 128.32C198.969 124.56 201.337 122.128 205.017 122.128C208.217 122.128 210.185 124.032 210.745 126.64L208.505 127.2C208.057 125.344 206.841 124.208 205.049 124.208C202.809 124.208 201.449 125.744 201.449 128.224C201.449 130.656 202.729 132.112 204.985 132.112C207.017 132.112 208.361 130.8 208.649 128.736L210.953 129.024ZM215.544 134V124.384H211.24V122.32H222.248V124.384H217.944V134H215.544Z" />
                            <?php if($state_link_CT) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-de">
                            <?php if($state_link_DE) { ?><a href="<?php echo $state_link_DE; ?>" class="state-link" data-selected-state="DE" aria-label="Visit the Delaware Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M259.25 94C259.25 91.7909 261.041 90 263.25 90H329C331.209 90 333 91.7909 333 94V166H259.25V94Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M259.25 94C259.25 91.7909 261.041 90 263.25 90H329C331.209 90 333 91.7909 333 94V166H259.25V94Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M318.8,98.2h5.5l0.5,0.5v5.5h-1.1V100l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V98.2z M312.9,100.9l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V100.9z"/>
                                <path class="state-abbreviation" d="M285.491 134V122.32H290.211C293.971 122.32 296.515 124.272 296.515 128.096C296.515 131.808 294.019 134 290.211 134H285.491ZM287.891 131.936H290.035C292.115 131.936 294.035 130.88 294.035 128.16C294.035 125.232 292.115 124.384 290.035 124.384H287.891V131.936ZM298.475 134V122.32H307.339V124.384H300.875V127.072H306.075V129.136H300.875V131.936H307.531V134H298.475Z" />
                            <?php if($state_link_DE) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-dc">
                            <?php if($state_link_DC) { ?><a href="<?php echo $state_link_DC; ?>" class="state-link" data-selected-state="DC" aria-label="Visit the Washington, D.C. Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2 182C2 179.791 3.79086 178 6 178H71.75C73.9591 178 75.75 179.791 75.75 182V254H2V182Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2 182C2 179.791 3.79086 178 6 178H71.75C73.9591 178 75.75 179.791 75.75 182V254H2V182Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M61.3,186.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V186.3z M55.4,189l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V189z"/>
                                <path class="state-abbreviation" d="M27.0222 222V210.32H31.7422C35.5022 210.32 38.0462 212.272 38.0462 216.096C38.0462 219.808 35.5502 222 31.7422 222H27.0222ZM29.4222 219.936H31.5662C33.6462 219.936 35.5662 218.88 35.5662 216.16C35.5662 213.232 33.6462 212.384 31.5662 212.384H29.4222V219.936ZM51.3986 217.024C50.9506 220.016 48.9506 222.192 45.3026 222.192C41.5586 222.192 39.4146 219.696 39.4146 216.32C39.4146 212.56 41.7826 210.128 45.4626 210.128C48.6626 210.128 50.6306 212.032 51.1906 214.64L48.9506 215.2C48.5026 213.344 47.2866 212.208 45.4946 212.208C43.2546 212.208 41.8946 213.744 41.8946 216.224C41.8946 218.656 43.1746 220.112 45.4306 220.112C47.4626 220.112 48.8066 218.8 49.0946 216.736L51.3986 217.024Z" />
                            <?php if($state_link_DC) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-fl">
                            <?php if($state_link_FL) { ?><a href="<?php echo $state_link_FL; ?>" class="state-link" data-selected-state="FL" aria-label="Visit the Florida Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M87.75 182C87.75 179.791 89.5409 178 91.75 178H157.5C159.709 178 161.5 179.791 161.5 182V254H87.75V182Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M87.75 182C87.75 179.791 89.5409 178 91.75 178H157.5C159.709 178 161.5 179.791 161.5 182V254H87.75V182Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M147.3,186.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V186.3z M141.3,189l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V189z"/>
                                <path class="state-abbreviation" d="M115.749 222V210.32H124.405V212.384H118.149V215.312H123.253V217.376H118.149V222H115.749ZM125.905 222V210.32H128.305V219.936H134.513V222H125.905Z" />
                            <?php if($state_link_FL) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ga">
                            <?php if($state_link_GA) { ?><a href="<?php echo $state_link_GA; ?>" class="state-link" data-selected-state="GA" aria-label="Visit the Georgia Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M173.5 182C173.5 179.791 175.291 178 177.5 178H243.25C245.459 178 247.25 179.791 247.25 182V254H173.5V182Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M173.5 182C173.5 179.791 175.291 178 177.5 178H243.25C245.459 178 247.25 179.791 247.25 182V254H173.5V182Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M233,186.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4H233V186.3z M227,189l0.5-0.5h4.4v1.1h-3.8v7.6 h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V189z"/>
                                <path class="state-abbreviation" d="M207.959 222L207.847 219.232H207.799C207.063 221.12 205.431 222.192 203.383 222.192C199.655 222.192 197.719 219.776 197.719 216.304C197.719 212.592 200.183 210.128 203.975 210.128C206.903 210.128 208.983 211.664 209.735 214.08L207.463 214.672C207.015 213.28 205.895 212.208 203.959 212.208C201.655 212.208 200.199 213.76 200.199 216.272C200.199 218.72 201.591 220.128 203.751 220.128C205.527 220.128 207.143 219.184 207.495 217.504H204.039V215.68H209.975V222H207.959ZM221.062 222L219.958 219.312H214.598L213.558 222H211.158L215.878 210.32H218.678L223.622 222H221.062ZM215.382 217.312H219.142L217.254 212.688H217.174L215.382 217.312Z" />
                            <?php if($state_link_GA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-hi">
                            <?php if($state_link_HI) { ?><a href="<?php echo $state_link_HI; ?>" class="state-link" data-selected-state="HI" aria-label="Visit the Hawai'i Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M259.25 182C259.25 179.791 261.041 178 263.25 178H329C331.209 178 333 179.791 333 182V254H259.25V182Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M259.25 182C259.25 179.791 261.041 178 263.25 178H329C331.209 178 333 179.791 333 182V254H259.25V182Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M318.8,186.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V186.3z M312.9,189l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V189z"/>
                                <path class="state-abbreviation" d="M296.673 222V217.104H290.625V222H288.225V210.32H290.625V215.04H296.673V210.32H299.073V222H296.673ZM301.632 222V210.32H304.032V222H301.632Z" />
                            <?php if($state_link_HI) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-id">
                            <?php if($state_link_ID) { ?><a href="<?php echo $state_link_ID; ?>" class="state-link" data-selected-state="ID" aria-label="Visit the Idaho Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2 270C2 267.791 3.79086 266 6 266H71.75C73.9591 266 75.75 267.791 75.75 270V342H2V270Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2 270C2 267.791 3.79086 266 6 266H71.75C73.9591 266 75.75 267.791 75.75 270V342H2V270Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M61.3,274.2h5.5l0.5,0.5v5.5h-1.1V276l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V274.2z M55.4,276.9l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V276.9z"/>
                                <path class="state-abbreviation" d="M31.1863 310V298.32H33.5863V310H31.1863ZM36.1394 310V298.32H40.8594C44.6194 298.32 47.1634 300.272 47.1634 304.096C47.1634 307.808 44.6674 310 40.8594 310H36.1394ZM38.5394 307.936H40.6834C42.7634 307.936 44.6834 306.88 44.6834 304.16C44.6834 301.232 42.7634 300.384 40.6834 300.384H38.5394V307.936Z" />
                            <?php if($state_link_ID) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-il">
                            <?php if($state_link_IL) { ?><a href="<?php echo $state_link_IL; ?>" class="state-link" data-selected-state="IL" aria-label="Visit the Illinois Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M87.75 270C87.75 267.791 89.5409 266 91.75 266H157.5C159.709 266 161.5 267.791 161.5 270V342H87.75V270Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M87.75 270C87.75 267.791 89.5409 266 91.75 266H157.5C159.709 266 161.5 267.791 161.5 270V342H87.75V270Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M147.3,274.2h5.5l0.5,0.5v5.5h-1.1V276l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V274.2z M141.3,276.9l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V276.9z"/>
                                <path class="state-abbreviation" d="M118.35 310V298.32H120.75V310H118.35ZM123.303 310V298.32H125.703V307.936H131.911V310H123.303Z" />
                            <?php if($state_link_IL) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-in">
                            <?php if($state_link_IN) { ?><a href="<?php echo $state_link_IN; ?>" class="state-link" data-selected-state="IN" aria-label="Visit the Indiana Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M173.5 270C173.5 267.791 175.291 266 177.5 266H243.25C245.459 266 247.25 267.791 247.25 270V342H173.5V270Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M173.5 270C173.5 267.791 175.291 266 177.5 266H243.25C245.459 266 247.25 267.791 247.25 270V342H173.5V270Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M233,274.2h5.5l0.5,0.5v5.5h-1.1V276l-7.4,7.4l-0.8-0.8l7.4-7.4H233V274.2z M227,276.9l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V276.9z"/>
                                <path class="state-abbreviation" d="M202.553 310V298.32H204.953V310H202.553ZM215.907 298.32H218.195V310H215.187L210.435 302.416L209.795 301.296V310H207.507V298.32H210.515L215.251 305.952L215.907 307.12V298.32Z" />
                            <?php if($state_link_IN) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ia">
                            <?php if($state_link_IA) { ?><a href="<?php echo $state_link_IA; ?>" class="state-link" data-selected-state="IA" aria-label="Visit the Iowa Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M259.25 270C259.25 267.791 261.041 266 263.25 266H329C331.209 266 333 267.791 333 270V342H259.25V270Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M259.25 270C259.25 267.791 261.041 266 263.25 266H329C331.209 266 333 267.791 333 270V342H259.25V270Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M318.8,274.2h5.5l0.5,0.5v5.5h-1.1V276l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V274.2z M312.9,276.9l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V276.9z"/>
                                <path class="state-abbreviation" d="M288.608 310V298.32H291.008V310H288.608ZM302.265 310L301.161 307.312H295.801L294.761 310H292.361L297.081 298.32H299.881L304.825 310H302.265ZM296.585 305.312H300.345L298.457 300.688H298.377L296.585 305.312Z" />
                            <?php if($state_link_IA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ks">
                            <?php if($state_link_KS) { ?><a href="<?php echo $state_link_KS; ?>" class="state-link" data-selected-state="KS" aria-label="Visit the Kansas Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2 358C2 355.791 3.79086 354 6 354H71.75C73.9591 354 75.75 355.791 75.75 358V430H2V358Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2 358C2 355.791 3.79086 354 6 354H71.75C73.9591 354 75.75 355.791 75.75 358V430H2V358Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M61.3,362.1h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V362.1z M55.4,364.8l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V364.8z"/>
                                <path class="state-abbreviation" d="M28.3191 386.32H30.7191V391.536L35.9991 386.32H38.8951L33.9991 391.12L39.1511 398H36.1751L32.2871 392.784L30.7191 394.32V398H28.3191V386.32ZM41.2713 393.92C41.9273 395.44 43.2393 396.16 44.9833 396.16C46.8553 396.16 47.5753 395.36 47.5753 394.56C47.5753 393.76 46.9513 393.376 45.7993 393.216C45.0793 393.12 44.1033 393.04 43.3353 392.944C41.4153 392.704 39.8633 391.856 39.8633 389.776C39.8633 387.584 41.8313 386.128 44.6793 386.128C46.6153 386.128 48.3913 386.72 49.5753 388.512L47.8313 389.872C46.9673 388.576 45.8953 388.144 44.6633 388.144C43.1913 388.144 42.3113 388.832 42.3113 389.632C42.3113 390.368 42.8233 390.688 43.9593 390.816C44.6313 390.896 45.4313 390.976 46.2473 391.072C47.9753 391.28 50.0233 391.984 50.0233 394.384C50.0233 396.768 47.9593 398.192 44.9353 398.192C42.2793 398.192 40.2953 397.168 39.2553 394.864L41.2713 393.92Z" />
                            <?php if($state_link_KS) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ky">
                            <?php if($state_link_KY) { ?><a href="<?php echo $state_link_KY; ?>" class="state-link" data-selected-state="KY" aria-label="Visit the Kentucky Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M87.75 358C87.75 355.791 89.5409 354 91.75 354H157.5C159.709 354 161.5 355.791 161.5 358V430H87.75V358Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M87.75 358C87.75 355.791 89.5409 354 91.75 354H157.5C159.709 354 161.5 355.791 161.5 358V430H87.75V358Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M147.3,362.1h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V362.1z M141.3,364.8l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V364.8z"/>
                                <path class="state-abbreviation" d="M113.975 386.32H116.375V391.536L121.655 386.32H124.551L119.655 391.12L124.807 398H121.831L117.943 392.784L116.375 394.32V398H113.975V386.32ZM136.5 386.32L131.892 393.744V398H129.492V393.712L124.884 386.32H127.604L130.788 391.616L133.972 386.32H136.5Z" />
                            <?php if($state_link_KY) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-la">
                            <?php if($state_link_LA) { ?><a href="<?php echo $state_link_LA; ?>" class="state-link" data-selected-state="LA" aria-label="Visit the Louisiana Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M173.5 358C173.5 355.791 175.291 354 177.5 354H243.25C245.459 354 247.25 355.791 247.25 358V430H173.5V358Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M173.5 358C173.5 355.791 175.291 354 177.5 354H243.25C245.459 354 247.25 355.791 247.25 358V430H173.5V358Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M233,362.1h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4H233V362.1z M227,364.8l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V364.8z"/>
                                <path class="state-abbreviation" d="M200.257 398V386.32H202.657V395.936H208.865V398H200.257ZM219.117 398L218.013 395.312H212.653L211.613 398H209.213L213.933 386.32H216.733L221.677 398H219.117ZM213.437 393.312H217.197L215.309 388.688H215.229L213.437 393.312Z" />
                            <?php if($state_link_LA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-me">
                            <?php if($state_link_ME) { ?><a href="<?php echo $state_link_ME; ?>" class="state-link" data-selected-state="ME" aria-label="Visit the Maine Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M259.25 358C259.25 355.791 261.041 354 263.25 354H329C331.209 354 333 355.791 333 358V430H259.25V358Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M259.25 358C259.25 355.791 261.041 354 263.25 354H329C331.209 354 333 355.791 333 358V430H259.25V358Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M318.8,362.1h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V362.1z M312.9,364.8l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V364.8z"/>
                                <path class="state-abbreviation" d="M297.448 386.32V398H295.048V388.912H294.968L291.816 398H289.56L286.312 388.912H286.248V398H283.96V386.32H287.752L290.744 395.12H290.792L293.752 386.32H297.448ZM300.007 398V386.32H308.871V388.384H302.407V391.072H307.607V393.136H302.407V395.936H309.063V398H300.007Z" />
                            <?php if($state_link_ME) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-md">
                            <?php if($state_link_MD) { ?><a href="<?php echo $state_link_MD; ?>" class="state-link" data-selected-state="MD" aria-label="Visit the Maryland Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2 446C2 443.791 3.79086 442 6 442H71.75C73.9591 442 75.75 443.791 75.75 446V518H2V446Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2 446C2 443.791 3.79086 442 6 442H71.75C73.9591 442 75.75 443.791 75.75 446V518H2V446Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M61.3,449.9h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V449.9z M55.4,452.6l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6V457h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V452.6z"/>
                                <path class="state-abbreviation" d="M39.1274 474.32V486H36.7274V476.912H36.6474L33.4954 486H31.2394L27.9914 476.912H27.9274V486H25.6394V474.32H29.4314L32.4234 483.12H32.4714L35.4314 474.32H39.1274ZM41.6863 486V474.32H46.4063C50.1663 474.32 52.7103 476.272 52.7103 480.096C52.7103 483.808 50.2143 486 46.4063 486H41.6863ZM44.0863 483.936H46.2303C48.3103 483.936 50.2303 482.88 50.2303 480.16C50.2303 477.232 48.3103 476.384 46.2303 476.384H44.0863V483.936Z" />
                            <?php if($state_link_MD) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ma">
                            <?php if($state_link_MA) { ?><a href="<?php echo $state_link_MA; ?>" class="state-link" data-selected-state="MA" aria-label="Visit the Massachusetts Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M87.75 446C87.75 443.791 89.5409 442 91.75 442H157.5C159.709 442 161.5 443.791 161.5 446V518H87.75V446Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M87.75 446C87.75 443.791 89.5409 442 91.75 442H157.5C159.709 442 161.5 443.791 161.5 446V518H87.75V446Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M147.3,449.9h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V449.9z M141.3,452.6l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6V457h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V452.6z"/>
                                <path class="state-abbreviation" d="M125.049 474.32V486H122.649V476.912H122.569L119.417 486H117.161L113.913 476.912H113.849V486H111.561V474.32H115.353L118.345 483.12H118.393L121.353 474.32H125.049ZM136.312 486L135.208 483.312H129.848L128.808 486H126.408L131.128 474.32H133.928L138.872 486H136.312ZM130.632 481.312H134.392L132.504 476.688H132.424L130.632 481.312Z" />
                            <?php if($state_link_MA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-mi">
                            <?php if($state_link_MI) { ?><a href="<?php echo $state_link_MI; ?>" class="state-link" data-selected-state="MI" aria-label="Visit the Michigan Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M173.5 446C173.5 443.791 175.291 442 177.5 442H243.25C245.459 442 247.25 443.791 247.25 446V518H173.5V446Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M173.5 446C173.5 443.791 175.291 442 177.5 442H243.25C245.459 442 247.25 443.791 247.25 446V518H173.5V446Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M233,449.9h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4H233V449.9z M227,452.6l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6V457h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V452.6z"/>
                                <path class="state-abbreviation" d="M214.643 474.32V486H212.243V476.912H212.163L209.011 486H206.755L203.507 476.912H203.443V486H201.155V474.32H204.947L207.939 483.12H207.987L210.947 474.32H214.643ZM217.202 486V474.32H219.602V486H217.202Z" />
                            <?php if($state_link_MI) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-mn">
                            <?php if($state_link_MN) { ?><a href="<?php echo $state_link_MN; ?>" class="state-link" data-selected-state="MN" aria-label="Visit the Minnesota Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M259.25 446C259.25 443.791 261.041 442 263.25 442H329C331.209 442 333 443.791 333 446V518H259.25V446Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M259.25 446C259.25 443.791 261.041 442 263.25 442H329C331.209 442 333 443.791 333 446V518H259.25V446Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M318.8,449.9h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V449.9z M312.9,452.6l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6V457h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V452.6z"/>                                <path class="state-abbreviation" d="M296.245 474.32V486H293.845V476.912H293.765L290.613 486H288.357L285.109 476.912H285.045V486H282.757V474.32H286.549L289.541 483.12H289.589L292.549 474.32H296.245ZM307.203 474.32H309.491V486H306.483L301.731 478.416L301.091 477.296V486H298.803V474.32H301.811L306.547 481.952L307.203 483.12V474.32Z" />
                            <?php if($state_link_MN) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ms">
                            <?php if($state_link_MS) { ?><a href="<?php echo $state_link_MS; ?>" class="state-link" data-selected-state="MS" aria-label="Visit the Mississippi Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2 534C2 531.791 3.79086 530 6 530H71.75C73.9591 530 75.75 531.791 75.75 534V606H2V534Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2 534C2 531.791 3.79086 530 6 530H71.75C73.9591 530 75.75 531.791 75.75 534V606H2V534Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M61.3,538.1h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V538.1z M55.4,540.8l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V540.8z"/>
                                <path class="state-abbreviation" d="M39.6118 562.32V574H37.2118V564.912H37.1318L33.9798 574H31.7238L28.4758 564.912H28.4118V574H26.1238V562.32H29.9158L32.9078 571.12H32.9558L35.9158 562.32H39.6118ZM43.4666 569.92C44.1226 571.44 45.4346 572.16 47.1786 572.16C49.0506 572.16 49.7706 571.36 49.7706 570.56C49.7706 569.76 49.1466 569.376 47.9946 569.216C47.2746 569.12 46.2986 569.04 45.5306 568.944C43.6106 568.704 42.0586 567.856 42.0586 565.776C42.0586 563.584 44.0266 562.128 46.8746 562.128C48.8106 562.128 50.5866 562.72 51.7706 564.512L50.0266 565.872C49.1626 564.576 48.0906 564.144 46.8586 564.144C45.3866 564.144 44.5066 564.832 44.5066 565.632C44.5066 566.368 45.0186 566.688 46.1546 566.816C46.8266 566.896 47.6266 566.976 48.4426 567.072C50.1706 567.28 52.2186 567.984 52.2186 570.384C52.2186 572.768 50.1546 574.192 47.1306 574.192C44.4746 574.192 42.4906 573.168 41.4506 570.864L43.4666 569.92Z" />
                            <?php if($state_link_MS) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-mo">
                            <?php if($state_link_MO) { ?><a href="<?php echo $state_link_MO; ?>" class="state-link" data-selected-state="MO" aria-label="Visit the Missouri Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M87.75 534C87.75 531.791 89.5409 530 91.75 530H157.5C159.709 530 161.5 531.791 161.5 534V606H87.75V534Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M87.75 534C87.75 531.791 89.5409 530 91.75 530H157.5C159.709 530 161.5 531.791 161.5 534V606H87.75V534Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M147.3,538.1h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V538.1z M141.3,540.8l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V540.8z"/>
                                <path class="state-abbreviation" d="M124.432 562.32V574H122.032V564.912H121.952L118.8 574H116.544L113.296 564.912H113.232V574H110.944V562.32H114.736L117.728 571.12H117.776L120.736 562.32H124.432ZM132.639 574.192C128.895 574.192 126.399 571.744 126.399 568.16C126.399 564.576 128.895 562.128 132.639 562.128C136.399 562.128 138.895 564.576 138.895 568.16C138.895 571.744 136.399 574.192 132.639 574.192ZM128.879 568.16C128.879 570.496 130.287 572.112 132.639 572.112C135.007 572.112 136.415 570.496 136.415 568.16C136.415 565.824 135.007 564.208 132.639 564.208C130.287 564.208 128.879 565.824 128.879 568.16Z" />
                            <?php if($state_link_MO) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-mt">
                            <?php if($state_link_MT) { ?><a href="<?php echo $state_link_MT; ?>" class="state-link" data-selected-state="MT" aria-label="Visit the Montana Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M173.5 534C173.5 531.791 175.291 530 177.5 530H243.25C245.459 530 247.25 531.791 247.25 534V606H173.5V534Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M173.5 534C173.5 531.791 175.291 530 177.5 530H243.25C245.459 530 247.25 531.791 247.25 534V606H173.5V534Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M233,538.1h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4H233V538.1z M227,540.8l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V540.8z"/>
                                <path class="state-abbreviation" d="M211.393 562.32V574H208.993V564.912H208.913L205.761 574H203.505L200.257 564.912H200.193V574H197.905V562.32H201.697L204.689 571.12H204.737L207.697 562.32H211.393ZM217.2 574V564.384H212.896V562.32H223.904V564.384H219.6V574H217.2Z" />
                            <?php if($state_link_MT) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ne">
                            <?php if($state_link_NE) { ?><a href="<?php echo $state_link_NE; ?>" class="state-link" data-selected-state="NE" aria-label="Visit the Nebraska Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M259.25 534C259.25 531.791 261.041 530 263.25 530H329C331.209 530 333 531.791 333 534V606H259.25V534Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M259.25 534C259.25 531.791 261.041 530 263.25 530H329C331.209 530 333 531.791 333 534V606H259.25V534Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M318.8,538.1h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V538.1z M312.9,540.8l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V540.8z"/>
                                <path class="state-abbreviation" d="M293.758 562.32H296.046V574H293.038L288.286 566.416L287.646 565.296V574H285.358V562.32H288.366L293.102 569.952L293.758 571.12V562.32ZM298.608 574V562.32H307.472V564.384H301.008V567.072H306.208V569.136H301.008V571.936H307.664V574H298.608Z" />
                            <?php if($state_link_NE) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-nv">
                            <?php if($state_link_NV) { ?><a href="<?php echo $state_link_NV; ?>" class="state-link" data-selected-state="NV" aria-label="Visit the Nevada Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2 622C2 619.791 3.79086 618 6 618H71.75C73.9591 618 75.75 619.791 75.75 622V694H2V622Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2 622C2 619.791 3.79086 618 6 618H71.75C73.9591 618 75.75 619.791 75.75 622V694H2V622Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M61.3,625.9h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V625.9z M55.4,628.6l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6V633h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V628.6z"/>
                                <path class="state-abbreviation" d="M35.7425 650.32H38.0305V662H35.0225L30.2705 654.416L29.6305 653.296V662H27.3425V650.32H30.3505L35.0865 657.952L35.7425 659.12V650.32ZM41.9685 650.32L45.6165 659.552H45.6805L49.1685 650.32H51.5845L46.9925 662H44.1925L39.4085 650.32H41.9685Z" />
                            <?php if($state_link_NV) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-nh">
                            <?php if($state_link_NH) { ?><a href="<?php echo $state_link_NH; ?>" class="state-link" data-selected-state="NH" aria-label="Visit the New Hampshire Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M87.75 622C87.75 619.791 89.5409 618 91.75 618H157.5C159.709 618 161.5 619.791 161.5 622V694H87.75V622Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M87.75 622C87.75 619.791 89.5409 618 91.75 618H157.5C159.709 618 161.5 619.791 161.5 622V694H87.75V622Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M147.3,625.9h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V625.9z M141.3,628.6l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6V633h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V628.6z"/>
                                <path class="state-abbreviation" d="M120.977 650.32H123.265V662H120.257L115.505 654.416L114.865 653.296V662H112.577V650.32H115.585L120.321 657.952L120.977 659.12V650.32ZM134.275 662V657.104H128.227V662H125.827V650.32H128.227V655.04H134.275V650.32H136.675V662H134.275Z" />
                            <?php if($state_link_NH) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-nj">
                            <?php if($state_link_NJ) { ?><a href="<?php echo $state_link_NJ; ?>" class="state-link" data-selected-state="NJ" aria-label="Visit the New Jersey Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M173.5 622C173.5 619.791 175.291 618 177.5 618H243.25C245.459 618 247.25 619.791 247.25 622V694H173.5V622Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M173.5 622C173.5 619.791 175.291 618 177.5 618H243.25C245.459 618 247.25 619.791 247.25 622V694H173.5V622Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M233,625.9h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4H233V625.9z M227,628.6l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6V633h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V628.6z"/>
                                <path class="state-abbreviation" d="M207.985 650.32H210.273V662H207.265L202.513 654.416L201.873 653.296V662H199.585V650.32H202.593L207.329 657.952L207.985 659.12V650.32ZM221.395 657.616C221.395 660.848 219.411 662.192 216.547 662.192C213.459 662.192 212.083 660.416 211.955 657.584L214.195 657.424C214.355 659.136 215.011 660.208 216.579 660.208C218.147 660.208 218.995 659.472 218.995 657.632V650.32H221.395V657.616Z" />
                            <?php if($state_link_NJ) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-nm">
                            <?php if($state_link_NM) { ?><a href="<?php echo $state_link_NM; ?>" class="state-link" data-selected-state="NM" aria-label="Visit the New Mexico Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M259.25 622C259.25 619.791 261.041 618 263.25 618H329C331.209 618 333 619.791 333 622V694H259.25V622Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M259.25 622C259.25 619.791 261.041 618 263.25 618H329C331.209 618 333 619.791 333 622V694H259.25V622Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M318.8,625.9h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V625.9z M312.9,628.6l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6V633h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V628.6z"/>
                                <path class="state-abbreviation" d="M291.157 650.32H293.445V662H290.437L285.685 654.416L285.045 653.296V662H282.757V650.32H285.765L290.501 657.952L291.157 659.12V650.32ZM309.495 650.32V662H307.095V652.912H307.015L303.863 662H301.607L298.359 652.912H298.295V662H296.007V650.32H299.799L302.791 659.12H302.839L305.799 650.32H309.495Z" />
                            <?php if($state_link_NM) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ny">
                            <?php if($state_link_NY) { ?><a href="<?php echo $state_link_NY; ?>" class="state-link" data-selected-state="NY" aria-label="Visit the New York Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2 710C2 707.791 3.79086 706 6 706H71.75C73.9591 706 75.75 707.791 75.75 710V782H2V710Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2 710C2 707.791 3.79086 706 6 706H71.75C73.9591 706 75.75 707.791 75.75 710V782H2V710Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M61.3,714.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V714.3z M55.4,717l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V717z"/>
                                <path class="state-abbreviation" d="M36.0706 738.32H38.3586V750H35.3506L30.5986 742.416L29.9586 741.296V750H27.6706V738.32H30.6786L35.4146 745.952L36.0706 747.12V738.32ZM51.3046 738.32L46.6966 745.744V750H44.2966V745.712L39.6886 738.32H42.4086L45.5926 743.616L48.7766 738.32H51.3046Z" />
                            <?php if($state_link_NY) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-nc">
                            <?php if($state_link_NC) { ?><a href="<?php echo $state_link_NC; ?>" class="state-link" data-selected-state="NC" aria-label="Visit the North Carolina Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M87.75 710C87.75 707.791 89.5409 706 91.75 706H157.5C159.709 706 161.5 707.791 161.5 710V782H87.75V710Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M87.75 710C87.75 707.791 89.5409 706 91.75 706H157.5C159.709 706 161.5 707.791 161.5 710V782H87.75V710Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M147.3,714.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V714.3z M141.3,717l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V717z"/>
                                <path class="state-abbreviation" d="M121.039 738.32H123.327V750H120.319L115.567 742.416L114.927 741.296V750H112.639V738.32H115.647L120.383 745.952L121.039 747.12V738.32ZM137.281 745.024C136.833 748.016 134.833 750.192 131.185 750.192C127.441 750.192 125.297 747.696 125.297 744.32C125.297 740.56 127.665 738.128 131.345 738.128C134.545 738.128 136.513 740.032 137.073 742.64L134.833 743.2C134.385 741.344 133.169 740.208 131.377 740.208C129.137 740.208 127.777 741.744 127.777 744.224C127.777 746.656 129.057 748.112 131.313 748.112C133.345 748.112 134.689 746.8 134.977 744.736L137.281 745.024Z" />
                            <?php if($state_link_NC) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-nd">
                            <?php if($state_link_ND) { ?><a href="<?php echo $state_link_ND; ?>" class="state-link" data-selected-state="ND" aria-label="Visit the North Dakota Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M173.5 710C173.5 707.791 175.291 706 177.5 706H243.25C245.459 706 247.25 707.791 247.25 710V782H173.5V710Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M173.5 710C173.5 707.791 175.291 706 177.5 706H243.25C245.459 706 247.25 707.791 247.25 710V782H173.5V710Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M233,714.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4H233V714.3z M227,717l0.5-0.5h4.4v1.1h-3.8v7.6 h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V717z"/>
                                <path class="state-abbreviation" d="M206.938 738.32H209.226V750H206.218L201.466 742.416L200.826 741.296V750H198.538V738.32H201.546L206.282 745.952L206.938 747.12V738.32ZM211.788 750V738.32H216.508C220.268 738.32 222.812 740.272 222.812 744.096C222.812 747.808 220.316 750 216.508 750H211.788ZM214.188 747.936H216.332C218.412 747.936 220.332 746.88 220.332 744.16C220.332 741.232 218.412 740.384 216.332 740.384H214.188V747.936Z" />
                            <?php if($state_link_ND) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-oh">
                            <?php if($state_link_OH) { ?><a href="<?php echo $state_link_OH; ?>" class="state-link" data-selected-state="OH" aria-label="Visit the Ohio Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M259.25 710C259.25 707.791 261.041 706 263.25 706H329C331.209 706 333 707.791 333 710V782H259.25V710Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M259.25 710C259.25 707.791 261.041 706 263.25 706H329C331.209 706 333 707.791 333 710V782H259.25V710Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M318.8,714.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V714.3z M312.9,717l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V717z"/>
                                <path class="state-abbreviation" d="M289.412 750.192C285.668 750.192 283.172 747.744 283.172 744.16C283.172 740.576 285.668 738.128 289.412 738.128C293.172 738.128 295.668 740.576 295.668 744.16C295.668 747.744 293.172 750.192 289.412 750.192ZM285.652 744.16C285.652 746.496 287.06 748.112 289.412 748.112C291.78 748.112 293.188 746.496 293.188 744.16C293.188 741.824 291.78 740.208 289.412 740.208C287.06 740.208 285.652 741.824 285.652 744.16ZM306.087 750V745.104H300.039V750H297.639V738.32H300.039V743.04H306.087V738.32H308.487V750H306.087Z" />
                            <?php if($state_link_OH) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ok">
                            <?php if($state_link_OK) { ?><a href="<?php echo $state_link_OK; ?>" class="state-link" data-selected-state="OK" aria-label="Visit the Oklahoma Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2 798C2 795.791 3.79086 794 6 794H71.75C73.9591 794 75.75 795.791 75.75 798V870H2V798Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2 798C2 795.791 3.79086 794 6 794H71.75C73.9591 794 75.75 795.791 75.75 798V870H2V798Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M61.3,802.2h5.5l0.5,0.5v5.5h-1.1V804l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V802.2z M55.4,804.9l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V804.9z"/>
                                <path class="state-abbreviation" d="M32.7952 838.192C29.0512 838.192 26.5552 835.744 26.5552 832.16C26.5552 828.576 29.0512 826.128 32.7952 826.128C36.5552 826.128 39.0512 828.576 39.0512 832.16C39.0512 835.744 36.5552 838.192 32.7952 838.192ZM29.0352 832.16C29.0352 834.496 30.4432 836.112 32.7952 836.112C35.1632 836.112 36.5712 834.496 36.5712 832.16C36.5712 829.824 35.1632 828.208 32.7952 828.208C30.4432 828.208 29.0352 829.824 29.0352 832.16ZM41.0222 826.32H43.4222V831.536L48.7022 826.32H51.5982L46.7022 831.12L51.8542 838H48.8782L44.9902 832.784L43.4222 834.32V838H41.0222V826.32Z" />
                            <?php if($state_link_OK) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-or">
                            <?php if($state_link_OR) { ?><a href="<?php echo $state_link_OR; ?>" class="state-link" data-selected-state="OR" aria-label="Visit the Oregon Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M87.75 798C87.75 795.791 89.5409 794 91.75 794H157.5C159.709 794 161.5 795.791 161.5 798V870H87.75V798Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M87.75 798C87.75 795.791 89.5409 794 91.75 794H157.5C159.709 794 161.5 795.791 161.5 798V870H87.75V798Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M147.3,802.2h5.5l0.5,0.5v5.5h-1.1V804l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V802.2z M141.3,804.9l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V804.9z"/>
                                <path class="state-abbreviation" d="M118.412 838.192C114.668 838.192 112.172 835.744 112.172 832.16C112.172 828.576 114.668 826.128 118.412 826.128C122.172 826.128 124.668 828.576 124.668 832.16C124.668 835.744 122.172 838.192 118.412 838.192ZM114.652 832.16C114.652 834.496 116.06 836.112 118.412 836.112C120.78 836.112 122.188 834.496 122.188 832.16C122.188 829.824 120.78 828.208 118.412 828.208C116.06 828.208 114.652 829.824 114.652 832.16ZM134.495 838C134.111 837.584 133.967 836.384 133.855 835.424C133.711 834.144 133.343 833.728 132.079 833.728H129.039V838H126.639V826.32H132.927C135.471 826.32 136.767 827.584 136.767 829.68C136.767 832.016 135.183 832.624 133.983 832.704V832.752C135.087 832.848 135.967 833.28 136.271 835.024C136.527 836.48 136.703 837.488 137.263 838H134.495ZM129.039 831.696H131.999C132.895 831.696 134.335 831.504 134.335 830.016C134.335 829.024 133.743 828.384 132.367 828.384H129.039V831.696Z" />
                            <?php if($state_link_OR) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-pa">
                            <?php if($state_link_PA) { ?><a href="<?php echo $state_link_PA; ?>" class="state-link" data-selected-state="PA" aria-label="Visit the Pennsylvania Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M173.5 798C173.5 795.791 175.291 794 177.5 794H243.25C245.459 794 247.25 795.791 247.25 798V870H173.5V798Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M173.5 798C173.5 795.791 175.291 794 177.5 794H243.25C245.459 794 247.25 795.791 247.25 798V870H173.5V798Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M233,802.2h5.5l0.5,0.5v5.5h-1.1V804l-7.4,7.4l-0.8-0.8l7.4-7.4H233V802.2z M227,804.9l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V804.9z"/>
                                <path class="state-abbreviation" d="M199.952 838V826.32H205.408C208.192 826.32 209.968 827.536 209.968 830.08C209.968 832.608 208.208 833.968 205.312 833.968H202.352V838H199.952ZM202.352 831.904H205.168C206.704 831.904 207.552 831.44 207.552 830.176C207.552 828.848 206.688 828.384 205.248 828.384H202.352V831.904ZM219.422 838L218.318 835.312H212.958L211.918 838H209.518L214.238 826.32H217.038L221.982 838H219.422ZM213.742 833.312H217.502L215.614 828.688H215.534L213.742 833.312Z" />
                            <?php if($state_link_PA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-pr">
                            <?php if($state_link_PR) { ?><a href="<?php echo $state_link_PR; ?>" class="state-link" data-selected-state="PR" aria-label="Visit the Puerto Rico Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M259.25 798C259.25 795.791 261.041 794 263.25 794H329C331.209 794 333 795.791 333 798V870H259.25V798Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M259.25 798C259.25 795.791 261.041 794 263.25 794H329C331.209 794 333 795.791 333 798V870H259.25V798Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M318.8,802.2h5.5l0.5,0.5v5.5h-1.1V804l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V802.2z M312.9,804.9l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V804.9z"/>
                                <path class="state-abbreviation" d="M285.343 838V826.32H290.799C293.583 826.32 295.359 827.536 295.359 830.08C295.359 832.608 293.599 833.968 290.703 833.968H287.743V838H285.343ZM287.743 831.904H290.559C292.095 831.904 292.943 831.44 292.943 830.176C292.943 828.848 292.079 828.384 290.639 828.384H287.743V831.904ZM304.917 838C304.533 837.584 304.389 836.384 304.277 835.424C304.133 834.144 303.765 833.728 302.501 833.728H299.461V838H297.061V826.32H303.349C305.893 826.32 307.189 827.584 307.189 829.68C307.189 832.016 305.605 832.624 304.405 832.704V832.752C305.509 832.848 306.389 833.28 306.693 835.024C306.949 836.48 307.125 837.488 307.685 838H304.917ZM299.461 831.696H302.421C303.317 831.696 304.757 831.504 304.757 830.016C304.757 829.024 304.165 828.384 302.789 828.384H299.461V831.696Z" />
                            <?php if($state_link_PR) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ri">
                            <?php if($state_link_RI) { ?><a href="<?php echo $state_link_RI; ?>" class="state-link" data-selected-state="RI" aria-label="Visit the Rhode Island Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2 886C2 883.791 3.79086 882 6 882H71.75C73.9591 882 75.75 883.791 75.75 886V958H2V886Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2 886C2 883.791 3.79086 882 6 882H71.75C73.9591 882 75.75 883.791 75.75 886V958H2V886Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M61.3,890.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V890.3z M55.4,893l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V893z"/>
                                <path class="state-abbreviation" d="M39.3313 926C38.9473 925.584 38.8033 924.384 38.6913 923.424C38.5473 922.144 38.1793 921.728 36.9153 921.728H33.8753V926H31.4753V914.32H37.7633C40.3073 914.32 41.6033 915.584 41.6033 917.68C41.6033 920.016 40.0193 920.624 38.8193 920.704V920.752C39.9233 920.848 40.8033 921.28 41.1073 923.024C41.3633 924.48 41.5393 925.488 42.0993 926H39.3313ZM33.8753 919.696H36.8353C37.7313 919.696 39.1713 919.504 39.1713 918.016C39.1713 917.024 38.5793 916.384 37.2033 916.384H33.8753V919.696ZM43.8816 926V914.32H46.2816V926H43.8816Z" />
                            <?php if($state_link_RI) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-sc">
                            <?php if($state_link_SC) { ?><a href="<?php echo $state_link_SC; ?>" class="state-link" data-selected-state="SC" aria-label="Visit the South Carolina Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M87.75 886C87.75 883.791 89.5409 882 91.75 882H157.5C159.709 882 161.5 883.791 161.5 886V958H87.75V886Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M87.75 886C87.75 883.791 89.5409 882 91.75 882H157.5C159.709 882 161.5 883.791 161.5 886V958H87.75V886Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M147.3,890.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V890.3z M141.3,893l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V893z"/>
                                <path class="state-abbreviation" d="M114.553 921.92C115.209 923.44 116.521 924.16 118.265 924.16C120.137 924.16 120.857 923.36 120.857 922.56C120.857 921.76 120.233 921.376 119.081 921.216C118.361 921.12 117.385 921.04 116.617 920.944C114.697 920.704 113.145 919.856 113.145 917.776C113.145 915.584 115.113 914.128 117.961 914.128C119.897 914.128 121.673 914.72 122.857 916.512L121.113 917.872C120.249 916.576 119.177 916.144 117.945 916.144C116.473 916.144 115.593 916.832 115.593 917.632C115.593 918.368 116.105 918.688 117.241 918.816C117.913 918.896 118.713 918.976 119.529 919.072C121.257 919.28 123.305 919.984 123.305 922.384C123.305 924.768 121.241 926.192 118.217 926.192C115.561 926.192 113.577 925.168 112.537 922.864L114.553 921.92ZM136.664 921.024C136.216 924.016 134.216 926.192 130.568 926.192C126.824 926.192 124.68 923.696 124.68 920.32C124.68 916.56 127.048 914.128 130.728 914.128C133.928 914.128 135.896 916.032 136.456 918.64L134.216 919.2C133.768 917.344 132.552 916.208 130.76 916.208C128.52 916.208 127.16 917.744 127.16 920.224C127.16 922.656 128.44 924.112 130.696 924.112C132.728 924.112 134.072 922.8 134.36 920.736L136.664 921.024Z" />
                            <?php if($state_link_SC) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-sd">
                            <?php if($state_link_SD) { ?><a href="<?php echo $state_link_SD; ?>" class="state-link" data-selected-state="SD" aria-label="Visit the South Dakota Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M173.5 886C173.5 883.791 175.291 882 177.5 882H243.25C245.459 882 247.25 883.791 247.25 886V958H173.5V886Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M173.5 886C173.5 883.791 175.291 882 177.5 882H243.25C245.459 882 247.25 883.791 247.25 886V958H173.5V886Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M233,890.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4H233V890.3z M227,893l0.5-0.5h4.4v1.1h-3.8v7.6 h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V893z"/>
                                <path class="state-abbreviation" d="M200.451 921.92C201.107 923.44 202.419 924.16 204.163 924.16C206.035 924.16 206.755 923.36 206.755 922.56C206.755 921.76 206.131 921.376 204.979 921.216C204.259 921.12 203.283 921.04 202.515 920.944C200.595 920.704 199.043 919.856 199.043 917.776C199.043 915.584 201.011 914.128 203.859 914.128C205.795 914.128 207.571 914.72 208.755 916.512L207.011 917.872C206.147 916.576 205.075 916.144 203.843 916.144C202.371 916.144 201.491 916.832 201.491 917.632C201.491 918.368 202.003 918.688 203.139 918.816C203.811 918.896 204.611 918.976 205.427 919.072C207.155 919.28 209.203 919.984 209.203 922.384C209.203 924.768 207.139 926.192 204.115 926.192C201.459 926.192 199.475 925.168 198.435 922.864L200.451 921.92ZM211.171 926V914.32H215.891C219.651 914.32 222.195 916.272 222.195 920.096C222.195 923.808 219.699 926 215.891 926H211.171ZM213.571 923.936H215.715C217.795 923.936 219.715 922.88 219.715 920.16C219.715 917.232 217.795 916.384 215.715 916.384H213.571V923.936Z" />
                            <?php if($state_link_SD) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-tn">
                            <?php if($state_link_TN) { ?><a href="<?php echo $state_link_TN; ?>" class="state-link" data-selected-state="TN" aria-label="Visit the Tennessee Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M259.25 886C259.25 883.791 261.041 882 263.25 882H329C331.209 882 333 883.791 333 886V958H259.25V886Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M259.25 886C259.25 883.791 261.041 882 263.25 882H329C331.209 882 333 883.791 333 886V958H259.25V886Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M318.8,890.3h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V890.3z M312.9,893l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V893z"/>
                                <path class="state-abbreviation" d="M288.301 926V916.384H283.997V914.32H295.005V916.384H290.701V926H288.301ZM304.907 914.32H307.195V926H304.187L299.435 918.416L298.795 917.296V926H296.507V914.32H299.515L304.251 921.952L304.907 923.12V914.32Z" />
                            <?php if($state_link_TN) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-tx">
                            <?php if($state_link_TX) { ?><a href="<?php echo $state_link_TX; ?>" class="state-link" data-selected-state="TX" aria-label="Visit the Texas Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2 974C2 971.791 3.79086 970 6 970H71.75C73.9591 970 75.75 971.791 75.75 974V1046H2V974Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2 974C2 971.791 3.79086 970 6 970H71.75C73.9591 970 75.75 971.791 75.75 974V1046H2V974Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M61.3,978.2h5.5l0.5,0.5v5.5h-1.1V980l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V978.2z M55.4,980.9l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V980.9z"/>
                                <path class="state-abbreviation" d="M31.7546 1014V1004.38H27.4506V1002.32H38.4586V1004.38H34.1546V1014H31.7546ZM50.4077 1014H47.5917L44.4877 1009.7L41.3997 1014H38.7917L43.1597 1008.08L39.0157 1002.32H41.8317L44.7277 1006.46L47.6397 1002.32H50.2477L46.0557 1008.02L50.4077 1014Z" />
                            <?php if($state_link_TX) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-ut">
                            <?php if($state_link_UT) { ?><a href="<?php echo $state_link_UT; ?>" class="state-link" data-selected-state="UT" aria-label="Visit the Utah Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M87.75 974C87.75 971.791 89.5409 970 91.75 970H157.5C159.709 970 161.5 971.791 161.5 974V1046H87.75V974Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M87.75 974C87.75 971.791 89.5409 970 91.75 970H157.5C159.709 970 161.5 971.791 161.5 974V1046H87.75V974Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M147.3,978.2h5.5l0.5,0.5v5.5h-1.1V980l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V978.2z M141.3,980.9l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V980.9z"/>
                                <path class="state-abbreviation" d="M118.914 1014.19C116.242 1014.19 113.714 1013.12 113.714 1008.91V1002.32H116.114V1008.86C116.114 1011.26 117.298 1012.11 118.978 1012.11C120.562 1012.11 121.682 1011.26 121.682 1008.86V1002.32H124.082V1008.91C124.082 1013.09 121.65 1014.19 118.914 1014.19ZM129.731 1014V1004.38H125.427V1002.32H136.435V1004.38H132.131V1014H129.731Z" />
                            <?php if($state_link_UT) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-vt">
                            <?php if($state_link_VT) { ?><a href="<?php echo $state_link_VT; ?>" class="state-link" data-selected-state="VT" aria-label="Visit the Vermont Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M173.5 974C173.5 971.791 175.291 970 177.5 970H243.25C245.459 970 247.25 971.791 247.25 974V1046H173.5V974Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M173.5 974C173.5 971.791 175.291 970 177.5 970H243.25C245.459 970 247.25 971.791 247.25 974V1046H173.5V974Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M233,978.2h5.5l0.5,0.5v5.5h-1.1V980l-7.4,7.4l-0.8-0.8l7.4-7.4H233V978.2z M227,980.9l0.5-0.5h4.4v1.1h-3.8 v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V980.9z"/>
                                <path class="state-abbreviation" d="M201.047 1002.32L204.695 1011.55H204.759L208.247 1002.32H210.663L206.071 1014H203.271L198.487 1002.32H201.047ZM215.434 1014V1004.38H211.13V1002.32H222.138V1004.38H217.834V1014H215.434Z" />
                            <?php if($state_link_VT) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-va">
                            <?php if($state_link_VA) { ?><a href="<?php echo $state_link_VA; ?>" class="state-link" data-selected-state="VA" aria-label="Visit the Virginia Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M259.25 974C259.25 971.791 261.041 970 263.25 970H329C331.209 970 333 971.791 333 974V1046H259.25V974Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M259.25 974C259.25 971.791 261.041 970 263.25 970H329C331.209 970 333 971.791 333 974V1046H259.25V974Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M318.8,978.2h5.5l0.5,0.5v5.5h-1.1V980l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V978.2z M312.9,980.9l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V980.9z"/>
                                <path class="state-abbreviation" d="M286.765 1002.32L290.413 1011.55H290.477L293.965 1002.32H296.381L291.789 1014H288.989L284.205 1002.32H286.765ZM305.484 1014L304.38 1011.31H299.02L297.98 1014H295.58L300.3 1002.32H303.1L308.044 1014H305.484ZM299.804 1009.31H303.564L301.676 1004.69H301.596L299.804 1009.31Z" />
                            <?php if($state_link_VA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-wa">
                            <?php if($state_link_WA) { ?><a href="<?php echo $state_link_WA; ?>" class="state-link" data-selected-state="WA" aria-label="Visit the Washington Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2 1062C2 1059.79 3.79086 1058 6 1058H71.75C73.9591 1058 75.75 1059.79 75.75 1062V1134H2V1062Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2 1062C2 1059.79 3.79086 1058 6 1058H71.75C73.9591 1058 75.75 1059.79 75.75 1062V1134H2V1062Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M61.3,1066.1h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V1066.1z M55.4,1068.8l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V1068.8z"/>
                                <path class="state-abbreviation" d="M41.0344 1090.32L38.1704 1102H34.9704L32.7784 1093.15H32.6824L30.4744 1102H27.3064L24.4264 1090.32H26.9064L29.0184 1099.36H29.0984L31.3544 1090.32H34.2184L36.4904 1099.36H36.5864L38.6824 1090.32H41.0344ZM50.859 1102L49.755 1099.31H44.395L43.355 1102H40.955L45.675 1090.32H48.475L53.419 1102H50.859ZM45.179 1097.31H48.939L47.051 1092.69H46.971L45.179 1097.31Z" />
                            <?php if($state_link_WA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-wv">
                            <?php if($state_link_WV) { ?><a href="<?php echo $state_link_WV; ?>" class="state-link" data-selected-state="WV" aria-label="Visit the West Virginia Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M87.75 1062C87.75 1059.79 89.5409 1058 91.75 1058H157.5C159.709 1058 161.5 1059.79 161.5 1062V1134H87.75V1062Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M87.75 1062C87.75 1059.79 89.5409 1058 91.75 1058H157.5C159.709 1058 161.5 1059.79 161.5 1062V1134H87.75V1062Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M147.3,1066.1h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V1066.1z M141.3,1068.8l0.5-0.5h4.4 v1.1h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V1068.8z"/>
                                <path class="state-abbreviation" d="M126.745 1090.32L123.881 1102H120.681L118.489 1093.15H118.393L116.185 1102H113.017L110.137 1090.32H112.617L114.729 1099.36H114.809L117.065 1090.32H119.929L122.201 1099.36H122.297L124.393 1090.32H126.745ZM129.586 1090.32L133.234 1099.55H133.298L136.786 1090.32H139.202L134.61 1102H131.81L127.026 1090.32H129.586Z" />
                            <?php if($state_link_WV) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-wi">
                            <?php if($state_link_WI) { ?><a href="<?php echo $state_link_WI; ?>" class="state-link" data-selected-state="WI" aria-label="Visit the Wisconsin Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M173.5 1062C173.5 1059.79 175.291 1058 177.5 1058H243.25C245.459 1058 247.25 1059.79 247.25 1062V1134H173.5V1062Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M173.5 1062C173.5 1059.79 175.291 1058 177.5 1058H243.25C245.459 1058 247.25 1059.79 247.25 1062V1134H173.5V1062Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M233,1066.1h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4H233V1066.1z M227,1068.8l0.5-0.5h4.4v1.1 h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V1068.8z"/>
                                <path class="state-abbreviation" d="M216.206 1090.32L213.342 1102H210.142L207.95 1093.15H207.854L205.646 1102H202.478L199.598 1090.32H202.078L204.19 1099.36H204.27L206.526 1090.32H209.39L211.662 1099.36H211.758L213.854 1090.32H216.206ZM217.671 1102V1090.32H220.071V1102H217.671Z" />
                            <?php if($state_link_WI) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="mobile-wy">
                            <?php if($state_link_WY) { ?><a href="<?php echo $state_link_WY; ?>" class="state-link" data-selected-state="WY" aria-label="Visit the Wyoming Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M259.25 1062C259.25 1059.79 261.041 1058 263.25 1058H329C331.209 1058 333 1059.79 333 1062V1134H259.25V1062Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M259.25 1062C259.25 1059.79 261.041 1058 263.25 1058H329C331.209 1058 333 1059.79 333 1062V1134H259.25V1062Z" stroke="url(#BorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M318.8,1066.1h5.5l0.5,0.5v5.5h-1.1v-4.2l-7.4,7.4l-0.8-0.8l7.4-7.4h-4.2V1066.1z M312.9,1068.8l0.5-0.5h4.4 v1.1h-3.8v7.6h7.6v-3.8h1.1v4.3l-0.5,0.5h-8.7l-0.5-0.5V1068.8z"/>
                                <path class="state-abbreviation" d="M298.573 1090.32L295.709 1102H292.509L290.317 1093.15H290.221L288.013 1102H284.845L281.965 1090.32H284.445L286.557 1099.36H286.637L288.893 1090.32H291.757L294.029 1099.36H294.125L296.221 1090.32H298.573ZM310.422 1090.32L305.814 1097.74V1102H303.414V1097.71L298.806 1090.32H301.526L304.71 1095.62L307.894 1090.32H310.422Z" />
                            <?php if($state_link_WY) { ?></a><?php } ?>
                        </g>

                        <defs>
                            <linearGradient id="DefaultBorderGradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="5%" stop-color="#FF6D00" />
                                <stop offset="95%" stop-color="#6300C7" stop-opacity="0" />
                            </linearGradient>
                            <linearGradient id="HoverStateBorderGradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="5%" stop-color="#FFFFFF" />
                                <stop offset="95%" stop-color="#6300C7" stop-opacity="0" />
                            </linearGradient>
                            <linearGradient id="HoverStateGradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="5%" stop-color="#6300C7" />
                                <stop offset="95%" stop-color="#6300C7" stop-opacity="0" />
                            </linearGradient>
                            <linearGradient id="BorderDisabledGradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="5%" stop-color="#ACB0BF"/>
                                <stop offset="95%" stop-color="#191326" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>

                <div id="desktop-map-wrapper">
                    <svg id="social-map-desktop" class="social-listening-map" viewbox="0 0 1272 964" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMin slice">
                        <g class="state-box" id="desktop-ak">
                            <?php if($state_link_AK) { ?><a href="<?php echo $state_link_AK; ?>" class="state-link" data-selected-state="AK" aria-label="Visit the Alaska Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2.0625 114C2.0625 111.791 3.85336 110 6.0625 110H114.062C116.272 110 118.062 111.791 118.062 114V206H2.0625V114Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2.0625 114C2.0625 111.791 3.85336 110 6.0625 110H114.062C116.272 110 118.062 111.791 118.062 114V206H2.0625V114Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M101.8,117.9h7.3l0.7,0.7v7.3h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V117.9z M93.9,121.5l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7H94.6l-0.7-0.7V121.5z"/>
                                <path class="state-abbreviation" d="M55.8509 169L53.9189 164.296H44.5389L42.7189 169H38.5189L46.7789 148.56H51.6789L60.3309 169H55.8509ZM45.9109 160.796H52.4909L49.1869 152.704H49.0469L45.9109 160.796ZM62.74 148.56H66.94V157.688L76.18 148.56H81.248L72.68 156.96L81.696 169H76.488L69.684 159.872L66.94 162.56V169H62.74V148.56Z" />
                            <?php if($state_link_AK) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-wa">
                            <?php if($state_link_WA) { ?><a href="<?php echo $state_link_WA; ?>" class="state-link" data-selected-state="WA" aria-label="Visit the Washington Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2.0625 330C2.0625 327.791 3.85336 326 6.0625 326H114.062C116.272 326 118.062 327.791 118.062 330V422H2.0625V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>    
                                <path class="state-outline" d="M2.0625 330C2.0625 327.791 3.85336 326 6.0625 326H114.062C116.272 326 118.062 327.791 118.062 330V422H2.0625V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M101.6,334.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V334.3z M93.7,338l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7H94.4l-0.7-0.7V338 z"/>
                                <path class="state-abbreviation" d="M63.8414 364.56L58.8294 385H53.2294L49.3934 369.516H49.2254L45.3614 385H39.8174L34.7774 364.56H39.1174L42.8134 380.38H42.9534L46.9014 364.56H51.9134L55.8894 380.38H56.0574L59.7254 364.56H63.8414ZM81.0345 385L79.1025 380.296H69.7225L67.9025 385H63.7025L71.9625 364.56H76.8625L85.5145 385H81.0345ZM71.0945 376.796H77.6745L74.3705 368.704H74.2305L71.0945 376.796Z" />
                            <?php if($state_link_WA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-or">
                            <?php if($state_link_OR) { ?><a href="<?php echo $state_link_OR; ?>" class="state-link" data-selected-state="OR" aria-label="Visit the Oregon Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2.0625 438C2.0625 435.791 3.85336 434 6.0625 434H114.062C116.272 434 118.062 435.791 118.062 438V530H2.0625V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2.0625 438C2.0625 435.791 3.85336 434 6.0625 434H114.062C116.272 434 118.062 435.791 118.062 438V530H2.0625V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M101.6,442.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V442.3z M93.7,446l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7H94.4l-0.7-0.7V446 z"/>
                                <path class="state-abbreviation" d="M49.1904 493.336C42.6384 493.336 38.2704 489.052 38.2704 482.78C38.2704 476.508 42.6384 472.224 49.1904 472.224C55.7704 472.224 60.1384 476.508 60.1384 482.78C60.1384 489.052 55.7704 493.336 49.1904 493.336ZM42.6104 482.78C42.6104 486.868 45.0744 489.696 49.1904 489.696C53.3344 489.696 55.7984 486.868 55.7984 482.78C55.7984 478.692 53.3344 475.864 49.1904 475.864C45.0744 475.864 42.6104 478.692 42.6104 482.78ZM77.3357 493C76.6637 492.272 76.4117 490.172 76.2157 488.492C75.9637 486.252 75.3197 485.524 73.1077 485.524H67.7877V493H63.5877V472.56H74.5917C79.0437 472.56 81.3117 474.772 81.3117 478.44C81.3117 482.528 78.5397 483.592 76.4397 483.732V483.816C78.3717 483.984 79.9117 484.74 80.4437 487.792C80.8917 490.34 81.1997 492.104 82.1797 493H77.3357ZM67.7877 481.968H72.9677C74.5357 481.968 77.0557 481.632 77.0557 479.028C77.0557 477.292 76.0197 476.172 73.6117 476.172H67.7877V481.968Z" />
                            <?php if($state_link_OR) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-ca">
                            <?php if($state_link_CA) { ?><a href="<?php echo $state_link_CA; ?>" class="state-link" data-selected-state="CA" aria-label="Visit the California Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2.0625 546C2.0625 543.791 3.85336 542 6.0625 542H114.062C116.272 542 118.062 543.791 118.062 546V638H2.0625V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M2.0625 546C2.0625 543.791 3.85336 542 6.0625 542H114.062C116.272 542 118.062 543.791 118.062 546V638H2.0625V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M101.6,550.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V550.3z M93.7,554l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7H94.4l-0.7-0.7V554 z"/>
                                <path class="state-abbreviation" d="M59.9397 592.292C59.1557 597.528 55.6557 601.336 49.2717 601.336C42.7197 601.336 38.9677 596.968 38.9677 591.06C38.9677 584.48 43.1117 580.224 49.5517 580.224C55.1517 580.224 58.5957 583.556 59.5757 588.12L55.6557 589.1C54.8717 585.852 52.7437 583.864 49.6077 583.864C45.6877 583.864 43.3077 586.552 43.3077 590.892C43.3077 595.148 45.5477 597.696 49.4957 597.696C53.0517 597.696 55.4037 595.4 55.9077 591.788L59.9397 592.292ZM77.7122 601L75.7802 596.296H66.4002L64.5802 601H60.3802L68.6402 580.56H73.5402L82.1922 601H77.7122ZM67.7722 592.796H74.3522L71.0482 584.704H70.9082L67.7722 592.796Z" />
                            <?php if($state_link_CA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-hi">
                            <?php if($state_link_HI) { ?><a href="<?php echo $state_link_HI; ?>" class="state-link" data-selected-state="HI" aria-label="Visit the Hawai'i Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M2.0625 762C2.0625 759.791 3.85336 758 6.0625 758H114.062C116.272 758 118.062 759.791 118.062 762V854H2.0625V762Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>    
                                <path class="state-outline" d="M2.0625 762C2.0625 759.791 3.85336 758 6.0625 758H114.062C116.272 758 118.062 759.791 118.062 762V854H2.0625V762Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M101.6,766.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V766.3z M93.7,770l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7H94.4l-0.7-0.7V770= z"/>
                                <path class="state-abbreviation" d="M61.022 817V808.432H50.438V817H46.238V796.56H50.438V804.82H61.022V796.56H65.222V817H61.022ZM69.699 817V796.56H73.899V817H69.699Z" />
                            <?php if($state_link_HI) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-id">
                            <?php if($state_link_ID) { ?><a href="<?php echo $state_link_ID; ?>" class="state-link" data-selected-state="ID" aria-label="Visit the Idaho Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M130.062 330C130.062 327.791 131.853 326 134.062 326H242.062C244.272 326 246.062 327.791 246.062 330V422H130.062V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M130.062 330C130.062 327.791 131.853 326 134.062 326H242.062C244.272 326 246.062 327.791 246.062 330V422H130.062V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M229.4,334.3h7.3l0.7,0.7v7.3 H236v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V334.3z M221.5,337.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V337.9z"/>
                                <path class="state-abbreviation" d="M174.607 385V364.56H178.807V385H174.607ZM183.275 385V364.56H191.535C198.115 364.56 202.567 367.976 202.567 374.668C202.567 381.164 198.199 385 191.535 385H183.275ZM187.475 381.388H191.227C194.867 381.388 198.227 379.54 198.227 374.78C198.227 369.656 194.867 368.172 191.227 368.172H187.475V381.388Z" />
                            <?php if($state_link_ID) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-nv">
                            <?php if($state_link_NV) { ?><a href="<?php echo $state_link_NV; ?>" class="state-link" data-selected-state="NV" aria-label="Visit the Nevada Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M130.062 438C130.062 435.791 131.853 434 134.062 434H242.062C244.272 434 246.062 435.791 246.062 438V530H130.062V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M130.062 438C130.062 435.791 131.853 434 134.062 434H242.062C244.272 434 246.062 435.791 246.062 438V530H130.062V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M229.4,442.3h7.3l0.7,0.7v7.3 H236v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V442.3z M221.5,445.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V445.9z"/>
                                <path class="state-abbreviation" d="M182.581 472.56H186.585V493H181.321L173.005 479.728L171.885 477.768V493H167.881V472.56H173.145L181.433 485.916L182.581 487.96V472.56ZM193.476 472.56L199.86 488.716H199.972L206.076 472.56H210.304L202.268 493H197.368L188.996 472.56H193.476Z" />
                            <?php if($state_link_NV) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-ut">
                            <?php if($state_link_UT) { ?><a href="<?php echo $state_link_UT; ?>" class="state-link" data-selected-state="UT" aria-label="Visit the Utah Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M130.062 546C130.062 543.791 131.853 542 134.062 542H242.062C244.272 542 246.062 543.791 246.062 546V638H130.062V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M130.062 546C130.062 543.791 131.853 542 134.062 542H242.062C244.272 542 246.062 543.791 246.062 546V638H130.062V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M229.4,550.3h7.3l0.7,0.7v7.3 H236v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V550.3z M221.5,553.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V553.9z"/>
                                <path class="state-abbreviation" d="M178.068 601.336C173.392 601.336 168.968 599.46 168.968 592.096V580.56H173.168V592.012C173.168 596.212 175.24 597.696 178.18 597.696C180.952 597.696 182.912 596.212 182.912 592.012V580.56H187.112V592.096C187.112 599.404 182.856 601.336 178.068 601.336ZM196.998 601V584.172H189.466V580.56H208.73V584.172H201.198V601H196.998Z" />
                            <?php if($state_link_UT) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-az">
                            <?php if($state_link_AZ) { ?><a href="<?php echo $state_link_AZ; ?>" class="state-link" data-selected-state="AZ" aria-label="Visit the Arizona Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M130.062 654C130.062 651.791 131.853 650 134.062 650H242.062C244.272 650 246.062 651.791 246.062 654V746H130.062V654Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M130.062 654C130.062 651.791 131.853 650 134.062 650H242.062C244.272 650 246.062 651.791 246.062 654V746H130.062V654Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M229.4,658.3h7.3l0.7,0.7v7.3 H236v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V658.3z M221.5,661.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V661.9z"/>
                                <path class="state-abbreviation" d="M184.835 709L182.903 704.296H173.523L171.703 709H167.503L175.763 688.56H180.663L189.315 709H184.835ZM174.895 700.796H181.475L178.171 692.704H178.031L174.895 700.796ZM207.712 688.56V691.948L195.868 705.388H207.712V709H190.52V705.724L202.532 692.172H190.772V688.56H207.712Z" />
                            <?php if($state_link_AZ) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-mt">
                            <?php if($state_link_MT) { ?><a href="<?php echo $state_link_MT; ?>" class="state-link" data-selected-state="MT" aria-label="Visit the Montana Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M258.062 330C258.062 327.791 259.853 326 262.062 326H370.062C372.272 326 374.062 327.791 374.062 330V422H258.062V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M258.062 330C258.062 327.791 259.853 326 262.062 326H370.062C372.272 326 374.062 327.791 374.062 330V422H258.062V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M357.4,334.3h7.3l0.7,0.7v7.3 H364v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V334.3z M349.5,337.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V337.9z"/>
                                <path class="state-abbreviation" d="M317.844 364.56V385H313.644V369.096H313.504L307.988 385H304.04L298.356 369.096H298.244V385H294.24V364.56H300.876L306.112 379.96H306.196L311.376 364.56H317.844ZM328.006 385V368.172H320.474V364.56H339.738V368.172H332.206V385H328.006Z" />
                            <?php if($state_link_MT) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-wy">
                            <?php if($state_link_WY) { ?><a href="<?php echo $state_link_WY; ?>" class="state-link" data-selected-state="WY" aria-label="Visit the Wyoming Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M258.062 438C258.062 435.791 259.853 434 262.062 434H370.062C372.272 434 374.062 435.791 374.062 438V530H258.062V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M258.062 438C258.062 435.791 259.853 434 262.062 434H370.062C372.272 434 374.062 435.791 374.062 438V530H258.062V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M357.4,442.3h7.3l0.7,0.7v7.3 H364v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V442.3z M349.5,445.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V445.9z"/>
                                <path class="state-abbreviation" d="M320.347 472.56L315.335 493H309.735L305.899 477.516H305.731L301.867 493H296.323L291.283 472.56H295.623L299.319 488.38H299.459L303.407 472.56H308.419L312.395 488.38H312.563L316.231 472.56H320.347ZM341.082 472.56L333.018 485.552V493H328.818V485.496L320.754 472.56H325.514L331.086 481.828L336.658 472.56H341.082Z" />
                            <?php if($state_link_WY) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-co">
                            <?php if($state_link_CO) { ?><a href="<?php echo $state_link_CO; ?>" class="state-link" data-selected-state="CO" aria-label="Visit the Colorado Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M258.062 546C258.062 543.791 259.853 542 262.062 542H370.062C372.272 542 374.062 543.791 374.062 546V638H258.062V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M258.062 546C258.062 543.791 259.853 542 262.062 542H370.062C372.272 542 374.062 543.791 374.062 546V638H258.062V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M357.4,550.3h7.3l0.7,0.7v7.3 H364v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V550.3z M349.5,553.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V553.9z"/>
                                <path class="state-abbreviation" d="M314.477 592.292C313.693 597.528 310.193 601.336 303.809 601.336C297.257 601.336 293.505 596.968 293.505 591.06C293.505 584.48 297.649 580.224 304.089 580.224C309.689 580.224 313.133 583.556 314.113 588.12L310.193 589.1C309.409 585.852 307.281 583.864 304.145 583.864C300.225 583.864 297.845 586.552 297.845 590.892C297.845 595.148 300.085 597.696 304.033 597.696C307.589 597.696 309.941 595.4 310.445 591.788L314.477 592.292ZM327.667 601.336C321.115 601.336 316.747 597.052 316.747 590.78C316.747 584.508 321.115 580.224 327.667 580.224C334.247 580.224 338.615 584.508 338.615 590.78C338.615 597.052 334.247 601.336 327.667 601.336ZM321.087 590.78C321.087 594.868 323.551 597.696 327.667 597.696C331.811 597.696 334.275 594.868 334.275 590.78C334.275 586.692 331.811 583.864 327.667 583.864C323.551 583.864 321.087 586.692 321.087 590.78Z" />
                            <?php if($state_link_CO) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-nm">
                            <?php if($state_link_NM) { ?><a href="<?php echo $state_link_NM; ?>" class="state-link" data-selected-state="NM" aria-label="Visit the New Mexico Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M258.062 654C258.062 651.791 259.853 650 262.062 650H370.062C372.272 650 374.062 651.791 374.062 654V746H258.062V654Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M258.062 654C258.062 651.791 259.853 650 262.062 650H370.062C372.272 650 374.062 651.791 374.062 654V746H258.062V654Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M357.4,658.3h7.3l0.7,0.7v7.3 H364v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V658.3z M349.5,661.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V661.9z"/>
                                <path class="state-abbreviation" d="M307.368 688.56H311.372V709H306.108L297.792 695.728L296.672 693.768V709H292.668V688.56H297.932L306.22 701.916L307.368 703.96V688.56ZM339.459 688.56V709H335.259V693.096H335.119L329.603 709H325.655L319.971 693.096H319.859V709H315.855V688.56H322.491L327.727 703.96H327.811L332.991 688.56H339.459Z" />
                            <?php if($state_link_NM) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-nd">
                            <?php if($state_link_ND) { ?><a href="<?php echo $state_link_ND; ?>" class="state-link" data-selected-state="ND" aria-label="Visit the North Dakota Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M386.062 330C386.062 327.791 387.853 326 390.062 326H498.062C500.272 326 502.062 327.791 502.062 330V422H386.062V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M386.062 330C386.062 327.791 387.853 326 390.062 326H498.062C500.272 326 502.062 327.791 502.062 330V422H386.062V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M485.4,334.3h7.3l0.7,0.7v7.3 H492v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V334.3z M477.5,337.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V337.9z"/>
                                <path class="state-abbreviation" d="M438.047 364.56H442.051V385H436.787L428.471 371.728L427.351 369.768V385H423.347V364.56H428.611L436.899 377.916L438.047 379.96V364.56ZM446.535 385V364.56H454.795C461.375 364.56 465.827 367.976 465.827 374.668C465.827 381.164 461.459 385 454.795 385H446.535ZM450.735 381.388H454.487C458.127 381.388 461.487 379.54 461.487 374.78C461.487 369.656 458.127 368.172 454.487 368.172H450.735V381.388Z" />
                            <?php if($state_link_ND) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-sd">
                            <?php if($state_link_SD) { ?><a href="<?php echo $state_link_SD; ?>" class="state-link" data-selected-state="SD" aria-label="Visit the South Dakota Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M386.062 438C386.062 435.791 387.853 434 390.062 434H498.062C500.272 434 502.062 435.791 502.062 438V530H386.062V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M386.062 438C386.062 435.791 387.853 434 390.062 434H498.062C500.272 434 502.062 435.791 502.062 438V530H386.062V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M485.4,442.3h7.3l0.7,0.7v7.3 H492v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V442.3z M477.5,445.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V445.9z"/>
                                <path class="state-abbreviation" d="M426.696 485.86C427.844 488.52 430.14 489.78 433.192 489.78C436.468 489.78 437.728 488.38 437.728 486.98C437.728 485.58 436.636 484.908 434.62 484.628C433.36 484.46 431.652 484.32 430.308 484.152C426.948 483.732 424.232 482.248 424.232 478.608C424.232 474.772 427.676 472.224 432.66 472.224C436.048 472.224 439.156 473.26 441.228 476.396L438.176 478.776C436.664 476.508 434.788 475.752 432.632 475.752C430.056 475.752 428.516 476.956 428.516 478.356C428.516 479.644 429.412 480.204 431.4 480.428C432.576 480.568 433.976 480.708 435.404 480.876C438.428 481.24 442.012 482.472 442.012 486.672C442.012 490.844 438.4 493.336 433.108 493.336C428.46 493.336 424.988 491.544 423.168 487.512L426.696 485.86ZM445.455 493V472.56H453.715C460.295 472.56 464.747 475.976 464.747 482.668C464.747 489.164 460.379 493 453.715 493H445.455ZM449.655 489.388H453.407C457.047 489.388 460.407 487.54 460.407 482.78C460.407 477.656 457.047 476.172 453.407 476.172H449.655V489.388Z" />
                            <?php if($state_link_SD) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-ne">
                            <?php if($state_link_NE) { ?><a href="<?php echo $state_link_NE; ?>" class="state-link" data-selected-state="NE" aria-label="Visit the Nebraska Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M386.062 546C386.062 543.791 387.853 542 390.062 542H498.062C500.272 542 502.062 543.791 502.062 546V638H386.062V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M386.062 546C386.062 543.791 387.853 542 390.062 542H498.062C500.272 542 502.062 543.791 502.062 546V638H386.062V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M485.4,550.3h7.3l0.7,0.7v7.3 H492v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V550.3z M477.5,553.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V553.9z"/>
                                <path class="state-abbreviation" d="M439.92 580.56H443.924V601H438.66L430.344 587.728L429.224 585.768V601H425.22V580.56H430.484L438.772 593.916L439.92 595.96V580.56ZM448.408 601V580.56H463.92V584.172H452.608V588.876H461.708V592.488H452.608V597.388H464.256V601H448.408Z" />
                            <?php if($state_link_NE) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-ks">
                            <?php if($state_link_KS) { ?><a href="<?php echo $state_link_KS; ?>" class="state-link" data-selected-state="KS" aria-label="Visit the Kansas Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M386.062 654C386.062 651.791 387.853 650 390.062 650H498.062C500.272 650 502.062 651.791 502.062 654V746H386.062V654Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M386.062 654C386.062 651.791 387.853 650 390.062 650H498.062C500.272 650 502.062 651.791 502.062 654V746H386.062V654Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M485.4,658.3h7.3l0.7,0.7v7.3 H492v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V658.3z M477.5,661.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V661.9z"/>
                                <path class="state-abbreviation" d="M425.59 688.56H429.79V697.688L439.03 688.56H444.098L435.53 696.96L444.546 709H439.338L432.534 699.872L429.79 702.56V709H425.59V688.56ZM448.256 701.86C449.404 704.52 451.7 705.78 454.752 705.78C458.028 705.78 459.288 704.38 459.288 702.98C459.288 701.58 458.196 700.908 456.18 700.628C454.92 700.46 453.212 700.32 451.868 700.152C448.508 699.732 445.792 698.248 445.792 694.608C445.792 690.772 449.236 688.224 454.22 688.224C457.608 688.224 460.716 689.26 462.788 692.396L459.736 694.776C458.224 692.508 456.348 691.752 454.192 691.752C451.616 691.752 450.076 692.956 450.076 694.356C450.076 695.644 450.972 696.204 452.96 696.428C454.136 696.568 455.536 696.708 456.964 696.876C459.988 697.24 463.572 698.472 463.572 702.672C463.572 706.844 459.96 709.336 454.668 709.336C450.02 709.336 446.548 707.544 444.728 703.512L448.256 701.86Z" />
                            <?php if($state_link_KS) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-ok">
                            <?php if($state_link_OK) { ?><a href="<?php echo $state_link_OK; ?>" class="state-link" data-selected-state="OK" aria-label="Visit the Oklahoma Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M386.062 762C386.062 759.791 387.853 758 390.062 758H498.062C500.272 758 502.062 759.791 502.062 762V854H386.062V762Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M386.062 762C386.062 759.791 387.853 758 390.062 758H498.062C500.272 758 502.062 759.791 502.062 762V854H386.062V762Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M485.4,766.3h7.3l0.7,0.7v7.3 H492v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V766.3z M477.5,769.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V769.9z"/>
                                <path class="state-abbreviation" d="M433.423 817.336C426.871 817.336 422.503 813.052 422.503 806.78C422.503 800.508 426.871 796.224 433.423 796.224C440.003 796.224 444.371 800.508 444.371 806.78C444.371 813.052 440.003 817.336 433.423 817.336ZM426.843 806.78C426.843 810.868 429.307 813.696 433.423 813.696C437.567 813.696 440.031 810.868 440.031 806.78C440.031 802.692 437.567 799.864 433.423 799.864C429.307 799.864 426.843 802.692 426.843 806.78ZM447.82 796.56H452.02V805.688L461.26 796.56H466.328L457.76 804.96L466.776 817H461.568L454.764 807.872L452.02 810.56V817H447.82V796.56Z" />
                            <?php if($state_link_OK) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-tx">
                            <?php if($state_link_TX) { ?><a href="<?php echo $state_link_TX; ?>" class="state-link" data-selected-state="TX" aria-label="Visit the Texas Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M386.062 870C386.062 867.791 387.853 866 390.062 866H498.062C500.272 866 502.062 867.791 502.062 870V962H386.062V870Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M386.062 870C386.062 867.791 387.853 866 390.062 866H498.062C500.272 866 502.062 867.791 502.062 870V962H386.062V870Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M485.4,874.3h7.3l0.7,0.7v7.3 H492v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V874.3z M477.5,877.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V877.9z"/>
                                <path class="state-abbreviation" d="M431.602 925V908.172H424.07V904.56H443.334V908.172H435.802V925H431.602ZM464.245 925H459.317L453.885 917.468L448.481 925H443.917L451.561 914.64L444.309 904.56H449.237L454.305 911.812L459.401 904.56H463.965L456.629 914.528L464.245 925Z" />
                            <?php if($state_link_TX) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-mn">
                            <?php if($state_link_MN) { ?><a href="<?php echo $state_link_MN; ?>" class="state-link" data-selected-state="MN" aria-label="Visit the Minnesota Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M514.062 330C514.062 327.791 515.853 326 518.062 326H626.062C628.272 326 630.062 327.791 630.062 330V422H514.062V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M514.062 330C514.062 327.791 515.853 326 518.062 326H626.062C628.272 326 630.062 327.791 630.062 330V422H514.062V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M613.4,334.3h7.3l0.7,0.7v7.3 H620v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V334.3z M605.5,337.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V337.9z"/>
                                <path class="state-abbreviation" d="M572.272 364.56V385H568.072V369.096H567.932L562.416 385H558.468L552.784 369.096H552.672V385H548.668V364.56H555.304L560.54 379.96H560.624L565.804 364.56H572.272ZM591.45 364.56H595.454V385H590.19L581.874 371.728L580.754 369.768V385H576.75V364.56H582.014L590.302 377.916L591.45 379.96V364.56Z" />
                            <?php if($state_link_MN) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-ia">
                            <?php if($state_link_IA) { ?><a href="<?php echo $state_link_IA; ?>" class="state-link" data-selected-state="IA" aria-label="Visit the Iowa Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M514.062 438C514.062 435.791 515.853 434 518.062 434H626.062C628.272 434 630.062 435.791 630.062 438V530H514.062V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M514.062 438C514.062 435.791 515.853 434 518.062 434H626.062C628.272 434 630.062 435.791 630.062 438V530H514.062V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M613.4,442.3h7.3l0.7,0.7v7.3 H620v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V442.3z M605.5,445.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V445.9z"/>
                                <path class="state-abbreviation" d="M558.908 493V472.56H563.108V493H558.908ZM582.808 493L580.876 488.296H571.496L569.676 493H565.476L573.736 472.56H578.636L587.288 493H582.808ZM572.868 484.796H579.448L576.144 476.704H576.004L572.868 484.796Z" />
                            <?php if($state_link_IA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-mo">
                            <?php if($state_link_MO) { ?><a href="<?php echo $state_link_MO; ?>" class="state-link" data-selected-state="MO" aria-label="Visit the Missouri Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M514.062 546C514.062 543.791 515.853 542 518.062 542H626.062C628.272 542 630.062 543.791 630.062 546V638H514.062V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M514.062 546C514.062 543.791 515.853 542 518.062 542H626.062C628.272 542 630.062 543.791 630.062 546V638H514.062V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M613.4,550.3h7.3l0.7,0.7v7.3 H620v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V550.3z M605.5,553.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V553.9z"/>
                                <path class="state-abbreviation" d="M571.725 580.56V601H567.525V585.096H567.385L561.869 601H557.921L552.237 585.096H552.125V601H548.121V580.56H554.757L559.993 595.96H560.077L565.257 580.56H571.725ZM586.087 601.336C579.535 601.336 575.167 597.052 575.167 590.78C575.167 584.508 579.535 580.224 586.087 580.224C592.667 580.224 597.035 584.508 597.035 590.78C597.035 597.052 592.667 601.336 586.087 601.336ZM579.507 590.78C579.507 594.868 581.971 597.696 586.087 597.696C590.231 597.696 592.695 594.868 592.695 590.78C592.695 586.692 590.231 583.864 586.087 583.864C581.971 583.864 579.507 586.692 579.507 590.78Z" />
                            <?php if($state_link_MO) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-ar">
                            <?php if($state_link_AR) { ?><a href="<?php echo $state_link_AR; ?>" class="state-link" data-selected-state="AR" aria-label="Visit the Arkansas Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M514.062 654C514.062 651.791 515.853 650 518.062 650H626.062C628.272 650 630.062 651.791 630.062 654V746H514.062V654Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M514.062 654C514.062 651.791 515.853 650 518.062 650H626.062C628.272 650 630.062 651.791 630.062 654V746H514.062V654Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M613.4,658.3h7.3l0.7,0.7v7.3 H620v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V658.3z M605.5,661.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V661.9z"/>
                                <path class="state-abbreviation" d="M567.618 709L565.686 704.296H556.306L554.486 709H550.286L558.546 688.56H563.446L572.098 709H567.618ZM557.678 700.796H564.258L560.954 692.704H560.814L557.678 700.796ZM588.256 709C587.584 708.272 587.332 706.172 587.136 704.492C586.884 702.252 586.24 701.524 584.028 701.524H578.708V709H574.508V688.56H585.512C589.964 688.56 592.232 690.772 592.232 694.44C592.232 698.528 589.46 699.592 587.36 699.732V699.816C589.292 699.984 590.832 700.74 591.364 703.792C591.812 706.34 592.12 708.104 593.1 709H588.256ZM578.708 697.968H583.888C585.456 697.968 587.976 697.632 587.976 695.028C587.976 693.292 586.94 692.172 584.532 692.172H578.708V697.968Z" />
                            <?php if($state_link_AR) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-la">
                            <?php if($state_link_LA) { ?><a href="<?php echo $state_link_LA; ?>" class="state-link" data-selected-state="LA" aria-label="Visit the Louisiana Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M514.062 762C514.062 759.791 515.853 758 518.062 758H626.062C628.272 758 630.062 759.791 630.062 762V854H514.062V762Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M514.062 762C514.062 759.791 515.853 758 518.062 758H626.062C628.272 758 630.062 759.791 630.062 762V854H514.062V762Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M613.4,766.3h7.3l0.7,0.7v7.3 H620v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V766.3z M605.5,769.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7V769.9z"/>
                                <path class="state-abbreviation" d="M554.355 817V796.56H558.555V813.388H569.419V817H554.355ZM587.361 817L585.429 812.296H576.049L574.229 817H570.029L578.289 796.56H583.189L591.841 817H587.361ZM577.421 808.796H584.001L580.697 800.704H580.557L577.421 808.796Z" />
                            <?php if($state_link_LA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-wi">
                            <?php if($state_link_WI) { ?><a href="<?php echo $state_link_WI; ?>" class="state-link" data-selected-state="WI" aria-label="Visit the Wisconsin Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M642.062 222C642.062 219.791 643.853 218 646.062 218H754.062C756.272 218 758.062 219.791 758.062 222V314H642.062V222Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M642.062 222C642.062 219.791 643.853 218 646.062 218H754.062C756.272 218 758.062 219.791 758.062 222V314H642.062V222Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M741.4,226.3h7.3l0.7,0.7v7.3 H748v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V226.3z M733.5,229.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V229.9z"/>
                                <path class="state-abbreviation" d="M710.267 256.56L705.255 277H699.655L695.819 261.516H695.651L691.787 277H686.243L681.203 256.56H685.543L689.239 272.38H689.379L693.327 256.56H698.339L702.315 272.38H702.483L706.151 256.56H710.267ZM712.83 277V256.56H717.03V277H712.83Z" />
                            <?php if($state_link_WI) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-il">
                            <?php if($state_link_IL) { ?><a href="<?php echo $state_link_IL; ?>" class="state-link" data-selected-state="IL" aria-label="Visit the Illinois Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M642.062 330C642.062 327.791 643.853 326 646.062 326H754.062C756.272 326 758.062 327.791 758.062 330V422H642.062V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M642.062 330C642.062 327.791 643.853 326 646.062 326H754.062C756.272 326 758.062 327.791 758.062 330V422H642.062V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M741.4,334.3h7.3l0.7,0.7v7.3 H748v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V334.3z M733.5,337.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V337.9z"/>
                                <path class="state-abbreviation" d="M689.082 385V364.56H693.282V385H689.082ZM697.75 385V364.56H701.95V381.388H712.814V385H697.75Z" />
                            <?php if($state_link_IL) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-in">
                            <?php if($state_link_IN) { ?><a href="<?php echo $state_link_IN; ?>" class="state-link" data-selected-state="IN" aria-label="Visit the Indiana Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M642.062 438C642.062 435.791 643.853 434 646.062 434H754.062C756.272 434 758.062 435.791 758.062 438V530H642.062V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M642.062 438C642.062 435.791 643.853 434 646.062 434H754.062C756.272 434 758.062 435.791 758.062 438V530H642.062V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M741.4,442.3h7.3l0.7,0.7v7.3 H748v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V442.3z M733.5,445.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V445.9z"/>
                                <path class="state-abbreviation" d="M686.375 493V472.56H690.575V493H686.375ZM709.743 472.56H713.747V493H708.483L700.167 479.728L699.047 477.768V493H695.043V472.56H700.307L708.595 485.916L709.743 487.96V472.56Z" />
                            <?php if($state_link_IN) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-ky">
                            <?php if($state_link_KY) { ?><a href="<?php echo $state_link_KY; ?>" class="state-link" data-selected-state="KY" aria-label="Visit the Kentucky Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M642.062 546C642.062 543.791 643.853 542 646.062 542H754.062C756.272 542 758.062 543.791 758.062 546V638H642.062V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M642.062 546C642.062 543.791 643.853 542 646.062 542H754.062C756.272 542 758.062 543.791 758.062 546V638H642.062V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M741.4,550.3h7.3l0.7,0.7v7.3 H748v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V550.3z M733.5,553.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V553.9z"/>
                                <path class="state-abbreviation" d="M681.426 580.56H685.626V589.688L694.866 580.56H699.934L691.366 588.96L700.382 601H695.174L688.37 591.872L685.626 594.56V601H681.426V580.56ZM720.844 580.56L712.78 593.552V601H708.58V593.496L700.516 580.56H705.276L710.848 589.828L716.42 580.56H720.844Z" />
                            <?php if($state_link_KY) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-tn">
                            <?php if($state_link_TN) { ?><a href="<?php echo $state_link_TN; ?>" class="state-link" data-selected-state="TN" aria-label="Visit the Tennessee Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M642.062 654C642.062 651.791 643.853 650 646.062 650H754.062C756.272 650 758.062 651.791 758.062 654V746H642.062V654Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M642.062 654C642.062 651.791 643.853 650 646.062 650H754.062C756.272 650 758.062 651.791 758.062 654V746H642.062V654Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M741.4,658.3h7.3l0.7,0.7v7.3 H748v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V658.3z M733.5,661.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V661.9z"/>
                                <path class="state-abbreviation" d="M686.371 709V692.172H678.839V688.56H698.103V692.172H690.571V709H686.371ZM715.43 688.56H719.434V709H714.17L705.854 695.728L704.734 693.768V709H700.73V688.56H705.994L714.282 701.916L715.43 703.96V688.56Z" />
                            <?php if($state_link_TN) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-ms">
                            <?php if($state_link_MS) { ?><a href="<?php echo $state_link_MS; ?>" class="state-link" data-selected-state="MS" aria-label="Visit the Mississippi Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M642.062 762C642.062 759.791 643.853 758 646.062 758H754.062C756.272 758 758.062 759.791 758.062 762V854H642.062V762Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M642.062 762C642.062 759.791 643.853 758 646.062 758H754.062C756.272 758 758.062 759.791 758.062 762V854H642.062V762Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M741.4,766.3h7.3l0.7,0.7v7.3 H748v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V766.3z M733.5,769.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V769.9z"/>
                                <path class="state-abbreviation" d="M701.352 796.56V817H697.152V801.096H697.012L691.496 817H687.548L681.864 801.096H681.752V817H677.748V796.56H684.384L689.62 811.96H689.704L694.884 796.56H701.352ZM708.098 809.86C709.246 812.52 711.542 813.78 714.594 813.78C717.87 813.78 719.13 812.38 719.13 810.98C719.13 809.58 718.038 808.908 716.022 808.628C714.762 808.46 713.054 808.32 711.71 808.152C708.35 807.732 705.634 806.248 705.634 802.608C705.634 798.772 709.078 796.224 714.062 796.224C717.45 796.224 720.558 797.26 722.63 800.396L719.578 802.776C718.066 800.508 716.19 799.752 714.034 799.752C711.458 799.752 709.918 800.956 709.918 802.356C709.918 803.644 710.814 804.204 712.802 804.428C713.978 804.568 715.378 804.708 716.806 804.876C719.83 805.24 723.414 806.472 723.414 810.672C723.414 814.844 719.802 817.336 714.51 817.336C709.862 817.336 706.39 815.544 704.57 811.512L708.098 809.86Z" />
                            <?php if($state_link_MS) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-mi">
                            <?php if($state_link_MI) { ?><a href="<?php echo $state_link_MI; ?>" class="state-link" data-selected-state="MI" aria-label="Visit the Michigan Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M770.062 330C770.062 327.791 771.853 326 774.062 326H882.062C884.272 326 886.062 327.791 886.062 330V422H770.062V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M770.062 330C770.062 327.791 771.853 326 774.062 326H882.062C884.272 326 886.062 327.791 886.062 330V422H770.062V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M869.4,334.3h7.3l0.7,0.7v7.3 H876v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V334.3z M861.5,337.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V337.9z"/>
                                <path class="state-abbreviation" d="M835.532 364.56V385H831.332V369.096H831.192L825.676 385H821.728L816.044 369.096H815.932V385H811.928V364.56H818.564L823.8 379.96H823.884L829.064 364.56H835.532ZM840.01 385V364.56H844.21V385H840.01Z" />
                            <?php if($state_link_MI) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-oh">
                            <?php if($state_link_OH) { ?><a href="<?php echo $state_link_OH; ?>" class="state-link" data-selected-state="OH" aria-label="Visit the Ohio Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M770.062 438C770.062 435.791 771.853 434 774.062 434H882.062C884.272 434 886.062 435.791 886.062 438V530H770.062V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M770.062 438C770.062 435.791 771.853 434 774.062 434H882.062C884.272 434 886.062 435.791 886.062 438V530H770.062V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M869.4,442.3h7.3l0.7,0.7v7.3 H876v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V442.3z M861.5,445.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V445.9z"/>
                                <path class="state-abbreviation" d="M816.315 493.336C809.763 493.336 805.395 489.052 805.395 482.78C805.395 476.508 809.763 472.224 816.315 472.224C822.895 472.224 827.263 476.508 827.263 482.78C827.263 489.052 822.895 493.336 816.315 493.336ZM809.735 482.78C809.735 486.868 812.199 489.696 816.315 489.696C820.459 489.696 822.923 486.868 822.923 482.78C822.923 478.692 820.459 475.864 816.315 475.864C812.199 475.864 809.735 478.692 809.735 482.78ZM845.497 493V484.432H834.913V493H830.713V472.56H834.913V480.82H845.497V472.56H849.697V493H845.497Z" />
                            <?php if($state_link_OH) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-wv">
                            <?php if($state_link_WV) { ?><a href="<?php echo $state_link_WV; ?>" class="state-link" data-selected-state="WV" aria-label="Visit the West Virginia Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M770.062 546C770.062 543.791 771.853 542 774.062 542H882.062C884.272 542 886.062 543.791 886.062 546V638H770.062V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M770.062 546C770.062 543.791 771.853 542 774.062 542H882.062C884.272 542 886.062 543.791 886.062 546V638H770.062V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M869.4,550.3h7.3l0.7,0.7v7.3 H876v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V550.3z M861.5,553.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V553.9z"/>
                                <path class="state-abbreviation" d="M831.773 580.56L826.761 601H821.161L817.325 585.516H817.157L813.293 601H807.749L802.709 580.56H807.049L810.745 596.38H810.885L814.833 580.56H819.845L823.821 596.38H823.989L827.657 580.56H831.773ZM836.744 580.56L843.128 596.716H843.24L849.344 580.56H853.572L845.536 601H840.636L832.264 580.56H836.744Z" />
                            <?php if($state_link_WV) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-nc">
                            <?php if($state_link_NC) { ?><a href="<?php echo $state_link_NC; ?>" class="state-link" data-selected-state="NC" aria-label="Visit the North Carolina Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M770.062 654C770.062 651.791 771.853 650 774.062 650H882.062C884.272 650 886.062 651.791 886.062 654V746H770.062V654Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M770.062 654C770.062 651.791 771.853 650 774.062 650H882.062C884.272 650 886.062 651.791 886.062 654V746H770.062V654Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M869.4,658.3h7.3l0.7,0.7v7.3 H876v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V658.3z M861.5,661.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V661.9z"/>
                                <path class="state-abbreviation" d="M821.788 688.56H825.792V709H820.528L812.212 695.728L811.092 693.768V709H807.088V688.56H812.352L820.64 701.916L821.788 703.96V688.56ZM850.211 700.292C849.427 705.528 845.927 709.336 839.543 709.336C832.991 709.336 829.239 704.968 829.239 699.06C829.239 692.48 833.383 688.224 839.823 688.224C845.423 688.224 848.867 691.556 849.847 696.12L845.927 697.1C845.143 693.852 843.015 691.864 839.879 691.864C835.959 691.864 833.579 694.552 833.579 698.892C833.579 703.148 835.819 705.696 839.767 705.696C843.323 705.696 845.675 703.4 846.179 699.788L850.211 700.292Z" />
                            <?php if($state_link_NC) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-al">
                            <?php if($state_link_AL) { ?><a href="<?php echo $state_link_AL; ?>" class="state-link" data-selected-state="AL" aria-label="Visit the Alabama Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M770.062 762C770.062 759.791 771.853 758 774.062 758H882.062C884.272 758 886.062 759.791 886.062 762V854H770.062V762Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M770.062 762C770.062 759.791 771.853 758 774.062 758H882.062C884.272 758 886.062 759.791 886.062 762V854H770.062V762Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M869.4,766.3h7.3l0.7,0.7v7.3 H876v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V766.3z M861.5,769.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V769.9z"/>
                                <path class="state-abbreviation" d="M825.587 817L823.655 812.296H814.275L812.455 817H808.255L816.515 796.56H821.415L830.067 817H825.587ZM815.647 808.796H822.227L818.923 800.704H818.783L815.647 808.796ZM832.476 817V796.56H836.676V813.388H847.54V817H832.476Z" />
                            <?php if($state_link_AL) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-ny">
                            <?php if($state_link_NY) { ?><a href="<?php echo $state_link_NY; ?>" class="state-link" data-selected-state="NY" aria-label="Visit the New York Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M898.062 222C898.062 219.791 899.853 218 902.062 218H1010.06C1012.27 218 1014.06 219.791 1014.06 222V314H898.062V222Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M898.062 222C898.062 219.791 899.853 218 902.062 218H1010.06C1012.27 218 1014.06 219.791 1014.06 222V314H898.062V222Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M997.4,224.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V224.3z M989.5,227.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V227.9z"/>
                                <path class="state-abbreviation" d="M951.155 256.56H955.159V277H949.895L941.579 263.728L940.459 261.768V277H936.455V256.56H941.719L950.007 269.916L951.155 271.96V256.56ZM977.814 256.56L969.75 269.552V277H965.55V269.496L957.486 256.56H962.246L967.818 265.828L973.39 256.56H977.814Z" />
                            <?php if($state_link_NY) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-pa">
                            <?php if($state_link_PA) { ?><a href="<?php echo $state_link_PA; ?>" class="state-link" data-selected-state="PA" aria-label="Visit the Pennsylvania Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M898.062 330C898.062 327.791 899.853 326 902.062 326H1010.06C1012.27 326 1014.06 327.791 1014.06 330V422H898.062V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M898.062 330C898.062 327.791 899.853 326 902.062 326H1010.06C1012.27 326 1014.06 327.791 1014.06 330V422H898.062V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M997.4,334.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V334.3z M989.5,337.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V337.9z"/>
                                <path class="state-abbreviation" d="M937.822 385V364.56H947.37C952.242 364.56 955.35 366.688 955.35 371.14C955.35 375.564 952.27 377.944 947.202 377.944H942.022V385H937.822ZM942.022 374.332H946.95C949.638 374.332 951.122 373.52 951.122 371.308C951.122 368.984 949.61 368.172 947.09 368.172H942.022V374.332ZM971.894 385L969.962 380.296H960.582L958.762 385H954.562L962.822 364.56H967.722L976.374 385H971.894ZM961.954 376.796H968.534L965.23 368.704H965.09L961.954 376.796Z" />
                            <?php if($state_link_PA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-md">
                            <?php if($state_link_MD) { ?><a href="<?php echo $state_link_MD; ?>" class="state-link" data-selected-state="MD" aria-label="Visit the Maryland Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M898.062 438C898.062 435.791 899.853 434 902.062 434H1010.06C1012.27 434 1014.06 435.791 1014.06 438V530H898.062V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M898.062 438C898.062 435.791 899.853 434 902.062 434H1010.06C1012.27 434 1014.06 435.791 1014.06 438V530H898.062V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M997.4,442.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V442.3z M989.5,445.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V445.9z"/>
                                <path class="state-abbreviation" d="M956.504 472.56V493H952.304V477.096H952.164L946.648 493H942.7L937.016 477.096H936.904V493H932.9V472.56H939.536L944.772 487.96H944.856L950.036 472.56H956.504ZM960.982 493V472.56H969.242C975.822 472.56 980.274 475.976 980.274 482.668C980.274 489.164 975.906 493 969.242 493H960.982ZM965.182 489.388H968.934C972.574 489.388 975.934 487.54 975.934 482.78C975.934 477.656 972.574 476.172 968.934 476.172H965.182V489.388Z" />
                            <?php if($state_link_MD) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-va">
                            <?php if($state_link_VA) { ?><a href="<?php echo $state_link_VA; ?>" class="state-link" data-selected-state="VA" aria-label="Visit the Virginia Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M898.062 546C898.062 543.791 899.853 542 902.062 542H1010.06C1012.27 542 1014.06 543.791 1014.06 546V638H898.062V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M898.062 546C898.062 543.791 899.853 542 902.062 542H1010.06C1012.27 542 1014.06 543.791 1014.06 546V638H898.062V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M997.4,550.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V550.3z M989.5,553.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V553.9z"/>
                                <path class="state-abbreviation" d="M939.683 580.56L946.067 596.716H946.179L952.283 580.56H956.511L948.475 601H943.575L935.203 580.56H939.683ZM972.441 601L970.509 596.296H961.129L959.309 601H955.109L963.369 580.56H968.269L976.921 601H972.441ZM962.501 592.796H969.081L965.777 584.704H965.637L962.501 592.796Z" />
                            <?php if($state_link_VA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-sc">
                            <?php if($state_link_SC) { ?><a href="<?php echo $state_link_SC; ?>" class="state-link" data-selected-state="SC" aria-label="Visit the South Carolina Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M898.062 654C898.062 651.791 899.853 650 902.062 650H1010.06C1012.27 650 1014.06 651.791 1014.06 654V746H898.062V654Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M898.062 654C898.062 651.791 899.853 650 902.062 650H1010.06C1012.27 650 1014.06 651.791 1014.06 654V746H898.062V654Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M997.4,658.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V658.3z M989.5,661.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V661.9z"/>
                                <path class="state-abbreviation" d="M938.436 701.86C939.584 704.52 941.88 705.78 944.932 705.78C948.208 705.78 949.468 704.38 949.468 702.98C949.468 701.58 948.376 700.908 946.36 700.628C945.1 700.46 943.392 700.32 942.048 700.152C938.688 699.732 935.972 698.248 935.972 694.608C935.972 690.772 939.416 688.224 944.4 688.224C947.788 688.224 950.896 689.26 952.968 692.396L949.916 694.776C948.404 692.508 946.528 691.752 944.372 691.752C941.796 691.752 940.256 692.956 940.256 694.356C940.256 695.644 941.152 696.204 943.14 696.428C944.316 696.568 945.716 696.708 947.144 696.876C950.168 697.24 953.752 698.472 953.752 702.672C953.752 706.844 950.14 709.336 944.848 709.336C940.2 709.336 936.728 707.544 934.908 703.512L938.436 701.86ZM977.131 700.292C976.347 705.528 972.847 709.336 966.463 709.336C959.911 709.336 956.159 704.968 956.159 699.06C956.159 692.48 960.303 688.224 966.743 688.224C972.343 688.224 975.787 691.556 976.767 696.12L972.847 697.1C972.063 693.852 969.935 691.864 966.799 691.864C962.879 691.864 960.499 694.552 960.499 698.892C960.499 703.148 962.739 705.696 966.687 705.696C970.243 705.696 972.595 703.4 973.099 699.788L977.131 700.292Z" />
                            <?php if($state_link_SC) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-ga">
                            <?php if($state_link_GA) { ?><a href="<?php echo $state_link_GA; ?>" class="state-link" data-selected-state="GA" aria-label="Visit the Georgia Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M898.062 762C898.062 759.791 899.853 758 902.062 758H1010.06C1012.27 758 1014.06 759.791 1014.06 762V854H898.062V762Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M898.062 762C898.062 759.791 899.853 758 902.062 758H1010.06C1012.27 758 1014.06 759.791 1014.06 762V854H898.062V762Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M997.4,766.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V766.3z M989.5,769.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V769.9z"/>
                                <path class="state-abbreviation" d="M951.835 817L951.639 812.156H951.555C950.267 815.46 947.411 817.336 943.827 817.336C937.303 817.336 933.915 813.108 933.915 807.032C933.915 800.536 938.227 796.224 944.863 796.224C949.987 796.224 953.627 798.912 954.943 803.14L950.967 804.176C950.183 801.74 948.223 799.864 944.835 799.864C940.803 799.864 938.255 802.58 938.255 806.976C938.255 811.26 940.691 813.724 944.471 813.724C947.579 813.724 950.407 812.072 951.023 809.132H944.975V805.94H955.363V817H951.835ZM974.765 817L972.833 812.296H963.453L961.633 817H957.433L965.693 796.56H970.593L979.245 817H974.765ZM964.825 808.796H971.405L968.101 800.704H967.961L964.825 808.796Z" />
                            <?php if($state_link_GA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-fl">
                            <?php if($state_link_FL) { ?><a href="<?php echo $state_link_FL; ?>" class="state-link" data-selected-state="FL" aria-label="Visit the Florida Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M898.062 870C898.062 867.791 899.853 866 902.062 866H1010.06C1012.27 866 1014.06 867.791 1014.06 870V962H898.062V870Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M898.062 870C898.062 867.791 899.853 866 902.062 866H1010.06C1012.27 866 1014.06 867.791 1014.06 870V962H898.062V870Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M997.4,874.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V874.3z M989.5,877.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V877.9z"/>
                                <path class="state-abbreviation" d="M940.529 925V904.56H955.677V908.172H944.729V913.296H953.661V916.908H944.729V925H940.529ZM958.303 925V904.56H962.503V921.388H973.367V925H958.303Z" />
                            <?php if($state_link_FL) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-vt">
                            <?php if($state_link_VT) { ?><a href="<?php echo $state_link_VT; ?>" class="state-link" data-selected-state="VT" aria-label="Visit the Vermont Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M1026.06 114C1026.06 111.791 1027.85 110 1030.06 110H1138.06C1140.27 110 1142.06 111.791 1142.06 114V206H1026.06V114Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M1026.06 114C1026.06 111.791 1027.85 110 1030.06 110H1138.06C1140.27 110 1142.06 111.791 1142.06 114V206H1026.06V114Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M1125.4,124.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V124.3z M1117.5,127.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6 l-0.7-0.7V127.9z"/>
                                <path class="state-abbreviation" d="M1067.74 148.56L1074.12 164.716H1074.23L1080.34 148.56H1084.57L1076.53 169H1071.63L1063.26 148.56H1067.74ZM1092.92 169V152.172H1085.38V148.56H1104.65V152.172H1097.12V169H1092.92Z" />
                            <?php if($state_link_VT) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-ma">
                            <?php if($state_link_MA) { ?><a href="<?php echo $state_link_MA; ?>" class="state-link" data-selected-state="MA" aria-label="Visit the Massachusetts Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M1026.06 222C1026.06 219.791 1027.85 218 1030.06 218H1138.06C1140.27 218 1142.06 219.791 1142.06 222V314H1026.06V222Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M1026.06 222C1026.06 219.791 1027.85 218 1030.06 218H1138.06C1140.27 218 1142.06 219.791 1142.06 222V314H1026.06V222Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M1125.4,224.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V224.3z M1117.5,227.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6 l-0.7-0.7V227.9z"/>
                                <path class="state-abbreviation" d="M1084.8 256.56V277H1080.6V261.096H1080.46L1074.95 277H1071L1065.32 261.096H1065.2V277H1061.2V256.56H1067.84L1073.07 271.96H1073.16L1078.34 256.56H1084.8ZM1104.51 277L1102.58 272.296H1093.2L1091.38 277H1087.18L1095.44 256.56H1100.34L1108.99 277H1104.51ZM1094.57 268.796H1101.15L1097.85 260.704H1097.71L1094.57 268.796Z" />
                            <?php if($state_link_MA) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-nj">
                            <?php if($state_link_NJ) { ?><a href="<?php echo $state_link_NJ; ?>" class="state-link" data-selected-state="NJ" aria-label="Visit the New Jersey Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M1026.06 330C1026.06 327.791 1027.85 326 1030.06 326H1138.06C1140.27 326 1142.06 327.791 1142.06 330V422H1026.06V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M1026.06 330C1026.06 327.791 1027.85 326 1030.06 326H1138.06C1140.27 326 1142.06 327.791 1142.06 330V422H1026.06V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M1125.4,334.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V334.3z M1117.5,337.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6 l-0.7-0.7V337.9z"/>
                                <path class="state-abbreviation" d="M1079.88 364.56H1083.88V385H1078.62L1070.3 371.728L1069.18 369.768V385H1065.18V364.56H1070.44L1078.73 377.916L1079.88 379.96V364.56ZM1103.35 377.328C1103.35 382.984 1099.87 385.336 1094.86 385.336C1089.46 385.336 1087.05 382.228 1086.83 377.272L1090.75 376.992C1091.03 379.988 1092.17 381.864 1094.92 381.864C1097.66 381.864 1099.15 380.576 1099.15 377.356V364.56H1103.35V377.328Z" />
                            <?php if($state_link_NJ) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-de">
                            <?php if($state_link_DE) { ?><a href="<?php echo $state_link_DE; ?>" class="state-link" data-selected-state="DE" aria-label="Visit the Delaware Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M1026.06 438C1026.06 435.791 1027.85 434 1030.06 434H1138.06C1140.27 434 1142.06 435.791 1142.06 438V530H1026.06V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M1026.06 438C1026.06 435.791 1027.85 434 1030.06 434H1138.06C1140.27 434 1142.06 435.791 1142.06 438V530H1026.06V438Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M1125.4,442.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V442.3z M1117.5,445.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6 l-0.7-0.7V445.9z"/>
                                <path class="state-abbreviation" d="M1065.45 493V472.56H1073.71C1080.29 472.56 1084.74 475.976 1084.74 482.668C1084.74 489.164 1080.38 493 1073.71 493H1065.45ZM1069.65 489.388H1073.4C1077.04 489.388 1080.4 487.54 1080.4 482.78C1080.4 477.656 1077.04 476.172 1073.4 476.172H1069.65V489.388ZM1088.18 493V472.56H1103.69V476.172H1092.38V480.876H1101.48V484.488H1092.38V489.388H1104.02V493H1088.18Z" />
                            <?php if($state_link_DE) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-dc">
                            <?php if($state_link_DC) { ?><a href="<?php echo $state_link_DC; ?>" class="state-link" data-selected-state="DC" aria-label="Visit the Washington, D.C. Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M1026.06 546C1026.06 543.791 1027.85 542 1030.06 542H1138.06C1140.27 542 1142.06 543.791 1142.06 546V638H1026.06V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M1026.06 546C1026.06 543.791 1027.85 542 1030.06 542H1138.06C1140.27 542 1142.06 543.791 1142.06 546V638H1026.06V546Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M1125.4,550.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V550.3z M1117.5,553.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6 l-0.7-0.7V553.9z"/>
                                <path class="state-abbreviation" d="M1063.32 601V580.56H1071.58C1078.16 580.56 1082.61 583.976 1082.61 590.668C1082.61 597.164 1078.24 601 1071.58 601H1063.32ZM1067.52 597.388H1071.27C1074.91 597.388 1078.27 595.54 1078.27 590.78C1078.27 585.656 1074.91 584.172 1071.27 584.172H1067.52V597.388ZM1105.98 592.292C1105.19 597.528 1101.69 601.336 1095.31 601.336C1088.76 601.336 1085.01 596.968 1085.01 591.06C1085.01 584.48 1089.15 580.224 1095.59 580.224C1101.19 580.224 1104.63 583.556 1105.61 588.12L1101.69 589.1C1100.91 585.852 1098.78 583.864 1095.65 583.864C1091.73 583.864 1089.35 586.552 1089.35 590.892C1089.35 595.148 1091.59 597.696 1095.53 597.696C1099.09 597.696 1101.44 595.4 1101.95 591.788L1105.98 592.292Z" />
                            <?php if($state_link_DC) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-me">
                            <?php if($state_link_ME) { ?><a href="<?php echo $state_link_ME; ?>" class="state-link" data-selected-state="ME" aria-label="Visit the Maine Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M1153.81 6C1153.81 3.79086 1155.6 2 1157.81 2H1265.81C1268.02 2 1269.81 3.79086 1269.81 6V98H1153.81V6Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M1153.81 6C1153.81 3.79086 1155.6 2 1157.81 2H1265.81C1268.02 2 1269.81 3.79086 1269.81 6V98H1153.81V6Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M1253.4,14.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V14.3z M1245.5,17.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6l-0.7-0.7 V17.9z"/>
                                <path class="state-abbreviation" d="M1214.13 40.56V61H1209.93V45.096H1209.79L1204.27 61H1200.32L1194.64 45.096H1194.53V61H1190.52V40.56H1197.16L1202.4 55.96H1202.48L1207.66 40.56H1214.13ZM1218.61 61V40.56H1234.12V44.172H1222.81V48.876H1231.91V52.488H1222.81V57.388H1234.45V61H1218.61Z" />
                            <?php if($state_link_ME) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-nh">
                            <?php if($state_link_NH) { ?><a href="<?php echo $state_link_NH; ?>" class="state-link" data-selected-state="NH" aria-label="Visit the New Hampshire Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M1153.81 114C1153.81 111.791 1155.6 110 1157.81 110H1265.81C1268.02 110 1269.81 111.791 1269.81 114V206H1153.81V114Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M1153.81 114C1153.81 111.791 1155.6 110 1157.81 110H1265.81C1268.02 110 1269.81 111.791 1269.81 114V206H1153.81V114Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M1253.4,124.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V124.3z M1245.5,127.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6 l-0.7-0.7V127.9z"/>
                                <path class="state-abbreviation" d="M1205.43 148.56H1209.43V169H1204.17L1195.85 155.728L1194.73 153.768V169H1190.73V148.56H1195.99L1204.28 161.916L1205.43 163.96V148.56ZM1228.7 169V160.432H1218.12V169H1213.92V148.56H1218.12V156.82H1228.7V148.56H1232.9V169H1228.7Z" />
                            <?php if($state_link_NH) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-ri">
                            <?php if($state_link_RI) { ?><a href="<?php echo $state_link_RI; ?>" class="state-link" data-selected-state="RI" aria-label="Visit the Rhode Island Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M1153.81 222C1153.81 219.791 1155.6 218 1157.81 218H1265.81C1268.02 218 1269.81 219.791 1269.81 222V314H1153.81V222Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M1153.81 222C1153.81 219.791 1155.6 218 1157.81 218H1265.81C1268.02 218 1269.81 219.791 1269.81 222V314H1153.81V222Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M1253.4,224.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V224.3z M1245.5,227.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6 l-0.7-0.7V227.9z"/>
                                <path class="state-abbreviation" d="M1212.61 277C1211.94 276.272 1211.69 274.172 1211.49 272.492C1211.24 270.252 1210.6 269.524 1208.38 269.524H1203.06V277H1198.86V256.56H1209.87C1214.32 256.56 1216.59 258.772 1216.59 262.44C1216.59 266.528 1213.82 267.592 1211.72 267.732V267.816C1213.65 267.984 1215.19 268.74 1215.72 271.792C1216.17 274.34 1216.48 276.104 1217.46 277H1212.61ZM1203.06 265.968H1208.24C1209.81 265.968 1212.33 265.632 1212.33 263.028C1212.33 261.292 1211.3 260.172 1208.89 260.172H1203.06V265.968ZM1220.57 277V256.56H1224.77V277H1220.57Z" />
                            <?php if($state_link_RI) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-ct">
                            <?php if($state_link_CT) { ?><a href="<?php echo $state_link_CT; ?>" class="state-link" data-selected-state="CT" aria-label="Visit the Connecticut Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M1153.94 330C1153.94 327.791 1155.73 326 1157.94 326H1265.94C1268.15 326 1269.94 327.791 1269.94 330V422H1153.94V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M1153.94 330C1153.94 327.791 1155.73 326 1157.94 326H1265.94C1268.15 326 1269.94 327.791 1269.94 330V422H1153.94V330Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M1253.4,334.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V334.3z M1245.5,337.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6 l-0.7-0.7V337.9z"/>
                                <path class="state-abbreviation" d="M1212.95 376.292C1212.17 381.528 1208.67 385.336 1202.28 385.336C1195.73 385.336 1191.98 380.968 1191.98 375.06C1191.98 368.48 1196.12 364.224 1202.56 364.224C1208.16 364.224 1211.61 367.556 1212.59 372.12L1208.67 373.1C1207.88 369.852 1205.75 367.864 1202.62 367.864C1198.7 367.864 1196.32 370.552 1196.32 374.892C1196.32 379.148 1198.56 381.696 1202.51 381.696C1206.06 381.696 1208.41 379.4 1208.92 375.788L1212.95 376.292ZM1220.98 385V368.172H1213.45V364.56H1232.71V368.172H1225.18V385H1220.98Z" />
                            <?php if($state_link_CT) { ?></a><?php } ?>
                        </g>
                        <g class="state-box" id="desktop-pr">
                            <?php if($state_link_PR) { ?><a href="<?php echo $state_link_PR; ?>" class="state-link" data-selected-state="PR" aria-label="Visit the Puerto Rico Social Dashboard in a New Window" target="_blank"><?php } ?>
                                <path class="state-hover-outline" d="M1153.94 870C1153.94 867.791 1155.73 866 1157.94 866H1265.94C1268.15 866 1269.94 867.791 1269.94 870V962H1153.94V870Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="state-outline" d="M1153.94 870C1153.94 867.791 1155.73 866 1157.94 866H1265.94C1268.15 866 1269.94 867.791 1269.94 870V962H1153.94V870Z" stroke="url(#DesktopBorderDisabledGradient)" stroke-width="4"/>
                                <path class="external-link-icon" d="M1253.4,874.3h7.3l0.7,0.7v7.3 h-1.4v-5.5l-9.9,9.9l-1-1l9.9-9.9h-5.5V874.3z M1245.5,877.9l0.7-0.7h5.8v1.4h-5.1v10.2h10.2v-5.1h1.4v5.8l-0.7,0.7h-11.6 l-0.7-0.7V877.9z"/>
                                <path class="state-abbreviation" d="M1193.07 925V904.56H1202.62C1207.49 904.56 1210.6 906.688 1210.6 911.14C1210.6 915.564 1207.52 917.944 1202.45 917.944H1197.27V925H1193.07ZM1197.27 914.332H1202.2C1204.88 914.332 1206.37 913.52 1206.37 911.308C1206.37 908.984 1204.86 908.172 1202.34 908.172H1197.27V914.332ZM1227.32 925C1226.65 924.272 1226.4 922.172 1226.2 920.492C1225.95 918.252 1225.31 917.524 1223.1 917.524H1217.78V925H1213.58V904.56H1224.58C1229.03 904.56 1231.3 906.772 1231.3 910.44C1231.3 914.528 1228.53 915.592 1226.43 915.732V915.816C1228.36 915.984 1229.9 916.74 1230.43 919.792C1230.88 922.34 1231.19 924.104 1232.17 925H1227.32ZM1217.78 913.968H1222.96C1224.52 913.968 1227.04 913.632 1227.04 911.028C1227.04 909.292 1226.01 908.172 1223.6 908.172H1217.78V913.968Z" />
                            <?php if($state_link_PR) { ?></a><?php } ?>
                        </g>

                        <defs>
                            <linearGradient id="DesktopBorderGradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="5%" stop-color="#FF6D00" />
                                <stop offset="95%" stop-color="#6300C7" stop-opacity="0" />
                            </linearGradient>
                            <linearGradient id="DesktopBorderHoverGradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="5%" stop-color="#FFFFFF" />
                                <stop offset="95%" stop-color="#6300C7" stop-opacity="0" />
                            </linearGradient>
                            <linearGradient id="DesktopHoverGradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="5%" stop-color="#6300C7" />
                                <stop offset="95%" stop-color="#6300C7" stop-opacity="0" />
                            </linearGradient>
                            <linearGradient id="DesktopBorderDisabledGradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="5%" stop-color="#ACB0BF"/>
                                <stop offset="95%" stop-color="#191326" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>

            <div class="social-listening-footer-section">

                <?php if($state_abbreviations && is_array($state_abbreviations)) : ?>
                    <form class="social-listening-form" id="state-selection-form">
                        <label for="state-selector">Or select a location</label>
                        <select id="state-selector">
                            <option value="" selected="true" disabled="disabled" >Select a Region</option>
                            <?php foreach($state_abbreviations as $state) : 
                                if(get_field('state_dashboard_links_' . $state) && is_array(get_field('state_dashboard_links_' . $state)) && isset(get_field('state_dashboard_links_' . $state)['url'])) : ?>
                                    <option value="<?php echo $state; ?>"><?php echo get_state_by_abbreviation($state); ?></option>
                                <?php endif;
                            endforeach; ?>
                        </select> 

                            <?php foreach($state_abbreviations as $state) : 
                                if(get_field('state_dashboard_links_' . $state) && is_array(get_field('state_dashboard_links_' . $state)) && isset(get_field('state_dashboard_links_' . $state)['url'])) : ?>
                                    <input type="hidden" class="state-dashboard-links" id="state-link-<?php echo $state; ?>" value="<?php echo get_field('state_dashboard_links_' . $state)['url']; ?>" />
                                <?php endif;
                            endforeach; ?>
                        
                        <div class="map-btn-wrapper large-button-white button-with-icon external">
                            <a id="state-dashboard-external-link" class="btn-white btn-with-icon disabled-link" href="javascript: void(0)" target="_blank" title="">Visit Social Dashboard</a>
                        </div>
                        <div role="region" id="selected-state-information" aria-live="polite" class="a11y-visible">
                            <h3 id="selected-state">No region selected.</h3>
                        </div>
                    </form>
                <?php endif; 

                if($display_contact_link) : ?>
                    <div class="contact-wrapper">
                        <p><a href="/contact" title="Contact Us" target="_self">Questions? Contact us</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="content-end-triangle"><img src="<?php echo get_stylesheet_directory_uri();?>/assets/images/content-end-triangle.svg" alt="" /></div>
    </div>



<?php endif; ?>