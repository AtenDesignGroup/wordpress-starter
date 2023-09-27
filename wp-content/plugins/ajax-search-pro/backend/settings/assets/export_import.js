jQuery(function($){
    $('#asp_export_textarea').on('click', function(){
        var $this = $(this);
        $this.select();

        // Work around Chrome's little problem
        $this.mouseup(function() {
            // Prevent further mouseup intervention
            $this.unbind("mouseup");
            return false;
        });
    });

    $('#asp_export_button').on('click', function(){
        if ($('#asp_export :selected').length < 1) return false;
        $(this).next().removeClass('hiddend');
        $(this).prop('disabled', true);
        var $this = $(this);
        setTimeout(function(){
            var searches = [];
            $('#asp_export :selected').each(function(i, selected){
                searches.push( $(this).val() );
            });
            if (searches.length > 0)
                $('#asp_export_textarea').val(JSON.stringify(searches));
            $this.prop('disabled', false);
            $this.next().addClass('hiddend');
        }, 1000);
    });

    $('#asp_export_button_sett').on('click', function(){
        if ($('#asp_export_sett :selected').length < 1) return false;
        $(this).next().removeClass('hiddend');
        $(this).prop('disabled', true);
        var $this = $(this);
        setTimeout(function(){
            $('#asp_export_textarea_sett').val($('#asp_export_sett').val());
            $this.prop('disabled', false);
            $this.next().addClass('hiddend');
        }, 1000);
    });

    $('#asp_import_button_sett').on('click', function(e){
        return confirm(msg('suc_msg'));
    });

    $('.tabs a[tabid=1]').trigger('click');

    // ------------------------------------------- ETC -----------------------------------------------------------------
    function msg(k) {
        return typeof ASP_EI_LOC[k] != 'undefined' ? ASP_EI_LOC[k] : '';
    }
});