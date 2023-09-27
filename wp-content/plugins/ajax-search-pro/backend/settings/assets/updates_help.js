jQuery(function($){
    $("dt.changelog_title a").on('click', function(e){
        e.preventDefault();
        var $next = $(this).parent().next();
        if ($next.hasClass('hiddend')) {
            $next.removeClass('hiddend');
            $(this).html('hide changelog');
        } else {
            $next.addClass('hiddend');
            $(this).html('view changelog');
        }
    });

    $("#asp_activate").on("click", function(){
        if ( $("#asp_key").val() == "" ) return false;
        var data = {
            "action": "asp_license_ajax",
            "op": "activate",
            "asp_license_request_nonce": $('#asp_license_request_nonce').val(),
            "asp_key": $("#asp_key").val()
        };
        var $this = $(this);
        $this.attr("disabled", "disabled");
        $(".asp_auto_update .small-loading").css("display", "inline-block");
        $(".asp_auto_update div.errorMsg").html("").css("display", "none");
        var post = $.post(ajaxurl, data, function (response) {
            if (response.status == 1) {
                location.href = location.href;
                return false;
            }
            $this.removeAttr("disabled");
            $(".asp_auto_update .small-loading").css("display", "none");
            $(".asp_auto_update div.errorMsg").html(response.msg).css("display", "block");
        }, "JSON");
    });
    $("#asp_deactivate").on("click", function(){
        var c = confirm("Are you sure?");
        if (!c) return false;
        var data = {
            "action": "asp_license_ajax",
            "op": "deactivate",
            "asp_license_request_nonce": $('#asp_license_request_nonce').val(),
            "asp_key": $("#asp_key").val()
        };
        var $this = $(this);
        $this.attr("disabled", "disabled");
        $(".asp_auto_update .small-loading").css("display", "inline-block");
        $(".asp_auto_update div.errorMsg").html("").css("display", "none");
        var post = $.post(ajaxurl, data, function (response) {
            if (response.status == 1) {
                location.href = location.href;
                return false;
            }
            $this.removeAttr("disabled");
            $(".asp_auto_update .small-loading").css("display", "none");
            $(".asp_auto_update div.errorMsg").html(response.msg).css("display", "block");
        }, "JSON");
    });
    $("#asp_deactivated").on("click", function(){
        if ( $("#asp_keyd").val() == "" || $("#asp_site_url").val() == "" ) return false;
        var data = {
            "action": "asp_license_ajax",
            "op": "deactivate_remote",
            "asp_license_request_nonce": $('#asp_license_request_nonce').val(),
            "asp_key": $("#asp_keyd").val(),
            "site_url": $("#asp_site_url").val()
        };
        var $this = $(this);
        $this.attr("disabled", "disabled");
        $(".asp_remote_deactivate .small-loading").css("display", "inline-block");
        $(".asp_remote_deactivate div.errorMsg").html("").css("display", "none");
        $(".asp_remote_deactivate div.infoMsg").html("").css("display", "none")
        var post = $.post(ajaxurl, data, function (response) {
            if (response.status == 1) {
                $(".asp_remote_deactivate div.infoMsg").html(response.msg).css("display", "block");
                $(".asp_remote_deactivate .small-loading").css("display", "none");
                return false;
            }
            $this.removeAttr("disabled");
            $(".asp_remote_deactivate .small-loading").css("display", "none");
            $(".asp_remote_deactivate div.errorMsg").html(response.msg).css("display", "block");
        }, "JSON");
    });
});