jQuery(function ($) {
    var $success = $("#asp_i_success");
    var $error = $("#asp_i_error");
    var $error_cont = $("#asp_i_error_cont");

    $('#asp_reset, #asp_wipe').on('click', function(e){
        e.preventDefault();
        asp_clear_msg();

        if ( $(this).attr('id') == 'asp_reset' )
            var r = confirm(msg('msg_res'));
        else if ( $(this).attr('id') == 'asp_wipe' )
            var r = confirm(msg('msg_rem'));
        else
            var r = true;
        if (r == true) {
            asp_disable_buttons();
            var data = {
                'action' : 'asp_maintenance_admin_ajax',
                'data' : $(this).closest('form').serialize()
            };
            $.post(ajaxurl, data)
                .done(asp_on_post_success)
                .fail(asp_on_post_failure);
            $('.loading-small', $(this).parent()).removeClass('hiddend');
        }
        return true;
    });

    $('#asp_index_defrag').on('click', function(e){
        e.preventDefault();
        asp_clear_msg();
        asp_disable_buttons();
        var data = {
            'action' : 'asp_maintenance_admin_ajax',
            'data' : $(this).closest('form').serialize()
        };
        $.ajax({
            'url': ajaxurl,
            'data': data,
            'method': 'POST',
            'timeout': 60000
        }).done(function(){
            asp_enable_buttons();
            asp_show_success('Index table optimized!');
        }).fail(function(){
            asp_enable_buttons();
            asp_show_success('Index table optimized!');
        });
        $('.loading-small', $(this).parent()).removeClass('hiddend');
        return true;
    });

    function asp_on_post_success(response) {
        var res = response.replace(/^\s*[\r\n]/gm, "");
        res = res.match(/!!!ASP_MAINT_START!!!(.*[\s\S]*)!!!ASP_MAINT_STOP!!!/);
        if (res != null && (typeof res[1] != 'undefined')) {
            res = JSON.parse(res[1]);
            if (typeof res.status != "undefined" && res.status == 1 ) {
                if ( res.action == 'redirect' ) {
                    asp_show_success('<strong>SUCCESS: </strong>' + res.msg);
                    setTimeout(function () {
                        location.href = ASP_MNT.admin_url + '/plugins.php';
                    }, 5000);
                } else if ( res.action == 'refresh' ) {
                    asp_show_success(msg('msg_suc'));
                    $('form#asp_empty_redirect input[name=asp_mnt_msg]').val(res.msg);
                    $('form#asp_empty_redirect').submit();
                } else {
                    asp_show_success('<strong>'+msg('msg_ssc')+' </strong>' + res.msg);
                }
            } else {
                if (typeof res.status != "undefined" && res.status == 0 ) {
                    asp_show_error('<strong>'+msg('msg_fal')+' </strong>' + res.msg);
                } else {
                    asp_show_error(msg('msg_err') + ' ', response);
                }
                asp_enable_buttons();
            }
        } else { // Failure?
            asp_show_error(msg('msg_err') + ' ', response);
            asp_enable_buttons();
        }
    }
    function asp_on_post_failure(response, t) {
        if (t === "timeout") {
            asp_show_error(msg('msg_tim'));
        } else {
            asp_show_error(msg('msg_err') + ' ', response);
        }
        asp_enable_buttons();
    }

    function asp_show_success(msg) {
        $success.removeClass('hiddend').html(msg);
    }

    function asp_show_error(msg, response) {
        $error.removeClass('hiddend').html(msg);
        if ( typeof response !== 'undefined') {
            console.log(response);
            if (
                typeof response.status != 'undefined' &&
                typeof response.statusText != 'undefined'
            ) {
                $error_cont.removeClass('hiddend').val("Status: " + response.status + "\nCode: " + response.statusText);
            } else {
                $error_cont.removeClass('hiddend').val(response);
            }
        }
    }

    function asp_disable_buttons() {
        $('#asp_reset, #asp_wipe, #asp_index_defrag').addClass('disabled');
    }

    function asp_enable_buttons() {
        $('.loading-small').addClass('hiddend');
        $('#asp_reset, #asp_wipe, #asp_index_defrag').removeClass('disabled');
    }

    function asp_clear_msg() {
        $error_cont.addClass('hiddend');
        $error.addClass('hiddend');
        $success.addClass('hiddend');
    }

    // ------------------------------------------- ETC -----------------------------------------------------------------
    function msg(k) {
        return typeof ASP_MNT[k] != 'undefined' ? ASP_MNT[k] : '';
    }
});
