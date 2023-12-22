jQuery(function($){
    var post;
    var st;
    var $editor = $('#wpd-synonyms-editor');
    var tagArgs = {
        "edit-on-delete": false,
        "forbidden-chars": [ ".", "?", "!" ],
        "close-class": "wpd-tag-i",
        "tag-box-class": "wpd-tagging",
        "type-zone-class": "wpd-type-zone",
        "type-zone-placeholder": "Enter synonyms here..",
        "tag-box-editable-class": "editable",
        "tag-class": "wpd-tag",
        "tags-limit": 15,
        "tag-char": "",
        "no-spacebar": true,
        "pre-tags-separator": ","
    };

    var nonce = $('#asp_synonyms_request_nonce').val();

    $('#asp_syn_add').on('click', function(e){
        editorOpen();
    });

    // Search language change
    $('#wpd-syn-search-lang').on('change', function(){
        editorErrorHide();
        clearTimeout(st);
        st = setTimeout(function() {
            editorClose();
            findKeyword();
        }, 220);
    });
    // Search input
    $('#wpd-search-synonyms').on('input', function(){
        editorErrorHide();
        clearTimeout(st);
        st = setTimeout(function() {
            editorClose();
            findKeyword();
        }, 220);
    });

    $('#syn-editor-cancel').on('click', function(e){
        editorErrorHide();
        editorClose();
    });

    $('#syn-editor-save').on('click', function(e){
        editorErrorHide();
        if ( editorSaveCheck() == true ) {
            var synonyms = [];
            jQuery('#wpd-tag input[name="tag[]"]').each(function(i, o){
                synonyms.push($(this).val());
            });
            var data = {
                action: 'asp_syn_admin_ajax',
                op: 'update',
                asp_synonyms_request_nonce: nonce,
                keyword: $('#wpd-synonym-input').val(),
                synonyms: synonyms,
                lang: $('#wpd-synonym-lang').val(),
                overwrite_existing: editorIsUpdate() ? 1 : 0
            };
            $('#asp_syn_add').attr('disabled', 'disabled');
            if ( editorIsUpdate() )
                $editor.parent().find('>.wpd-syn-overlay').css('display', 'block');
            else
                $editor.find('.wpd-syn-overlay').css('display', 'block');
            if (post != null && typeof post.abort != 'undefined')
                post.abort();
            post = $.post(ajaxurl, data)
                .done(function(response){
                    var res = response.replace(/^\s*[\r\n]/gm, "");
                    res = res.match(/!!!ASP_SYN_START!!!(.*[\s\S]*)!!!ASP_SYN_END!!!/);
                    if (res != null && (typeof res[1] != 'undefined')) {
                        res = parseInt(res[1]); // Number of affected rows by the query, -1 on error
                        if ( res == -1 ) {
                            editorErrorRaise(msg('edt_er1'));
                        } else if ( res == 0 && !editorIsUpdate() ) {
                            editorErrorRaise(msg('edt_er2'));
                        } else {
                            // Success
                            if ( editorIsUpdate() ) {
                                updateKeyword();
                                return true; // Exit here, let the second function finish the task
                            } else {
                                editorClose();
                                findKeyword(false);
                            }
                        }
                    }
                    $('#asp_syn_add').removeAttr('disabled');
                    if ( editorIsUpdate() )
                        $editor.parent().find('>.wpd-syn-overlay').css('display', 'none');
                    else
                        $editor.find('.wpd-syn-overlay').css('display', 'none');
                }).fail(function(){
                    $('#asp_syn_add').removeAttr('disabled');
                    if ( editorIsUpdate() )
                        $editor.parent().find('>.wpd-syn-overlay').css('display', 'none');
                    else
                        $editor.find('.wpd-syn-overlay').css('display', 'none');
                });
        }
    });

    $('#wpd-synonyms-editor').on('input', '.wpd-type-zone', function(){
        editorErrorHide();
    });

    $('#wpd-synonym-input').keypress( function(e) {
        var chr = String.fromCharCode(e.which);
        // Ignore some characters
        if ( e.which == 13 ) {
            e.preventDefault();
            $('#wpd-tag .wpd-type-zone').get(0).focus();
        }
    });
    $('#wpd-synonym-input').on('input', function(){
        editorErrorHide();
    });

    $('.wpd-syn-results').on('click', '.syn-edit-row', function(e){
        editorErrorHide();
        editorClose();
        editorOpen($(this).closest('.wpd-synonyms-row'));
        $(this).closest('.wpd-synonyms-row').addClass('hiddend');
    });

    $('.wpd-syn-results').on('click', '.syn-delete-row', function(){
        if ( !confirm(msg('res_ms1')) )
            return false;

        editorErrorHide();
        $row = $(this).closest('.wpd-synonyms-row');
        var data = {
            action: 'asp_syn_admin_ajax',
            op: 'delete',
            asp_synonyms_request_nonce: nonce,
            id: $row.data('id')
        };
        $row.find('.wpd-syn-overlay').css('display', 'block');
        if (post != null && typeof post.abort != 'undefined')
            post.abort();
        post = $.post(ajaxurl, data)
            .done(function(response){
                var res = response.replace(/^\s*[\r\n]/gm, "");
                res = res.match(/!!!ASP_SYN_START!!!(.*[\s\S]*)!!!ASP_SYN_END!!!/);
                if (res != null && (typeof res[1] != 'undefined')) {
                    res = parseInt(res[1]); // Number of affected rows by the query, -1 on error
                    if ( res == -1 ) {
                        $row.find('.wpd-syn-overlay').css('display', 'none');
                        editorErrorRaise(msg('edt_er1'), $row);
                    } else if ( res == 0 ) {
                        $row.find('.wpd-syn-overlay').css('display', 'none');
                        editorErrorRaise(msg('edt_er3'), $row);
                    } else {
                        $row.detach();
                    }
                }

            }).fail(function(){
                $row.find('.wpd-syn-overlay').css('display', 'none');
            });
    });

    $('#asp-syn-remove-all').on('click', function(){
        if ( !confirm(msg('del_all')) )
            return false;

        var data = {
            action: 'asp_syn_admin_ajax',
            op: 'wipe',
            asp_synonyms_request_nonce: nonce
        };

        $('#wpd-syn-results').find('.wpd-syn-overlay').css('display', 'block');
        if (post != null && typeof post.abort != 'undefined')
            post.abort();
        post = $.post(ajaxurl, data)
            .done(function(response){
                $('#wpd-syn-results').find('.wpd-syn-overlay').css('display', 'block');
                findKeyword(false);
            }).fail(function(){
                $('#wpd-syn-results').find('.wpd-syn-overlay').css('display', 'block');
            });
    });

    var mw = window.WPD_Modal;
    var $export_div = $('#syn-export-modal').detach();
    var $import_div = $('#syn-import-modal').detach();

    $('#asp-syn-export').on('click', function(){
        mw.options({
            'type': 'info',
            'content': $export_div,
            'header': msg('mod_ms1'),
            'buttons': {
                'okay': {
                    'text': msg('mod_ms3'),
                    'type': 'okay',
                    'click': function(e, button){}
                }
            }
        });
        mw.show();
    });

    $('#asp-syn-import').on('click', function(){
        mw.options({
            'type': 'info',
            'content': $import_div,
            'header': msg('mod_ms2'),
            'buttons': {
                'okay': {
                    'text': msg('mod_ms3'),
                    'type': 'okay',
                    'click': function(e, button){}
                }
            }
        });
        mw.show();
    });

    $('#wpd_modal').on('click', '#syn-export-generate', function(){
        var $loader = $(this).closest('#wpd_modal_inner').find('.wpd-syn-overlay');
        var $error = $('#syn-export-error');
        var $errorMsg = $('#syn-export-error .errorMsg');

        $loader.css('display', 'block');
        $error.addClass('hiddend');

        var data = {
            action: 'asp_syn_admin_ajax',
            op: 'export',
            asp_synonyms_request_nonce: nonce
        };
        if (post != null && typeof post.abort != 'undefined')
            post.abort();
        post = $.post(ajaxurl, data)
            .done(function(response){
                var res = response.replace(/^\s*[\r\n]/gm, "");
                res = res.match(/!!!ASP_SYN_START!!!(.*[\s\S]*)!!!ASP_SYN_END!!!/);
                if (res != null && (typeof res[1] != 'undefined')) {
                    res = res[1];
                    if ( res == "-1" || res == "0" ) {
                        $error.removeClass('hiddend');
                        $errorMsg.removeClass('hiddend');
                    }
                    if ( res == "-1" ) {
                        $errorMsg.text(msg('gen_er1'));
                    } else if ( res == "0" ) {
                        $errorMsg.text(msg('gen_er2'));
                    } else {
                        $('#syn-export-download a').attr('href', res + '?' + (Math.random() + 1).toString(36).substring(2,7));
                        $('#syn-export-download').removeClass('hiddend');
                    }
                }
                $loader.css('display', 'none');
            }).fail(function(){
                $loader.css('display', 'block');
            });
    });


    $('#wpd_modal').on('input change focus blur', 'input[name=syn-import-upload]', function(){
        if ( $(this).val() == '' ) {
            $('#syn-import-upload').attr('disabled', 'disabled');
        } else {
            $('#syn-import-upload').removeAttr('disabled');
        }
    });
    $('#wpd_modal').on('click', '#syn-import-upload', function(){
        var $loader = $(this).closest('#wpd_modal_inner').find('.wpd-syn-overlay');
        var $error = $('#syn-import-error');
        var $errorMsg = $('#syn-import-error .errorMsg');
        var $infoMsg = $('#syn-import-error .infoMsg');

        $error.addClass('hiddend');
        $loader.css('display', 'block');

        var data = {
            action: 'asp_syn_admin_ajax',
            path: $('input[name=syn-import-upload]').val(),
            op: 'import',
            asp_synonyms_request_nonce: nonce
        };
        if (post != null && typeof post.abort != 'undefined')
            post.abort();
        post = $.post(ajaxurl, data)
            .done(function(response){
                var res = response.replace(/^\s*[\r\n]/gm, "");
                res = res.match(/!!!ASP_SYN_START!!!(.*[\s\S]*)!!!ASP_SYN_END!!!/);
                if (res != null && (typeof res[1] != 'undefined')) {
                    res = res[1];
                    if ( res == "-2" || res == "-1" || res == "0" ) {
                        $error.removeClass('hiddend');
                        $errorMsg.removeClass('hiddend');
                        $infoMsg.addClass('hiddend');
                    }
                    if ( res == "-2" ) {
                        $errorMsg.text(msg('gen_er1'));
                    } else if ( res == "-1" ) {
                        $errorMsg.text(msg('gen_er3'));
                    } else if ( res == "0" ) {
                        $errorMsg.text(msg('gen_er4'));
                    } else {
                        $infoMsg.html(msg('gen_ms1') + ' <strong>' + res + '</strong> ' + msg('gen_ms2'));
                        $error.removeClass('hiddend');
                        $infoMsg.removeClass('hiddend');
                        $errorMsg.addClass('hiddend');

                        $('#syn-import-download').removeClass('hiddend');
                        $('#wpd-search-synonyms').val('');
                        $('#wpd-syn-search-lang').val('any').trigger('change');
                    }
                }
                $loader.css('display', 'none');
            }).fail(function(){
                $loader.css('display', 'block');
            });
    });
    // --------------------------------------- RESULTS LIST FUNCTIONS --------------------------------------------------

    function updateKeyword() {
        var keyword = $editor.prev().data('keyword');
        var lang = $editor.prev().data('lang')
        if ( keyword == '' )
            return false;
        var data = {
            action: 'asp_syn_admin_ajax',
            op: 'findexact',
            asp_synonyms_request_nonce: nonce,
            keyword: keyword,
            lang: lang
        };

        if (post != null && typeof post.abort != 'undefined')
            post.abort();
        $('#wpd-syn-results>.wpd-syn-overlay').css('display', 'block');
        post = $.post(ajaxurl, data)
            .done(function(response) {
                var res = response.replace(/^\s*[\r\n]/gm, "");
                res = res.match(/!!!ASP_SYN_START!!!(.*[\s\S]*)!!!ASP_SYN_END!!!/);
                if (res != null && (typeof res[1] != 'undefined')) {
                    res = JSON.parse(res[1]);
                    if ( res.length > 0 ) {
                        $.each(res, function(i, o){
                            var lang = o.lang == '' ? 'default' : o.lang;
                            var $n = $('#wpd-synonyms-row-sample').clone();
                            $n.find('.syn-kw-col').html(o.keyword + '<span>' + '[language: ' + lang + ']' + '</span>');
                            $n.find('.syn-syn-col').html(o.synonyms.replace(/,/g, ', '));
                            $n.data('keyword', o.keyword);
                            $n.data('lang', o.lang);
                            $n.data('id', o.id);
                            $n.attr('id', '');
                            $n.removeClass('hiddend');
                            $editor.prev().detach();    // Remove the old
                            $n.insertBefore($editor);    // Add the new
                            editorClose();
                            return false; // Break
                        });
                    }
                }
                $('#wpd-syn-results>.wpd-syn-overlay').css('display', 'none');
            });
    }

    function findKeyword(phrase) {
        if ( typeof phrase == 'undefined' )
            phrase = $('#wpd-search-synonyms').val();
        else if ( phrase === false )
            phrase = '';
        var data = {
            action: 'asp_syn_admin_ajax',
            op: 'find',
            asp_synonyms_request_nonce: nonce,
            keyword: phrase,
            lang: $('#wpd-syn-search-lang').val()
        };

        if (post != null && typeof post.abort != 'undefined')
            post.abort();
        $('#wpd-syn-results>.wpd-syn-overlay').css('display', 'block');
        post = $.post(ajaxurl, data)
            .done(function(response) {
                var res = response.replace(/^\s*[\r\n]/gm, "");
                res = res.match(/!!!ASP_SYN_START!!!(.*[\s\S]*)!!!ASP_SYN_END!!!/);
                if (res != null && (typeof res[1] != 'undefined')) {
                    res = JSON.parse(res[1]);
                    $('#wpd-syn-results').find('.wpd-synonyms-row').detach();
                    if ( res.length > 0 ) {
                        $('#wpd-syn-results').find('.wpd-synonyms-nores').addClass('hiddend');
                        $.each(res, function(i, o){
                            var $n = $('#wpd-synonyms-row-sample').clone();
                            var lang = o.lang == '' ? 'default' : o.lang;
                            $n.find('.syn-kw-col').html(o.keyword + '<span>' + '[language: ' + lang + ']' + '</span>');
                            $n.find('.syn-syn-col').html(o.synonyms.replace(/,/g, ', '));
                            $n.data('keyword', o.keyword);
                            $n.data('lang', o.lang);
                            $n.data('id', o.id);
                            $n.attr('id', '');
                            $n.removeClass('hiddend');
                            $n.appendTo('#wpd-syn-results');
                        });
                    } else {
                        $('#wpd-syn-results').find('.wpd-synonyms-nores').removeClass('hiddend');
                    }

                }
                $('#wpd-syn-results>.wpd-syn-overlay').css('display', 'none');
            });
    }

    // ------------------------------------- EDITOR RELATED FUNCTIONS --------------------------------------------------

    function editorOpen($node) {
        editorReset();
        $editor.removeClass('hiddend');
        editorErrorHide();
        // Empty editor
        if ( typeof $node == 'undefined' ) {
            $editor.data('update', 0);
            $('#wpd-synonym-input').removeAttr('disabled');
            $('#wpd-synonym-lang').removeAttr('disabled');
            $editor.detach().insertAfter('.wpd-synonyms-row-head');
            $('#wpd-tag').tagging(tagArgs);
        } else {
            $editor.data('update', 1);
            $editor.detach().insertAfter($node);
            $('#wpd-synonym-input').val($node.data('keyword')).attr('disabled', 'disabled');
            $('#wpd-synonym-lang').val($node.data('lang')).attr('disabled', 'disabled');
            $('#wpd-tag').html($node.find('.syn-syn-col').text());
            $('#wpd-tag').tagging(tagArgs);
        }
    }
    function editorIsUpdate() {
        return $editor.data('update') == 1;
    }
    function editorClose($node) {
        if ( typeof $node != 'undefined' )
            $node.removeClass('hiddend');
        editorReset();
        $editor.addClass('hiddend');
        $('#asp_syn_add').removeAttr('disabled');
        if ( $editor.prev().length > 0 && $editor.prev().hasClass('wpd-synonyms-row') ) {
            $editor.prev().removeClass('hiddend');
        }
    }
    function editorSaveCheck() {
        // Trigger the tag input save
        $('.wpd-type-zone').trigger('blur');

        if ( $('#wpd-synonym-input').val().trim() == '' ) {
            editorErrorRaise(msg('edt_er4'));
            return false;
        }

        // Synonyms field check
        var synonyms = [];
        jQuery('#wpd-tag input[name="tag[]"]').each(function(i, o){
            synonyms.push($(this).val());
        });
        if ( synonyms.length <= 0 ) {
            editorErrorRaise(msg('edt_er5'));
            return false;
        }
        return true;
    }
    function editorErrorHide() {
        $('.wpd-synonyms-err').addClass('hiddend');
    }
    function editorErrorRaise(msg, $node) {
        $editor = typeof $node == 'undefined' ? $editor : $node;
        $editor.find('.wpd-synonyms-err').removeClass('hiddend');
        $editor.find('.wpd-synonyms-err .errorMsg').html(msg);
    }
    function editorReset() {
        $('#wpd-synonym-input').val('');
        if (
            typeof $('#wpd-tag').data('tag-box') != 'undefined' &&
            $('#wpd-tag').data('tag-box') != null
        )
            $('#wpd-tag').tagging('destroy', tagArgs);
    }

    // ------------------------------------------- ETC -----------------------------------------------------------------
    function msg(k) {
        return typeof ASP_SYN_MSG[k] != 'undefined' ? ASP_SYN_MSG[k] : '';
    }
});
