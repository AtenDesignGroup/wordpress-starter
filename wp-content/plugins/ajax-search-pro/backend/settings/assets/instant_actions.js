jQuery(function($){
    // ---------------------- HELPER FUNCTIONS ----------------------
    function h_code(str){
        var hash = 0;
        if (str.length == 0) return hash;
        for (i = 0; i < str.length; i++) {
            char = str.charCodeAt(i);
            hash = ((hash<<5)-hash)+char;
            hash = hash & hash; // Convert to 32bit integer
        }
        return hash;
    }
    function disable_instant() {
        $('input[name=autocomplete_instant_limit]').closest('.item').addClass('hiddend');
    }
    function enable_instant() {
        $('input[name=autocomplete_instant_limit]').closest('.item').removeClass('hiddend');
    }
    function get_working_configuration() {
        var s = $('input[name=autocomplete_source]').val() +
            $('input[name=autocomplete_length]').val() +
            $('input[name=autocomplete_instant_limit]').val();
        return h_code(s);
    }
    function get_last_working_configuration() {
        return $('input[name=autocomplete_instant_gen_config]').val();
    }
    function set_last_working_configuration() {
        $('input[name=autocomplete_instant_gen_config]').val(get_working_configuration());
    }

    // -------------------- THE ACTUAL HANDLERS --------------------
    $('select[name=autocomplete_instant]').on('change', function(e){
        if ( $(this).val() != 'enabled' ) {
            disable_instant();
        } else {
            enable_instant();
        }
        // Resolve button statuses
        if ( get_working_configuration() == get_last_working_configuration() ) {
            $('input[id^=asp_inst_generate]').addClass('hiddend');
            $('#asp_inst_generate_d').removeClass('hiddend');
        } else if ( !$('#asp_inst_generate_d').hasClass('hiddend') ) {
            $('input[id^=asp_inst_generate]').removeClass('hiddend');
            $('#asp_inst_generate_d').addClass('hiddend');
        }
    });
    $('#asp_inst_generate').on('click', function(e){
        $(this).addClass('hiddend');
        $('#asp_inst_generate_save').addClass('hiddend');
        $('#asp_inst_generate_cancel').removeClass('hiddend');
        $('#wpd_white_fixed_bg').css('display', 'block');
        $('#asp_inst_generate').closest('.item').addClass('highlight_over_bg');
        // Generate DB
        // Set status
        set_last_working_configuration();
    });

    $('#asp_inst_generate_save').on('click', function(e){
        $(this).addClass('hiddend');
        $('#asp_inst_generate').addClass('hiddend');
        $('#asp_inst_generate_cancel').removeClass('hiddend');
        $('#wpd_white_fixed_bg').css('display', 'block');
        $('#asp_inst_generate').closest('.item').addClass('highlight_over_bg');
        // Generate DB
        // Set status
        set_last_working_configuration();
        // Trigger save
    });

    $('#asp_inst_generate_cancel').on('click', function(e) {
        $(this).addClass('hiddend');
        $('#asp_inst_generate_save').removeClass('hiddend');
        $('#asp_inst_generate').removeClass('hiddend');
        $('#wpd_white_fixed_bg').css('display', 'none');
        $('#asp_inst_generate').closest('.item').removeClass('highlight_over_bg');
    });
});
