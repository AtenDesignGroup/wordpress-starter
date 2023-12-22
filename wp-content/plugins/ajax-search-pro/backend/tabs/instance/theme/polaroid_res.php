<div class="item item-rlayout item-rlayout-polaroid">
    <p><?php echo __('These options are hidden, because the <span>vertical</span> results layout is selected.', 'ajax-search-pro'); ?></p>
    <p><?php echo __('You can change that under the <a href="#402" data-asp-os-highlight="resultstype" tabid="402">Layout Options -> Results layout</a> panel,
        <br>..or choose a <a href="#601" tabid="601">different theme</a> with a different pre-defined layout.', 'ajax-search-pro'); ?></p>
</div>
<div class="item"><?php
    $o = new wpdreamsCustomSelect("pifnoimage", __('If no image found', 'ajax-search-pro'),  array(
        'selects'=>array(
            array('option' => 'Show description instead', 'value' => 'descinstead'),
            array('option' => 'Show only the title', 'value' => 'titleonly'),
            array('option' => 'Dont show that result', 'value' => 'removeres')
        ),
        'value'=>$sd['pifnoimage']
    ));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("pshowdesc", __('Show descripton on the back of the polaroid', 'ajax-search-pro'), $sd['pshowdesc']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsNumericUnit("prescontainerheight", __('Container height', 'ajax-search-pro'), array(
        'value' => $sd['prescontainerheight'],
        'units'=>array('px'=>'px')));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsNumericUnit("preswidth", __('Result width', 'ajax-search-pro'), array(
        'value' => $sd['preswidth'],
        'units'=>array('px'=>'px')));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsNumericUnit("presheight", __('Result max. height', 'ajax-search-pro'), array(
        'value' => $sd['presheight'],
        'units'=>array('px'=>'px')));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsNumericUnit("prespadding", __('Result padding', 'ajax-search-pro'), array(
        'value' => $sd['prespadding'],
        'units'=>array('px'=>'px')));
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsYesNo("pshowsubtitle", __('Show date/author', 'ajax-search-pro'), $sd['pshowsubtitle']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsFontComplete("prestitlefont", __('Result title font', 'ajax-search-pro'), $sd['prestitlefont']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsFontComplete("pressubtitlefont", __('Result sub-title font', 'ajax-search-pro'), $sd['pressubtitlefont']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>

<div class="item"><?php
    $o = new wpdreamsFontComplete("presdescfont", __('Result description font', 'ajax-search-pro'), $sd['presdescfont']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsGradient("prescontainerbg", __('Container background', 'ajax-search-pro'), $sd['prescontainerbg']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsGradient("pdotssmallcolor", __('Nav dot colors', 'ajax-search-pro'), $sd['pdotssmallcolor']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsGradient("pdotscurrentcolor", __('Nav active dot color', 'ajax-search-pro'), $sd['pdotscurrentcolor']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsGradient("pdotsflippedcolor", __('Nav flipped dot color', 'ajax-search-pro'), $sd['pdotsflippedcolor']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>