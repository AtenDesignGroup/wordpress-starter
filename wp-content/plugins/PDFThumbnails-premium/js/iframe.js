jQuery(document).ready(function($){

    PDFJS.workerSrc = pdfth_trans.worker_src;
    PDFJS.cMapUrl = pdfth_trans.cmap_url;
    PDFJS.cMapPacked = true;

    PDFJS.getDocument(pdfth_trans.pdf_url).then(
        function(pdfDoc_) {

            pdfDoc_.getPage(1).then(function(page) {

                var vp = page.getViewport(1.0);

                var pageWidth = vp.width;
                var pageHeight = vp.height;

                var maxwidth = pdfth_trans.maxwidth;

                if (maxwidth < pageWidth && pageWidth > 0 && maxwidth > 0) {
                    // Scale down to maxwidth
                    var scale = maxwidth / pageWidth;

                    vp = page.getViewport(scale);

                    pageWidth = pageWidth * scale;
                    pageHeight = pageHeight * scale;
                }

                var canvas = $('<canvas></canvas>');

                canvas[0].width = pageWidth;
                canvas[0].height = pageHeight;

                canvas.css('width', pageWidth);
                canvas.css('height', pageHeight);

                $('body').append(canvas);

                // Render PDF page into canvas context
                var ctx = canvas[0].getContext('2d');
                var renderContext = {
                    canvasContext: ctx,
                    viewport: vp
                };
                var renderTask = page.render(renderContext);

                // Wait for rendering to finish
                renderTask.promise.then(function () {
                    var photo = canvas[0].toDataURL('image/'+pdfth_trans.imagetype);

                    var successCallback = function(data) {
                        var msg;
                        if (data && data.success && data.success === true) {
                            msg = '';
                        }
                        else {
                            if (data && data.error) {
                                msg = data.error;
                            }
                            else {
                                msg = 'Unable to reach thumbnail_receive';
                            }
                        }
                        top.pdfth_completed_thumbnail(pdfth_trans.span_id, msg);
                    };

                    var errorCallback = function(jqXHR, textStatus, errorThrown) {
                        top.pdfth_completed_thumbnail(pdfth_trans.span_id, textStatus+' from thumbnail_receive: '+errorThrown);
                    };

                    $.ajax({
                        method: 'POST',
                        url: pdfth_trans.thumbnail_receive,
                        contentType: 'application/x-www-form-urlencoded',
                        data: {
                            'imagedata': photo,
                            'post_id': pdfth_trans.attachment_post_id,
                            'pdf_url': pdfth_trans.pdf_url,
                            'nonce': pdfth_trans.nonce,
                            'imagetype': pdfth_trans.imagetype
                        },
                        dataType: 'json',
                        success: successCallback,
                        error: errorCallback
                    });
                });

            });

        },
        function(e) {
            var msg = 'Error rendering PDF: '+e.message;
            if (e.name == 'UnexpectedResponseException' && e.status == 0) {
                msg = 'PDF needs to be on same HTTP/HTTPS as the web page. '+msg;
            }
            top.pdfth_completed_thumbnail(pdfth_trans.span_id, msg);
        }
    );

});