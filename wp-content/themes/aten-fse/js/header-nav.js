jQuery(document).ready(function(m){function e(){var e,a=window.innerWidth;a<768?(m("header").hasClass("mobile-layout")||(m("header").addClass("mobile-layout"),m("header").hasClass("tablet-layout")?m("header").removeClass("tablet-layout"):(m("#mega-menu-primary").children("li").addClass("utility-items").appendTo("#mega-menu-main-nav"),m(".gtranslate_wrapper").wrap('<li id="mobile-translate-widget"></li>'),m("#mobile-translate-widget").appendTo("#mega-menu-main-nav")),m("header form.wp-block-search").wrap('<li id="mobile-search-widget"></li>'),m("#mobile-search-widget").prependTo("#mega-menu-main-nav")),m("body").hasClass("has-easy-notification-bar")?(e=m(".easy-notification-bar").outerHeight()+70,m("#mega-menu-main-nav").css({top:e+"px"})):m("#mega-menu-main-nav").css({top:"70px"})):768<=a&&a<1024?(m("header").hasClass("tablet-layout")||(m("header").addClass("tablet-layout"),m("header").hasClass("mobile-layout")?(m("header").removeClass("mobile-layout"),m("header form.wp-block-search").detach().insertAfter("#mega-menu-wrap-primary"),m("#mobile-search-widget").remove()):(m("#mega-menu-primary").children("li").addClass("utility-items").appendTo("#mega-menu-main-nav"),m(".gtranslate_wrapper").wrap('<li id="mobile-translate-widget"></li>'),m("#mobile-translate-widget").appendTo("#mega-menu-main-nav"))),m("body").hasClass("has-easy-notification-bar")?(e=m(".easy-notification-bar").outerHeight()+70,m("#mega-menu-main-nav").css({top:e+"px"})):m("#mega-menu-main-nav").css({top:"70px"})):((m("header").hasClass("tablet-layout")||m("header").hasClass("mobile-layout"))&&(m("header").removeClass("mobile-layout tablet-layout"),m("#mega-menu-main-nav").children(".utility-items").appendTo("#mega-menu-primary").removeClass("utility-items"),m("header form.wp-block-search").detach().insertAfter("#mega-menu-wrap-primary"),m(".gtranslate_wrapper").detach().insertBefore("header form.wp-block-search"),m("#mobile-search-widget, #mobile-translate-widget").remove()),m("li.mega-menu-item").on("open_panel",function(){var e=m(this).closest("ul.max-mega-menu");m("ul.max-mega-menu").not(e).each(function(){m(this).data("maxmegamenu").hideAllPanels()})})),window.innerWidth<1024?m("button.mega-menu-button").unbind("keypress keyup").on("keypress",function(e){if(13==e.which)return e.preventDefault(),!1}):m("button.mega-menu-button").unbind("keypress keyup").on("keydown",function(e){13==e.which&&(e.preventDefault(),e.stopImmediatePropagation(),n(m(this)))}),window.innerWidth<1024&&(m(".mega-menu-item-has-children .mega-sub-menu .mega-menu-link").on("keydown",function(e){"Escape"===e.key&&(e.stopImmediatePropagation(),e=m(this).closest(".mega-menu-item-has-children").find(".mega-menu-button"),m(e).focus(),n(e))}),m(".mega-menu-item-has-children > .mega-menu-button").on("keydown",function(e){"Escape"===e.key&&m(".mega-toggle-label").focus()}))}function n(e){var a=e.siblings(".mega-menu-link"),n=e.closest("ul"),t=e.closest("li");"false"!==m(e).attr("aria-expanded")||m(t).hasClass("menu-toggle-on")?(m(e).attr("aria-expanded","false"),m(n).data("maxmegamenu").hidePanel(a)):(m(e).attr("aria-expanded","true"),m(n).data("maxmegamenu").showPanel(a))}m(".gt_selector").find('option[value=""]').remove(),m("#mega-menu-wrap-main-nav").attr("aria-label","Main Menu"),m("#mega-menu-wrap-primary").attr("aria-label","Utility Menu"),m("#mega-menu-wrap-main-nav .mega-toggle-label").unwrap(),m("#mega-menu-wrap-main-nav .mega-toggle-label .mega-toggle-label-open").remove(),m("#mega-menu-wrap-main-nav .mega-toggle-label .mega-toggle-label-closed").addClass("mega-toggle-label-open"),m('<span class="menu-icon">language</span>').appendTo(".gtranslate_wrapper"),m('<span class="menu-icon right">expand_more</span>').appendTo(".gtranslate_wrapper"),m(".menu-icon, .mobile-menu-icon, .wp-block-search__button").addClass("notranslate"),m("span.mega-toggle-label").convertElement("button"),m("button.mega-menu-button").on("click",function(){n(m(this))}),m(".mega-sub-menu").parent().on("keyup.megamenu",function(e){var a=e.keyCode||e.which,n=m(e.target);9===a&&(m(this).addClass("mega-keyboard-navigation"),n.parent().parent().is(".max-mega-menu"))&&e.stopImmediatePropagation()}),e(),m(window).resize(function(){e()})});