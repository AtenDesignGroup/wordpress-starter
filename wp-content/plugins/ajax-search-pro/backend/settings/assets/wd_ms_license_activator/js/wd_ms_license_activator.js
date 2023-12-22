jQuery(function($){
    let nonce = $('#asp_mediaservice_request_nonce').val();
    let activate = function(e){
        e.preventDefault();
        let $parent = $(this).closest('.wd_MSLicenseActivator');
        let $log = $parent.find('.ms_license_log'),
            $this = $(this),
            license = $(this).parent().find('input[name=ms_license_key]').val();
        let data = {
            'action': 'asp_media_service',
            'asp_mediaservice_request_nonce': nonce,
            'ms_license_key': license
        };
        $this.attr('disabled', 'disabled');
        $this.find('i').addClass('fa-spinner fa-spin');
        $.ajax({
            'url': ajaxurl,
            'data': data,
            'method': 'POST',
            'timeout': 10000
        }).done(function(response){
            response = JSON.parse(response);
            $this.removeAttr('disabled').find('i').removeClass('fa-spinner fa-spin');
            $log.find('p').addClass('hiddend');

            if ( response.success ) {
                $parent.find('.ms_license_active').removeClass('hiddend');
                $parent.find('.ms_license_inactive').addClass('hiddend');
                $parent.find('.ms_license_active').find('span.ms_license_key').html(license);
                $log.find('p.successMsg').removeClass('hiddend');
                $parent.find('input[name=ms_license_active]').val(1).trigger('change');
                $parent.trigger('refresh');
            } else {
                $parent.find('input[name=ms_license_active]').val(0).trigger('change');
                $log.find('p.errorMsg').html(response.text).removeClass('hiddend');
            }
        }).fail(function(){
            $this.removeAttr('disabled').find('i').removeClass('fa-spinner fa-spin');
            $log.find('p').addClass('hiddend');
            $log.find('p.errorMsg').html('Server error, please refresh the page and try again!').removeClass('hiddend');
            $parent.find('input[name=ms_license_active]').val(0).trigger('change');
        });
    };

    let deactivate = function(e){
        e.preventDefault();
        let $parent = $(this).closest('.wd_MSLicenseActivator'),
            $this = $(this),
            $log = $parent.find('.ms_license_log');
        if ( confirm('Do you want to deactivate your license key?') ) {
            $this.attr('disabled', 'disabled');
            $this.find('i').addClass('fa-spinner fa-spin');
            $log.find('p').addClass('hiddend');
            let data = {
                'action': 'asp_media_service',
                'asp_mediaservice_request_nonce': nonce,
                'ms_deactivate': 1
            };
            $.ajax({
                'url': ajaxurl,
                'data': data,
                'method': 'POST',
                'timeout': 10000
            }).done(function () {
                $this.removeAttr('disabled');
                $this.find('i').removeClass('fa-spinner fa-spin');
                $parent.find('.ms_license_usage').addClass('hiddend');
                $parent.find('.ms_license_active').addClass('hiddend');
                $parent.find('.ms_license_inactive').removeClass('hiddend');
                $parent.find('.ms_license_inactive').find('input.ms_license_key').val('');
                $parent.find('input[name=ms_license_active]').val(0).trigger('change');
            });
        }
    };

    let refresh = function(){
        if ( $(this).find('input[name=ms_license_active]').val() == 1 ) {
            $(this).addClass('disabled');
            $.ajax({
                'url': location.href,
                'method': 'GET',
                'timeout': 10000
            }).done(function (response) {
                $('body').find('.wd_MSLicenseActivator').replaceWith($(response).find('.wd_MSLicenseActivator'));
                $('.wd_MSLicenseActivator button[name=ms_license_activate]').on('click', activate);
                $('.wd_MSLicenseActivator button[name=ms_license_deactivate]').on('click', deactivate);
                $('body').find('.wd_MSLicenseActivator').removeClass('disabled');
            });
        }
    };

    $('.wd_MSLicenseActivator button[name=ms_license_activate]').on('click', activate );
    $('.wd_MSLicenseActivator button[name=ms_license_deactivate]').on('click', deactivate);
    $('.wd_MSLicenseActivator').on('refresh', refresh)
});