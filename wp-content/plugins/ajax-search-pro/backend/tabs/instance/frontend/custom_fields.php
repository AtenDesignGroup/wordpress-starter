<span class="asp_legend_docs">
    <a target="_blank" href="https://documentation.ajaxsearchpro.com/frontend-search-settings/custom-field-selectors"><span class="fa fa-book"></span>
        <?php echo __('Documentation', 'ajax-search-pro'); ?>
    </a>
</span>
<?php
$cf_tooltip_msg = sprintf( __('One item per line. Use the <strong>{get_values}</strong> variable to get custom field values automatically. 
                   For more info see the 
                   <a target="_blank" href="%s">documentation</a>.', 'ajax-search-pro'), 'https://documentation.ajaxsearchpro.com/frontend-search-settings/custom-field-selectors' );

?>
<script>
    jQuery(function($) {
        var sortableCont = $("#csf_sortable");
        var $deleteIcon = $("<a class='deleteIcon'></a>");
        var $editIcon = $("<a class='editIcon'></a>");
        var resetValues = {};
        var $current = null;

        //$('#asp_edit_field').fadeOut(0);

        // Store defaults
        $('#asp_new_field input, #asp_new_field select, #asp_new_field textarea').each(function(){
            if ( $(this).is(':checkbox')) {
                resetValues[$(this).attr('name')] = $(this).is(':checked');
            } else {
                resetValues[$(this).attr('name')] = $(this).val();
            }
        });

        // Fields for checking
        var fields = ['asp_f_title', 'asp_f_field'];

        function checkEmpty(parent) {

            var empty = false;
            $(fields).each(function () {
                if ($(parent + ' *[name="' + this.toString() + '"]').val() == '') {
                    $(parent + ' *[name="' + this.toString() + '"]').addClass('missing');
                    empty = true;
                }
            });
            return empty;
        }
        $('#asp_new_field, #asp_edit_field').click(function(e){
            if ($(e.target).attr('name') == 'add' || $(e.target).attr('name') == 'save') return;
            $(fields).each(function () {
                $('#asp_new_field *[name="' + this.toString() + '"]').removeClass('missing');
                $('#asp_edit_field *[name="' + this.toString() + '"]').removeClass('missing');
            });
        });

        function initDatePickers() {
            if (typeof $('.asp_f_datepicker_value').datepicker != "undefined") {
                $('.asp_f_datepicker_value').datepicker("destroy");
                $('.asp_f_datepicker_value').each(function(){
                    $(this).datepicker({
                        dateFormat : $('.asp_f_datepicker_format', $(this).parent()).val(),
                        changeMonth: true,
                        changeYear: true
                    });
                    if ( $(this).val() == "" )
                        $('.asp_f_datepicker_value').datepicker( "setDate", "+0" );
                });
            } else {
                $('.asp_f_datepicker_value').each(function(){
                    $(this).datepicker({
                        dateFormat : $('.asp_f_datepicker_format', $(this).parent()).val(),
                        changeMonth: true,
                        changeYear: true
                    });
                });
            }
            $('.asp_f_datepicker_defval').each(function(){
                if ( $(this).val() == "current" || $(this).val() == "none" )
                    $('.asp_f_datepicker_value', $(this).parent() ).attr("disabled", true);
                else
                    $('.asp_f_datepicker_value', $(this).parent() ).removeAttr("disabled");
                if ( $(this).val() == "relative" ) {
                    $('.asp_f_datepicker_from', $(this).parent() ).removeClass("hiddend");
                    $('.asp_f_datepicker_value', $(this).parent() ).addClass("hiddend");
                } else {
                    $('.asp_f_datepicker_from', $(this).parent() ).addClass("hiddend");
                    $('.asp_f_datepicker_value', $(this).parent() ).removeClass("hiddend");
                }
            });
        }

        function resetNew() {
            $('#asp_new_field input, #asp_new_field select, #asp_new_field textarea').each(function(){
                if ( $(this).is(':checkbox')) {
                    $(this).prop('checked', resetValues[$(this).attr('name')]);
                } else {
                    $(this).val(resetValues[$(this).attr('name')]);
                }
            });
            $('#asp_new_field select[name="asp_f_type"]').trigger('change');
            $('#asp_new_field select[name="asp_f_source"]').trigger('focusin');
            $('#asp_new_field elect[name="asp_f_source"]').trigger('change');
            initDatePickers();
        }

        function resetEdit() {
            $('#asp_edit_field input, #asp_edit_field select, #asp_edit_field textarea').each(function(){
                $(this).val(resetValues[$(this).attr('name')]);
                if ( $(this).is(':checkbox')) {
                    $(this).prop('checked', resetValues[$(this).attr('name')]);
                } else {
                    $(this).val(resetValues[$(this).attr('name')]);
                }
            });
            $('#asp_edit_field select[name="asp_f_type"]').trigger('change');
            $('#asp_edit_field select[name="asp_f_source"]').trigger('focusin');
            $('#asp_edit_field select[name="asp_f_source"]').trigger('change');
            initDatePickers();
        }

        /* Type change */
        $('select[name="asp_f_type"]').on('change', function(){
            var id = $(this).parent().parent()[0].id;
            var val = $(this).val();
            $('#' + id + ' .asp_f_type').addClass('hiddend');
            $('#' + id + ' .asp_f_' + $(this).val()).removeClass('hiddend');
            if (val == 'slider') {
                $($('#' + id + ' .asp_f_operator optgroup')[1]).addClass('hiddend');
                $('#' + id + ' .asp_f_operator select').val('eq');
            } else {
                $($('#' + id + ' .asp_f_operator optgroup')[1]).removeClass('hiddend');
            }
            if (val == 'checkboxes') {
                $('#' + id + ' .asp_f_operator select').val('like');
            }
            if (val == 'range' || val == 'number_range' || val == 'datepicker') {
                $('#' + id + ' .asp_f_operator').addClass('hiddend');
            } else {
                $('#' + id + ' .asp_f_operator').removeClass('hiddend');
            }

            if ( val == 'hidden' || val == 'slider' || val == 'range' ) {
                $('#' + id + ' .asp_f_required').addClass('hiddend');
            } else {
                $('#' + id + ' .asp_f_required').removeClass('hiddend');
            }
        });
        /* Reset it on page load */
        $('select[name="asp_f_type"]').change();

        // Source change
        $('select[name="asp_f_source"]').on('focusin', function(){
            $(this).data('val', $(this).val());
        });
        $('select[name="asp_f_source"]').on('change', function(){
            var $parent = $(this).closest('.asp_ffield_container');
            var o = JSON.parse(WD_Helpers.Base64.decode($("input.wd_args", $parent).val()));
            var prev = $(this).data('val');
            var current = $(this).val();
            if ( $(this).val() == 'usermeta' ) {
                o.usermeta = 1;
            } else {
                o.usermeta = 0;
            }
            $("input.wd_args", $parent).val( WD_Helpers.Base64.encode(JSON.stringify(o)) );
            // Only trigger if really changes
            if ( current != prev )
                $("input.wd_cf_search", $parent).trigger('keyup');
        });
        $('select[name="asp_f_source"]').trigger('focusin');
        $('select[name="asp_f_source"]').trigger('change');

        /* Sortable */
        sortableCont.sortable({
        }, {
            update: function (event, ui) {
                var parent = $('#asp_new_field').parent();
                 var items = $('#csf_sortable li');
                 var hidden = $('input[name=custom_field_items]', parent);
                 var val = "";
                 items.each(function () {
                    val += "|" + $(this).attr('custom-data');
                 });
                 val = val.substring(1);
                 hidden.val(val);
            }
        }).disableSelection();

        // Add the items to the sortable on initialisation
        var fields_val = $('input[name=custom_field_items]').val();
        if (typeof(fields_val) != 'undefined' && fields_val != '') {
            var items = fields_val.split('|');
            $.each(items, function(key, value){
                vals = JSON.parse(WD_Helpers.Base64.decode(value));
                var $li = $("<li class='ui-state-default'/>").html(vals.asp_f_title + "<a class='deleteIcon'></a><a class='editIcon'></a>");
                $li.attr("custom-data", value);
                sortableCont.append($li);
            });
            sortableCont.sortable("refresh");
            sortableCont.sortable('option', 'update').call(sortableCont);
        }


        // Add new item
        $('#asp_new_field button[name=add]').click(function(){
            var data = {};

            if (checkEmpty('#asp_new_field') == true) return;

            $('#asp_new_field input, #asp_new_field select, #asp_new_field textarea').each(function(){
                if ($(this).parent().hasClass('hiddend')) return;
                if ($(this).attr('type') == 'checkbox') {
                    if ($(this).prop('checked') == true)
                        data[$(this).attr('name')] = 'asp_checked';
                    else
                        data[$(this).attr('name')] = 'asp_unchecked';
                } else {
                    data[$(this).attr('name')] = $(this).val();
                }
            });

            var $li = $("<li class='ui-state-default'/>")
                .html(data.asp_f_title + "<a class='deleteIcon'></a><a class='editIcon'></a>");
            $li.attr("custom-data", WD_Helpers.Base64.encode(JSON.stringify(data)));

            sortableCont.append($li);
            sortableCont.sortable("refresh");
            sortableCont.sortable('option', 'update').call(sortableCont);
            initDatePickers();
            $('#asp_new_field').fadeOut(10);
            resetNew();
            $('#asp_new_field').fadeIn();
        });

        // Remove item
        $('#csf_sortable').on('click', 'li a.deleteIcon', function(){
            $(this).parent().remove();
            sortableCont.sortable("refresh");
            sortableCont.sortable('option', 'update').call(sortableCont);
            $('#asp_edit_field button[name=back]').click();
            initDatePickers();
        });

        // Edit item
        $('#csf_sortable').on('click', 'li a.editIcon', function(e){
            resetEdit();
            $('#asp_new_field').fadeOut(0);
            $('#asp_edit_field').fadeIn();
            $current = $(e.target).parent();
            var data = JSON.parse(WD_Helpers.Base64.decode($current.attr("custom-data")));
            $('#asp_edit_title').text(data.asp_f_title);

            $.each(data, function(key, val){
                if (val == 'asp_checked') {
                    $('#asp_edit_field *[name=' + key + ']').prop('checked', true);
                } else if (val == 'asp_unchecked') {
                    $('#asp_edit_field *[name=' + key + ']').prop('checked', false);
                } else {
                    $('#asp_edit_field *[name=' + key + ']').val(val);
                }
                if (key == 'asp_f_type')
                    $('#asp_edit_field select[name=asp_f_type]').change();
            });
            $('#asp_edit_field input[name=asp_f_dropdown_search]').change();

            $('#asp_edit_field select[name="asp_f_source"]').trigger('focusin');
            $('#asp_edit_field select[name="asp_f_source"]').trigger('change');

            $('#asp_edit_field input[name="asp_f_required"]').trigger('change');

            initDatePickers();
        });

        // Back to new
        $('#asp_edit_field button[name=back]').click(function(){
            resetNew();
            $('#asp_edit_field').fadeOut(0);
            $('#asp_new_field').fadeIn();
        });

        // Save modifications
        $('#asp_edit_field button[name=save]').click(function(){
            if (checkEmpty('#asp_edit_field') == true) return;

            var data = {};
            $('#asp_edit_field input, #asp_edit_field select, #asp_edit_field textarea').each(function(){
                if ($(this).parent().hasClass('hiddend')) return;

                if ($(this).attr('type') == 'checkbox') {
                    if ($(this).prop('checked') == true)
                        data[$(this).attr('name')] = 'asp_checked';
                    else
                        data[$(this).attr('name')] = 'asp_unchecked';
                } else {
                    data[$(this).attr('name')] = $(this).val();
                }

            });
            $current.attr("custom-data", WD_Helpers.Base64.encode(JSON.stringify(data)));

            sortableCont.sortable("refresh");
            sortableCont.sortable('option', 'update').call(sortableCont);
            $('#asp_edit_field button[name=back]').click();
        });

        // Reset Values
        $('#asp_new_field button[name=reset]').click(function(){
            resetNew();
        });

        initDatePickers();

        $('.asp_f_datepicker_format').on("keyup", function(){
            initDatePickers();
        });
        $('.asp_f_datepicker_defval').on("change", function(){
            initDatePickers();
        });

        $('.asp_f_datepicker_store_format').on("change", function(){
            $(".greenMsg", $(this).parent()).addClass("hiddend");
            $(".greenMsg.msg_" + $(this).val(), $(this).parent()).removeClass("hiddend");
        });
        $('.asp_f_datepicker_store_format').change();

        $('input[name=asp_f_dropdown_search]').change(function(){
            if ( $(this).prop('checked') )
                $('input[name=asp_f_dropdown_search_text]', $(this).parent()).removeAttr('disabled');
            else
                $('input[name=asp_f_dropdown_search_text]', $(this).parent()).attr('disabled', true);
        });
        $('input[name=asp_f_dropdown_search]').change();

        $('input[name=asp_f_required]').change(function(){
            if ( $(this).prop('checked') )
                $(this).closest('fieldset').find('input[name=asp_f_invalid_input_text]').closest('label').removeClass('disabled');
            else
                $(this).closest('fieldset').find('input[name=asp_f_invalid_input_text]').closest('label').addClass('disabled');
        });
        $('input[name=asp_f_required]').change();
    });
</script>
<style>
    .asp_f_datepicker_from_days,
    .asp_f_datepicker_from_months {
        width: 34px !important;
        margin: 0 1px !important;
    }
    .asp_f_datepicker_value {
        margin-bottom: 10px !important;
    }
    .asp_f_datepicker_from {
        display: inline;
        padding: 5px 10px 50px 0px !important;
        margin: 0 !important;
        position: relative;
    }
    .asp_f_datepicker_from .descMsg {
        position: absolute;
    }
    input[name=asp_f_dropdown_search_text] {
        width: 126px !important;
    }
    .asp_ffield_container {
        display: flex;
        justify-content: flex-end;
    }
    .asp_ffield_container>.wd_cf_search {
        margin: 0;
    }
</style>
<div class="wpd-60-pc customContent">

    <fieldset class="wpd-text-right" id="asp_new_field">
        <legend><?php echo __('Add new item', 'ajax-search-pro'); ?></legend>
        <div class='one-item'>
            <label for='asp_f_title'><?php echo __('Title label', 'ajax-search-pro'); ?></label>
            <input type='text' placeholder="<?php echo esc_attr__('Title here..', 'ajax-search-pro'); ?>" name='asp_f_title'/>
        </div>
        <div class='one-item'>
            <label for='asp_f_show_title'><?php echo __('Show the label on the frontend?', 'ajax-search-pro'); ?></label>
            <input type='checkbox' name='asp_f_show_title' value="yes" checked/>
        </div>
        <div class='one-item'>
            <label for='asp_f_field'><?php echo __('Custom Field', 'ajax-search-pro'); ?></label>
            <div class="asp_ffield_container">
                <?php new wd_CFSearchCallBack('asp_f_field', '', array('value'=>'', 'args'=>array('controls_position' => 'left', 'class'=>'wpd-text-right'))); ?>
                <select name="asp_f_source">
                    <option value="postmeta"><?php echo __('Post meta', 'ajax-search-pro'); ?></option>
                    <option value="usermeta"><?php echo __('User meta', 'ajax-search-pro'); ?></option>
                </select>
            </div>
        </div>
        <div class='one-item'>
            <label for='asp_f_type'><?php echo __('Type', 'ajax-search-pro'); ?></label>
            <select name='asp_f_type'/>
            <option value="radio"><?php echo __('Radio', 'ajax-search-pro'); ?></option>
            <option value="dropdown"><?php echo __('Dropdown', 'ajax-search-pro'); ?></option>
            <option value="checkboxes"><?php echo __('Checkboxes', 'ajax-search-pro'); ?></option>
	        <option value="hidden"><?php echo __('Hidden', 'ajax-search-pro'); ?></option>
            <option value="text"><?php echo __('Text', 'ajax-search-pro'); ?></option>
            <option value="datepicker"><?php echo __('DatePicker', 'ajax-search-pro'); ?></option>
            <option value="number_range"><?php echo __('Number Range', 'ajax-search-pro'); ?></option>
            <option value="slider"><?php echo __('Slider', 'ajax-search-pro'); ?></option>
            <option value="range"><?php echo __('Range Slider', 'ajax-search-pro'); ?></option>
            </select>
        </div>
        <div class='one-item asp_f_radio asp_f_type'>
            <label for='asp_f_radio_value'><?php echo __('Radio values', 'ajax-search-pro'); ?></label>
            <textarea name='asp_f_radio_value'/>
||Any value**
sample_value1||Sample Label 1
sample_value2||Sample Label 2
sample_value3||Sample Label 3</textarea>
            <p class="descMsg"><?php echo $cf_tooltip_msg; ?></p>
        </div>
        <div class='one-item asp_f_dropdown asp_f_type hiddend'>
            <label for='asp_f_dropdown_multi'><?php echo __('Multiselect?', 'ajax-search-pro'); ?></label>
            <input type='checkbox' name='asp_f_dropdown_multi' value="yes" /><br><br>
            <label for='asp_f_dropdown_search'><?php echo __('Searchable?', 'ajax-search-pro'); ?></label>
            <input type='checkbox' name='asp_f_dropdown_search' value="yes" />
            <label for='asp_f_dropdown_search_text'><?php echo __('placeholder', 'ajax-search-pro'); ?></label>
            <input type='text' name='asp_f_dropdown_search_text' value="Select options.." disabled/><br><br>
            <label for='asp_f_dropdown_value'><?php echo __('Dropdown values', 'ajax-search-pro'); ?></label>
            <textarea name='asp_f_dropdown_value'/>
||Any value**
sample_value1||Sample Label 1
sample_value2||Sample Label 2
sample_value3||Sample Label 3</textarea>
            <p class="descMsg"><?php echo $cf_tooltip_msg; ?></p>
            <label for='asp_f_dropdown_logic'><?php echo __('Drop-down values logic', 'ajax-search-pro'); ?></label>
            <select name='asp_f_dropdown_logic'/>
                <option value="OR">OR</option>
                <option value="AND">AND</option>
                <option value="ANDSE">AND in separate fields</option>
            </select>
        </div>
        <div class='one-item asp_f_checkboxes asp_f_type hiddend'>
            <label for='asp_f_checkboxes_value'><?php echo __('Checkbox values', 'ajax-search-pro'); ?></label>
            <textarea name='asp_f_checkboxes_value'/>
||Select all**
sample_value1||Sample Label 1**
sample_value2||Sample Label 2
sample_value3||Sample Label 3**</textarea>
            <p class="descMsg"><?php echo $cf_tooltip_msg; ?></p>
            <br><br>
            <label for='asp_f_checkboxes_logic'><?php echo __('Checkbox logic', 'ajax-search-pro'); ?></label>
            <select name='asp_f_checkboxes_logic'/>
                <option value="OR">OR</option>
                <option value="AND">AND</option>
            </select>
        </div>
	    <div class='one-item asp_f_hidden asp_f_type'>
		    <label for='asp_f_hidden_value'><?php echo __('Hidden value', 'ajax-search-pro'); ?></label>
		    <textarea name='asp_f_hidden_value'/></textarea>
		    <p class="descMsg"><?php echo __('An invisible element. Used for filtering every time without user input.', 'ajax-search-pro'); ?></p>
	    </div>
        <div class='one-item asp_f_text asp_f_type'>
            <label for='asp_f_text_value'><?php echo __('Text input', 'ajax-search-pro'); ?></label>
            <textarea name='asp_f_text_value'/></textarea>
            <p class="descMsg"><?php echo __('A text input element.', 'ajax-search-pro'); ?></p>
        </div>
        <div class='one-item asp_f_datepicker asp_f_type'>
            <label for='asp_f_datepicker_store_format'><?php echo __('Date storage format', 'ajax-search-pro'); ?></label>
            <select class="asp_f_datepicker_store_format" name="asp_f_datepicker_store_format">
                <option value="datetime"><?php echo __('MySQL DateTime/ACF datetime field', 'ajax-search-pro'); ?></option>
                <option value="acf"><?php echo __('ACF date ', 'ajax-search-pro'); ?></option>
                <option value="timestamp"><?php echo __('Timestamp', 'ajax-search-pro'); ?></option>
            </select>
            <p class="msg_acf"></p>
            <p class="greenMsg msg_datetime">
                <?php echo __('NOTICE: The MySql datetime format is <strong>Y-m-d H:i:s</strong>, for example: 2001-03-10 17:16:18', 'ajax-search-pro'); ?>
            </p>
            <p class="greenMsg msg_timestamp">
                <?php echo __('NOTICE: The timestamp is a numeric format, for example <strong>1465111713</strong>. This translates to: 06/05/2016 @ 7:28am (UTC)', 'ajax-search-pro'); ?>
            </p>
            <div class="one-item-sub">
                <label for='asp_f_datepicker_placeholder'>
                    <?php echo __('Placeholder text', 'ajax-search-pro'); ?>
                </label><input style="width:120px;" name='asp_f_datepicker_placeholder' class="asp_f_datepicker_placeholder" value="Choose date"/>
            </div>
            <div class="one-item-sub">
                <label for='asp_f_datepicker_format'>
                    <?php echo __('Display format', 'ajax-search-pro'); ?>
                </label><input style="width:120px;" name='asp_f_datepicker_format' class="asp_f_datepicker_format" value="dd/mm/yy"/>
                <p class="descMsg">
                    <?php echo sprintf( __('dd/mm/yy is the most used format, <a href="%s" target="_blank">list of accepted params</a>', 'ajax-search-pro'), 'http://api.jqueryui.com/datepicker/#utility-formatDate' ); ?>
                </p>
            </div>
            <label for='asp_f_datepicker_value'><?php echo __('Default Value', 'ajax-search-pro'); ?></label><br>
            <select class="asp_f_datepicker_defval" name="asp_f_datepicker_defval">
                <option value="current"><?php echo __('Current date', 'ajax-search-pro'); ?></option>
                <option value="none"><?php echo __('Empty (no date)', 'ajax-search-pro'); ?></option>
                <option value="relative"><?php echo __('Relative date', 'ajax-search-pro'); ?></option>
                <option value="selected"><?php echo __('Select date', 'ajax-search-pro'); ?></option>
            </select>
            <input class="asp_f_datepicker_value" name='asp_f_datepicker_value' value=""/>
            <fieldset class="asp_f_datepicker_from hiddend">
                <input class="asp_f_datepicker_from_days" name='asp_f_datepicker_from_days' value="0"/> days and
                <input class="asp_f_datepicker_from_months" name='asp_f_datepicker_from_months' value="0"/> months from now.
                <p class="descMsg"><?php echo __('Use <strong>negative values</strong> to indicate date before the current.', 'ajax-search-pro'); ?></p>
            </fieldset>
            <br>
            <label for='asp_f_datepicker_operator'><?php echo __('Show results..', 'ajax-search-pro'); ?></label>
            <select name='asp_f_datepicker_operator'/>
            <option value="before"><?php echo __('..before the date (to date)', 'ajax-search-pro'); ?></option>
            <option value="before_inc"><?php echo __('..before the date (to date) inclusive', 'ajax-search-pro'); ?></option>
            <option value="after"><?php echo __('..after the date (from date)', 'ajax-search-pro'); ?></option>
            <option value="after_inc"><?php echo __('..after the date (from date) inclusive', 'ajax-search-pro'); ?></option>
            <option value="match"><?php echo __('..matching the date', 'ajax-search-pro'); ?></option>
            <option value="nomatch"><?php echo __('..not matching the date', 'ajax-search-pro'); ?></option>
            </select>
        </div>
		<div style='line-height: 33px;' class='one-item asp_f_number_range asp_f_type hiddend'>
			<label for='asp_f_number_range_from'><?php echo __('Min/Max Range', 'ajax-search-pro'); ?></label>
			<input class="ninedigit" type='number' value="" name='asp_f_number_range_from'/> - <input class="ninedigit" value="" type='number' name='asp_f_number_range_to'/><br />
			<p class="descMsg"><?php echo __('Leave them empty to get the min/max range automatically.', 'ajax-search-pro'); ?></p>
			<label for='asp_f_number_range_default1'><?php echo __('Input 1 default', 'ajax-search-pro'); ?></label>
			<input class="ninedigit" type='number' value="" name='asp_f_number_range_default1'/>
			<label for='asp_f_number_range_default2'><?php echo __('Input 2 default', 'ajax-search-pro'); ?></label>
			<input class="ninedigit" type='number' value="" name='asp_f_number_range_default2'/>
			<p class="descMsg"><?php echo __('When empty, the placeholder texts are displayed.', 'ajax-search-pro'); ?></p>
			<label for='asp_f_number_range_placeholder1'><?php echo __('Placeholder Text #1', 'ajax-search-pro'); ?></label>
			<input type='text' value="From" name='asp_f_number_range_placeholder1'/>
			<label for='asp_f_number_range_placeholder2'><?php echo __('Placeholder Text #2', 'ajax-search-pro'); ?></label>
			<input type='text' value="To" name='asp_f_number_range_placeholder2'/>
			<label for='asp_f_number_range_t_separator'><?php echo __('Thousands separator', 'ajax-search-pro'); ?></label>
			<input class="threedigit" type='text' value=" " name='asp_f_number_range_t_separator'/>
		</div>
        <div style='line-height: 33px;' class='one-item asp_f_slider asp_f_type hiddend'>
            <label for='asp_f_slider_from'><?php echo __('Slider range', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="1" name='asp_f_slider_from'/> - <input class="threedigit" value="1000" type='text' name='asp_f_slider_to'/><br />
            <p class="descMsg"><?php echo __('Leave them empty to get the values automatically.', 'ajax-search-pro'); ?></p>
            <label for='asp_f_slider_step'><?php echo __('Step', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="1" name='asp_f_slider_step'/><br />
            <label for='asp_f_slider_prefix'><?php echo __('Prefix', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="$" name='asp_f_slider_prefix'/>
            <label for='asp_f_slider_suffix'><?php echo __('Suffix', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value=",-" name='asp_f_slider_suffix'/><br />
            <label for='asp_f_slider_default'><?php echo __('Default Value', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="500" name='asp_f_slider_default'/><br />
            <p class="descMsg"><?php echo __('Leave it empty to set the handle automatically.', 'ajax-search-pro'); ?></p>
            <label for='asp_f_slider_t_separator'><?php echo __('Thousands separator', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value=" " name='asp_f_slider_t_separator'/>
            <label for='asp_f_slider_decimals'><?php echo __('Decimal places', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="0" name='asp_f_slider_decimals'/>
        </div>
        <div style='line-height: 33px;' class='one-item asp_f_range asp_f_type hiddend'>
            <label for='asp_f_range_from'><?php echo __('Slider range', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="1" name='asp_f_range_from'/> - <input class="threedigit" value="1000" type='text' name='asp_f_range_to'/><br />
            <p class="descMsg"><?php echo __('Leave them empty to get the values automatically.', 'ajax-search-pro'); ?></p>
            <label for='asp_f_slider_step'><?php echo __('Step', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="1" name='asp_f_range_step'/><br />
            <label for='asp_f_slider_prefix'><?php echo __('Prefix', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="$" name='asp_f_range_prefix'/>
            <label for='asp_f_slider_suffix'><?php echo __('Suffix', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value=",-" name='asp_f_range_suffix'/><br />
            <label for='asp_f_range_default1'><?php echo __('Track 1 default', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="" name='asp_f_range_default1'/>
            <label for='asp_f_range_default2'><?php echo __('Track 2 default', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="" name='asp_f_range_default2'/>
            <p class="descMsg"><?php echo __('Leave them empty to set the handles automatically.', 'ajax-search-pro'); ?></p>
            <label for='asp_f_range_t_separator'><?php echo __('Thousands separator', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value=" " name='asp_f_range_t_separator'/>
            <label for='asp_f_range_decimals'><?php echo __('Decimal places', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="0" name='asp_f_range_decimals'/>
        </div>
        <div class='one-item asp_f_operator'>
            <label for='asp_f_operator'><?php echo __('Operator', 'ajax-search-pro'); ?></label>
            <select name='asp_f_operator'/>
            <optgroup label="Numeric operators">
                <option value="eq">EQUALS</option>
                <option value="neq">NOT EQUALS</option>
                <option value="lt">LESS THEN</option>
                <option value="let">LESS OR EQUALS THEN</option>
                <option value="gt">MORE THEN</option>
                <option value="get">MORE OR EQUALS THEN</option>
            </optgroup>
            <optgroup label="String operators">
                <option value="elike">EXACTLY LIKE</option>
                <option value="like" selected="selected">LIKE</option>
                <option value="not elike">NOT EXACTLY LIKE</option>
                <option value="not like">NOT LIKE</option>
            </optgroup>
            </select>
            <p class="descMsg"><?php echo __('Use the numeric operators for numeric values and string operators for text values', 'ajax-search-pro'); ?></p>
        </div>
        <div class='one-item asp_f_required'>
            <label for='asp_f_required'><?php echo __('Required?', 'ajax-search-pro'); ?>
                <input type="checkbox" name="asp_f_required">
            </label>
            <label for='asp_f_required'><?php echo __('..text: ', 'ajax-search-pro'); ?>
                <input type='text' value="This field is required!" name='asp_f_invalid_input_text'/>
            </label>
            <p class="descMsg"><?php echo __('The plugin will not trigger search until the required field is set.', 'ajax-search-pro'); ?></p>
        </div>
        <div class='one-item'>
            <button type='button' style='margin-right: 20px;' name='reset'><?php echo __('Reset', 'ajax-search-pro'); ?></button>
            <button type='button' name='add'><?php echo __('Add!', 'ajax-search-pro'); ?></button>
        </div>
    </fieldset>

    <fieldset class="wpd-text-right" style="display:none;" id="asp_edit_field">
        <legend>Edit: <strong><span id="asp_edit_title"></span></strong></legend>
        <div class='one-item'>
            <label for='asp_f_title'><?php echo __('Title label', 'ajax-search-pro'); ?></label>
            <input type='text' placeholder="<?php echo esc_attr__('Title here..', 'ajax-search-pro'); ?>" name='asp_f_title'/>
        </div>
        <div class='one-item'>
            <label for='asp_f_show_title'><?php echo __('Show the label on the frontend?', 'ajax-search-pro'); ?></label>
            <input type='checkbox' name='asp_f_show_title' value="yes" checked/>
        </div>
        <div class='one-item'>
            <label for='asp_f_field'><?php echo __('Custom Field', 'ajax-search-pro'); ?></label>
            <div class="asp_ffield_container">
                <?php new wd_CFSearchCallBack('asp_f_field', '', array('value'=>'', 'args'=>array('controls_position' => 'left', 'class'=>'wpd-text-right'))); ?>
                <select name="asp_f_source">
                    <option value="postmeta"><?php echo __('Post meta', 'ajax-search-pro'); ?></option>
                    <option value="usermeta"><?php echo __('User meta', 'ajax-search-pro'); ?></option>
                </select>
            </div>
        </div>
        <div class='one-item'>
            <label for='asp_f_type'><?php echo __('Type', 'ajax-search-pro'); ?></label>
            <select name='asp_f_type'/>
            <option value="radio"><?php echo __('Radio', 'ajax-search-pro'); ?></option>
            <option value="dropdown"><?php echo __('Dropdown', 'ajax-search-pro'); ?></option>
            <option value="checkboxes"><?php echo __('Checkboxes', 'ajax-search-pro'); ?></option>
	        <option value="hidden"><?php echo __('Hidden', 'ajax-search-pro'); ?></option>
            <option value="text"><?php echo __('Text', 'ajax-search-pro'); ?></option>
            <option value="datepicker"><?php echo __('DatePicker', 'ajax-search-pro'); ?></option>
            <option value="number_range"><?php echo __('Number Range', 'ajax-search-pro'); ?></option>
            <option value="slider"><?php echo __('Slider', 'ajax-search-pro'); ?></option>
            <option value="range"><?php echo __('Range Slider', 'ajax-search-pro'); ?></option>
            </select>
        </div>
        <div class='one-item asp_f_radio asp_f_type'>
            <label for='asp_f_radio_value'><?php echo __('Radio values', 'ajax-search-pro'); ?></label>
            <textarea name='asp_f_radio_value'/></textarea>
            <p class="descMsg"><?php echo $cf_tooltip_msg; ?></p>
        </div>
        <div class='one-item asp_f_dropdown asp_f_type hiddend'>
            <label for='asp_f_dropdown_multi'><?php echo __('Multiselect?', 'ajax-search-pro'); ?></label>
            <input type='checkbox' name='asp_f_dropdown_multi' value="yes" /><br><br>
            <label for='asp_f_dropdown_search'><?php echo __('Searchable?', 'ajax-search-pro'); ?></label>
            <input type='checkbox' name='asp_f_dropdown_search' value="yes" />
            <label for='asp_f_dropdown_search_text'><?php echo __('placeholder', 'ajax-search-pro'); ?></label>
            <input type='text' name='asp_f_dropdown_search_text' value="Select options.." disabled/><br><br>
            <label for='asp_f_dropdown_value'><?php echo __('Dropdown values', 'ajax-search-pro'); ?></label>
            <textarea name='asp_f_dropdown_value'/></textarea>
            <p class="descMsg"><?php echo $cf_tooltip_msg; ?></p>
            <label for='asp_f_dropdown_logic'><?php echo __('Drop-down values logic', 'ajax-search-pro'); ?></label>
            <select name='asp_f_dropdown_logic'/>
                <option value="OR">OR</option>
                <option value="AND">AND</option>
                <option value="ANDSE">AND in separate fields</option>
            </select>
        </div>
        <div class='one-item asp_f_checkboxes asp_f_type hiddend'>
            <label for='asp_f_checkboxes_value'><?php echo __('Checkbox values', 'ajax-search-pro'); ?></label>
            <textarea name='asp_f_checkboxes_value'/></textarea><br><br>
            <p class="descMsg"><?php echo $cf_tooltip_msg; ?></p>
            <label for='asp_f_checkboxes_logic'><?php echo __('Checkbox logic', 'ajax-search-pro'); ?></label>
            <select name='asp_f_checkboxes_logic'/>
                <option value="OR">OR</option>
                <option value="AND">AND</option>
            </select>
        </div>
	    <div class='one-item asp_f_hidden asp_f_type'>
            <label for='asp_f_hidden_value'><?php echo __('Hidden value', 'ajax-search-pro'); ?></label>
            <textarea name='asp_f_hidden_value'/></textarea>
            <p class="descMsg"><?php echo __('An invisible element. Used for filtering every time without user input.', 'ajax-search-pro'); ?></p>
        </div>
        <div class='one-item asp_f_text asp_f_type'>
            <label for='asp_f_text_value'><?php echo __('Text input', 'ajax-search-pro'); ?></label>
            <textarea name='asp_f_text_value'/></textarea>
            <p class="descMsg"><?php echo __('A text input element.', 'ajax-search-pro'); ?></p>
        </div>
        <div class='one-item asp_f_datepicker asp_f_type'>
            <label for='asp_f_datepicker_store_format'><?php echo __('Date storage format', 'ajax-search-pro'); ?></label>
            <select class="asp_f_datepicker_store_format" name="asp_f_datepicker_store_format">
                <option value="datetime"><?php echo __('MySQL DateTime/ACF datetime field', 'ajax-search-pro'); ?></option>
                <option value="acf"><?php echo __('ACF date field', 'ajax-search-pro'); ?></option>
                <option value="timestamp"><?php echo __('Timestamp', 'ajax-search-pro'); ?></option>
            </select>
            <p class="msg_acf"></p>
            <p class="greenMsg msg_datetime">
                <?php echo __('NOTICE: The MySql datetime format is <strong>Y-m-d H:i:s</strong>, for example: 2001-03-10 17:16:18', 'ajax-search-pro'); ?>
            </p>
            <p class="greenMsg msg_timestamp">
                <?php echo __('NOTICE: The timestamp is a numeric format, for example <strong>1465111713</strong>. This translates to: 06/05/2016 @ 7:28am (UTC)', 'ajax-search-pro'); ?>
            </p>
            <div class="one-item-sub">
                <label for='asp_f_datepicker_placeholder'>
                    <?php echo __('Placeholder text', 'ajax-search-pro'); ?>
                </label><input style="width:120px;" name='asp_f_datepicker_placeholder' class="asp_f_datepicker_placeholder" value="Choose date"/>
            </div>
            <div class="one-item-sub">
                <label for='asp_f_datepicker_format'>
                    <?php echo __('Display format', 'ajax-search-pro'); ?>
                </label><input style="width:120px;" name='asp_f_datepicker_format' class="asp_f_datepicker_format" value="dd/mm/yy"/>
                <p class="descMsg">
                    <?php echo sprintf( __('dd/mm/yy is the most used format, <a href="%s" target="_blank">list of accepted params</a>', 'ajax-search-pro'), 'http://api.jqueryui.com/datepicker/#utility-formatDate' ); ?>
                </p>
            </div>
            <label for='asp_f_datepicker_value'><?php echo __('Default Value', 'ajax-search-pro'); ?></label><br>
            <select class="asp_f_datepicker_defval" name="asp_f_datepicker_defval">
                <option value="current"><?php echo __('Current date', 'ajax-search-pro'); ?></option>
                <option value="none"><?php echo __('Empty (no date)', 'ajax-search-pro'); ?></option>
                <option value="relative"><?php echo __('Relative date', 'ajax-search-pro'); ?></option>
                <option value="selected"><?php echo __('Select date', 'ajax-search-pro'); ?></option>
            </select>
            <input class="asp_f_datepicker_value" name='asp_f_datepicker_value' value=""/>
            <fieldset class="asp_f_datepicker_from hiddend">
                <input class="asp_f_datepicker_from_days" name='asp_f_datepicker_from_days' value="0"/> days and
                <input class="asp_f_datepicker_from_months" name='asp_f_datepicker_from_months' value="0"/> months from now.
                <p class="descMsg"><?php echo __('Use <strong>negative values</strong> to indicate date before the current.', 'ajax-search-pro'); ?></p>
            </fieldset>
            <br>
            <label for='asp_f_datepicker_operator'><?php echo __('Show results..', 'ajax-search-pro'); ?></label>
            <select name='asp_f_datepicker_operator'/>
                <option value="before"><?php echo __('..before the date (to date)', 'ajax-search-pro'); ?></option>
                <option value="before_inc"><?php echo __('..before the date (to date) inclusive', 'ajax-search-pro'); ?></option>
                <option value="after"><?php echo __('..after the date (from date)', 'ajax-search-pro'); ?></option>
                <option value="after_inc"><?php echo __('..after the date (from date) inclusive', 'ajax-search-pro'); ?></option>
                <option value="match"><?php echo __('..matching the date', 'ajax-search-pro'); ?></option>
                <option value="nomatch"><?php echo __('..not matching the date', 'ajax-search-pro'); ?></option>
            </select>
        </div>
		<div style='line-height: 33px;' class='one-item asp_f_number_range asp_f_type hiddend'>
			<label for='asp_f_number_range_from'><?php echo __('Min/Max Range', 'ajax-search-pro'); ?></label>
			<input class="ninedigit" type='number' value="" name='asp_f_number_range_from'/> - <input class="ninedigit" value="" type='number' name='asp_f_number_range_to'/><br />
			<p class="descMsg"><?php echo __('Leave them empty to get the min/max range automatically.', 'ajax-search-pro'); ?></p>
			<label for='asp_f_number_range_default1'><?php echo __('Input 1 default', 'ajax-search-pro'); ?></label>
			<input class="ninedigit" type='number' value="" name='asp_f_number_range_default1'/>
			<label for='asp_f_number_range_default2'><?php echo __('Input 2 default', 'ajax-search-pro'); ?></label>
			<input class="ninedigit" type='number' value="" name='asp_f_number_range_default2'/>
			<p class="descMsg"><?php echo __('When empty, the placeholder texts are displayed.', 'ajax-search-pro'); ?></p>
			<label for='asp_f_number_range_placeholder1'><?php echo __('Placeholder Text #1', 'ajax-search-pro'); ?></label>
			<input type='text' value="From" name='asp_f_number_range_placeholder1'/>
			<label for='asp_f_number_range_placeholder2'><?php echo __('Placeholder Text #2', 'ajax-search-pro'); ?></label>
			<input type='text' value="To" name='asp_f_number_range_placeholder2'/>
			<label for='asp_f_number_range_t_separator'><?php echo __('Thousands separator', 'ajax-search-pro'); ?></label>
			<input class="threedigit" type='text' value=" " name='asp_f_number_range_t_separator'/>
		</div>
        <div style='line-height: 33px;' class='one-item asp_f_slider asp_f_type hiddend'>
            <label for='asp_f_slider_from'><?php echo __('Slider range', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="" name='asp_f_slider_from'/> - <input class="threedigit" value="" type='text' name='asp_f_slider_to'/><br />
            <p class="descMsg"><?php echo __('Leave them empty to get the values automatically.', 'ajax-search-pro'); ?></p>
            <label for='asp_f_slider_step'><?php echo __('Step', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="1" name='asp_f_slider_step'/><br />
            <label for='asp_f_slider_prefix'><?php echo __('Prefix', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="$" name='asp_f_slider_prefix'/>
            <label for='asp_f_slider_suffix'><?php echo __('Suffix', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value=",-" name='asp_f_slider_suffix'/><br />
            <label for='asp_f_slider_default'><?php echo __('Default Value', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="" name='asp_f_slider_default'/><br />
            <p class="descMsg"><?php echo __('Leave it empty to set the handle automatically.', 'ajax-search-pro'); ?></p>
            <label for='asp_f_slider_t_separator'><?php echo __('Thousands separator', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value=" " name='asp_f_slider_t_separator'/>
            <label for='asp_f_slider_decimals'><?php echo __('Decimal places', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="0" name='asp_f_slider_decimals'/>
        </div>
        <div style='line-height: 33px;' class='one-item asp_f_range asp_f_type hiddend'>
            <label for='asp_f_range_from'><?php echo __('Slider range', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="" name='asp_f_range_from'/> - <input class="threedigit" value="" type='text' name='asp_f_range_to'/><br />
            <p class="descMsg"><?php echo __('Leave them empty to get the values automatically.', 'ajax-search-pro'); ?></p>
            <label for='asp_f_slider_step'><?php echo __('Step', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="1" name='asp_f_range_step'/><br />
            <label for='asp_f_slider_prefix'><?php echo __('Prefix', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="$" name='asp_f_range_prefix'/>
            <label for='asp_f_slider_suffix'><?php echo __('Suffix', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value=",-" name='asp_f_range_suffix'/><br />
            <label for='asp_f_range_default1'><?php echo __('Track 1 default', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="" name='asp_f_range_default1'/>
            <label for='asp_f_range_default2'><?php echo __('Track 2 default', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="" name='asp_f_range_default2'/>
            <p class="descMsg"><?php echo __('Leave them empty to set the handles automatically.', 'ajax-search-pro'); ?></p>
            <label for='asp_f_range_t_separator'><?php echo __('Thousands separator', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value=" " name='asp_f_range_t_separator'/>
            <label for='asp_f_range_decimals'><?php echo __('Decimal places', 'ajax-search-pro'); ?></label>
            <input class="threedigit" type='text' value="0" name='asp_f_range_decimals'/>
        </div>
        <div class='one-item asp_f_operator'>
            <label for='asp_f_operator'><?php echo __('Operator', 'ajax-search-pro'); ?></label>
            <select name='asp_f_operator'/>
            <optgroup label="Numeric operators">
                <option value="eq">EQUALS</option>
                <option value="neq">NOT EQUALS</option>
                <option value="lt">LESS THEN</option>
                <option value="let">LESS OR EQUALS THEN</option>
                <option value="gt">MORE THEN</option>
                <option value="get">MORE OR EQUALS THEN</option>
            </optgroup>
            <optgroup label="String operators">
                <option value="elike">EXACTLY LIKE</option>
                <option value="like">LIKE</option>
                <option value="not elike">NOT EXACTLY LIKE</option>
                <option value="not like">NOT LIKE</option>
            </optgroup>
            </select>
            <p class="descMsg"><?php echo __('Use the numeric operators for numeric values and string operators for text values.', 'ajax-search-pro'); ?></p>
        </div>
        <div class='one-item asp_f_required'>
            <label for='asp_f_required'><?php echo __('Required?', 'ajax-search-pro'); ?>
                <input type="checkbox" name="asp_f_required">
            </label>
            <label for='asp_f_required'><?php echo __('..text: ', 'ajax-search-pro'); ?>
                <input type='text' value="This field is required!" name='asp_f_invalid_input_text'/>
            </label>
            <p class="descMsg"><?php echo __('The plugin will not trigger search until the required field is set.', 'ajax-search-pro'); ?></p>
        </div>
        <div class='one-item'>
            <button type='button' style='margin-right: 20px;' name='back'><?php echo __('Back', 'ajax-search-pro'); ?></button>
            <button type='button' name='save'><?php echo __('Save!', 'ajax-search-pro'); ?></button>
        </div>
    </fieldset>

    <input type="hidden" name="custom_field_items" value="<?php
        if (isset($_POST['custom_field_items']))
            echo esc_attr($_POST['custom_field_items']);
        else
            echo $sd['custom_field_items'];

    ?>" />
</div>
<div class="wpd-40-pc customFieldsSortable">
    <div class="sortablecontainer">
        <ul id="csf_sortable">

        </ul>
    </div>
</div>