
function pdfth_generate_thumbnail(span_id, site_url, pdf_url, post_id, nonce) {
    var $ = jQuery;

    var iframe_url = site_url+'/?pdfth_is_iframe=1&pdfth_pdfurl='+encodeURIComponent(pdf_url)
            +'&pdfth_postid='+post_id+'&pdfth_span_id='+span_id+'&pdfth_nonce='+nonce;
    var iframe = $('<iframe></iframe>',
        {
            'src': iframe_url,
            'id': span_id+'_iframe',
            //'style': 'display: none;' Did not work with Firefox - rendertask never returned
            'style': 'width: 1px; height: 1px;'
        });
    $('body').append(iframe);
    $('span#'+span_id).empty().append(document.createTextNode('Generating...'));
}

function pdfth_completed_thumbnail(span_id, text) {
    if (!text) {
        text = 'Completed!'
    }
    jQuery('span#'+span_id).empty().append(document.createTextNode(text));
    jQuery('iframe#'+span_id+'_iframe').remove();

    jQuery('span#'+span_id).trigger('pdfth_completed_thumbnail', {'span_id': span_id, 'text': text});
}

function pdfth_generate_all_thumbnails(jOutputDiv, onlynew) {
    function outputLine(text) {
        jOutputDiv.append(jQuery('<div></div>').append(document.createTextNode(text)));
    }

    outputLine("Please keep this page open. Fetching list of " + (onlynew ? 'PDFs that do not have thumbnails.' : 'all PDFs.'));

    var callback = function(data) {
        if (data.error) {
            if (data.error.message) {
                outputLine(data.error.message);
            }
            return;
        }

        if (data.length == 0) {
            outputLine('There are no PDFs that need thumbnails! Finished processing - you may now move away from this page.');
            return;
        }

        var resultsProcObject = {
            data: data,
            startCount: data.length,
            doneCount: 0,
            pdfth_completed_thumbnail : function(e, data) {
                ++this.doneCount;
                var span_id = data.span_id;

                this.submitNext();

                if (this.doneCount >= this.startCount) {
                    outputLine("Completed all thumbnail generation! You may now move away from this page.");
                }

                jOutputDiv.animate({scrollTop: jOutputDiv.height()});
            },
            submitNext : function() {
                var pdf = this.data.pop();
                if (pdf) {
                    var spanid = pdf[0];
                    var span = jQuery('<span></span>', {'id': spanid});
                    span.append(document.createTextNode('Loading ' + pdf[2]));
                    jOutputDiv.append(jQuery('<div></div>').append(document.createTextNode(pdf[5]+': ')).append(span));

                    pdfth_generate_thumbnail(pdf[0], pdf[1], pdf[2], pdf[3], pdf[4]);
                }
            }
        };

        jQuery(jOutputDiv).on('pdfth_completed_thumbnail', function(e, data) { resultsProcObject.pdfth_completed_thumbnail(e,data); } );

        // Basically run five threads
        var i = 0;
        while (i<5) {
            resultsProcObject.submitNext();
            ++i;
        }

    };

    jQuery.ajax({
        url: ajaxurl,
        data: {'action': 'pdfth_get_all_pdfs', 'pdfth_onlynew': onlynew},
        dataType: 'json',
        type: 'POST',
        success: function(resp){
            callback(resp);
        }
    }).fail(function(){
        callback({error: {message: 'Problem contacting the web server'}});
    });
}
