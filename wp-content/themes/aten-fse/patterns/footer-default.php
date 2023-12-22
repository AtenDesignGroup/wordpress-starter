<?php

/**
 * @file
 * Title: Default Footer
 * Slug: aten-fse/footer-default
 * Categories: footer
 * Block Types: core/template-part/footer
 */
?>

<div class="footer-accent">
  <svg style="display: none;" width="100%" height="100%" class="wave" xmlns="http://www.w3.org/2000/svg">
    <defs></defs>
    <path id="footer-wave1" d="" />
  </svg>
  <svg style="display: none;" width="100%" height="100%" class="wave" xmlns="http://www.w3.org/2000/svg">
    <defs></defs>
    <path id="footer-wave2" d="" />
  </svg>
</div>
<svg id="static-wave" display="block" viewBox="0 0 1620 120" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M1620 69.2379C1620 69.2379 718 208.655 0 0V120H1620V69.2379Z" fill="#F3F7FF" />
</svg>
<div class="footer-wrapper l-gutter">
  <div class="footer-upper">
    <div class="footer-upper-wrapper">
      <figure class="footer-logo wp-block-image size-full">
        <a href="/" class="footer-logo-link">
          <img decoding="async" loading="lazy" src="/wp-content/themes/aten-fse/assets/logo.svg" alt="Aten logo" />
        </a>
      </figure>

      <nav class="footer-upper-menu" role="navigation" aria-label="footer menu">
        <?php
        // Display the 'upper-footer' menu.
        wp_nav_menu([
          'theme_location' => 'upper-footer',
          'container' => FALSE,
          'menu_class' => 'footer-upper-menu',
        ]);
        ?>
      </nav>
      <div class="footer-locations">
        <h2 class="visually-hidden">City locations and hours</h2>
        <div class="city-hall-column">
          <?php dynamic_sidebar('upper-footer-location-1'); ?>
        </div>
        <div class="services-column">
          <?php dynamic_sidebar('upper-footer-services-1'); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="footer-lower">
    <nav aria-label="social menu" class="footer-social">
      <ul>
        <li class="social-item">
          <a href="https://www.facebook.com/@AtenDesignGroup/">
            <span class="visually-hidden">Facebook</span>
            <svg fill="none" height="31" viewBox="0 0 31 31" width="31" xmlns="http://www.w3.org/2000/svg">
              <path d="m11.2109 28h5.1407v-12.5234h3.9922l.6562-4.9766h-4.6484v-3.44531c0-.76563.1093-1.36719.4375-1.75.3281-.4375 1.039-.65625 2.0234-.65625h2.625v-4.42969c-.9844-.109375-2.2969-.21875-3.8281-.21875-1.9688 0-3.5.601562-4.6485 1.75-1.2031 1.14844-1.75 2.73438-1.75 4.8125v3.9375h-4.2109v4.9766h4.2109z" fill="#222"></path>
            </svg>
          </a>
        </li>
        <li class="social-item">
          <a href="https://twitter.com/atendesigngroup">
            <span class="visually-hidden">Twitter</span>
            <svg fill="none" height="31" viewBox="0 0 31 31" width="31" xmlns="http://www.w3.org/2000/svg">
              <path d="m25.1016 8.02083c1.0937-.82031 2.0781-1.80469 2.8984-3.00781-1.0938.49219-2.1875.82031-3.2812.92969 1.2031-.76563 2.0781-1.80469 2.5156-3.17188-1.1485.65625-2.3516 1.14844-3.6641 1.36719-.5469-.54688-1.2031-.98438-1.9141-1.3125-.7109-.32813-1.4765-.49219-2.2968-.49219-1.0391 0-1.9688.27344-2.8438.76563-.875.54687-1.5859 1.25781-2.0781 2.13281-.5469.875-.7656 1.85937-.7656 2.84375 0 .4375 0 .875.1093 1.3125-2.3515-.10938-4.53901-.65625-6.61714-1.75-2.07812-1.03906-3.77344-2.46094-5.19531-4.26563-.54687.92969-.82031 1.91407-.82031 2.89844 0 .98438.21875 1.91406.71093 2.73438.43751.875 1.09375 1.53129 1.85938 2.07809-.92969 0-1.80469-.2734-2.57031-.7656v.1094c0 1.3672.4375 2.5703 1.3125 3.6094.875 1.0937 1.96875 1.75 3.28125 2.0234-.54688.1094-1.03907.1641-1.53125.1641-.32813 0-.71094 0-1.03906-.0547.32812 1.1484.98437 2.0781 1.96874 2.8437.98438.7656 2.07813 1.0938 3.39063 1.0938-2.13281 1.6406-4.53906 2.4609-7.16406 2.4609-.546878 0-.984378 0-1.36719-.0547 2.625 1.75 5.57812 2.5703 8.80469 2.5703 3.33591 0 6.28901-.8203 8.91411-2.5703 2.3515-1.5312 4.2109-3.5547 5.5234-6.1797 1.2578-2.4062 1.914-4.9765 1.914-7.60153 0-.32813-.0546-.54688-.0546-.71094z" fill="#222"></path>
            </svg>
          </a>
        </li>
        <li class="social-item">
          <a href="https://www.instagram.com/atendesigngroup/">
            <span class="visually-hidden">Instagram</span>
            <svg fill="none" height="31" viewBox="0 0 31 31" width="31" xmlns="http://www.w3.org/2000/svg">
              <path d="m14.1722 8.05501c-3.375 0-6.06446 2.74219-6.06446 6.06449 0 3.375 2.68946 6.0644 6.06446 6.0644 3.3223 0 6.0644-2.6894 6.0644-6.0644 0-3.3223-2.7421-6.06449-6.0644-6.06449zm0 10.01949c-2.1621 0-3.9551-1.7402-3.9551-3.955 0-2.1621 1.7403-3.9024 3.9551-3.9024 2.1621 0 3.9023 1.7403 3.9023 3.9024 0 2.2148-1.7402 3.955-3.9023 3.955zm7.6992-10.23042c0-.79102-.6328-1.42383-1.4238-1.42383s-1.4238.63281-1.4238 1.42383c0 .79101.6328 1.42382 1.4238 1.42382s1.4238-.63281 1.4238-1.42382zm4.0078 1.42382c-.1054-1.89843-.5273-3.58593-1.8984-4.95703-1.3711-1.37109-3.0586-1.79297-4.957-1.89843-1.9512-.10547-7.8047-.10547-9.7559 0-1.89844.10546-3.5332.52734-4.95703 1.89843-1.3711 1.3711-1.79297 3.0586-1.89844 4.95703-.10547 1.9512-.10547 7.8047 0 9.7559.10547 1.8984.52734 3.5332 1.89844 4.957 1.42383 1.3711 3.05859 1.793 4.95703 1.8984 1.9512.1055 7.8047.1055 9.7559 0 1.8984-.1054 3.5859-.5273 4.957-1.8984 1.3711-1.4238 1.793-3.0586 1.8984-4.957.1055-1.9512.1055-7.8047 0-9.7559zm-2.5312 11.8125c-.3692 1.0547-1.2129 1.8457-2.2149 2.2676-1.582.6328-5.2734.4746-6.9609.4746-1.7402 0-5.43165.1582-6.96094-.4746-1.05469-.4219-1.84571-1.2129-2.26758-2.2676-.63281-1.5293-.47461-5.2207-.47461-6.9609 0-1.6875-.1582-5.37894.47461-6.96097.42187-1.00195 1.21289-1.79297 2.26758-2.21484 1.52929-.63282 5.22074-.47461 6.96094-.47461 1.6875 0 5.3789-.15821 6.9609.47461 1.002.36914 1.793 1.21289 2.2149 2.21484.6328 1.58203.4746 5.27347.4746 6.96097 0 1.7402.1582 5.4316-.4746 6.9609z" fill="#222"></path>
            </svg>
          </a>
        </li>
        <li class="social-item">
          <a href="https://www.youtube.com/atendesigngroup">
            <span class="visually-hidden">YouTube</span>
            <svg fill="none" height="31" viewBox="0 0 31 31" width="31" xmlns="http://www.w3.org/2000/svg">
              <path d="m27.4359 6.57692c.2051.92308.4103 2.35898.5128 4.20518l.0513 2.5641-.0513 2.5641c-.1025 1.9487-.3077 3.3333-.5128 4.2564-.2051.6154-.5128 1.1282-.9231 1.5384-.4615.4616-.9743.7693-1.5897.9231-.9231.2564-2.9744.4103-6.2564.5128l-4.6667.0513-4.66667-.0513c-3.28205-.1025-5.38461-.2564-6.25641-.5128-.61538-.1538-1.17948-.4615-1.58974-.9231-.46154-.4102-.769231-.923-.923077-1.5384-.256411-.9231-.410257-2.3077-.5128209-4.2564l-.0512821-2.5641c0-.718 0-1.5898.0512821-2.5641.1025639-1.8462.2564099-3.2821.5128209-4.20518.153846-.61538.461537-1.1282.923077-1.58974.41026-.41026.97436-.71795 1.58974-.92308.8718-.20513 2.97436-.41025 6.25641-.51282l4.66667-.05128 4.6667.05128c3.282.10257 5.3333.30769 6.2564.51282.6154.20513 1.1282.51282 1.5897.92308.4103.46154.718.97436.9231 1.58974zm-16.3077 10.97438 7.3333-4.2051-7.3333-4.15389z" fill="#222"></path>
            </svg>
          </a>
        </li>
      </ul>
    </nav>
    <nav class="footer-lower-menu" role="navigation" aria-label="footer utility">
      <?php
      // Display the 'lower-footer' menu.
      wp_nav_menu([
        'theme_location' => 'lower-footer',
        'container' => FALSE,
        'menu_class' => 'footer-lower-menu',
      ]);
      ?>
    </nav>
  </div>
  <a href="#wp--skip-link--target" class="back-to-top" aria-label="Scroll back to top">
    <icon><img src="/wp-content/themes/aten-fse/assets/icons/arrow_circle_up.svg" alt="arrow up icon"></icon>
  </a>
</div>
