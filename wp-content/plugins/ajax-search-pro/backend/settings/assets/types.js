// Javascript For the types
jQuery(function($){
    // ------------------------- GENERICS ------------------------
    /**
     * YesNo button
     */
    $('.wpdreamsYesNo .wpdreamsYesNoInner').on('click', function () {
        var hidden = $(this).prev();
        var val = $(hidden).val();
        if (val == 1) {
            val = 0;
            $(this).parent().removeClass("active");
        } else {
            val = 1;
            $(this).parent().addClass("active");
        }
        $(hidden).val(val);
        $(hidden).trigger('change').trigger('input');;
    });
    $('.wpdreamsYesNo .triggerer').on('click', function () {
        var hidden = $('input[type=hidden]', $(this).parent());
        var div = $(this).parent();
        var val = $(hidden).val();
        if (val == 0) {
            div.removeClass("active");
        } else {
            div.addClass("active");
        }
        $(hidden).trigger('change').trigger('input');
    });

    /**
     * Up-down arrow
     */
    $('.wpdreams-updown .wpdreams-uparrow').on('click', function() {
        var prev = $(this).parent().prev();
        while (!prev.is('input')) {
            prev = prev.prev();
        }
        prev.val(parseFloat($(prev).val()) + 1);
        prev.trigger('change');
    });
    $('.wpdreams-updown .wpdreams-downarrow').on('click', function() {
        var prev = $(this).parent().prev();
        while (!prev.is('input')) {
            prev = prev.prev();
        }
        prev.val(parseFloat($(prev).val()) - 1);
        prev.trigger('change');
    });

    /**
     * 4 value storage (padding, margin etc..)
     */
    $('.wd_an_inputs input[type=text].wd_an_noparam').on('keydown', function (e) {
        var k = e.which;
        var ok = k >= 65 && k <= 90 || // A-Z
            k >= 96 && k <= 105 || // a-z
            k >= 35 && k <= 40 || // arrows
            k == 8 || // Backspaces
            k >= 48 && k <= 57; // 0-9

        if (!ok){
            e.preventDefault();
        }
    });
    $('.wd_an_inputs input[type=text].wd_an_noparam').on('keyup', function () {
        var values = [];
        $(this).closest('.wd_an_inputs').find('input[type=text].wd_an_noparam').each(function () {
            var val = $(this).val();
            if ( val == '' )
                val = $(this).data('default');
            values.push( val );
        });
        $(this).closest('.wd_an_inputs').find('input[isparam=1]').val(values.join(' ')).trigger('change');
    });
    $('.wd_an_inputs>div.triggerer').bind('click', function () {
        var hidden = $("input[isparam=1]", $(this).parent());
        var values = hidden.val().split(' ');
        var i = 0;
        $('input[type=text].wd_an_noparam', $(this).parent()).each(function () {
            if ( typeof values[i] != 'undefined' ) {
                $(this).val(values[i]);
                i++;
            }
        });
        hidden.trigger('change');
    });

    $('.wpdreamsFour input[type=text]').on('change', function() {
        var value = "";
        $('input[type=text]', $(this).parent()).each(function () {
            value += $(this).val() + "||";
        });
        $('input[isparam=1]', $(this).parent()).val("||" + value);
        $('input[isparam=1]', $(this).parent()).trigger('change');
    });
    $('.wpdreamsFour>fieldset>.triggerer').bind('click', function () {
        var hidden = $("input[isparam=1]", $(this).parent());
        var values = hidden.val().match(/\|\|(.*?)\|\|(.*?)\|\|(.*?)\|\|(.*?)\|\|/);
        var i = 1;
        $('input[type=text]', $(this).parent()).each(function () {
            if ($(this).attr('name') != null) {
                $(this).val(values[i]);
                i++;
            }
        });
        hidden.trigger('change');
    });

    /**
     * wpdreamsUpload - File uploader window
     */
    var custom_uploader, $cup_text;
    $('.wpdreamsUpload .wdUploadButton').on('click', function(e){
        e.preventDefault();
        $cup_text = $(this).parent().find('input.wdUploadText');
        //If the uploader object has already been created, reopen the dialog
        if ( custom_uploader ) {
            custom_uploader.open();
            return;
        }

        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose File',
            button: {
                text: 'Choose File'
            },
            multiple: false
        });

        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function () {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $cup_text.val(attachment.url).trigger('change');
        });

        //Open the uploader dialog
        custom_uploader.open();
    });

    // This needs to be triggered once the window is loaded, otherwise it may have no effect on init
    $('textarea.wd_textarea_expandable').textareaAutoSize();
    var textAuto;
    $('.tabs a[tabid]').on('click', function(){
        clearTimeout(textAuto);
        textAuto = setTimeout(function(){
            $('textarea.wd_textarea_expandable').trigger('input');
        }, 50);
    });
    /*$( window ).on( "load", function() {
        $('textarea.wd_textarea_expandable').trigger('input');
    });*/

    // --------------------- COMPLEX TYPES -----------------------
    /**
     * Array-chained select
     */
    $('.wpdreamsCustomArraySelect select').on('change', function() {
        var $hidden = $('input[isparam=1]', $(this).parent());
        var valArr = [];

        $('select', $(this).parent()).each(function(index){
            valArr.push( $(this).val() );
        });

        $hidden.val( valArr.join('||') );
    });


    /**
     * Base64 textarea wd_Textarea_B64
     */
    var _wd_tt = null;
    $('textarea.wd_textarea_b64').on("keyup paste", function () {
        clearTimeout(_wd_tt);
        var $this = $(this);
        _wd_tt = setTimeout(function(){
            $this.prev().val( WD_Helpers.Base64.encode($this.val()) );
        }, 200);
    });


    $('div.wd_sortable_editable').each(function(){
        var id = $(this).attr('id').match(/^wd_sortable_editable-(.*)/)[1];
        var selector = "#sortable" + id;
        var parent = $(this);
        var items = $('ul[id*=sortable] li', parent);
        var hidden = $('input[isparam=1]', parent);

        function parse_val() {
            var items = $('ul[id*=sortable] li', parent);
            var obj = {};
            items.each(function () {
                obj[$('label', this).html()] = $('input', this).val();
            });
            hidden.val("_decode_" + WD_Helpers.Base64.encode(JSON.stringify(obj)));
        }

        parent.on('keyup', 'li input[type="text"]', function(){
            parse_val();
        });

        $(selector).sortable({}, {
            update: function (event, ui) {
                parse_val();
            }
        }).disableSelection();
    });


    $('div.wpdreamsSortable').each(function(){
        var id = $(this).attr('id').match(/^wpdreamsSortable-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var name = $('input[isparam=1]', this).attr('name');

        $(selector).sortable({}, {
            update: function (event, ui) {
                var parent = $(ui.item).parent();
                while (!parent.hasClass('wpdreamsSortable')) {
                    parent = $(parent).parent();
                }
                var items = $('ul[id*=sortable] li', parent);
                var hidden = $('input[name=' + name + ']', parent);
                var val = "";
                items.each(function () {
                    val += "|" + $(this).html();
                });
                val = val.substring(1);
                hidden.val(val);
            }
        }).disableSelection();
    });

    $('div.wd_post_type_sortalbe').each(function(){
        var parent = $(this);
        var id = $(this).attr('id').match(/^wd_post_type_sortalbe-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var hidden = $('input[isparam=1]', parent);

        $(selector).sortable({}, {
            update: function (event, ui) {}
        }).disableSelection();
        $(selector).on('sortupdate', function(event, ui) {
            var items = $('ul[id*=sortable] li', parent);
            var val = [];
            items.each(function () {
                val.push($(this).html().trim());
            });
            hidden.val( '_decode_' + WD_Helpers.Base64.encode(JSON.stringify(val)) );
        });
        $(selector).trigger("sortupdate");
    });

    $('div.wpdreamsTaxonomySelect').each(function(){
        var id = $(this).attr('id').match(/^wpdreamsTaxonomySelect-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var name = $('input[isparam=1]', this).attr('name');

        $(selector).sortable({
            connectWith: ".connectedSortable"
        }, {
            update: function (event, ui) {
            }
        }).disableSelection();
        $(selector).on('sortupdate', function(event, ui) {
            if (typeof(ui)!='undefined')
                var parent = $(ui.item).parent();
            else
                var parent = $(event.target);
            while (!parent.hasClass('wpdreamsTaxonomySelect')) {
                parent = $(parent).parent();
            }
            var items = $('ul[id*=sortable_conn] li', parent);
            var hidden = $('input[name=' + name + ']', parent);
            var val = "";
            items.each(function () {
                val += "|" + $(this).attr('taxonomy');
            });
            val = val.substring(1);
            hidden.val(val).trigger('input');
        });
        $("#sortablecontainer" + id + " .arrow-all-left").on('click', function(){
            $("#sortable_conn" + id + " li")
                .detach().appendTo("#sortable" + id + "");
            $(selector).trigger("sortupdate");
        });
        $("#sortablecontainer" + id + " .arrow-all-right").on('click', function(){
            $("#sortable" + id + " li:not(.hiddend)")
                .detach().appendTo("#sortable_conn" + id);
            $(selector).trigger("sortupdate");
        });
    });

    /**
     * Date interval selector
     */
    $('div.wd_DateInterval').each(function() {
        var hidden = $('input[isparam=1]', this);
        var val = hidden.val();

        var $this = $(this);
        var o = {
            "mode": $("select.wd_di_mode", $this),
            "from": $("select.wd_di_from", $this),
            "to": $("select.wd_di_to", $this),
            "fromDate": $("input.wd_di_fromDate", $this),
            "toDate": $("input.wd_di_toDate", $this),
            "fromyy": $("input.wd_di_fromyy", $this),
            "frommm": $("input.wd_di_frommm", $this),
            "fromdd": $("input.wd_di_fromdd", $this),
            "toyy": $("input.wd_di_toyy", $this),
            "tomm": $("input.wd_di_tomm", $this),
            "todd": $("input.wd_di_todd", $this)
        }

        function showHideOptions() {
            if (o.from.val() == "date") {
                $(".wd_di_fromreld", $this).addClass("hiddend");
                o.fromDate.removeClass("hiddend");
            } else if (o.from.val() == "rel_date") {
                $(".wd_di_fromreld", $this).removeClass("hiddend");
                o.fromDate.addClass("hiddend");
            } else {
                $(".wd_di_fromreld", $this).add(o.fromDate).addClass("hiddend");
            }

            if (o.to.val() == "date") {
                $(".wd_di_toreld", $this).addClass("hiddend");
                o.toDate.removeClass("hiddend");
            } else if (o.to.val() == "rel_date") {
                $(".wd_di_toreld", $this).removeClass("hiddend");
                o.toDate.addClass("hiddend");
            } else {
                $(".wd_di_toreld", $this).add(o.toDate).addClass("hiddend");
            }
        }

        function init() {
            var vals = val.split("|");
            var from = vals[5].split(",");
            var to = vals[6].split(",");

            o.mode.val(vals[0]);
            o.from.val(vals[1]);
            o.to.val(vals[2]);
            o.fromDate.val(vals[3]);
            o.toDate.val(vals[4]);

            o.fromyy.val(from[0]);
            o.frommm.val(from[1]);
            o.fromdd.val(from[2]);

            o.toyy.val(to[0]);
            o.tomm.val(to[1]);
            o.todd.val(to[2]);

            o.fromDate.datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true,
                onSelect: parseParams
            });
            o.toDate.datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true,
                onSelect: parseParams
            });
            showHideOptions();
        }

        function parseParams() {
            var res = o.mode.val() + "|" + o.from.val() + "|" + o.to.val();
            res += "|" + o.fromDate.val() + "|" + o.toDate.val();

            res += "|" + ( o.fromyy.val() == "" ? 0 : o.fromyy.val() );
            res += "," + ( o.frommm.val() == "" ? 0 : o.frommm.val() );
            res += "," + ( o.fromdd.val() == "" ? 0 : o.fromdd.val() );

            res += "|" + ( o.toyy.val() == "" ? 0 : o.toyy.val() );
            res += "," + ( o.tomm.val() == "" ? 0 : o.tomm.val() );
            res += "," + ( o.todd.val() == "" ? 0 : o.todd.val() );

            hidden.val(res);
            showHideOptions();
        }

        init();

        $("input", $this).on("keyup", function(){
           parseParams();
        });
        $("select", $this).on("change", function(){
            parseParams();
        });
    });

    /**
     * Date interval selector
     */
    $('div.wd_DateFilter').each(function() {
        var hidden = $('input[isparam=1]', this);
        var val = hidden.val();

        var $this = $(this);
        var o = {
            "state": $("select.wd_di_state", $this),
            "date": $("input.wd_di_date", $this),
            "yy": $("input.wd_di_yy", $this),
            "mm": $("input.wd_di_mm", $this),
            "dd": $("input.wd_di_dd", $this)
        };

        function showHideOptions() {
            if (o.state.val() == "date") {
                $(".wd_di_rel_date", $this).addClass("hiddend");
                $this.find('div.wd_di_date').removeClass("hiddend");
            } else if (o.state.val() == "rel_date") {
                $(".wd_di_rel_date", $this).removeClass("hiddend");
                $this.find('div.wd_di_date').addClass("hiddend");
            } else {
                $(".wd_di_rel_date", $this).addClass("hiddend");
                $this.find('div.wd_di_date').addClass("hiddend");
            }
        }

        function init() {
            var vals = val.split("|");
            var rel_date = vals[2].split(",");

            o.state.val(vals[0]);
            o.date.val(vals[1]);

            o.yy.val(rel_date[0]);
            o.mm.val(rel_date[1]);
            o.dd.val(rel_date[2]);

            o.date.datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true,
                onSelect: parseParams
            });
            showHideOptions();
        }

        function parseParams() {
            var res = o.state.val() + "|" + o.date.val();

            res += "|" + ( o.yy.val() == "" ? 0 : o.yy.val() );
            res += "," + ( o.mm.val() == "" ? 0 : o.mm.val() );
            res += "," + ( o.dd.val() == "" ? 0 : o.dd.val() );

            hidden.val(res);
            showHideOptions();
        }

        init();

        $("input", $this).on("keyup", function(){
            parseParams();
        });
        $("select", $this).on("change", function(){
            parseParams();
        });
    });

    /**
     * Tag search and select
     */
    $('div.wpdreamsSearchTags').each(function() {

        function parseParams( $el ) {
            var tags = [];

            $(".wd_tagSelectContent .wd_tag", $el).each(function(){
                tags.push($(this).attr("tagid"));
            });

            return tags.join("|");
        }

        function refreshResults( $el ) {
            $(".wd_tagSearchResults:not(.hiddend) p span", $el).each(function() {
                if ( $(".wd_tagSelectContent:not(.hiddend) span.wd_tag[tagid=" + $(this).attr("termid") + "]", $this).length == 0 ) {
                    $(this).addClass("not_listed_yo");
                } else {
                    $(this).removeClass("not_listed_yo");
                }
            });
        }

        var hidden = $('input[isparam=1]', this);
        var $this = $(this);

        var t = null;
        var post = null;
        $(".wd_tagSelSearch input.wd_tagSelectSearch", $this).on("keyup", function(){
            var phrase = $(this).val();
            var $inp = $(this);
            var $loader = $(".loading-small", $inp.parent());
            var $x = $(".wd_ts_close", $inp.parent());

            clearTimeout(t);
            if (post != null)
                post.abort();

            if (phrase == "") {
                $loader.addClass('hiddend');
                $(".wd_tagSelSearch .wd_ts_close", $this).trigger('click');
                return;
            }
            let nonce = $(this).parent().find('.wd_tag_search_nonce').val();

            t = setTimeout(function(){
                $loader.removeClass('hiddend');
                $x.removeClass('hiddend');
                var data = {
                    'action': 'wd_search_tags',
                    'wd_tag_phrase': phrase,
                    'wd_tag_search_nonce': nonce,
                    'wd_required': 1
                };
                post = $.post(ajaxurl, data, function(response) {
                    response = response.replace(/^\s*[\r\n]/gm, "");
                    response = response.match(/!!WDSTART!!(.*[\s\S]*)!!WDEND!!/)[1];

                    if (response != "") {
                        $(".wd_tagSearchResults:not(.hiddend)", $this).html(response);
                    }

                    refreshResults($this);

                    $loader.addClass('hiddend');
                }, 'text');
            }, 150);
        });

        $(".wd_tagSelSearch .wd_ts_close", $this).on("click", function(){
            if (post != null)
                post.abort();
            $(".wd_tagSelSearch input.wd_tagSelectSearch", $this).val("");
            $(".wd_tagSearchResults:not(.hiddend)", $this).html("");
            $(".wd_ts_close", $this).addClass('hiddend');
            $(".loading-small", $this).addClass('hiddend');
        });

        $(".wd_tagSearchResults:not(.hiddend)", $this).on("click", "span.not_listed_yo", function() {

            $("<span class='wd_tag' tagid='" + $(this).attr("termid") + "'><a class='wd_tag_remove'></a>" + $(this).parent().text().replace(">>ADD", "") + "</span>")
                .appendTo( $(".wd_tagSelectContent:not(.hiddend)", $this) );

            //check exists
            refreshResults($this);
            hidden.val(parseParams($this));
        });

        /* Tag remove */
        $(".wd_tagSelectContent", $this).on("click", "span.wd_tag a", function() {
            if ( $(this).hasClass("wd_tag_remove") ) {
                // In case of clicking the remove item

                $(this).parent().remove();
                refreshResults($this);
            }
            hidden.val(parseParams($this));
        });
    });

    /**
     * Tag selector
     */
    $('div.wpdreamsSelectTags').each(function(){

        function parseParams( $el ) {
            var active = $(".wpdreamsYesNoSt", $el).hasClass("active") ? 1 : 0;
            var source = $(".wd_tagSelectSource", $el).val();
            var dmode = $(".wd_tagDisplayMode", $el).val();
            var tags = [];
            var selTags = [];
            var defTag = "";

            var allStatus = $("select.wd_tagAllStatus", $el).val();

            $(".wd_tagSelectContent.ts_selected .wd_tag", $el).each(function(){
                tags.push($(this).attr("tagid"));
                if ( $("a.wd_tag_checked", $(this)).length > 0 )
                    selTags.push($(this).attr("tagid"));
            });
            $(".wd_tagSelectContent.ts_all .wd_tag", $el).each(function(){
                defTag = $(this).attr("tagid");
            });

            return active + "|" + dmode + "|" + source+ "|" + allStatus + "|" + tags.join(",") + "|" + selTags.join(",") + "|" + defTag;
        }

        function showHideStuff( $el ) {
            var vals = hidden.val().split("|");
            $(".showif_c", $el).each(function(){
                var rules = $(this).attr("showif").split("|");
                for (var i=0;i<rules.length;i++) {
                    if ( (rules[i].indexOf(vals[1]) > -1) && (rules[i].indexOf(vals[2]) > -1) ) {
                        $(this).removeClass("hiddend");
                        break;
                    } else {
                        $(this).addClass("hiddend");
                    }
                }
            });
        }

        function refreshResults( $el ) {
            $(".wd_tagSearchResults:not(.hiddend) p span", $el).each(function() {
                if ( $(".wd_tagSelectContent:not(.hiddend) span.wd_tag[tagid=" + $(this).attr("termid") + "]", $this).length == 0 ) {
                    $(this).addClass("not_listed_yo");
                } else {
                    $(this).removeClass("not_listed_yo");
                }
            });
        }

        var $this = $(this);
        var id = $(this).attr('id').match(/^wpdreamsSelectTags-(.*)/)[1];
        //var selector = "#sortable" + id +", #sortable_conn" + id;
        var hidden = $('input[isparam=1]', this);

        $(".wd_tagSelectContent", $this).sortable({
            items: ".wd_tag",
            update: function( event, ui ) {
                hidden.val(parseParams($this));
            }
        });

        $(".wpdreamsYesNoSt", $this).on("click", function(){
            $(this).toggleClass("active");
            hidden.val(parseParams($this));
            showHideStuff();
        });
        $(".wd_tagSelectSource", $this).on("change", function(){
            hidden.val(parseParams($this));
            showHideStuff();
            refreshResults($this);
        });
        $(".wd_tagDisplayMode", $this).on("change", function(){
            hidden.val(parseParams($this));
            showHideStuff();
            refreshResults($this);
        });

        $("select.wd_tagAllStatus", $this).on("change", function(){
            hidden.val(parseParams($this));
            showHideStuff();
            refreshResults($this);
        });

        var t = null;
        var post = null;
        $(".wd_tagSelSearch input.wd_tagSelectSearch", $this).on("keyup", function(){
            var phrase = $(this).val();
            var $inp = $(this);
            var $loader = $(".loading-small", $inp.parent());
            var $x = $(".wd_ts_close", $inp.parent());

            clearTimeout(t);
            if (post != null)
                post.abort();

            if (phrase == "") {
                $loader.addClass('hiddend');
                $(".wd_tagSelSearch .wd_ts_close", $this).trigger('click');
                return;
            }
            let nonce = $(this).parent().find('.wd_tag_search_nonce').val();

            t = setTimeout(function(){
                $loader.removeClass('hiddend');
                $x.removeClass('hiddend');
                var data = {
                    'action': 'wd_search_tag',
                    'wd_tag_phrase': phrase,
                    'wd_tag_search_nonce': nonce,
                    'wd_required': 1
                };
                post = $.post(ajaxurl, data, function(response) {
                    response = response.replace(/^\s*[\r\n]/gm, "");
                    response = response.match(/!!WDSTART!!(.*[\s\S]*)!!WDEND!!/)[1];

                    if (response != "") {
                        $(".wd_tagSearchResults:not(.hiddend)", $this).html(response);
                    }

                    refreshResults($this);

                    $loader.addClass('hiddend');
                }, 'text');
            }, 150);
        });

        $(".wd_tagSelSearch .wd_ts_close", $this).on("click", function(){
            if (post != null)
                post.abort();
            $(".wd_tagSelSearch input.wd_tagSelectSearch", $this).val("");
            $(".wd_tagSearchResults:not(.hiddend)", $this).html("");
            $(".wd_ts_close", $this).addClass('hiddend');
            $(".loading-small", $this).addClass('hiddend');
        });

        $(".wd_tagSearchResults:not(.hiddend)", $this).on("click", "span.not_listed_yo", function() {
            var oneTagOnly = $(".wd_tagSelectContent:not(.hiddend)", $this).hasClass("ts_all");

            if ( oneTagOnly ) {
                $(".wd_tagSelectContent:not(.hiddend)", $this).html(
                    ("<span class='wd_tag' tagid='" + $(this).attr("termid") + "'><a class='wd_tag_remove'></a>" + $(this).parent().text().replace(">>USE", "") + "</span>")
                );
            } else {
                $("<span class='wd_tag' tagid='" + $(this).attr("termid") + "'><a class='wd_tag_remove'></a><a class='wd_tag_checked'></a>" + $(this).parent().text().replace(">>USE", "") + "</span>")
                    .appendTo( $(".wd_tagSelectContent:not(.hiddend)", $this) );
            }

            //check exists
            refreshResults($this);
            hidden.val(parseParams($this));
        });

        /* Tag remove */
        $(".wd_tagSelectContent", $this).on("click", "span.wd_tag a", function() {
            if ( $(this).hasClass("wd_tag_remove") ) {
                // In case of clicking the remove item

                $(this).parent().remove();
                refreshResults($this);
            } else {
                // In case if clicking the tick

                if ( $(this).hasClass("wd_tag_checked") ) {
                    $(this).removeClass("wd_tag_checked");
                    $(this).addClass("wd_tag_unchecked")
                } else {
                    $(this).addClass("wd_tag_checked");
                    $(this).removeClass("wd_tag_unchecked")
                }
            }

            hidden.val(parseParams($this));
        });
    });


    /**
     * BuddyPress XProfile fields selector
     */
    $('div.wpdreamsBP_XProfileFields').each(function(){
        var id = $(this).attr('id').match(/^wpdreamsBP_XProfileFields-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var name = $('input[isparam=1]', this).attr('name');

        $(selector).sortable({
            connectWith: ".connectedSortable"
        }, {
            update: function (event, ui) {

            }
        }).disableSelection();
        $(selector).on('sortupdate', function(event, ui) {
            if (typeof(ui)!='undefined')
                var parent = $(ui.item).parent();
            else
                var parent = $(event.target);
            while (!parent.hasClass('wpdreamsBP_XProfileFields')) {
                parent = $(parent).parent();
            }
            var items = $('ul[id*=sortable_conn] li', parent);
            var hidden = $('input[name=' + name + ']', parent);
            var val = "";
            items.each(function () {
                val += "|" + $(this).attr('bid');
            });
            val = val.substring(1);
            hidden.val(val);
        });
        $("#sortablecontainer" + id + " .arrow-all-left").on('click', function(){
            $("#sortable_conn" + id + " li")
                .detach().appendTo("#sortable" + id + "");
            $(selector).trigger("sortupdate");
        });
        $("#sortablecontainer" + id + " .arrow-all-right").on('click', function(){
            $("#sortable" + id + " li:not(.hiddend)")
                .detach().appendTo("#sortable_conn" + id);
            $(selector).trigger("sortupdate");
        });
    });

    /**
     * Draggable selector
     */
    $('div.wpdreamsDraggable').each(function(){
        var id = $(this).attr('id').match(/^wpdreamsDraggable-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var name = $('input[isparam=1]', this).attr('name');

        $(selector).sortable({
            connectWith: ".connectedSortable"
        }, {
            update: function (event, ui) {
            }
        }).disableSelection();

        $(selector).on('sortupdate', function(event, ui) {
            if (typeof(ui)!='undefined')
                var parent = $(ui.item).parent();
            else
                var parent = $(event.target);
            while (!parent.hasClass('wpdreamsDraggable')) {
                parent = $(parent).parent();
            }
            var items = $('ul[id*=sortable_conn] li', parent);
            var hidden = $('input[name=' + name + ']', parent);
            var val = "";
            items.each(function () {
                val += "|" + $(this).attr('key');
            });
            val = val.substring(1);
            hidden.val(val);
        });
    });

    /**
     * User role select
     */
    $('div.wpdreamsUserRoleSelect').each(function(){
        var id = $(this).attr('id').match(/^wpdreamsUserRoleSelect-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var name = $('input[isparam=1]', this).attr('name');

        $(selector).sortable({
            connectWith: ".connectedSortable"
        }, {
            update: function (event, ui) {
            }
        }).disableSelection();

        $(selector).on('sortupdate', function(event, ui) {
            if (typeof(ui)!='undefined')
                var parent = $(ui.item).parent();
            else
                var parent = $(event.target);
            while (!parent.hasClass('wpdreamsUserRoleSelect')) {
                parent = $(parent).parent();
            }
            var items = $('ul[id*=sortable_conn] li', parent);
            var hidden = $('input[name=' +name + ']', parent);
            var val = "";
            items.each(function () {
                val += "|" + $(this).html();
            });
            val = val.substring(1);
            hidden.val(val);
        });

        $("#sortablecontainer" + id + " .arrow-all-left").on('click', function(){
            $("#sortable_conn" + id + " li")
                .detach().appendTo("#sortable" + id + "");
            $(selector).trigger("sortupdate");
        });
        $("#sortablecontainer" + id + " .arrow-all-right").on('click', function(){
            $("#sortable" + id + " li:not(.hiddend)")
                .detach().appendTo("#sortable_conn" + id);
            $(selector).trigger("sortupdate");
        });
    });

    /**
     * Custom Post types
     */
    $('div.wpdreamsCustomPostTypes').each(function(){
        var id = $(this).attr('id').match(/^wpdreamsCustomPostTypes-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var parent = $(this);
        while (!parent.hasClass('wpdreamsCustomPostTypes')) {
            parent = $(parent).parent();
        }
        var items = $('ul[id*=sortable_conn] li', parent);
        var hidden = $('input[isparam=1]', this);

        function parse_val() {
            var val = [];
            // Items need to be re-parsed!
            var items = $('ul[id*=sortable_conn] li', parent);
            items.each(function () {
                val.push($(this).data('ptype'));
            });
            hidden.val("_decode_" + WD_Helpers.Base64.encode(JSON.stringify(val)));
        }

        $(selector).sortable({
            connectWith: ".connectedSortable"
        }, {
            update: function (event, ui) {
                parse_val();
            }
        }).disableSelection();
    });

    /**
     * Custom Post types, built in version
     */
    $('div.wpdreamsCustomPostTypesAll').each(function(){
        var id = $(this).attr('id').match(/^wpdreamsCustomPostTypesAll-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var name = $('input[isparam=1]', this).attr('name');

        $(selector).sortable({
            connectWith: ".connectedSortable"
        }, {
            update: function (event, ui) {

            }
        }).disableSelection();
        $(selector).on('sortupdate', function(event, ui) {
            if (typeof(ui)!='undefined')
                var parent = $(ui.item).parent();
            else
                var parent = $(event.target);
            while (!parent.hasClass('wpdreamsCustomPostTypesAll')) {
                parent = $(parent).parent();
            }
            var items = $('ul[id*=sortable_conn] li', parent);
            var hidden = $('input[name=' + name + ']', parent);
            var val = "";
            items.each(function () {
                val += "|" + $(this).html();
            });
            val = val.substring(1);
            hidden.val(val);
        });

        $("#sortablecontainer" + id + " .arrow-all-left").on('click', function(){
            $("#sortable_conn" + id + " li")
                .detach().appendTo("#sortable" + id + "");
            $(selector).trigger("sortupdate");
        });
        $("#sortablecontainer" + id + " .arrow-all-right").on('click', function(){
            $("#sortable" + id + " li:not(.hiddend)")
                .detach().appendTo("#sortable_conn" + id);
            $(selector).trigger("sortupdate");
        });
    });

    /**
     * Custom post types, editable version
     */
    $('div.wpdreamsCustomPostTypesEditable').each(function(){
        var id = $(this).attr('id').match(/^wpdreamsCustomPostTypesEditable-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var name = $('input[isparam=1]', this).attr('name');

        $(selector).on('keyup', function() {
            var parent = $(this).parent();
            while (!parent.hasClass('wpdreamsCustomPostTypesEditable')) {
                parent = $(parent).parent();
            }
            var items = $('ul[id*=sortable_conn] li', parent);
            var hidden = $('input[name=' + name + ']', parent);
            var val = "";
            items.each(function () {
                val += "|" + $('label', this).html() + ";" + $('input', this).val();
            });
            val = val.substring(1);
            hidden.val(val);
        });
        $(selector).sortable({
            connectWith: ".connectedSortable"
        }, {
            update: function (event, ui) {
                $("#sortable_conn" + id + " li input").on('keyup', function() {
                    var parent = $(this).parent();
                    while (!parent.hasClass('wpdreamsCustomPostTypesEditable')) {
                        parent = $(parent).parent();
                    }
                    var items = $('ul[id*=sortable_conn] li', parent);
                    var hidden = $('input[name=' + name + ']', parent);
                    var val = "";
                    items.each(function () {
                        val += "|" + $('label', this).html() + ";" + $('input', this).val();
                    });
                    val = val.substring(1);
                    hidden.val(val);
                });
                if ($("#sortable_conn" + id + " li input").length != 0) {
                    $("#sortable_conn" + id + " li input").trigger('keyup');
                } else {
                    $("#sortable_conn" + id).each(function () {
                        var parent = $(this).parent();
                        while (!parent.hasClass('wpdreamsCustomPostTypesEditable')) {
                            parent = $(parent).parent();
                        }
                        var hidden = $('input[name=' + name + ']', parent);
                        hidden.val("");
                    });
                }
            }
        });
    });

    /**
     * Custom post types, editable version NEW
     */
    $('div.wd_cpt_editable').each(function(){
        var id = $(this).attr('id').match(/^wd_cpt_editable-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var parent = $(this);
        while (!parent.hasClass('wd_cpt_editable')) {
            parent = $(parent).parent();
        }
        var items = $('ul[id*=sortable_conn] li', parent);
        var hidden = $('input[isparam=1]', this);

        function parse_val() {
            var val = [];
            // Items need to be re-parsed!
            var items = $('ul[id*=sortable_conn] li', parent);
            items.each(function () {
                val.push({
                    "post_type": $('label', this).html(),
                    "name": $('input', this).val()
                });
            });
            hidden.val("_decode_" + WD_Helpers.Base64.encode(JSON.stringify(val)));
        }

        parent.on("keyup", "ul[id*=sortable_conn] li input", function(){
            parse_val();
        });

        $(selector).sortable({
            connectWith: ".connectedSortable"
        }, {
            update: function (event, ui) {
                parse_val();
            }
        });
    });

    /**
     * Custom field selectors
     */
    window.wd_cf_ajax_callback = function(r, $node, o, parent) {
        var $cf_parent = $node.closest('.wpdreamsCustomFields');
        var $drg = $(".draggablecontainer ul", $cf_parent);
        var id = $cf_parent.attr('id').match(/^wpdreamsCustomFields-(.*)/)[1];
        var html = '';
        var drag_opts = {
            connectToSortable: "#sortable_conn" + id,
            update: function (event, ui) {},
            cancel: ".ui-state-disabled",
            helper: "clone",
            items: "> li"
        };
        if ( r.length > 0 ) {
            $.each(r, function(i, v) {
                var title = v.meta_key.replace('__pods__', '[PODs] ');
                html += '<li class="ui-state-default" cf_name="'+v.meta_key+'">'+title+'<a class="deleteIcon"></a></li>';
            });
            $drg.html(html);
            $("#sortable" + id + " li").draggable(drag_opts).disableSelection();
            $("#sortable" + id + " li").trigger('sortupdate');
            $("#sortable_conn" + id).trigger('sortupdate');
        } else {
            $drg.html('No results for this phrase.');
        }
    }
    $('div.wpdreamsCustomFields').each(function(){
        var id = $(this).attr('id').match(/^wpdreamsCustomFields-(.*)/)[1];
        var name = $('input[isparam=1]', this).attr('name');
        var parent = $(this);
        var hidden = $('input[isparam=1]', this);

        function list_update() {
            $("#sortable" + id + " li").removeClass("ui-state-disabled");
            $('ul[id*=sortable_conn] li', parent).each(function (i, v) {
                $("#sortable" + id + " li[cf_name='"+$(this).attr('cf_name')+"']").addClass("ui-state-disabled");
            });
        }

        function data_update() {
            var items = $("#sortable_conn" + id + " li")
            var val = "";
            items.each(function () {
                val += "|" + $(this).attr('cf_name');
            });
            val = val.substring(1);
            hidden.val(val);
        }

        $("#sortable_conn" + id).sortable({
            update: function (event, ui) {
                var $item = $(ui.item);
                $item.css({
                    "width": "",
                    "height": ""
                });
                list_update();
                data_update();
            },
            items: "> li",
            cancel: ".ui-state-disabled",
            remove: function(event, ui) {}
        }).disableSelection();

        $("#sortable_conn" + id).on('sortupdate', function(event, ui) {
            list_update();
            data_update();
        });

        $("#sortable_conn" + id).on( "click", "li a.deleteIcon", function(e){
            e.preventDefault();
            $(this).parent().detach();
            list_update();
            data_update();
        });

        $("#draggablecontainer" + id + " .arrow-all-left").on('click', function(){
            $("#sortable_conn" + id + " li").detach();
            $("#sortable_conn" + id).trigger('sortupdate');
        });
        $("#draggablecontainer" + id + " .arrow-all-right").on('click', function(){
            $("#sortable" + id + " li:not(.hiddend):not(.ui-state-disabled)").clone().appendTo("#sortable_conn" + id);
            $("#sortable_conn" + id).trigger('sortupdate');
        });
    });
    /**
     *
    $('div.wpdreamsCustomFields').each(function(){
        var id = $(this).attr('id').match(/^wpdreamsCustomFields-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var name = $('input[isparam=1]', this).attr('name');

        $(selector).sortable({
            connectWith: ".connectedSortable"
        }, {
            update: function (event, ui) {
            }
        }).disableSelection();

        $(selector).on('sortupdate', function(event, ui) {
            if (typeof(ui)!='undefined')
                var parent = $(ui.item).parent();
            else
                var parent = $(event.target);
            while (!parent.hasClass('wpdreamsCustomFields')) {
                parent = $(parent).parent();
            }
            var items = $('ul[id*=sortable_conn] li', parent);
            var hidden = $('input[name=' + name + ']', parent);
            var val = "";
            items.each(function () {
                val += "|" + $(this).html();
            });
            val = val.substring(1);
            hidden.val(val);
        });

        $("#sortablecontainer" + id + " .arrow-all-left").on('click', function(){
            $("#sortable_conn" + id + " li")
                .detach().appendTo("#sortable" + id + "");
            $(selector).trigger("sortupdate");
        });
        $("#sortablecontainer" + id + " .arrow-all-right").on('click', function(){
            $("#sortable" + id + " li:not(.hiddend)")
                .detach().appendTo("#sortable_conn" + id);
            $(selector).trigger("sortupdate");
        });
    });
    */

    /**
     * User meta selectors
     */
    window.wd_um_ajax_callback = function(r, $node, o, parent) {
        var $cf_parent = $node.closest('.wd_UserMeta');
        var $drg = $(".draggablecontainer ul", $cf_parent);
        var id = $cf_parent.attr('id').match(/^wd_UserMeta-(.*)/)[1];
        var html = '';
        var drag_opts = {
            connectToSortable: "#sortable_conn" + id,
            update: function (event, ui) {},
            cancel: ".ui-state-disabled",
            helper: "clone",
            items: "> li"
        };
        if ( r.length > 0 ) {
            $.each(r, function(i, v) {
                html += '<li class="ui-state-default" cf_name="'+v.meta_key+'">'+v.meta_key+'<a class="deleteIcon"></a></li>';
            });
            $drg.html(html);
            $("#sortable" + id + " li").draggable(drag_opts).disableSelection();
            $("#sortable" + id + " li").trigger('sortupdate');
            $("#sortable_conn" + id).trigger('sortupdate');
        } else {
            $drg.html('No results for this phrase.');
        }
    }
    $('div.wd_UserMeta').each(function(){
        var id = $(this).attr('id').match(/^wd_UserMeta-(.*)/)[1];
        var name = $('input[isparam=1]', this).attr('name');
        var parent = $(this);
        var hidden = $('input[isparam=1]', this);

        function list_update() {
            $("#sortable" + id + " li").removeClass("ui-state-disabled");
            $('ul[id*=sortable_conn] li', parent).each(function (i, v) {
                $("#sortable" + id + " li[cf_name='"+$(this).attr('cf_name')+"']").addClass("ui-state-disabled");
            });
        }

        function data_update() {
            var items = $("#sortable_conn" + id + " li")
            var fields = [];
            items.each(function () {
                fields.push($(this).attr('cf_name'));
            });
            hidden.val("_decode_" + WD_Helpers.Base64.encode(JSON.stringify(fields)));
        }

        $("#sortable_conn" + id).sortable({
            update: function (event, ui) {
                var $item = $(ui.item);
                $item.css({
                    "width": "",
                    "height": ""
                });
                list_update();
                data_update();
            },
            items: "> li",
            cancel: ".ui-state-disabled",
            remove: function(event, ui) {}
        }).disableSelection();

        $("#sortable_conn" + id).on('sortupdate', function(event, ui) {
            list_update();
            data_update();
        });

        $("#sortable_conn" + id).on( "click", "li a.deleteIcon", function(e){
            e.preventDefault();
            $(this).parent().detach();
            list_update();
            data_update();
        });

        $("#draggablecontainer" + id + " .arrow-all-left").on('click', function(){
            $("#sortable_conn" + id + " li").detach();
            $("#sortable_conn" + id).trigger('sortupdate');
        });
        $("#draggablecontainer" + id + " .arrow-all-right").on('click', function(){
            $("#sortable" + id + " li:not(.hiddend):not(.ui-state-disabled)").clone().appendTo("#sortable_conn" + id);
            $("#sortable_conn" + id).trigger('sortupdate');
        });
    });

    /**
     * Term Meta selectors
     */
    $('div.wwpdreamsTermMeta').each(function(){
        var id = $(this).attr('id').match(/^wpdreamsTermMeta-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var name = $('input[isparam=1]', this).attr('name');

        $(selector).sortable({
            connectWith: ".connectedSortable"
        }, {
            update: function (event, ui) {
            }
        }).disableSelection();

        $(selector).on('sortupdate', function(event, ui) {
            if (typeof(ui)!='undefined')
                var parent = $(ui.item).parent();
            else
                var parent = $(event.target);
            while (!parent.hasClass('wpdreamsTermMeta')) {
                parent = $(parent).parent();
            }
            var items = $('ul[id*=sortable_conn] li', parent);
            var hidden = $('input[name=' + name + ']', parent);
            var val = "";
            items.each(function () {
                val += "|" + $(this).html();
            });
            val = val.substring(1);
            hidden.val(val);
        });

        $("#sortablecontainer" + id + " .arrow-all-left").on('click', function(){
            $("#sortable_conn" + id + " li")
                .detach().appendTo("#sortable" + id + "");
            $(selector).trigger("sortupdate");
        });
        $("#sortablecontainer" + id + " .arrow-all-right").on('click', function(){
            $("#sortable" + id + " li:not(.hiddend)")
                .detach().appendTo("#sortable_conn" + id);
            $(selector).trigger("sortupdate");
        });
    });

    /**
     * Taxonomy term selector
     */
    $('div.wpdreamsCustomTaxonomyTerm').each(function(){
        var id = $(this).attr('id').match(/^wpdreamsCustomTaxonomyTerm-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var name = $('input[isparam=1]', this).attr('name');
        $(selector).sortable({
            connectWith: ".connectedSortable"
        }, {
            update: function (event, ui) {
            }
        }).disableSelection();
        $("#taxonomy_selector_" + id).on('change', function() {
            var taxonomy = $(this).val();
            $("li", "#sortable" + id).css('display', 'none').addClass('hiddend');
            $("li[taxonomy='" + taxonomy + "']", "#sortable" + id).css('display', 'block').removeClass('hiddend');
        });
        $("#taxonomy_selector_" + id).trigger('change');
        $(selector).on('sortupdate', function(event, ui) {
            if (typeof(ui)!='undefined')
                var parent = $(ui.item).parent();
            else
                var parent = $(event.target);
            while (!parent.hasClass('wpdreamsCustomTaxonomyTerm')) {
                parent = $(parent).parent();
            }
            var items = $('ul[id*=sortable_conn] li', parent);
            var hidden = $('input[name=' + name + ']', parent);
            var val = "";
            items.each(function () {
                val += "|" + $(this).attr('term_id') + "-" + $(this).attr('taxonomy');
            });
            val = val.substring(1);
            hidden.val(val);
        });
        $("#wpdreamsCustomTaxonomyTerm-" + id + " .hide-children").on('change', function(){
            if ($(this).get(0).checked)
                $("#sortablecontainer" + id + " li").filter(':not(.termlevel-0)').css('display', 'none');
            else
                $("#sortablecontainer" + id + " li").filter(':not(.termlevel-0)').css('display', 'block');
            $(selector).trigger("sortupdate");
        });
        $("#sortablecontainer" + id + " .arrow-all-left").on('click', function(){
            $("#sortable_conn" + id + " li")
                .detach().appendTo("#sortable" + id + "");
            $(selector).trigger("sortupdate");
        });
        $("#sortablecontainer" + id + " .arrow-all-right").on('click', function(){
            $("#sortable" + id + " li:not(.hiddend)")
                .detach().appendTo("#sortable_conn" + id);
            $(selector).trigger("sortupdate");
        });
    });

    /**
     * Draggable FIELD
     */
    $('div.wd_DraggableFields').each(function(){
        var id = $(this).attr('id').match(/^wd_DraggableFields-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var hidden = $('input[isparam=1]', this);
        var name = hidden.attr('name');
        var parent = $(this);
        var actualData = JSON.parse( WD_Helpers.Base64.decode(hidden.val().replace(/^(_decode_)/,"")) );

        function tt_s_update( parent ) {
            // Items need to be re-parsed!
            var items = $('ul[id*=sortable_conn] li', parent);
            var selected = [];
            var unselected = [];
            var checked = [];
            var labels = {};
            var display_mode = $('.wd_df_display_mode', parent).length > 0 ? $('.wd_df_display_mode', parent).val() : 'checkboxes';
            items.each(function (i, v) {
                selected.push($(this).attr('field'));
                if ( $("input[type='checkbox']", this).length > 0 && $("input[type='checkbox']", this).get(0).checked )
                    checked.push($(this).attr('field'));
                if ( $("input[type='text']", this).length > 0 ) {
                    labels[$(this).attr('field')] = $("input[type='text']", this).val();
                }
            });
            // Unselected
            $('.draggablecontainer ul[id*=sortable] li:not(.ui-state-disabled)', parent).each(function (i, v) {
                unselected.push($(this).attr('field'));
            });
            actualData.display_mode = display_mode;
            actualData.selected = selected;
            actualData.unselected = unselected;
            actualData.checked = checked;
            actualData.labels = labels;
            actualData.required = $('input.wd_df_required', parent).length > 0 ? $('input.wd_df_required', parent).is(':checked') : false;
            actualData.invalid_input_text = $('input.wd_df_invalid_input_text', parent).length > 0 ? $('input.wd_df_invalid_input_text', parent).val() : '';
            hidden.val("_decode_" + WD_Helpers.Base64.encode(JSON.stringify(actualData)));
        }

        function list_update() {
            if ( $('input.wd_df_required', parent).length > 0 ) {
                if ( $('input.wd_df_required', parent).is(':checked') ) {
                    $('input.wd_df_invalid_input_text', parent).closest('label').removeClass('disabled');
                } else {
                    $('input.wd_df_invalid_input_text', parent).closest('label').addClass('disabled');
                }
            }
            $("#sortable" + id + " li").removeClass("ui-state-disabled");
            $('ul[id*=sortable_conn] li', parent).each(function (i, v) {
                $("#sortable" + id + " li[field='"+$(this).attr('field')+"']").addClass("ui-state-disabled");
            });
        }

        var drag_opts = {
            connectToSortable: "#sortable_conn" + id,
            update: function (event, ui) {},
            drag: function(e, ui) {
                if ( typeof ui.helper != 'undefined')
                    $(ui.helper).css({ height: 84, width: 260, zIndex: 1 });   //drag dimensions
            },
            cancel: ".ui-state-disabled",
            helper: "clone"
        };
        $("#sortable" + id + " li").draggable(drag_opts).disableSelection();

        $("#sortable_conn" + id).sortable({
            update: function (event, ui) {
                var $item = $(ui.item);
                $item.css({
                    "width": "",
                    "height": ""
                });
                tt_s_update( parent );
                list_update();
            }
        }).disableSelection();


        $(selector).on('sortupdate', function(event, ui) {
            tt_s_update( parent );
            list_update();
        });
        $("#sortable_conn" + id).on('change keyup', 'li input', function(){
            tt_s_update( parent );
            list_update();
        });
        var t;
        $("#sortable_conn" + id).on( "click", "li a.deleteIcon", function(e){
            e.preventDefault();
            $(this).parent().detach();
            //tt_s_update( parent );
            list_update();
            clearTimeout(t);
            t = setTimeout(function(){
                $(selector).trigger('sortupdate');
            }, 500);
        });

        $(".wd_df_display_mode, .wd_df_invalid_input_text, .wd_df_required", parent).on('change', function(){
            tt_s_update( parent );
            list_update();
        });

        tt_s_update( parent );
        list_update();
    });

    /**
     * Taxonomy Term include-exclude selector with AJAX support
     */
    $('div.wd_TaxonomyTermSelect').each(function(){

        // 1. ----------------- Sortable list --------------------
        var id = $(this).attr('id').match(/^wd_TaxonomyTermSelect-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var hidden = $('input[isparam=1]', this);
        var name = hidden.attr('name');
        var parent = $(this);
        var actualData = JSON.parse( WD_Helpers.Base64.decode(hidden.val().replace(/^(_decode_)/,"")) );
        var termData = JSON.parse( WD_Helpers.Base64.decode($("input.wd_term_data", parent).val()) );
        var tax_cache = {};
        var active_tax = "";

        function tt_s_update( parent ) {
            // Items need to be re-parsed!
            var items = $('ul[id*=sortable_conn] li', parent);
            var terms = [];
            var select_all = {
                "enabled": 0,
                "taxonomies": [],
                "text": ""
            };

            var un_checked = [];

            items.each(function (i, v) {
                var ex_ids = [];
                if ( $(this).attr('term_id') == -1 && typeof(termData[$(this).attr('taxonomy')]) != "undefined" ) {
                    $.each(termData[$(this).attr('taxonomy')], function(i, v){
                        if ( typeof(v) != "undefined" && v != null)
                            ex_ids.push(i);
                    });
                    terms.push({
                        "taxonomy": $(this).attr('taxonomy'),
                        "id": $(this).attr('term_id'),
                        "level": (typeof $(this).attr('term_level') != "undefined") ? $(this).attr('term_level') : 0,
                        "ex_ids": ex_ids
                    });
                } else {
                    terms.push({
                        "taxonomy": $(this).attr('taxonomy'),
                        "id": $(this).attr('term_id'),
                        "level": (typeof $(this).attr('term_level') != "undefined") ? $(this).attr('term_level') : 0,
                        "ex_ids": ex_ids
                    });
                }
                if ($("input[type='checkbox']", $(this)).length > 0 && !$("input[type='checkbox']", $(this)).get(0).checked)
                    un_checked.push($(this).attr('term_id'));
            });
            actualData.terms = terms;
            actualData.op_type = $(".tts_operation", parent).val();
            actualData.un_checked = un_checked;
            actualData.select_all = select_all;
            display_mode_parse();
            hidden.val("_decode_" + WD_Helpers.Base64.encode(JSON.stringify(actualData)));
        }

        function list_update() {
            $("#sortable" + id + " li").removeClass("ui-state-disabled");
            $('ul[id*=sortable_conn] li', parent).each(function (i, v) {
                if ( $(this).attr('term_id') == -2 )
                    $("#sortable" + id + " li[term_id='"+$(this).attr('term_id')+"']").addClass("ui-state-disabled");
                else
                    $("#sortable" + id + " li[term_id='"+$(this).attr('term_id')+"'][taxonomy='"+$(this).attr('taxonomy')+"']").addClass("ui-state-disabled");
            });
        }

        function res_update() {
            if ( typeof(termData[active_tax]) == "undefined" )
                return false;
            $("span", $res).each(function(){
                var term_id = $(this).attr("term_id");
                if (
                    typeof(termData[active_tax][term_id]) != "undefined" &&
                    termData[active_tax][term_id] != null
                ) {
                    $(this).addClass("wd_disabled");
                } else {
                    $(this).removeClass("wd_disabled");
                }
            });
        }

        var drag_opts = {
            connectToSortable: "#sortable_conn" + id,
            update: function (event, ui) {},
            cancel: ".ui-state-disabled",
            helper: "clone"
        };
        $("#sortable" + id + " li").draggable(drag_opts).disableSelection();

        $("#sortable_conn" + id).sortable({
            update: function (event, ui) {
                var $item = $(ui.item);
                $item.css({
                    "width": "",
                    "height": ""
                });
                if ( $item.attr("term_id") == '-2' ) {
                    var html = "";
                    $(".wd_tts_ajax_selector option", parent).each(function(i, v){
                        if ( typeof $(this).attr("disabled") == "undefined" )
                            html += '<input type="checkbox" value="'+$(this).attr("value")+'" checked="checked">'+$(this).attr("value")+' <br>';
                    });
                    $(".wd_tts_selectall_tax", $item).html(html);
                } else if ( $item.attr("term_id") == '-1' ) {
                    $("#sortable_conn" + id + " li[term_id!='-1'][taxonomy='" + $item.attr('taxonomy') + "']").detach();
                } else {
                    $("#sortable_conn" + id + " li[term_id='-1'][taxonomy='" + $item.attr('taxonomy') + "']").detach();
                }
                // Refresh cache with remaining
                tax_cache[$item.attr('taxonomy')] = $("#sortable" + id).html();
                tt_s_update( parent );
                list_update();
                $("#sortable_conn" + id).find("input")
                    .bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function(e) {
                        e.stopImmediatePropagation();
                    });
            },
            cancel: ".ui-state-disabled",
            remove: function(event, ui) {
                var $item = $(ui.item);
                tax_cache = {};
            }
        }).disableSelection();

        $("#sortable_conn" + id).find("input")
            .bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function(e) {
                e.stopImmediatePropagation();
            });

        $("#sortable_conn" + id).on("change", "li[term_id='-2'] input.wd_tts_for_all", function(){
            if ( $(this).is(':checked') )
               $(".wd_tts_selectall_tax", $(this).parent().parent()).css("display", "none");
            else
               $(".wd_tts_selectall_tax", $(this).parent().parent()).css("display", "");
        });
        $("#sortable_conn" + id + " li[term_id='-2'] input.wd_tts_for_all").trigger('change');

        $("#sortable_conn" + id).on("change keyup", "li[term_id='-2'] input", function(){
            tt_s_update( parent );
        });

        $("#sortable_conn" + id).on("change", "li input[type='checkbox']", function(){
            tt_s_update( parent );
        });

        $("#tax_ajax_selector_" + id).on('change', function() {
            var tax = $(this).val();
            if ( typeof (tax_cache[tax]) != "undefined" ) {
                $("#sortable" + id).html(tax_cache[tax]);
                $("#sortable" + id + " li").draggable(drag_opts).disableSelection();
                list_update();
                $(".hide-children", parent).trigger('change');
            } else {
                $('.dragLoader', parent).removeClass("hiddend");
                var data = {
                    'action': 'wd_print_taxonomy_terms',
                    'wd_required': 1,
                    'wd_taxonomy_search_nonce': $('.wd_taxonomy_search_nonce', parent).val(),   
                    'wd_args': $("input.wd_args", parent).val(),
                    'wd_taxonomy': $(this).val()
                };
                $.post(ajaxurl, data, function (response) {
                    if (response.length > 0) {
                        tax_cache[tax] = response;
                        $("#sortable" + id).html(response);
                        $("#sortable" + id + " li").draggable(drag_opts).disableSelection();
                        list_update();
                        $('.dragLoader', parent).addClass("hiddend");
                        $(".hide-children", parent).trigger('change');
                    }
                }, "text");
            }
        });
        // --------------------------------------------------------------

        // 2. ----------------- More options (taxonomy) --------------------
        var t = null;
        var p = null;
        var $loading = $(".wd_tts_ex_container span.loading-small", parent);
        var $close = $(".wd_tts_ex_container div.wd_ts_close", parent);
        var $input = $(".wd_tts_ex_container input", parent);
        var $res = $(".sortablecontainer .wd_tts_res", parent);
        var $ex_term_container = $(".wd_tts_excluded_t", parent);
        $input.on('keyup', function(){
            clearTimeout(t);
            var $this = $(this);
            $close.addClass("hiddend");
            t = setTimeout(function(){
                $loading.removeClass("hiddend");
                var data = {
                    'action': 'wd_search_taxonomy_terms',
                    'wd_s': $this.val(),
                    'wd_required': 1,
                    'wd_taxonomy_search_nonce': $('.wd_taxonomy_search_nonce', parent).val(),
                    'wd_args': $("input.wd_args", parent).val(),
                    'wd_taxonomy': active_tax
                };
                if (p != null) p.abort();
                p = $.post(ajaxurl, data, function (response) {
                    if (response.length > 0) {
                        $res.html(response);
                    }
                    $loading.addClass("hiddend");
                    $close.removeClass("hiddend");
                }, "text");
            }, 500);
        });

        $close.on('click', function(){
            clearTimeout(t);
            if (p != null) p.abort();
            $input.val("");
            $(this).addClass("hiddend");
            $res.html("");
        });
        $(selector).on('sortupdate', function(event, ui) {
            tt_s_update( parent );
            list_update();
        });
        $("#sortable_conn" + id +" li input").on('change', function(){
            tt_s_update( parent );
            list_update();
        });
        $("#sortable_conn" + id).on( "click", "li a.deleteIcon", function(e){
            e.preventDefault();
            $(this).parent().detach();
            tt_s_update( parent );
            list_update();
        });

        $($res).on("click", "span", function(){
            var term_id = $(this).attr("term_id");
            if (
                typeof(termData[active_tax]) != "undefined" &&
                typeof(termData[active_tax][term_id]) != "undefined" &&
                termData[active_tax][term_id] != null
            ) return false;

            // Clear the term container if there is nothing in it.
            if ( $("span", $ex_term_container).length == 0)
                $ex_term_container.html("");

            $ex_term_container.html($ex_term_container.html() + $(this).get(0).outerHTML);

            if (typeof(termData[active_tax]) == "undefined")
                termData[active_tax] = [];

            termData[active_tax][term_id] = $(this).html().trim();
            res_update();
            tt_s_update(parent);
        });

        $($ex_term_container).on("click", "span", function(){
            var term_id = $(this).attr("term_id");
            if (
                typeof(termData[active_tax]) == "undefined" ||
                typeof(termData[active_tax][term_id]) == "undefined" &&
                termData[active_tax][term_id] == null
            ) return false;

            delete termData[active_tax][term_id];
            $(this).detach();

            // Clear the term container if there is nothing in it.
            if ( $("span", $ex_term_container).length == 0)
                $ex_term_container.html("Nothing is excluded yet");

            res_update();
            tt_s_update(parent);
        });

        $(parent).on("click", "div.sortablecontainer a.wd_tts_showmore", function(e){
            e.preventDefault();
            e.stopImmediatePropagation();
            $res.html("");
            active_tax = $(this).parent().attr("taxonomy");
            $($("div.wd_tts_ex_container h3", parent).get(0)).html("Exclude terms from <b>" + active_tax + "</b>");
            if ( typeof (termData[active_tax]) == "undefined" ) {
                $("div.wd_tts_ex_container .wd_tts_excluded_t", parent).html("Nothing is excluded yet");
            } else {
                var html = "";
                $.each(termData[active_tax], function(i, v) {
                    if ( typeof(v) != "undefined" && v != null )
                        html += "<span term_id='" + i + "'>" + v + "</span>";
                });
                $("div.wd_tts_ex_container .wd_tts_excluded_t", parent).html(html);
            }
            $("div.wd_tts_ex_container", parent).removeClass("hiddend");
            $("div.wd_tts_ex_container", parent).css({
                "top": $(this).parent().position().top - 50
            });
        });
        // --------------------------------------------------------------

        // 3. ---------------------- Display Mode --------------------------
        var $dispMButton = $(".wd_tts_display_mode", parent);
        var $dispPopup = $(".wd_tts_disp_m_popup", parent);
        var $dispSearch = $(".wd_tts_search", parent);

        function display_mode_init() {
            if ( typeof(actualData["display_mode"]) != "object" )
                return false;
            $("fieldset", $dispPopup).each(function(){
                if ( typeof(actualData["display_mode"][$(this).attr("taxonomy")]) != "object" ) {
                    $(".wd_tts_as_defined", $(this)).addClass("zeroHeight");
                    $(".tts_d_dropdown", $(this)).addClass("hiddend");
                } else {
                    var data = actualData["display_mode"][$(this).attr("taxonomy")];
                    if ( data.type == "multisearch") {
                        $(".wd_tts_selectall", $(this)).addClass("hiddend");
                        $(".tts_d_defaults", $(this)).addClass("hiddend");
                        $(".wd_tts_placeholder", $(this)).removeClass("hiddend");
                    } else {
                        $(".wd_tts_selectall", $(this)).removeClass("hiddend");
                        $(".tts_d_defaults", $(this)).removeClass("hiddend");
                        $(".wd_tts_placeholder", $(this)).addClass("hiddend");
                    }
                    if ( data.type == "checkboxes") {
                        $(".tts_display_as", $(this)).val(data.type);
                        $(".tts_d_checkboxes", $(this)).val(data.default);
                    } else {
                        $(".tts_display_as", $(this)).val(data.type);
                        if ( !isNaN(data.default) ) {
                            $(".tts_d_dropdown", $(this)).val(0);
                            $(".wd_tts_as_defined", $(this)).removeClass("zeroHeight");
                        } else {
                            $(".tts_d_dropdown", $(this)).val(data.default);
                        }
                    }
                    $("input.wd_tts_box_header_text", $(this)).val(data.box_header_text);
                    $("input.wd_tts_select_all", $(this)).prop("checked", data.select_all);
                    $("input.wd_tts_select_all_text", $(this)).val(data.select_all_text);
                    $("input.wd_tts_placeholder_text", $(this)).val(data.box_placeholder_text);
                    if ( typeof data.required != "undefined")
                        $("input.wd_tts_required", $(this)).prop("checked", data.required);
                    if ( typeof data.invalid_input_text != "undefined")
                        $("input.wd_tts_required_text", $(this)).val(data.invalid_input_text);
                }
            });
        }

        function display_mode_update() {
            var taxonomies = [];

            // Visual changes
            $("fieldset", $dispPopup).each(function() {
                if ( $(".tts_display_as", $(this)).val() == "multisearch") {
                    $(".wd_tts_selectall", $(this)).addClass("hiddend");
                    $(".tts_d_defaults", $(this)).addClass("hiddend");
                    $(".wd_tts_placeholder", $(this)).removeClass("hiddend");
                    $(".wd_tts_as_defined", $(this)).addClass("zeroHeight");
                } else {
                    $(".wd_tts_selectall", $(this)).removeClass("hiddend");
                    $(".tts_d_defaults", $(this)).removeClass("hiddend");
                    $(".wd_tts_placeholder", $(this)).addClass("hiddend");
                    $(".wd_tts_as_defined", $(this)).removeClass("zeroHeight");
                }
                if ( $(".tts_display_as", $(this)).val() == "checkboxes" ) {
                    $(".wd_tts_as_defined", $(this)).addClass("zeroHeight");
                    $(".tts_d_dropdown", $(this)).addClass("hiddend");
                    $(".tts_d_checkboxes", $(this)).removeClass("hiddend");

                    $(".wd_tts_select_all_label", $(this)).html('"Select all option"?');
                    if ( $("input.wd_tts_select_all_text", $(this)).val() == "Choose one/Any" )
                        $("input.wd_tts_select_all_text", $(this)).val("Select all");
                } else {
                    $(".tts_d_checkboxes", $(this)).addClass("hiddend");
                    $(".tts_d_dropdown", $(this)).removeClass("hiddend");

                    if ( $(".tts_display_as", $(this)).val() != "multisearch" ) {
                        if ($(".tts_d_dropdown", $(this)).val() == 0) {
                            $(".wd_tts_as_defined", $(this)).removeClass("zeroHeight");
                        } else {
                            $(".wd_tts_as_defined", $(this)).addClass("zeroHeight");
                        }
                    }

                    $(".wd_tts_select_all_label", $(this)).html('"Choose one/Any option"?');
                    if ( $("input.wd_tts_select_all_text", $(this)).val() == "Select all" )
                        $("input.wd_tts_select_all_text", $(this)).val("Choose one/Any");
                }
                if ( $(".wd_tts_defined span", $(this)).length > 0 ) {
                    $(".wd_tts_res span", $(this)).removeClass("wd_disabled");
                    $(".wd_tts_res span[term_id='"+ $(".wd_tts_defined span", $(this)).attr("term_id") + "']", $(this)).addClass("wd_disabled");
                }
                if ( $("input.wd_tts_required", $(this)).is(':checked') ) {
                    $("input.wd_tts_required_text", $(this)).removeAttr('disabled');
                } else {
                    $("input.wd_tts_required_text", $(this)).attr('disabled', 'disabled');
                }
            });

            $(parent).find("div.sortablecontainer li").each(function(){
                taxonomies.push($(this).attr('taxonomy'));
            });
            taxonomies = taxonomies.filter((x, i, a) => a.indexOf(x) == i);
            $("fieldset", $dispPopup).addClass("hiddend");
            taxonomies.forEach((tax) => {
                $("fieldset[taxonomy=" + tax + "]", $dispPopup).removeClass("hiddend");
            });
        }

        function display_mode_parse() {
            // Parse data
            actualData["display_mode"] = {};
            $("fieldset", $dispPopup).each(function() {
                var data = {};
                data.type = $(".tts_display_as", $(this)).val();
                data.select_all = $(".wd_tts_select_all", $(this)).is(':checked');
                data.box_header_text = $("input.wd_tts_box_header_text", $(this)).val();
                data.box_placeholder_text = $("input.wd_tts_placeholder_text", $(this)).val();
                data.select_all_text = $(".wd_tts_select_all_text", $(this)).val();
                data.required = $(".wd_tts_required", $(this)).is(':checked');
                data.invalid_input_text = $(".wd_tts_required_text", $(this)).val();

                if ( $(".tts_display_as", $(this)).val() == "checkboxes" ) {
                    data.default = $(".tts_d_checkboxes", $(this)).val();
                } else {
                    if ( $(".tts_d_dropdown", $(this)).val() == 0 ) {
                        if ( $(".wd_tts_defined span", $(this)).length > 0 ) {
                            data.default = $(".wd_tts_defined span", $(this)).attr("term_id");
                        }
                    } else {
                        data.default = $(".tts_d_dropdown", $(this)).val();
                    }
                }
                actualData["display_mode"][$(this).attr("taxonomy")] = data;
            });
            hidden.val("_decode_" + WD_Helpers.Base64.encode(JSON.stringify(actualData)));
        }

        var st = null;
        var sp = null;
        $dispSearch.on('keyup', function(){
            clearTimeout(st);
            var $this = $(this);
            $(".wd_ts_close", $this.parent()).addClass("hiddend");
            st = setTimeout(function(){
                $(".loading-small", $this.parent()).removeClass("hiddend");
                var data = {
                    'action': 'wd_search_taxonomy_terms',
                    'wd_s': $this.val(),
                    'wd_required': 1,
                    'wd_args': $("input.wd_args", parent).val(),
                    'wd_taxonomy': $this.parent().parent().attr("taxonomy")
                };
                if (sp != null) sp.abort();
                sp = $.post(ajaxurl, data, function (response) {
                    if (response.length > 0) {
                        $(".wd_tts_res", $this.parent()).html(response);
                    }
                    $(".loading-small", $this.parent()).addClass("hiddend");
                    $(".wd_ts_close", $this.parent()).removeClass("hiddend");
                    display_mode_update();
                    display_mode_parse();
                }, "text");
            }, 500);
        });

        $(".wd_tts_as_defined .wd_tts_res", parent).on("click", "span", function(){
            if ( $(this).hasClass("wd_disabled") )
                return false;
            var term_id = $(this).attr("term_id");
            $(".wd_tts_defined", $(this).parent().parent()).html($(this).get(0).outerHTML);
            display_mode_update();
            tt_s_update( parent );
        });

        $("input.separate-filter-boxes", parent).on('change', function(){
            display_mode_update();
            display_mode_parse();
            tt_s_update(parent);
        });

        $(".wd_ts_close", $dispPopup).on('click', function(){
            clearTimeout(st);
            if (p != null) sp.abort();
            $(".wd_tts_search", $(this).parent()).val("");
            $(this).addClass("hiddend");
            $(".wd_tts_res", $(this).parent()).html("");
        });

        $(".tts_display_as, .tts_d_checkboxes, .wd_tts_required, .tts_d_dropdown, .wd_tts_select_all", $dispPopup).on('change', function(){
            display_mode_update();
            tt_s_update( parent );
        });

        var dt = null;
        $(".wd_tts_select_all_text, .wd_tts_box_header_text, .wd_tts_placeholder_text", $dispPopup).on("keyup paste", function(){
            clearTimeout(dt);
            dt = setTimeout(function(){
                display_mode_update();
                tt_s_update( parent );
            }, 500);
        });

        display_mode_init();
        $dispMButton.on('click', function(e){
            e.preventDefault();
            e.stopImmediatePropagation();
            $dispPopup.removeClass("hiddend");
            $dispPopup.css({
                "top": $(this).position().top - 50
            });
            display_mode_update();
            tt_s_update( parent );
        });

        // --------------------------------------------------------------

        // 4. ------------------------- MISC -------------------------------
        $("body").on("click", function(){
            $("div.wd_tts_ex_container", parent).addClass("hiddend");
            $dispPopup.addClass("hiddend");
        });
        $("div.wd_tts_ex_container", parent).bind("click touchend", function (e) {
            e.stopImmediatePropagation();
        });
        $("div.wd_tts_disp_m_popup", parent).bind("click touchend", function (e) {
            e.stopImmediatePropagation();
        });

        $(".hide-children", parent).on('change', function(){
            if ($(this).get(0).checked)
                $("#sortablecontainer" + id + " li").filter(':not(.termlevel-0)').css('display', 'none');
            else
                $("#sortablecontainer" + id + " li").filter(':not(.termlevel-0)').css('display', 'block');
            list_update();
        });
        $(".tts_operation", parent).on('change', function(){
            tt_s_update( parent );
            list_update();
            $(".tts_type", parent).html($(this).val());
        });

        tt_s_update(parent);
        // --------------------------------------------------------------
    });
    // --------------------------------------------------------------

    $('div.wd_cf_search').each(function() {
        var timeout = null;
        var parent = this;
        var id = $(this).attr('id').match(/^wd_cf_search-(.*)/)[1];
        var $res = $('.wd_cf_search_res', parent);

        $("input.wd_cf_search", parent).on('keyup', function() {
            var $this = $(this);
            let nonce = $(this).parent().find('.wd_cf_search_nonce').val();
            clearTimeout(timeout);
            if ( $this.val() == '' ) {
                $('.wd_ts_close', parent).addClass("hiddend");
                $('.loading-small', parent).addClass("hiddend");
                return;
            }
            timeout = setTimeout(function () {
                $('.loading-small', parent).removeClass("hiddend");
                $('.wd_ts_close', parent).addClass("hiddend");
                var data = {
                    'action': 'wd_search_cf',
                    'wd_phrase': $this.val(),
                    'wd_cf_search_nonce': nonce,
                    'wd_required': 1,
                    'wd_args': $("input.wd_args", parent).val()
                };
                $.post(ajaxurl, data, function (response) {
                    var o = JSON.parse(WD_Helpers.Base64.decode($("input.wd_args", parent).val()));
                    var reg = new RegExp(o.delimiter +'(.*[\s\S]*)'+ o.delimiter);
                    var data_r = response.match(reg);
                    data_r = JSON.parse(data_r[1]);
                    if ( typeof o.callback != 'undefined' && o.callback != '' ) {
                        if ( typeof window[o.callback] != 'undefined' )
                            window[o.callback].apply(null, [data_r, $this, o, parent, id]);
                    } else {
                        var html = '';
                        $.each(data_r, function(i, v){
                           html += '<li key="' + v.meta_key + '">' + v.meta_key + '</li>';
                        });
                        if ( html != '')
                            $res.html('<ul>' + html + '</ul>');
                        else
                            $res.html('<p>No results :(</p>');

                        $res.css('display', 'block');
                        var bottom_of_element = $this.offset().top + $res.outerHeight();
                        var bottom_of_screen = $(window).scrollTop() + window.innerHeight;
                        if( ( bottom_of_element > bottom_of_screen ) ){
                            $res.css({
                                left: $this.position().left + $this.outerWidth(true) + 5,
                                top: $this.position().top - (bottom_of_element - bottom_of_screen),
                                display: 'block',
                                minWidth: $this.width()
                            });
                        } else {
                            $res.css({
                                left: $this.position().left,
                                top: $this.position().top + $this.outerHeight(true) + 10,
                                display: 'block',
                                minWidth: $this.width()
                            });
                        }

                        $res.find('li').on('click', function(e){
                            $this.val($(this).attr('key'));
                            $res.css({display: 'none'});
                        });
                    }
                    $('.loading-small', parent).addClass("hiddend");
                    $('.wd_ts_close', parent).removeClass("hiddend");
                }, "text");
            }, 350);
        });
        $('.wd_ts_close', parent).on('click', function(){
            $("input.wd_cf_search", parent).val('');
            $(this).addClass("hiddend");
            $res.css({
                display: 'none'
            });
        });
        $("input.wd_cf_search", parent).on('click', function() {
            $res.css({
                display: 'block'
            });
        });
        $("input.wd_cf_search", parent).on('blur', function () {
            $(this).val($(this).val().trim());
        });
        $(parent).click("click", function (e) {
            e.stopImmediatePropagation();
        });
        $(document).on('click', function(){
            $('.wd_ts_close', parent).addClass("hiddend");
            $res.css({display: 'none'});
        });
    });
    /**
     * EXAMPLE HANDLER
    window.my_js_function_name = function(r, $node, o) {
        //console.log(r, $node, o);
    };
     */

    $('div.wd_cpt_search').each(function() {
        var timeout = null;
        var parent = this;
        var id = $(this).attr('id').match(/^wd_cpt_search-(.*)/)[1];
        var $res = $('.wd_cpt_search_res', parent);
        var $param = $('input[isparam]', parent);

        $("input.wd_cpt_search", parent).on('keyup', function() {
            var $this = $(this);
            let nonce = $this.parent().find('input.wd_cpt_search_nonce').val();
            clearTimeout(timeout);
            if ( $this.val() == '' ) {
                $('.wd_ts_close', parent).addClass("hiddend");
                $('.loading-small', parent).addClass("hiddend");
                return;
            }
            timeout = setTimeout(function () {
                $('.loading-small', parent).removeClass("hiddend");
                $('.wd_ts_close', parent).addClass("hiddend");
                var data = {
                    'action': 'wd_search_cb_cpt',
                    'wd_phrase': $this.val(),
                    'wd_cpt_search_nonce': nonce,
                    'wd_required': 1,
                    'wd_args': $("input.wd_args", parent).val()
                };
                $.post(ajaxurl, data, function (response) {
                    var o = JSON.parse(WD_Helpers.Base64.decode($("input.wd_args", parent).val()));
                    var reg = new RegExp(o.delimiter +'(.*[\s\S]*)'+ o.delimiter);
                    var data_r = response.match(reg);
                    data_r = JSON.parse(data_r[1]);
                    if ( typeof o.callback != 'undefined' && o.callback != '' ) {
                        if ( typeof window[o.callback] != 'undefined' )
                            window[o.callback].apply(null, [data_r, $this, o, parent, id]);
                    } else {
                        var html = '';
                        $.each(data_r, function(i, v){
                           html += '<li key="' + v.ID + '">' + v.post_title + ' (' + v.post_type + ')</li>';
                        });
                        if ( html != '')
                            $res.html('<ul>' + html + '</ul>');
                        else
                            $res.html('<p>No results :(</p>');

                        $res.css('display', 'block');
                        var bottom_of_element = $this.offset().top + $res.outerHeight();
                        var bottom_of_screen = $(window).scrollTop() + window.innerHeight;
                        if( ( bottom_of_element > bottom_of_screen ) ){
                            $res.css({
                                left: $this.position().left + $this.outerWidth(true) + 5,
                                top: $this.position().top - (bottom_of_element - bottom_of_screen),
                                display: 'block',
                                minWidth: $this.width()
                            });
                        } else {
                            $res.css({
                                left: $this.position().left,
                                top: $this.position().top + $this.outerHeight(true) + 10,
                                display: 'block',
                                minWidth: $this.width()
                            });
                        }

                        $res.find('li').on('click', function(e){
                            $this.val('');
                            $this.addClass('hiddend');
                            $(".wp_cpt_search_selected span:not(.fa)", parent).text($(this).text());
                            $(".wp_cpt_search_selected", parent).removeClass('hiddend');
                            $param.val($(this).attr('key'));
                            $res.css({display: 'none'});
                            $('.wd_ts_close', parent).addClass("hiddend");
                        });
                    }
                    $('.loading-small', parent).addClass("hiddend");
                    $('.wd_ts_close', parent).removeClass("hiddend");
                }, "text");
            }, 350);
        });
        $('.wd_ts_close', parent).on('click', function(){
            $("input.wd_cpt_search", parent).val('');
            $(this).addClass("hiddend");
            $res.css({
                display: 'none'
            });
        });
        $("input.wd_cpt_search", parent).on('click', function() {
            $res.css({
                display: 'block'
            });
        });
        $("input.wd_cpt_search", parent).on('blur', function () {
            $(this).val($(this).val().trim());
        });
        $(parent).click("click", function (e) {
            e.stopImmediatePropagation();
        });
        $(".wp_cpt_search_selected", parent).on('click', function () {
            $param.val('');
            $(this).addClass('hiddend');
            $('.wd_ts_close', parent).trigger('click');
            $("input.wd_cpt_search", parent).removeClass('hiddend');
        });
        if ( $(".wp_cpt_search_selected", parent).text().trim() == '' || $(".wp_cpt_search_selected", parent).text().trim() == '()' ) {
            $(".wp_cpt_search_selected", parent).addClass('hiddend');
            $("input.wd_cpt_search", parent).removeClass('hiddend');
        }
        $(document).on('click', function(){
            $('.wd_ts_close', parent).addClass("hiddend");
            $res.css({display: 'none'});
        });
    });
    /**
     * EXAMPLE HANDLER
    window.my_js_function_name = function(r, $node, o) {
        //console.log(r, $node, o);
    };
     */


    $('div.wd_taxterm_search').each(function() {
        var timeout = null;
        var parent = this;
        var id = $(this).attr('id').match(/^wd_taxterm_search-(.*)/)[1];
        var $res = $('.wd_taxterm_search_res', parent);
        $("select.wd_taxterm_tax", parent).on('change', function(){
            $("input.wd_taxterm_search", parent).trigger('keyup');
        });
        var $this = $("input.wd_taxterm_search", parent);
        $this.on('keyup', function() {
            clearTimeout(timeout);
            if ( $this.val() == '' ) {
                $('.wd_ts_close', parent).addClass("hiddend");
                $('.loading-small', parent).addClass("hiddend");
                return;
            }
            timeout = setTimeout(function () {
                $('.loading-small', parent).removeClass("hiddend");
                $('.wd_ts_close', parent).addClass("hiddend");
                var data = {
                    'action': 'wd_search_taxterm',
                    'wd_phrase': $this.val(),
                    'wd_taxonomy': $('.wd_taxterm_tax', parent).val(),
                    'wd_taxonomy_search_cb_nonce': $('.wd_taxonomy_search_cb_nonce', parent).val(),
                    'wd_required': 1,
                    'wd_args': $("input.wd_args", parent).val()
                };
                $.post(ajaxurl, data, function (response) {
                    var o = JSON.parse(WD_Helpers.Base64.decode($("input.wd_args", parent).val()));
                    var reg = new RegExp(o.delimiter +'(.*[\s\S]*)'+ o.delimiter);
                    var data_r = response.match(reg);
                    data_r = JSON.parse(data_r[1]);
                    if ( typeof o.callback != 'undefined' && o.callback != '' ) {
                        if ( typeof window[o.callback] != 'undefined' )
                            window[o.callback].apply(null, [data_r, $this, o, parent, id]);
                    } else {
                        var html = '';
                        $.each(data_r, function(i, v){
                            html += '<li class="t_'+v.taxonomy+'_'+v.term_id+'" data-taxonomy="'+v.taxonomy+'" data-id="' + v.term_id + '">' + v.name + '</li>';
                        });
                        if ( html != '')
                            $res.html('<ul>' + html + '</ul>');
                        else
                            $res.html('<p>No results :(</p>');

                        $res.css('display', 'block');
                        var bottom_of_element = $this.offset().top + $res.outerHeight();
                        var bottom_of_screen = $(window).scrollTop() + window.innerHeight;
                        if( ( bottom_of_element > bottom_of_screen ) ){
                            $res.css({
                                left: $this.position().left + $this.outerWidth(true) + 5,
                                top: $this.position().top - (bottom_of_element - bottom_of_screen),
                                display: 'block',
                                minWidth: $this.width()
                            });
                        } else {
                            $res.css({
                                left: $this.position().left,
                                top: $this.position().top + $this.outerHeight(true) + 10,
                                display: 'block',
                                minWidth: $this.width()
                            });
                        }
                    }

                    $('.loading-small', parent).addClass("hiddend");
                    $('.wd_ts_close', parent).removeClass("hiddend");

                    $this.trigger("wd_taxterm_search_end", [$this, $res, data_r]);
                }, "text");
            }, 350);
        });
        $('.wd_ts_close', parent).on('click', function(){
            $("input.wd_taxterm_search", parent).val('');
            $(this).addClass("hiddend");
            $res.css({
                display: 'none'
            });
        });
        $("input.wd_taxterm_search", parent).on('click', function() {
            $res.css({
                display: 'block'
            });
            $this.trigger("wd_taxterm_open_results", [$this, $res]);
        });
        $("input.wd_taxterm_search", parent).on('blur', function () {
            $(this).val($(this).val().trim());
        });
        $(parent).click("click", function (e) {
            e.stopImmediatePropagation();
        });
        $(document).on('click', function(){
            $('.wd_ts_close', parent).addClass("hiddend");
            $res.css({display: 'none'});
        });
    });
    /**
     * EXAMPLE HANDLER
     window.my_js_function_name = function(r, $node, o) {
        //console.log(r, $node, o);
    };
     */

    /**
     * User include-exclude selector with AJAX support
     */
    $('div.wd_userselect').each(function(){
        var id = $(this).attr('id').match(/^wd_userselect-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var hidden = $('input[isparam=1]', this);
        var name = hidden.attr('name');
        var parent = $(this);
        var actualData = JSON.parse( WD_Helpers.Base64.decode(hidden.val().replace(/^(_decode_)/,"")) );
        var _cache = "";

        function tt_s_update( parent ) {
            // Items need to be re-parsed!
            var items = $('ul[id*=sortable_conn] li', parent);
            var users = [];
            var un_checked = [];
            items.each(function (i, v) {
                users.push($(this).attr('user_id'));
                if ( $("input[type='checkbox']", this).length > 0 && !$("input[type='checkbox']", this).get(0).checked )
                    un_checked.push($(this).attr('user_id'));
            });
            actualData.users = users;
            actualData.op_type = $(".tts_operation", parent).val();
            actualData.un_checked = un_checked;
            hidden.val("_decode_" + WD_Helpers.Base64.encode(JSON.stringify(actualData)));
        }

        function list_update() {
            $("#sortable" + id + " li").removeClass("ui-state-disabled");
            $('ul[id*=sortable_conn] li', parent).each(function (i, v) {
                $("#sortable" + id + " li[user_id='"+$(this).attr('user_id')+"']").addClass("ui-state-disabled");
            });
        }

        var drag_opts = {
            connectToSortable: "#sortable_conn" + id,
            update: function (event, ui) {},
            cancel: ".ui-state-disabled",
            helper: "clone"
        };
        $("#sortable" + id + " li").draggable(drag_opts).disableSelection();

        $("#sortable_conn" + id).sortable({
            update: function (event, ui) {
                var $item = $(ui.item);
                $item.css({
                    "width": "",
                    "height": ""
                });
                if ( $item.attr("user_id") == '-1' ) {
                    $("#sortable_conn" + id + " li[user_id!='-1']").detach();
                } else {
                    $("#sortable_conn" + id + " li[user_id='-1']").detach();
                }
                // Refresh cache with remaining
                _cache = $("#sortable" + id).html();
                tt_s_update( parent );
                list_update();
            },
            cancel: ".ui-state-disabled",
            remove: function(event, ui) {
                var $item = $(ui.item);
                _cache = "";
            }
        }).disableSelection();

        var timeout = null;
        $(".wd_user_search", parent).on('keyup', function(){
            var $this = $(this);
            clearTimeout(timeout);
            timeout = setTimeout(function(){
                $('.dragLoader', parent).removeClass("hiddend");
                var data = {
                    'action': 'wd_search_users',
                    'wd_phrase': $this.val(),
                    'wd_user_select_nonce': $('.wd_user_select_nonce', parent).val(),
                    'wd_required': 1,
                    'wd_args': $("input.wd_args", parent).val()
                };
                $.post(ajaxurl, data, function (response) {
                    if (response.length > 0) {
                        _cache = response;
                        $("#sortable" + id).html(response);
                        $("#sortable" + id + " li").draggable(drag_opts).disableSelection();
                        list_update();
                        $('.dragLoader', parent).addClass("hiddend");
                        $(".hide-children", parent).trigger('change');
                    }
                }, "text");
            }, 350);
        });

        $(selector).on('sortupdate', function(event, ui) {
            tt_s_update( parent );
            list_update();
        });
        $("#sortable_conn" + id +" li input").on('change', function(){
            tt_s_update( parent );
            list_update();
        });
        $("#sortable_conn" + id).on( "click", "li a.deleteIcon", function(e){
            e.preventDefault();
            $(this).parent().detach();
            tt_s_update( parent );
            list_update();
        });

        $(".tts_operation", parent).on('change', function(){
            tt_s_update( parent );
            list_update();
            $(".tts_type", parent).html($(this).val());
        });

        tt_s_update( parent );
        list_update();
    });

    /**
     * Post/page/cpt selector with AJAX support
     */
    $('div.wd_cpt_select').each(function(){
        var id = $(this).attr('id').match(/^wd_cpt_select-(.*)/)[1];
        var selector = "#sortable" + id +", #sortable_conn" + id;
        var hidden = $('input[isparam=1]', this);
        var name = hidden.attr('name');
        var parent = $(this);
        var actualData = JSON.parse( WD_Helpers.Base64.decode(hidden.val().replace(/^(_decode_)/,"")) );
        var _cache = "";

        function tt_s_update( parent ) {
            // Items need to be re-parsed!
            var items = $('ul[id*=sortable_conn] li', parent);
            var ids = [];
            var checked = [];
            items.each(function (i, v) {
                ids.push($(this).attr('post_id'));
                if ( $("input[type='checkbox']", this).length > 0 && $("input[type='checkbox']", this).get(0).checked )
                    checked.push($(this).attr('post_id'));
            });
            actualData.ids = ids;
            actualData.parent_ids = checked;
            hidden.val("_decode_" + WD_Helpers.Base64.encode(JSON.stringify(actualData)));
        }

        function list_update() {
            $("#sortable" + id + " li").removeClass("ui-state-disabled");
            $('ul[id*=sortable_conn] li', parent).each(function (i, v) {
                $("#sortable" + id + " li[post_id='"+$(this).attr('post_id')+"']").addClass("ui-state-disabled");
            });
        }

        var drag_opts = {
            connectToSortable: "#sortable_conn" + id,
            update: function (event, ui) {},
            cancel: ".ui-state-disabled",
            helper: "clone"
        };
        $("#sortable" + id + " li").draggable(drag_opts).disableSelection();

        $("#sortable_conn" + id).sortable({
            update: function (event, ui) {
                var $item = $(ui.item);
                $item.css({
                    "width": "",
                    "height": ""
                });
                /*if ( $item.attr("user_id") == '-1' ) {
                    $("#sortable_conn" + id + " li[user_id!='-1']").detach();
                } else {
                    $("#sortable_conn" + id + " li[user_id='-1']").detach();
                }*/
                // Refresh cache with remaining
                _cache = $("#sortable" + id).html();
                tt_s_update( parent );
                list_update();
            },
            cancel: ".ui-state-disabled",
            remove: function(event, ui) {
                var $item = $(ui.item);
                _cache = "";
            }
        }).disableSelection();

        var timeout = null;
        $(".wd_cpt_search", parent).on('keyup', function(){
            var $this = $(this);
            let nonce = $this.parent().find('.wd_cpt_select_search_nonce').val();
            clearTimeout(timeout);
            timeout = setTimeout(function(){
                $('.dragLoader', parent).removeClass("hiddend");
                var data = {
                    'action': 'wd_search_cpt',
                    'wd_phrase': $this.val(),
                    'wd_cpt_select_search_nonce': nonce,
                    'wd_required': 1,
                    'wd_args': $("input.wd_args", parent).val()
                };
                $.post(ajaxurl, data, function (response) {
                    if (response.length > 0) {
                        _cache = response;
                        $("#sortable" + id).html(response);
                        $("#sortable" + id + " li").draggable(drag_opts).disableSelection();
                        list_update();
                        $('.dragLoader', parent).addClass("hiddend");
                        $(".hide-children", parent).trigger('change');
                    }
                }, "text");
            }, 350);
        });

        $(selector).on('sortupdate', function(event, ui) {
            tt_s_update( parent );
            list_update();
        });
        $("#sortable_conn" + id).on('change', 'li input', function(){
            tt_s_update( parent );
            list_update();
        });
        $("#sortable_conn" + id).on( "click", "li a.deleteIcon", function(e){
            e.preventDefault();
            $(this).parent().detach();
            tt_s_update( parent );
            list_update();
        });

        $(".tts_operation", parent).on('change', function(){
            tt_s_update( parent );
            list_update();
            $(".tts_type", parent).html($(this).val());
        });

        tt_s_update( parent );
        list_update();
    });


    // ----------------------- THEME RELATED ---------------------

    $('#asp_import_theme').on('click', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();

        $("#wpd_import_modal .errorMsg").addClass("hiddend");
        $('#wpd_import_modal').removeClass('hiddend');
        $('#wpd_imex_modal_bg').css('display', 'block');
    });
    $('#asp_export_theme').on('click', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();

        let skip = [
                'settingsimage_custom', 'magnifierimage_custom', 'search_text', 'box_width', 'box_width_tablet',
                'box_width_phone', 'results_width', 'results_width_tablet', 'results_width_phone', 'description_context',
                'descriptionlength', 'description_context_depth'
            ],
            parent = $("div[tabid=6]"),
            tabs = parent.find('a[tabid]').not('.disabled').map(function(){ return $(this).attr('tabid') });
            content = {};

        tabs.push('402');
        tabs.each(function(i, tabid){
           $(
               'div[tabid='+ tabid +'] input[isparam=1], ' +
               'div[tabid='+ tabid +'] select[isparam=1]'
           ).each(function(){
               let name = $(this).attr("name");
               if ( skip.indexOf(name) > -1 )
                   return true;
               let val = $(this).val().replace(/(\r\n|\n|\r)/gm,"");
               content[name] = val;
           });
        });

        $("select[name=resultstype]").each(function(){
            var name = $(this).attr("name");
            var val = $(this).val().replace(/(\r\n|\n|\r)/gm,"");
            content[name] = val;
        });

        content = JSON.stringify(content, null, 2);
        $("#wpd_export_modal textarea").val(WD_Helpers.Base64.encode(content));

        $('#wpd_export_modal').removeClass('hiddend');
        $('#wpd_imex_modal_bg').css('display', 'block');
    });
    $('#asp_import_theme_btn').on('click', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();
        var $this = $(this);
        try {
            var raw = WD_Helpers.Base64.decode($("#wpd_import_modal textarea").val());
            var data = JSON.parse(raw); // Check if valid
            if ( $(".wpdreamsThemeChooser select[name=themes] option[name=imported]").length == 0 ) {
                $(".wpdreamsThemeChooser select[name=themes]").append("<option name='imported'>Imported</option>");
                $(".wpdreamsThemeChooser select[name=themes]").after('<div name="Imported" style="display:none;"></div>');
            }
            $(".wpdreamsThemeChooser div[name=Imported]").html(raw);
            $(".wpdreamsThemeChooser select[name=themes]").val("Imported");
            $('#wpd_import_modal .wpd-modal-close').trigger('click');
            setTimeout(function(){
                $(".wpdreamsThemeChooser select[name=themes]").trigger('change');
            }, 500);
        } catch (e) {
            $(".errorMsg", $this.parent()).removeClass("hiddend");
        }
    });
    $('#wpd_import_modal textarea').on('click', function(e){
        $(".errorMsg", $(this).parent()).addClass("hiddend");
    });
    $('#wpd_import_modal .wpd-modal-close, #wpd_export_modal .wpd-modal-close, #wpd_imex_modal_bg').on('click', function(){
        $('#wpd_import_modal').addClass('hiddend');
        $('#wpd_export_modal').addClass('hiddend');
        $('#wpd_imex_modal_bg').css('display', 'none');
    });

    /**
     * Theme Chooser
     *
     * Since 4.5.4 better load balancing and loading bar implemented.
     */
    var lastSel = $(".wpdreamsThemeChooser select option:selected");

    $('.wpdreamsThemeChooser select').bind('change', function (e) {
        // Array of node values, before the change, before reset
        var affectedNodes = {};

        function th_reset(n) {
            var themeDiv = $('div[name="'+n+'"]');
            var items = JSON.parse( themeDiv.text() );
            affectedNodes = {}; // Reset this here

            $.each( items, function (key, value) {
                var param = $('input[name="' + key + '"]', parent);
                if (param.length == 0)
                    param = $('select[name="' + key + '"]', parent);
                if (param.length == 0)
                    param = $('textarea[name="' + key + '"]', parent);
                var currentVal = param.val();
                affectedNodes[key] = {
                    'node': param,
                    'pre': currentVal,
                    'post': value
                };

                if ( value != currentVal )
                    param.val(value);
            });
        }

        var c = confirm('Do you really want to load this template?');
        if (!c) {
            e.preventDefault();
            lastSel.prop("selected", true);
            return false;
        }
        var parent = $(this);
        while (parent.is('form') != true) {
            parent = parent.parent();
        }
        var themeDiv = $('div[name="' + $(this).val() + '"]');
        var items = JSON.parse( themeDiv.text() );
        var itemsCount = $.map(items, function(n, i) { return i; }).length;

        // Loader start here
        $("#wpd_body_loader").css("display", "block");
        $("#wpd_loading_msg").html("Loading..");

        if ( typeof items.custom_css_h == "undefined" ) {
            $('input[name="custom_css_h"]', parent).next().val("").trigger('keyup');
        }

        // Delay the start by 1 second for the browser to finish all pending operations
        setTimeout(function() {
            // Load the reference theme, if defined
            if ( typeof items._ref != 'undefined' )
                th_reset(items._ref);

            // Load the values, and the nodes that needs to be triggered
            $.each( items, function (key, value) {
                // Skip the reference key, just continue
                if ( key == '_ref' )
                    return true;

                param = $('input[name="' + key + '"]', parent);
                if (param.length == 0)
                    param = $('select[name="' + key + '"]', parent);
                if (param.length == 0)
                    param = $('textarea[name="' + key + '"]', parent);

                if ( typeof affectedNodes[key] == 'undefined' ) {
                    affectedNodes[key] = {
                        'node': param,
                        'pre': param.val(),
                        'post': value
                    };
                } else {
                    affectedNodes[key].post = value;
                }

                param.val(value);
            });

            var count = 0;
            var t = null;

            if ( Object.keys(affectedNodes).length > 0 ) {
                // Gather the nodes that need change
                var $nodes = [];
                $.each(affectedNodes, function (key, node) {
                   if ( node.pre != node.post )
                       $nodes.push( $('>.triggerer', node.node.parent()) );
                });

                if ( $nodes.length > 0 ) {
                    // Some nodes, load them :)
                    $.each($nodes, function (key, node) {
                        // Delay execution by 60ms for each item
                        setTimeout(function (tc, xnode) {

                            $("#wpd_loading_msg").html("Loading " + tc + "/" + $nodes.length);

                            // Abort the execution of the loading removal
                            clearTimeout(t);

                            xnode.trigger("click");

                            // Give 800 milliseconds to render, until the last one reached
                            // then remove the loader. The last timeout is not aborted,
                            // it is executed after 800 ms, finally removing the loading bar.
                            t = setTimeout(function () {

                                $("#wpd_body_loader").css("display", "none");
                                $("#asp_preview_window .refresh").trigger('click');
                                $("select[name='resultstype']").trigger('change');

                            }, (800));

                        }, (count * 60), (count + 1), node);
                        count++;
                    });
                } else {
                    // No nodes to load, close the loader
                    t = setTimeout(function () {

                        $("#wpd_body_loader").css("display", "none");
                        $("#asp_preview_window .refresh").trigger('click');

                    }, (100));
                }
            } else {
                // No nodes, close the loader
                t = setTimeout(function () {

                    $("#wpd_body_loader").css("display", "none");
                    $("#asp_preview_window .refresh").trigger('click');

                }, (100));
            }
        }, 1000);

    });
    $('.wpdreamsThemeChooser select').on('click', function(){
        lastSel = $(".wpdreamsThemeChooser select option:selected");
    });

    /**
     * Animation selector
     */
    $('.wpdreamsAnimations .wpdreamsanimationselect').on('change', function() {
        var parent = $(this).parent();
        $('span', parent).removeClass();
        $('span', parent).addClass("asp_an_" + $(this).val());
    });
    $('.wpdreamsAnimations .triggerer').bind('click', function () {
        var parent = $(this).parent();
        var select = $('select', parent);
        return;
    });

    /**
     * Numeric unit related
     */
    $('.wpdreamsNumericUnit select, .wpdreamsNumericUnit input[name=numeric]').on('change', function() {
        var value = "";
        var parent = $(this).parent();
        while (parent.hasClass('wpdreamsNumericUnit') != true) {
            parent = $(parent).parent();
        }
        var value = $('input[name=numeric]', parent).val() + $('select', parent).val();
        $('input[type=hidden]', parent).val(value);
    });

    $('.wpdreamsNumericUnit .triggerer').bind('click', function () {
        var value = "";
        var parent = $(this).parent();
        while (parent.hasClass('wpdreamsNumericUnit') != true) {
            parent = $(parent).parent();
        }
        var hiddenval = $('input[type=hidden]', parent).val();
        var value = hiddenval.match(/([0-9]+)(.*)/)
        $('input[name=numeric]', parent).val(value[1]);
        $('select', parent).val(value[2]);
    });

    /**
     * Image chooser (radio image)
     */
    $('.wpdreamsImageRadio img.radioimage').on('click', function() {
        var $parent = $(this).parent();
        var $hidden = $("input[class=realvalue]", $parent);
        $('img.selected', $parent).removeClass('selected');
        $(this).addClass('selected');
        var value = $(this).attr('sel').substring(1);
        $hidden.val(value);
        $hidden.trigger('change');
    });
    $('.wpdreamsImageRadio .triggerer').bind('click', function () {
        var $parent = $(this).parent();
        var $hidden = $("input[class=realvalue]", $parent);
        $('img.selected', $parent).removeClass('selected');
        $('img[src*="' + $hidden.val() + '"]', $parent).addClass('selected');
        $hidden.trigger('change');
    });

    /**
     * Image chooser (radio image new)
     */
    $('.wd_imageRadio img.image_radio').on('click', function() {
        var $parent = $(this).parent();
        var $hidden = $("input[class=realvalue]", $parent);
        $('img.selected', $parent).removeClass('selected');
        $(this).addClass('selected');
        var value = $(this).attr('sel');
        $hidden.val(value);
        $hidden.trigger('change');
    });
    $('.wd_imageRadio .triggerer').bind('click', function () {
        var $parent = $(this).parent();
        var $hidden = $("input[class=realvalue]", $parent);
        $('img.selected', $parent).removeClass('selected');
        $('img[sel="' + $hidden.val() + '"]', $parent).addClass('selected');
        $hidden.trigger('change');
    });

    /**
     * Loader chooser
     */
    $('.wpdreamsLoaderSelect .asp-select-loader, .wpdreamsLoaderSelect .asp-select-loader-selected').on('click', function() {
        var $parent = $(this).parent();
        var $hidden = $("input[class=realvalue]", $parent);
        $('div.asp-select-loader-selected', $parent)
            .addClass('asp-select-loader')
            .removeClass('asp-select-loader-selected');

        $(this).addClass('asp-select-loader-selected').removeClass('asp-select-loader');
        var value = $(this).attr('sel');
        $hidden.val(value);
        $hidden.trigger('change');
    });
    $('.wpdreamsLoaderSelect .triggerer').bind('click', function () {
        var $parent = $(this).parent();
        var $hidden = $("input[class=realvalue]", $parent);
        $('div.asp-select-loader-selected', $parent)
            .addClass('asp-select-loader')
            .removeClass('asp-select-loader-selected');

        $('div[sel*="' + $hidden.val() + '"]', $parent)
            .addClass('asp-select-loader-selected')
            .removeClass('asp-select-loader');

        $hidden.trigger('change');
    });

    /**
     * Spectrum: color chooser
     */
    $(".wpdreamsColorPicker .color").spectrum({
        showInput: true,
        showAlpha: true,
        showPalette: true,
        showSelectionPalette: true
    });
    $('.wpdreamsColorPicker .triggerer').bind('click', function () {
        function hex2rgb(hex, opacity) {
            var rgb = hex.replace('#', '').match(/(.{2})/g);

            var i = 3;
            while (i--) {
                rgb[i] = parseInt(rgb[i], 16);
            }

            if (typeof opacity == 'undefined') {
                return 'rgb(' + rgb.join(', ') + ')';
            }

            return 'rgba(' + rgb.join(', ') + ', ' + opacity + ')';
        }

        var parent = $(this).parent();
        var input = $('input.color', parent);
        var val = input.val();
        if (val.length <= 7) val = hex2rgb(val, 1);
        input.spectrum("set", val);
    });

    /**
     * Gradient chooser
     */
    // Initial sliders
    $('.wpdreamsGradient').each(function(){
        var $parent = $(this).closest('.wpdreamsGradient');
        var $this = $(this);
        var rotation = $this.find(".dslider").data('rotation');

        $this.find(".dslider").slider({
            orientation: 'horizontal',
            range: 'min',
            max: 360,
            value: rotation,
            step: 5,
            change: function() {
                $(this).closest(".wpdreamsGradient").find("select.grad_type").trigger('change');
            },
            slide: function(e, ui) {
                $(this).closest(".wpdreamsGradient").find(".dtxt").html(ui.value);
                $(this).closest(".wpdreamsGradient").find("select.grad_type").trigger('change');
            }
        });
    });
    $(".wpdreamsGradient select.grad_bg_type").on('change', function(){
        var $parent = $(this).closest('.wpdreamsGradient');
        var $colors = $parent.find('input.color');

        if ( $(this).val() == 0 ) {
            $parent.find('.grad_slider').css('display', 'none');
            $($colors[1]).val($($colors[0]).val());
            $($colors[1]).spectrum('set', $($colors[0]).val());
            $($colors[1]).closest('.wpdreamsColorPicker').css('display', 'none');
            $parent.find('select.grad_type').css('display', 'none');
            $parent.find('div.grad_ex').css('display', 'none');
        } else {
            $parent.find('.grad_slider').css('display', 'block');
            $($colors[1]).closest('.wpdreamsColorPicker').css('display', '');
            $parent.find('select.grad_type').css('display', '');
            $parent.find('select.grad_type').css('display', '').trigger('change');
            $parent.find('div.grad_ex').css('display', '');
        }
    });
    $(".wpdreamsGradient .color, .wpdreamsGradient .grad_type, .wpdreamsGradient .dslider").on('change', function () {
        var $parent = $(this).closest('.wpdreamsGradient');
        var $hidden = $('input.gradient', $parent);
        var $colors = $('input.color', $parent);
        var $type = $('select.grad_type', $parent);
        var $dslider = $('div.dslider', $parent);
        var $grad_ex = $('div.grad_ex', $parent);
        var $dbg = $('div.dbg', $parent);
        var $dtxt = $('div.dtxt', $parent);

        $dbg.css({
            "-webkit-transform": "rotate(" + $dslider.slider('value') + "deg)",
            "-moz-transform": "rotate(" + $dslider.slider('value') + "deg)",
            "transform": "rotate(" + $dslider.slider('value') + "deg)"
        });
        $dtxt.html($dslider.slider('value'));

        grad($grad_ex, $($colors[0]).val(), $($colors[1]).val(), $type.val(), $dslider.slider('value'));

        $hidden.val(
            $type.val() + '-' +
                $dslider.slider('value') + '-' +
                $($colors[0]).val() + '-' +
                $($colors[1]).val()
        );
        if ( $parent.find('select.grad_bg_type').val() == 0 ) {
            $hidden.val(
                $type.val() + '-' +
                $dslider.slider('value') + '-' +
                $($colors[0]).val() + '-' +
                $($colors[0]).val()
            );
        } else {
            $hidden.val(
                $type.val() + '-' +
                $dslider.slider('value') + '-' +
                $($colors[0]).val() + '-' +
                $($colors[1]).val()
            );
        }
        $hidden.trigger('change');
    });
    $(".wpdreamsGradient .grad_type").trigger('change');

    // After the trigger, change the states if needed
    $('.wpdreamsGradient').each(function(){
        var $parent = $(this).closest('.wpdreamsGradient');
        var $colors = $('input.color', $parent);

        if ( $($colors[0]).val() == $($colors[1]).val() ) {
            $parent.find('select.grad_bg_type').val(0).trigger('change');
        }
    });

    $(".wpdreamsGradient>.triggerer").on('click', function() {
        var $parent = $(this).closest('.wpdreamsGradient');
        var $hidden = $('input.gradient', $parent);
        var $colors = $('input.color', $parent);
        var $dslider = $('div.dslider', $parent);
        var $type = $('select.grad_type', $parent);
        var colors = $hidden.val().match(/(.*?)-(.*?)-(.*?)-(.*)/);

        if (colors == null || colors[1] == null) {
            //Fallback to older 1 color
            $type.val(0);
            $dslider.slider('value', 0);
            $($colors[0]).spectrum('set', $hidden.val());
            $($colors[1]).spectrum('set', $hidden.val());
        } else {
            $type.val(colors[1]);
            $dslider.slider('value', colors[2]);
            $($colors[0]).val(colors[3]);
            $($colors[1]).val(colors[4]);

            $($colors[0]).spectrum('set', colors[3]);
            $($colors[1]).spectrum('set', colors[4]);
        }

        if ( $($colors[0]).val() == $($colors[1]).val() ) {
            $parent.find('select.grad_bg_type').val(0).trigger('change');
        } else {
            $parent.find('select.grad_bg_type').val(1).trigger('change');
        }
    });
    function grad(el, c1, c2, t, d) {
        if (t != 0) {
            $(el).css('background-image', '-webkit-linear-gradient(' + d + 'deg, ' + c1 + ', ' + c2 + ')')
                .css('background-image', '-moz-linear-gradient(' + d + 'deg,  ' + c1 + ',  ' + c2 + ')')
                .css('background-image', '-ms-linear-gradient(' + d + 'deg,  ' + c1 + ',  ' + c2 + ')')
                .css('background-image', 'linear-gradient(' + d + 'deg,  ' + c1 + ',  ' + c2 + ')')
                .css('background-image', '-o-linear-gradient(' + d + 'deg,  ' + c1 + ',  ' + c2 + ')');
        } else {
            $(el).css('background-image', '-webkit-radial-gradient(center, ellipse cover, ' + c1 + ', ' + c2 + ')')
                .css('background-image', '-moz-radial-gradient(center, ellipse cover, ' + c1 + ',  ' + c2 + ')')
                .css('background-image', '-ms-radial-gradient(center, ellipse cover, ' + c1 + ',  ' + c2 + ')')
                .css('background-image', 'radial-gradient(ellipse at center, ' + c1 + ',  ' + c2 + ')')
                .css('background-image', '-o-radial-gradient(center, ellipse cover, ' + c1 + ',  ' + c2 + ')');
        }
    }

    /**
     * BoxShadow chooser
     */
    $('.wpdreamsBoxShadow input[type=text], .wpdreamsBoxShadow select').on('change', function () {
        var parent = $(this).closest('.wpdreamsBoxShadow');

        var hlength = $.trim( parent.find('input._xx_hlength_xx_').val() ) + "px ";
        var vlength = $.trim( parent.find('input._xx_vlength_xx_').val() ) + "px ";
        var blurradius = $.trim( parent.find('input._xx_blurradius_xx_').val() ) + "px ";
        var spread = $.trim( parent.find('input._xx_spread_xx_').val() ) + "px ";
        var color = $.trim( parent.find('input.color').val() ) + " ";
        var inset = $.trim( parent.find('select._xx_inset_xx_').val() ) + ";";
        var boxshadow = "box-shadow:" + hlength + vlength + blurradius + spread + color + inset;

        $('input[type=hidden]', parent).val(boxshadow).trigger('change');
    });
    $('.wpdreamsBoxShadow>.triggerer').bind('click', function () {
        var parent = $(this).closest('.wpdreamsBoxShadow');
        var hidden = parent.find("input[type=hidden]");
        var boxshadow = hidden.val().replace(/(\r\n|\n|\r)/gm, "").match(/box-shadow:(.*?)px (.*?)px (.*?)px (.*?)px (.*?)\) (.*?);/);
        var plus = ")";
        if (boxshadow == null) {
            boxshadow = hidden.val().replace(/(\r\n|\n|\r)/gm, "").match(/box-shadow:(.*?)px (.*?)px (.*?)px (.*?)px (.*?) (.*?);/);
            plus = '';
        }
        parent.find('input._xx_hlength_xx_').val(boxshadow[1]);
        parent.find('input._xx_vlength_xx_').val(boxshadow[2]);
        parent.find('input._xx_blurradius_xx_').val(boxshadow[3]);
        parent.find('input._xx_spread_xx_').val(boxshadow[4]);
        parent.find('input.color').val(boxshadow[5] + plus);
        parent.find('select._xx_inset_xx_').val(boxshadow[6]);
        parent.find('input.color').spectrum('set', boxshadow[5] + plus);
    });

    /**
     * Border chooser
     */
    $('.wpdreamsBorder input[type=text], .wpdreamsBorder select').on("change", function () {
        var parent = $(this).closest('.wpdreamsBorder');

        var w = $.trim( parent.find('input._xx_width_xx_').val() ) + "px ";
        var s = $.trim( parent.find('select._xx_style_xx_').val() ) + " ";
        var c = $.trim( parent.find('input.color').val() ) + ";";
        var border = "border:" + w + s + c;

        var tl = $.trim( parent.find('input._xx_topleft_xx_').val() ) + "px ";
        var tr = $.trim( parent.find('input._xx_topright_xx_').val() ) + "px ";
        var br = $.trim( parent.find('input._xx_bottomright_xx_').val() ) + "px ";
        var bl = $.trim( parent.find('input._xx_bottomleft_xx_').val() ) + "px;";
        var borderradius = "border-radius:" + tl + tr + br + bl;

        var value = border + borderradius;

        $('input[type=hidden]', parent).val(value).trigger('change');
    });
    $('.wpdreamsBorder select').on('change', function(){
        var parent = $(this).closest('.wpdreamsBorder');
        if( $(this).val() == 'none' ) {
            parent.find('.wpd_br_to_disable').addClass('disabled');
        } else {
            parent.find('.wpd_br_to_disable').removeClass('disabled');
        }
    });
    $('.wpdreamsBorder select').trigger('change');
    $('.wpdreamsBorder>.triggerer').bind('click', function () {
        var parent = $(this).closest('.wpdreamsBorder');
        var hidden = parent.find("input[type=hidden]");

        var border = hidden.val().replace(/(\r\n|\n|\r)/gm, "").match(/border:(.*?)px (.*?) (.*?);/);
        parent.find('input._xx_width_xx_').val(border[1]);
        parent.find('input.color').val(border[3]);

        var borderradius = hidden.val().replace(/(\r\n|\n|\r)/gm, "").match(/border-radius:(.*?)px(.*?)px(.*?)px(.*?)px;/);
        parent.find('input._xx_topleft_xx_').val(borderradius[1]);
        parent.find('input._xx_topright_xx_').val(borderradius[2]);
        parent.find('input._xx_bottomright_xx_').val(borderradius[3]);
        parent.find('input._xx_bottomleft_xx_').val(borderradius[4]);
        parent.find('select._xx_style_xx_').val(border[2]);
        parent.find('input.color').spectrum('set', border[3]);

        // Separate trigger! otherwise the loaded value is overridden
        parent.find('select._xx_style_xx_').trigger('change');
    });

    // ----------------------- ACCESSIBILITY ---------------------
    if ( $('#wpdreams').hasClass('wd-accessible') ) {
        $('#wpcontent').addClass('wd-accessible');
        $('#wpd_shortcode_modal').addClass('wd-accessible');
    }
    $('#wpdreams a.wd-accessible-switch').on('click', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();

        if ( $('#wpdreams').hasClass('wd-accessible') ) {
            $('#wpcontent').removeClass('wd-accessible');
            $('#wpdreams').removeClass('wd-accessible');
            $('#wpd_shortcode_modal').removeClass('wd-accessible');
            eraseCookie('asp-accessibility');
            $(this).html($(this).data('aenable'));
        } else {
            $('#wpcontent').addClass('wd-accessible');
            $('#wpdreams').addClass('wd-accessible');
            $('#wpd_shortcode_modal').addClass('wd-accessible');
            createCookie('asp-accessibility', 1, 365);
            $(this).html($(this).data('adisable'));
        }
    });


    // ----------------------- ETC.. ---------------------
    $('a.asp_search_delete').on('click', function(e) {
        e.preventDefault();
        var del = confirm("Do yo really want to delete this search bar?");
        if (del) {
            $(this).next().submit();
        }
    });

    // ----------------------- COOKIES ---------------------
    function createCookie(name,value,days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + value + expires + "; path=/";
    }

    function readCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }

    function eraseCookie(name) {
        createCookie(name,"",-1);
    }

});
