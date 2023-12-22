<div class="item"><?php
    $o = new wpdreamsFontComplete("titlefont", __('Results title link font', 'ajax-search-pro'), $sd['titlefont']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsFontComplete("authorfont", __('Author text font', 'ajax-search-pro'), $sd['authorfont']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsFontComplete("datefont", __('Date text font', 'ajax-search-pro'), $sd['datefont']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsFontComplete("descfont", __('Description text font', 'ajax-search-pro'), $sd['descfont']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsColorPicker("exsearchincategoriesboxcolor","Grouping box header background color", $sd['exsearchincategoriesboxcolor']);
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item"><?php
    $o = new wpdreamsColorPicker("groupingbordercolor","Grouping box border color", $sd['groupingbordercolor']);
    $params[$o->getName()] = $o->getData();
    ?></div>
<div class="item"><?php
    $o = new wpdreamsFontComplete("groupbytextfont", __('Grouping font color', 'ajax-search-pro'), $sd['groupbytextfont']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item">
    <?php
    $o = new wpdreamsColorPicker("showmorefont_bg","'Show more results' background color", $sd['showmorefont_bg']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>
<div class="item"><?php
    $o = new wpdreamsFontComplete("showmorefont", __('\'Show more results\' font', 'ajax-search-pro'), $sd['showmorefont']);
    $params[$o->getName()] = $o->getData();
    ?>
</div>