'use strict';
///<reference path="../../js/jquery.d.ts"/>
/*
 * To avoid loading many small files, simple controls are combined into this single file.
 */
jQuery(function ($) {
    //region Toggle checkbox
    {
        $('.ame-toggle-checkbox-control input[type="checkbox"]').each(function () {
            //When this box is checked, disable the hidden field that has the same name.
            const $box = $(this), $container = $box.closest('.ame-toggle-checkbox-control'), $alternative = $container.prev('input[name="' + $box.attr('name') + '"]');
            //The first update happens when the page is loaded.
            $alternative.prop('disabled', $box.is(':checked'));
            //Then enable/disable the hidden field whenever the box is checked or unchecked.
            $box.on('change', function () {
                $alternative.prop('disabled', $box.is(':checked'));
            });
        });
    }
    //endregion
    //region Color picker
    {
        //Initialize color pickers.
        jQuery(function ($) {
            let $pickers = $('.ame-customizable-color-picker');
            //We don't need to initialize color pickers in the Admin Customizer
            //module because the control class will do that.
            //TODO: This could be unified. The same observable updater would work for both.
            const $adminCustomizer = $('#ame-ac-admin-customizer');
            if ($adminCustomizer.length > 0) {
                $pickers = $pickers.filter(function () {
                    return ($(this).parents('#ame-ac-admin-customizer').length < 1);
                });
            }
            $pickers.css('visibility', 'visible').each(function () {
                //Trigger custom change events on the input element. We need to
                //store a reference to the input because the "clear" event will
                //run in the context of the "Clear" button, not the input.
                const $picker = $(this);
                $picker.wpColorPicker({
                    change: function (event, ui) {
                        $picker.trigger('adminMenuEditor:colorPickerChange', [ui.color.toString()]);
                        $picker.trigger('adminMenuEditor:controlValueChanged', [ui.color.toString()]);
                    },
                    clear: function () {
                        $picker.trigger('adminMenuEditor:colorPickerChange', ['']);
                        $picker.trigger('adminMenuEditor:controlValueChanged', ['']);
                    }
                });
                //Update the color picker when the observable changes.
                $picker.on('adminMenuEditor:observableValueChanged', function (event, newValue) {
                    if (typeof newValue !== 'string') {
                        newValue = '';
                    }
                    if (newValue === '') {
                        //Programmatically click the "Clear" button.
                        $picker.closest('.wp-picker-input-wrap').find('.wp-picker-clear').trigger('click');
                    }
                    else {
                        $picker.iris('color', newValue);
                    }
                });
            });
        });
    }
    //endregion
    //region Content toggle
    {
        function findAssociatedItems($toggle) {
            const itemSelector = $toggle.data('item-selector');
            const commonParentSelector = $toggle.data('parent-selector');
            return (commonParentSelector
                ? $toggle.closest(commonParentSelector).find(itemSelector)
                : $(itemSelector));
        }
        //Toggle the visibility of other content when the user clicks a link or button.
        $('.ame-content-toggle-control').on('click', function (event) {
            event.preventDefault();
            const $link = $(this);
            const $items = findAssociatedItems($link);
            const newStateIsVisible = !$items.first().is(':visible');
            $items.toggle(newStateIsVisible);
            const visibleText = $link.data('visible-state-text') || 'Hide details';
            const hiddenText = $link.data('hidden-state-text') || 'Show details';
            $link.text(newStateIsVisible ? visibleText : hiddenText);
        }).filter('[data-default-state="hidden"]').each(function () {
            //Hide content where it's set to be hidden by default.
            const $link = $(this);
            const $items = findAssociatedItems($link);
            $items.hide();
        });
    }
    //endregion
});
//# sourceMappingURL=combined-controls.js.map