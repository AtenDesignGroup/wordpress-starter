jQuery(document).ready(function(a){var n=window.location.host;a(".wp-element-button, .custom-button").each(function(){var t=a(this).attr("href");!0==("_blank"===a(this).attr("target"))?(a(this).addClass("external-link"),a(this).append('<span class="a11y-visible">External Link</span>')):t&&/(http(s?)):\/\//i.test(t)&&new URL(a(this).attr("href")).hostname!==n&&(a(this).addClass("external-link"),a(this).append('<span class="a11y-visible">External Link</span>'))})});