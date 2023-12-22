<?php foreach($filter->get() as $slider): ?>
    <div id="slider-handles-<?php echo $fieldset_id; ?>"></div>
    <div class="asp_noui_lu">

        <span class="asp_noui_l_pre"><?php echo $filter->data['slider_prefix']; ?></span>
        <span class="slider-handles-low" id="slider-handles-low-<?php echo $fieldset_id; ?>"></span>
        <span class="asp_noui_l_suff"><?php echo $filter->data['slider_suffix']; ?></span>

        <div class="clear"></div>
    </div>
    <input type="hidden" class="asp_slider_hidden" id="slider-values-low-<?php echo $fieldset_id; ?>" name="aspf[<?php echo $field_name; ?>]" value="<?php echo $slider->value; ?>">
    <?php ob_start(); ?>
    {
        "node": "#slider-handles-<?php echo $fieldset_id; ?>",
        "main": {
            "start": [ <?php echo $slider->value; ?> ],
            "step": <?php echo $filter->data['slider_step']; ?>,
            "range": {
                "min": [  <?php echo $filter->data['slider_from']; ?> ],
                "max": [  <?php echo $filter->data['slider_to']; ?> ]
            }
        },
        "links": [
            {
                "handle": "lower",
                "target": "#slider-handles-low-<?php echo $fieldset_id; ?>",
                "wNumb": {
                    "decimals": <?php echo $filter->data['slider_decimals']; ?>,
                    "thousand": "<?php echo $filter->data['slider_t_separator']; ?>"
                }
            },
            {
                "handle": "lower",
                "target": "#slider-values-low-<?php echo $fieldset_id; ?>",
                "wNumb": {
                    "decimals": <?php echo $filter->data['slider_decimals']; ?>,
                    "thousand": "<?php echo $filter->data['slider_t_separator']; ?>"
                }
            }
        ]
    }
    <?php $_asp_noui_out = ob_get_clean(); ?>
    <div id="noui-slider-json<?php echo $fieldset_id; ?>" class="noui-slider-json noui-slider-json<?php echo $id; ?>" data-aspnoui="<?php echo base64_encode($_asp_noui_out); ?>" style="display: none !important;"></div>
<?php endforeach; ?>