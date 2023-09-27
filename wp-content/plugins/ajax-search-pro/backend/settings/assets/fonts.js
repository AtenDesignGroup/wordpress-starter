var webFontLoaded = [];
function loadFonts(family) {
  WebFontConfig = {
    google: { families: [family+":400,700"] }
  };
  if ( webFontLoaded.indexOf(family) < 0 ) {
      if ( typeof WebFont !== 'undefined' ) {
          WebFont.load(WebFontConfig);
          webFontLoaded.push(family);
      }
  }
}

jQuery(function($){
    $('.wpdreamsFontComplete input[type=text], .wpdreamsFontComplete select').on('keyup change', function() {
        var p = $(this).closest('.wpdreamsFontComplete');
        var family, fonts;

        if ( p.find('select.wd_fonts_select').val() != 'custom' ) {
            var realFamilyName = p.find('select.wd_fonts_select').val().replace('--g--', '');
            loadFonts(realFamilyName);
            fonts = p.find('select.wd_fonts_select').val().replace(/'|"/gi, '');
            family = "font-family:" + fonts + ";";
        } else {
            if ( p.find('input.wd_fonts_custom').val() != '' ) {
                fonts = p.find('input.wd_fonts_custom').val().replace(/'|"/gi, '');
                family = "font-family:" + fonts + ";";
            } else {
                family = "font-family: Open Sans;";
            }
        }
        var weight = "font-weight:"+p.find('select.wd_font_weight').val()+";";
        var color = "color:"+p.find('.wd_fonts_type input.color').val()+";";
        var size = "font-size:"+p.find('input.wd_fonts_size').val()+";";
        var lh =  "line-height:"+p.find('input.wd_fonts_line').val()+";";
        var tShadow = p.find('input._xx_hlength_xx_').val() + "px " + p.find('input._xx_vlength_xx_').val() + "px ";
        tShadow += p.find('input._xx_blurradius_xx_').val() + "px " + p.find('.wpd_font_shadow input.color').val();
        var textShadow = "text-shadow:" + tShadow + ";";

        p.find("input[isparam=1]").val(weight+family+color+size+lh+textShadow).trigger('change');
    });

    $('.wpdreamsFontComplete select.wd_fonts_select').on('change', function(){
        var cf = $(this).closest('.wpdreamsFontComplete').find('label.wd_fonts_custom');
        if ( $(this).val() == 'custom' ) {
            cf.removeClass('hiddend');
        } else {
            cf.addClass('hiddend');
        }
    });
    $('.wpdreamsFontComplete select.wd_fonts_select').trigger('change');


    $(".wpdreamsFontComplete>.triggerer").on('click', function() {
        var p = $(this).closest('.wpdreamsFontComplete');
        var val = p.find("input[isparam=1]").val().replace(/(\r\n|\n|\r)/gm,"");

        var font = $.trim(val.match(/family:(.*?);/)[1]).replace('--g--', '');
        font = font.replace(/'|"/gi, '');

        if ( $("select.wd_fonts_select option[value='"+font+"']").length > 0 ) {
            p.find('select.wd_fonts_select').val( font );
        } else {
            p.find('select.wd_fonts_select').val('custom');
            p.find('input.wd_fonts_custom').val(font);
        }

        p.find('select.wd_font_weight').val( $.trim(val.match(/font-weight:(.*?);/)[1]) );
        p.find('input.wd_fonts_size').val( $.trim(val.match(/size:(.*?);/)[1]) );
        p.find('input.wd_fonts_line').val( $.trim(val.match(/height:(.*?);/)[1]) );
        p.find('.wd_fonts_type input.color').val( $.trim(val.match(/color:(.*?);/)[1]) );
        p.find('.wd_fonts_type input.color').spectrum('set', $.trim(val.match(/color:(.*?);/)[1]));

        var ts = val.match(/text-shadow:(.*?)px (.*?)px (.*?)px (.*?);/);
        if (ts != null && ts.length > 0) {
            p.find('input._xx_hlength_xx_').val( $.trim(ts[1]) );
            p.find('input._xx_vlength_xx_').val( $.trim(ts[2]) );
            p.find('input._xx_blurradius_xx_').val( $.trim(ts[3]) );
            p.find('.wpd_font_shadow input.color').val( $.trim(ts[4]) );
            p.find('.wpd_font_shadow input.color').spectrum('set', $.trim(ts[4]));
        }

        p.find('select.wd_fonts_select').trigger('change');
    });
});