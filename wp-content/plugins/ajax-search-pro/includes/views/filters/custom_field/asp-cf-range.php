<?php foreach($filter->get() as $range): ?>
    <div id="range-handles-<?php echo $fieldset_id; ?>"></div>
    <div class="asp_noui_lu">

        <span class="asp_noui_l_pre"><?php echo $filter->data['range_prefix']; ?></span>
        <span class="slider-handles-low" id="slider-handles-low-<?php echo $fieldset_id; ?>"></span>
        <span class="asp_noui_l_suff"><?php echo $filter->data['range_suffix']; ?></span>

        <span class="asp_noui_u_suff"><?php echo $filter->data['range_suffix']; ?></span>
        <span class="slider-handles-up" id="slider-handles-up-<?php echo $fieldset_id; ?>"></span>
        <span class="asp_noui_u_pre"><?php echo $filter->data['range_prefix']; ?></span>

        <div class="clear"></div>
    </div>
    <input type="hidden" class="asp_slider_hidden"
           id="slider-values-low-<?php echo $fieldset_id; ?>"
           name="aspf[<?php echo $field_name; ?>][lower]" value="<?php echo $range->value[0]; ?>">
    <input type="hidden" class="asp_slider_hidden"
           id="slider-values-up-<?php echo $fieldset_id; ?>"
           name="aspf[<?php echo $field_name; ?>][upper]" value="<?php echo $range->value[1]; ?>">
    <?php ob_start(); ?>
    {
        "node": "#range-handles-<?php echo $fieldset_id; ?>",
        "main": {
            "start": [ <?php echo $range->value[0]; ?>, <?php echo $range->value[1]; ?> ],
            "step": <?php echo $filter->data['range_step']; ?>,
            "range": {
                "min": [  <?php echo $filter->data['range_from']; ?> ],
                "max": [  <?php echo $filter->data['range_to']; ?> ]
            }
        },
        "links": [
            {
                "handle": "lower",
                "target": "#slider-handles-low-<?php echo $fieldset_id; ?>",
                "wNumb": {
                    "decimals": <?php echo $filter->data['range_decimals']; ?>,
                    "thousand": "<?php echo $filter->data['range_t_separator']; ?>"
                }
            },
            {
                "handle": "upper",
                "target": "#slider-handles-up-<?php echo $fieldset_id; ?>",
                "wNumb": {
                    "decimals": <?php echo $filter->data['range_decimals']; ?>,
                    "thousand": "<?php echo $filter->data['range_t_separator']; ?>"
                }
            },
            {
                "handle": "lower",
                "target": "#slider-values-low-<?php echo $fieldset_id; ?>",
                "wNumb": {
                    "decimals": <?php echo $filter->data['range_decimals']; ?>,
                    "thousand": "<?php echo $filter->data['range_t_separator']; ?>"
                }
            },
            {
                "handle": "upper",
                "target": "#slider-values-up-<?php echo $fieldset_id; ?>",
                "wNumb": {
                    "decimals": <?php echo $filter->data['range_decimals']; ?>,
                    "thousand": "<?php echo $filter->data['range_t_separator']; ?>"
                }
            }
        ]
    }
    <?php $_asp_noui_out = ob_get_clean(); ?>
    <div id="noui-slider-json<?php echo $fieldset_id; ?>" class="noui-slider-json noui-slider-json<?php echo $id; ?>" data-aspnoui="<?php echo base64_encode($_asp_noui_out); ?>" style="display: none !important;"></div>
<?php endforeach; ?>