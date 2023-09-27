(function ($) {
    /**
     * Contains pseudo selector, case insensitive
     */
    jQuery.expr[':'].Contains = function(a, i, m) {
        return jQuery(a).text().toUpperCase()
                .indexOf(m[3].toUpperCase()) >= 0;
    };

    var matches = [];

    function os_fetch_results( phrase ) {
        clean_phrase = phrase.replace(/,|\.|\!|\?/gi);

        var parts = clean_phrase.split(" ");
        var contains = "";

        exact_matches = [];
        matches = [];

        for (var i=0; i<parts.length; i++) {
            contains = contains + ":Contains(" + parts[i] + ")";
        }

        matches = $("#wpdreams fieldset .item" + contains).slice(0, 10);
    }

    function os_group_results() {

        grouped_res = {};

        for (var i=0; i<matches.length; i++) {

            var this_group = "";
            var tabid = 0;

            var _breaker = 0;

            $parent = $(matches[i]).parent();

            while (!$parent.hasClass("wpdreams-box")) {
                if (_breaker > 500) break;

                if ( typeof $parent.attr("tabid") != "undefined" ) {
                    if (this_group == "")
                        this_group = $("ul li a[tabid=" + $parent.attr("tabid") + "]").text();
                    else
                        this_group = $("ul li a[tabid=" + $parent.attr("tabid") + "]").text() + " >> " + this_group;
                }

                if (typeof $parent.attr('tabid') != "undefined" && tabid == 0)
                    tabid = $parent.attr('tabid');

                $parent = $parent.parent();
                _breaker++;
            }

            this_group = this_group == "" ? "ungrouped" : this_group;

            if (
                $( "label", $(matches[i])).length > 0 ||
                $( "fieldset legend", $(matches[i]) ).length > 0
            ) {

                if ( typeof grouped_res[this_group] == "undefined" )
                    grouped_res[this_group] = [];

                if ( $( "fieldset legend", $(matches[i]) ).length > 0 )
                    var title = $( $( "fieldset legend", $(matches[i])).get(0) ).text();
                else
                    var title = $( $( "label", $(matches[i]) ).get(0) ).text();

                grouped_res[this_group].push({
                    "title": title,
                    "tabid": tabid,
                    "group": this_group,
                    "node": i
                });
            }
        }



    }

    function os_show_results() {
        var remaining = 10;
        var resHTML = "";

        if ( Object.keys(grouped_res).length > 0) {
            $.each(grouped_res, function (k, v) {
                resHTML += "<label>" + k + "</label>";

                for (var i = 0; i < v.length; i++) {
                    resHTML += "<p><a class='asp-os-res' xnode='" + v[i].node + "' tabid='" + v[i].tabid + "' href='#" + v[i].tabid + "'>" + v[i].title + "</a></p>";
                }
            });
        } else {
            resHTML = "<label>No results.</label>";
        }

        $("#asp-os-results").html(resHTML);
    }

    function os_clear_results() {
        $("#asp-os-results").html("");
    }

    /**
     * Very simple main logic
     */
    var t = null;
    $('#asp-os-input').on('keyup', function(){
        $this = $(this);
        clearTimeout(t);
        t = setTimeout(function(){
            var phrase = $this.val().trim();
            if (phrase.length > 0) {
                var res = os_fetch_results( phrase );
                os_group_results()
                os_show_results();
            } else {
                os_clear_results();
            }
        }, 50)
    });

    $("#asp-os-results").on("click", "a.asp-os-res", function(){
        var tabid = $(this).attr("tabid");
        $('.tabs a[tabid=' + Math.floor( tabid / 100 ) + ']').trigger('click');
        $('.tabs a[tabid=' + tabid + ']').trigger('click');
        $('.asp-os-highlighted').removeClass("asp-os-highlighted");
        $(matches[$(this).attr("xnode")]).addClass("asp-os-highlighted");
    });

    $("#wpdreams").on("click mouseleave", ".asp-os-highlighted", function(){
        $(this).removeClass("asp-os-highlighted");
    });

})(jQuery);