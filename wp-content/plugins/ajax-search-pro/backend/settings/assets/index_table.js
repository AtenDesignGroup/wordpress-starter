jQuery(function ($) {
    // ------------- TAB HANDLING ---------------
    // Remove the # from the hash, as different browsers may or may not include it
    var hash = location.hash.replace('#','');
    if (hash != '') {
        hash = parseInt(hash);
        $('.tabs a[tabid=' + hash + ']').trigger('click');
    } else {
        $('.tabs a[tabid=1]').trigger('click');
    }

    $('.tabs a').on('click', function(){
        location.hash = $(this).attr('tabid');
    });
    // ------------------------------------------

    var post = null;
    var postTimeout = null;
    var indexing = false;
    var defragmenting = false;
    var defragCount = 0;
    var defragInterval = 300000; // Defrag at every X number of keywords found
    var failCount = 0;  // Consecutive failures counter
    var reloadStats = true;
    var statsData = {
        postsIndexed: 0,
        postsIndexedStart: 0,
        postsIndexedNow: 0,
        postsToIndex: 10,
        postsTotal: 10,
        keywordsFound: 0,
        totalKeywords: 0,
        runTimeNow: 0,
        postsPerSecond: 0
    };
    ASP_IT.stats = statsData;
    var $buttons = $("#index_buttons input[type='button']");
    var $progress = $(".wd_progress_text, .wd_progress, .wd_progress_stop");
    var $progress_bar = $(".wd_progress span");
    var $progress_text = $(".wd_progress_text");
    var $overlay = $("#asp_it_disable");
    var $success = $("#asp_i_success");
    var $error = $("#asp_i_error");
    var $error_cont = $("#asp_i_error_cont");
    var $dontclose = $("#asp_i_dontclose");
    var data = "";
    var keywords_found = 0;
    var remaining_blogs = [];
    var blog = "";
    var initial_action = "";
    var lastRequestDuration = 0;
    var longestRequestDuration = 0;
    var _lrStart;
    var _timer;

    function showTimer(duration, display) {
        duration = parseInt(duration);
        var timer = duration, minutes, seconds, hours;
        clearInterval(_timer);
        _timer = setInterval(function () {
            hours = parseInt(timer / 3600, 10);
            minutes = parseInt(parseInt(timer % 3600, 10) / 60, 10);
            seconds = parseInt(timer % 60, 10);

                    hours = hours < 10 ? "0" + hours : hours;
            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            $(display).text("Approx. remaining time: " + hours + ":" + minutes + ":" + seconds);

            if (--timer < 0) {
                timer = duration;
            }
        }, 1000);
    }

    function hideTimer(display) {
        clearInterval(_timer);
        $(display).text("");
    }

    function stats(first_run) {
        reloadStats = false;
        first_run = typeof first_run == 'undefined' ? false : first_run;
        $('.index_stats_container .index_stats').addClass('hiddend');
        $('.index_stats_container .index_stats_loader').removeClass('hiddend');

        $.ajax({
            'url': ajaxurl,
            'data': {
                'action': 'asp_indextable_get_stats',
                'asp_it_request_nonce': $('#asp_it_request_nonce').val(),
                'blog_id': blog,
                'data': $('#asp_indextable_settings').serialize(),
            },
            'method': 'POST',
            'timeout': 90000
        }).done(function(response){
            var res = response.replace(/^\s*[\r\n]/gm, "");
            res = res.match(/!!!ASP_INDEX_STAT_START!!!(.*[\s\S]*)!!!ASP_INDEX_STAT_STOP!!!/);
            if (res != null && (typeof res[1] != 'undefined')) {
                res = JSON.parse(res[1]);
                statsData.postsIndexed = Number(res.postsIndexed);
                if ( first_run )
                    statsData.postsIndexedStart = Number(res.postsIndexed);
                statsData.postsToIndex = Number(res.postsToIndex);
                statsData.totalKeywords = Number(res.totalKeywords);
                statsData.postsTotal = statsData.postsIndexed + statsData.postsToIndex;
                $("#indexed_counter").html(statsData.postsIndexed);
                $("#not_indexed_counter").html(statsData.postsToIndex);
                $("#keywords_counter").html(statsData.totalKeywords);
                $('.index_stats_container .index_stats').removeClass('hiddend');
                $('.index_stats_container .index_stats_loader').addClass('hiddend');
            }
        }).fail(function(r){
            console.log("Failed to get stats.");
            $('.index_stats_container .index_stats').removeClass('hiddend');
            $('.index_stats_container .index_stats_loader').addClass('hiddend');
        });
    }

    stats(true);
    var statsLastTrigger = (new Date()).getTime();
    setInterval(function () {
        var elapsedTime = ( (new Date()).getTime() - statsLastTrigger ) / 1000;
        if ( reloadStats && elapsedTime > 179 && !indexing && !defragmenting ) {
            stats();
            statsLastTrigger = (new Date()).getTime();
        }
    }, 500 );

    function index( action, failures, delay ) {
        action = typeof action == 'undefined' ? 'extend' : action;
        failures = typeof failures == 'undefined' ? false : failures;
        delay = typeof delay == 'undefined' ? 0 : delay;

        var timeout = 3000;
        if ( failures ) {
            timeout = 15000;
        } else {
            if ( lastRequestDuration >= 10 ) {
                timeout = 7000;
            } else if ( lastRequestDuration <= 4 ) {
                timeout = 1500;
            }
        }
        timeout += delay;

        data = {
            action: 'asp_indextable_admin_ajax',
            asp_index_action: action,
            blog_id: blog,
            data: $('#asp_indextable_settings').serialize(),
            asp_it_request_nonce: $('#asp_it_request_nonce').val(),
            last_request_duration: lastRequestDuration,
            longest_request_duration: longestRequestDuration
        };

        // Wait a bit to cool off the server
        postTimeout = setTimeout(function () {
            _lrStart = (new Date()).getTime();
            indexing = true;
            post = $.post(ajaxurl, data)
                .done(asp_on_post_success)
                .fail(asp_on_post_failure);
        }, timeout );
    }

    function defragment( the_action ) {
        the_action = typeof the_action == 'undefined' ? false : the_action;
        defragCount++;
        var dfStart = (new Date()).getTime();
        console.log("Optimizing: ", defragCount);
        defragmenting = true;
        $.ajax({
            'url': ajaxurl,
            'data': {
                'action': 'asp_indextable_optimize',
                'asp_it_request_nonce': $('#asp_it_request_nonce').val(),
            },
            'method': 'POST',
            'timeout': 60000
        }).done(function(r){
            console.log("[S] Optimizing finished in: ", parseInt(( (new Date()).getTime() - dfStart ) / 1000) );
            defragmenting = false;
            if ( the_action !== false ) {
                index(the_action, false, 15000);
            }
        }).fail(function(r){
            console.log("[F] Optimizing finished in: ", parseInt(( (new Date()).getTime() - dfStart ) / 1000) );
            defragmenting = false;
            if ( the_action !== false ) {
                index(the_action, false, 30000);
            }
        });
    }

    function asp_on_post_success(response) {
        lastRequestDuration = (( (new Date()).getTime() - _lrStart ) / 1000);
        longestRequestDuration = lastRequestDuration > longestRequestDuration ? lastRequestDuration : longestRequestDuration;

        indexing = false;

        var res = response.replace(/^\s*[\r\n]/gm, "");
        res = res.match(/!!!ASP_INDEX_START!!!(.*[\s\S]*)!!!ASP_INDEX_STOP!!!/);
        if (res != null && (typeof res[1] != 'undefined')) {
            res = JSON.parse(res[1]);

            if (
                typeof res.postsIndexedNow != "undefined" ||
                (typeof res.postsIndexedNow != "undefined" && remaining_blogs.length > 0)
            ) {
                failCount = 0; // Reset fail counter

                // New or extend operation
                res.postsToIndex = Number(res.postsToIndex);
                statsData.keywordsFound = Number(res.keywordsFound);
                statsData.totalIgnored = Number(res.totalIgnored);
                statsData.postsIndexedNow += Number(res.postsIndexedNow);
                statsData.runTimeNow += lastRequestDuration;
                statsData.postsPerSecond = statsData.postsIndexedNow / statsData.runTimeNow;

                $("#index_db_other_data").html('Ignored: ' + res.totalIgnored);

                if (res.postsToIndex > 0 || remaining_blogs.length > 0) {
                    var percent = ( (statsData.postsIndexedNow + statsData.postsIndexedStart) / statsData.postsTotal ) * 100;
                    percent = percent < 0 ? 0 : percent;
                    percent = percent > 100 ? 99.99 : percent;
                    keywords_found += statsData.keywordsFound;

                    $progress_bar.css('width', percent + "%");
                    showTimer(
                        ( (statsData.postsTotal - statsData.postsIndexedNow - statsData.postsIndexedStart) / statsData.postsPerSecond ) + longestRequestDuration,
                        '#it_timer'
                    );

                    if ($('input[name=it_blog_ids]').val() != "")
                        $progress_text.html(msg('msg_pro') + " " + percent.toFixed(2) + "% | " + msg('msg_kwf') + keywords_found + " | " + msg('msg_blo') + " " + blog);
                    else
                        $progress_text.html(msg('msg_pro') + " " + percent.toFixed(2) + "% | " + msg('msg_kwf') + keywords_found);

                    var the_action = 'extend';
                    // No posts left, try switching the blog
                    if (res.postsToIndex <= 0 && remaining_blogs.length > 0) {
                        blog = remaining_blogs.shift();
                        if (initial_action == 'new')
                            the_action = 'switching_blog';
                    }

                    if ( Math.floor( keywords_found / defragInterval) > defragCount ) {
                        defragment(the_action);
                    } else {
                        index(the_action);
                    }

                    reloadStats = true;

                    return;
                }

                // Indexing finished below this line
                keywords_found += statsData.keywordsFound;

                // Next indexing starting point
                statsData.postsIndexedStart += statsData.postsIndexedNow;

                if ( keywords_found > 10000 && defragCount == 0 ) {
                    // Trigger silent index table alter to prevent fragmentations
                    defragment(false );
                }

                stats();
                hideTimer("#it_timer");

                $('.wd_MSLicenseActivator').trigger('refresh');

                $success.removeClass('hiddend').html( sprintf(msg('msg_skw'), keywords_found) );
                if ( typeof WPD_Modal != 'undefined' && ASP_IT.first_index == 1 ) {
                    var $it_first_modal = $('#it_first_modal').detach();
                    WPD_Modal.options({
                        'type': 'info',
                        'content': $it_first_modal,
                        'header': msg('mod_h1'),
                        'buttons': {
                            'okay': {
                                'text': msg('mod_ms1'),
                                'type': 'okay',
                                'click': function(e, button){}
                            }
                        }
                    });
                    WPD_Modal.show();
                }
                createCookie('_asp_first_index', 1, 9999);
            } else {
                //statsData.postsToIndex = Number(res.postsToIndex);
                statsData.totalKeywords = Number(res.totalKeywords);

                $("#indexed_counter").html(0);
                $("#not_indexed_counter").html(statsData.postsToIndex);
                $("#keywords_counter").html(statsData.totalKeywords);

                $success.removeClass('hiddend').html(msg('msg_emp'));
            }
        } else {
            $error.removeClass('hiddend').html(msg('msg_er1') + ' ');
            $error_cont.removeClass('hiddend').val(response);
        }

        $buttons.removeAttr('disabled');
        $progress.addClass('hiddend');
        $overlay.addClass('hiddend');
        $dontclose.addClass('hiddend');
    }

    function asp_on_post_failure(response, t) {
        lastRequestDuration = parseInt(( (new Date()).getTime() - _lrStart ) / 1000);
        longestRequestDuration = lastRequestDuration > longestRequestDuration ? lastRequestDuration : longestRequestDuration;

        // Manual abort, do nothing
        if ( response.aborted || t == 'abort' )
            return;

        ++failCount;

        indexing = false;

        // 10 consecutive fails, abort
        if ( failCount > 10 ) {
            if (t === "timeout") {
                $error.removeClass('hiddend').html(msg('msg_er2'));
            } else {
                $error.removeClass('hiddend').html(msg('msg_er1') + ' ');
                console.log(response);
                if (
                    typeof response.status != 'undefined' &&
                    typeof response.statusText != 'undefined'
                ) {
                    $error_cont.removeClass('hiddend').val(msg('msg_sta') + " " + response.status + "\n" + msg('msg_cod') + " " + response.statusText);
                } else {
                    $error_cont.removeClass('hiddend').val(response);
                }
            }
            $buttons.removeAttr('disabled');
            $progress.addClass('hiddend');
            $overlay.addClass('hiddend');
            $dontclose.addClass('hiddend');
        } else {
            console.log('Index Table Request failed, but continuing in 15 seconds. Consecutive failures count:', failCount);
            console.log(response);

            index('extend', true);
        }
    }

    $('#asp_index_new, #asp_index_extend, #asp_index_delete').on('click', function (e) {
        if (!confirm($(this).attr('index_msg'))) {
            return false;
        }

        statsData.postsIndexedNow = 0;
        statsData.runTimeNow = 0;
        if ( $(this).attr('index_action') == 'new' ) {
            statsData.postsIndexed = 0;
			statsData.postsIndexedStart = 0;
		}

        $('.asp-notice-ri').css("display", "none");

        $('.wd_progress_stop').trigger('click');

        var blogids_input_val = $('input[name=it_blog_ids]').val().replace('xxx1', '');

        if ($('input.use-all-blogs').is(':checked')) {
            $(".wpdreamsBlogselect ul.connectedSortable li").each(function () {
                remaining_blogs.push($(this).attr('bid'));
            });
        } else if (blogids_input_val != "") {
            remaining_blogs = blogids_input_val.split('|');
        } else {
            remaining_blogs = ASP_IT.current_blog_id.slice(0);
        }

        // Still nothing
        if (remaining_blogs.length == 0)
            remaining_blogs = ASP_IT.current_blog_id.slice(0); // make a shadow clone, otherwise ASP_IT.curr.. will be altered

        blog = remaining_blogs.shift();
        $buttons.attr('disabled', 'disabled');
        $progress.removeClass('hiddend');
        $overlay.removeClass('hiddend');
        $dontclose.removeClass('hiddend');
        $success.addClass('hiddend');
        $error.addClass('hiddend');
        $error_cont.addClass('hiddend');

        initial_action = $(this).attr('index_action');

        index($(this).attr('index_action'));
    });

    $('.wd_progress_stop').on('click', function (e) {
        if (post != null) post.abort();
        clearTimeout(postTimeout);
        indexing = false;
        keywords_found = 0;
        data = "";
        if ( e.originalEvent !== undefined ) {
            stats();
        }
        hideTimer("#it_timer");
        $("#index_buttons input[type='button']").removeAttr('disabled');
        $(".wd_progress_text, .wd_progress, .wd_progress_stop").addClass('hiddend');
        $error.addClass('hiddend');
        $error_cont.addClass('hiddend');
        $progress_bar.css('width', "0%");
        $progress_text.html(msg('msg_ini'));
        $overlay.removeClass('hiddend');
        $dontclose.addClass('hiddend');
    });

    $("ul.connectedSortable", $('input[name=it_post_types]').parent()).on("sortupdate", function(){
        var val = JSON.parse( WD_Helpers.Base64.decode($('input[name=it_post_types]').val().replace(/^(_decode_)/,"")) );
        val = val.join(',');
        if ( val.indexOf('attachment') > -1 ) {
            $('#it_file_indexing').removeClass('disabled-opacity');
            $('#it_media_service').removeClass('hiddend');
            if (
                typeof WPD_Modal != 'undefined' &&
                readCookie('_asp_media_service_modal') == null &&
                $('input[name=ms_license_active]').val() != 1
            ) {
                WPD_Modal.options({
                    'type': 'info',
                    'content': $('#it_media_service_modal').detach(),
                    'header': msg('mod_h2'),
                    'buttons': {
                        'okay': {
                            'text': msg('mod_ms2'),
                            'type': 'okay',
                            'click': function(e, button){
                                createCookie('_asp_media_service_modal', 1, 9999);
                                $('#asp_media_service_link').get(0).click();
                            }
                        },
                        'cancel': {
                            'text': msg('mod_ms3'),
                            'type': 'cancel',
                            'click': function(e, button){
                                createCookie('_asp_media_service_modal', 1, 9999);
                            }
                        }
                    }
                });
                WPD_Modal.show();
            }
        } else {
            $('#it_file_indexing').addClass('disabled-opacity');
            $('#it_media_service').addClass('hiddend');
        }
    });
    $("ul.connectedSortable", $('input[name=it_post_types]').parent()).trigger("sortupdate");


    /**
     * @description determine if an array contains one or more items from another array.
     * @param {array} haystack the array to search.
     * @param {array} arr the array providing items to check for in the haystack.
     * @return {boolean} true|false if haystack contains at least one item from arr.
     */
    var findOne = function (haystack, arr) {
        return arr.some(function (v) {
            return haystack.indexOf(v) >= 0;
        });
    };
    var mimes = {
        'pdf': [
            'application/pdf'
        ],
        'text' : [
            'text/plain',
            'text/csv',
            'text/tab-separated-values',
            'text/calendar',
            'text/css',
            'text/html'
        ],
        'richtext' : [
            'text/richtext',
            'application/rtf'
        ],
        'mso_word' : [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-word.document.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'application/vnd.ms-word.template.macroEnabled.12',
            'application/vnd.oasis.opendocument.text'
        ],
        'mso_excel' : [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel.sheet.macroEnabled.12',
            'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'application/vnd.ms-excel.template.macroEnabled.12',
            'application/vnd.ms-excel.addin.macroEnabled.12',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.chart',
            'application/vnd.oasis.opendocument.database',
            'application/vnd.oasis.opendocument.formula'
        ],
        'mso_powerpoint' : [
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.template',
            'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.graphics'
        ],
        'image': [
            'image/jpeg',
            'image/gif',
            'image/png',
            'image/bmp',
            'image/tiff',
            'image/x-icon'
        ],
        'video': [
            'video/x-ms-asf',
            'video/x-ms-wmv',
            'video/x-ms-wmx',
            'video/x-ms-wm',
            'video/avi',
            'video/divx',
            'video/x-flv',
            'video/quicktime',
            'video/mpeg',
            'video/mp4',
            'video/ogg',
            'video/webm',
            'video/x-matroska'
        ],
        'audio': [
            'audio/mpeg',
            'audio/x-realaudio',
            'audio/wav',
            'audio/ogg',
            'audio/midi',
            'audio/x-ms-wma',
            'audio/x-ms-wax',
            'audio/x-matroska'
        ]
    };


    $("input[name=it_pool_size_auto]").on('change', function(){
       if ( $(this).val() == 1 ) {
           $('.it_pool_size.item').addClass('disabled');
       } else {
           $('.it_pool_size.item').removeClass('disabled');
       }
    });
    $("input[name=it_pool_size_auto]").trigger('change');

    WPD.Conditionals.init('#it_file_indexing');

    // ------------------------------------------- ETC -----------------------------------------------------------------
    function msg(k) {
        return typeof ASP_IT_MSG[k] != 'undefined' ? ASP_IT_MSG[k] : '';
    }
    function sprintf(s) {
        var i = 1, r = '';
        while ( s.indexOf('%s') !== false ) {
            r = typeof arguments[i] !== 'undefined' ? arguments[i] : '';
                s = s.replace('%s', r);
          i++;
          if ( i > 40 )
            break;
        }
        return s;
    }


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