(function() {
    if (typeof(wpdreams_asp_mce_button_menu)!="undefined") {
      tinymce.PluginManager.add('wpdreams_asp_mce_button', function( editor, url ) {
          eval("var asp_menus = [" + wpdreams_asp_mce_button_menu + "]");
          eval("var asp_res_menus = [" + wpdreams_asp_res_mce_button_menu + "]");
          eval("var asp_sett_menus = [" + wpdreams_asp_sett_mce_button_menu + "]");
          eval("var asp_two_column_menus = [" + wpdreams_asp_two_column_mce_button_menu + "]");
          editor.addButton( 'wpdreams_asp_mce_button', {
              text: 'ASP',
              icon: false,
              type: 'menubutton',
              menu: [
                  {
                      text: 'Search box',
                      menu: asp_menus
                  },
                  {
                      text: 'Result box',
                      menu: asp_res_menus
                  },
                  {
                      text: 'Settings box',
                      menu: asp_sett_menus
                  },
                  {
                      text: 'Two column layout',
                      menu: asp_two_column_menus
                  }
              ]
          });
      });
    }
})();