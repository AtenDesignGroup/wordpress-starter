jQuery(document).ready(function(n){var e=window.location.host;n(".cta-button").each(function(){var t=new URL(n(this).attr("href")).hostname,a="_blank"===n(this).attr("target");t===e&&!0!=a||n(this).addClass("external-link")})});