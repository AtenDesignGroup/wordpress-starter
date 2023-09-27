<ul id="tabs"  class='tabs subtheme-tabs'>
    <li><a tabid="601" class='subtheme current'><?php echo __('Overall box layout', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="602" class='subtheme'><?php echo __('Input field layout', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="603" class='subtheme'><?php echo __('Settings icon & dropdown', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="604" class='subtheme'><?php echo __('Magnifier & loading icon', 'ajax-search-pro'); ?></a></li>
	<li><a tabid="612" class='subtheme'><?php echo __('Search text button', 'ajax-search-pro'); ?></a></li>
	<li><a tabid="613" class='subtheme subtheme-rinfobox'><?php echo __('Result Info Box', 'ajax-search-pro'); ?></a></li>
	<li><a tabid="614" class='subtheme subtheme-kwsuggestions'><?php echo __('Keyword Suggestions', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="605" class='subtheme subtheme-rlayout subtheme-isotopic'><?php echo __('Isotopic Results', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="606" class='subtheme subtheme-rlayout subtheme-isotopic'><?php echo __('Isotopic Navigation', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="607" class='subtheme subtheme-rlayout subtheme-vertical'><?php echo __('Vertical Results', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="608" class='subtheme subtheme-rlayout subtheme-horizontal'><?php echo __('Horizontal Results', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="609" class='subtheme subtheme-rlayout subtheme-polaroid'><?php echo __('Polaroid Results', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="610" class='subtheme'><?php echo __('Typography', 'ajax-search-pro'); ?></a></li>
    <li><a tabid="611" class='subtheme'><?php echo __('Custom CSS', 'ajax-search-pro'); ?></a></li>
</ul>
<div class='tabscontent'>

    <div tabid="601">
        <?php include(ASP_PATH."backend/tabs/instance/theme/overall_box.php"); ?>
    </div>
    <div tabid="602">
        <?php include(ASP_PATH."backend/tabs/instance/theme/input_field.php"); ?>
    </div>
    <div tabid="603">
        <?php include(ASP_PATH."backend/tabs/instance/theme/sett_dropdown.php"); ?>
    </div>
    <div tabid="604">
        <?php include(ASP_PATH."backend/tabs/instance/theme/magn_load.php"); ?>
    </div>
	<div tabid="612">
		<?php include(ASP_PATH."backend/tabs/instance/theme/search_text.php"); ?>
	</div>
	<div tabid="613">
		<?php include(ASP_PATH."backend/tabs/instance/theme/result_info_box.php"); ?>
	</div>
	<div tabid="614">
		<?php include(ASP_PATH."backend/tabs/instance/theme/keyword_suggestions.php"); ?>
	</div>
    <div tabid="605">
        <?php include(ASP_PATH."backend/tabs/instance/theme/isotopic_res.php"); ?>
    </div>
    <div tabid="606">
        <?php include(ASP_PATH."backend/tabs/instance/theme/isotopic_nav.php"); ?>
    </div>
    <div tabid="607">
        <?php include(ASP_PATH."backend/tabs/instance/theme/vertical_res.php"); ?>
    </div>
    <div tabid="608">
        <?php include(ASP_PATH."backend/tabs/instance/theme/horizontal_res.php"); ?>
    </div>
    <div tabid="609">
        <?php include(ASP_PATH."backend/tabs/instance/theme/polaroid_res.php"); ?>
    </div>
    <div tabid="610">
        <?php include(ASP_PATH."backend/tabs/instance/theme/typography.php"); ?>
    </div>
    <div tabid="611">
        <?php include(ASP_PATH."backend/tabs/instance/theme/custom_css.php"); ?>
    </div> <!-- tab 18 -->

</div> <!-- .tabscontent -->

<?php if(ASP_DEBUG==1): ?>
    <textarea class='previewtextarea' style='display:block;width:600px;'>
    </textarea>
<?php endif; ?>

<script>
    jQuery(document).ready(function() {
        (function( $ ){
            $(".previewtextarea").click(function(){
                var skip = ['settingsimage_custom', 'magnifierimage_custom', 'search_text', 'res_z_index', 'sett_z_index'];
                var parent = $(this).parent().parent();
                var content = "";
                var v = "";
                $("input[isparam=1], select[isparam=1]", parent).each(function(){
                    var name = $(this).attr("name");
                    if ( skip.indexOf(name) > -1 )
                        return true;
                    var val = $(this).val().replace(/(\r\n|\n|\r)/gm,"");
                    content += '"'+name+'":"'+val+'",\n';
                });
                //$(this).val(content+v);

                $("select[name=resultstype]").each(function(){
                    var name = $(this).attr("name");
                    var val = $(this).val().replace(/(\r\n|\n|\r)/gm,"");
                    content += '"'+name+'":"'+val+'",\n';
                });
                $("input[name=showdescription]").each(function(){
                    var name = $(this).attr("name");
                    var val = $(this).val().replace(/(\r\n|\n|\r)/gm,"");
                    content += '"'+name+'":"'+val+'",\n';
                });

                content = content.trim();
                content = content.slice(0, - 1);
                $(this).val('"theme": {\n' + content + "\n}");
            });
        }(jQuery))
    });
</script>
<div class="item">
    <input name="reset_<?php echo $search['id']; ?>" class="asp_submit asp_submit_transparent asp_submit_reset" type="button" value="<?php echo __('Restore defaults', 'ajax-search-pro'); ?>">
    <input name="submit_<?php echo $search['id']; ?>" type="submit" value="<?php echo __('Save this search!', 'ajax-search-pro'); ?>" />
</div>