jQuery(document).ready(function(t){t(".entry-content a").each(function(){var a;t(this).hasClass("external-link")||(a=this.hostname||this.pathname,"_blank"!==t(this).attr("target")&&a===window.location.host)||(t(this).addClass("external-link"),t(this).append('<span class="a11y-visible">External Link</span>'))})});