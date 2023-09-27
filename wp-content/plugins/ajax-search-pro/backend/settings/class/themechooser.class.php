<?php
if (!class_exists("wpdreamsThemeChooser")) {
    /**
     * Class wpdreamsThemeChooser
     *
     * Theme selector class. Uses the json decoded data do form each theme.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsThemeChooser extends wpdreamsType {
        private $themes, $selected;

        function getType() {
            parent::getType();
            $this->processData();
            echo "
      <div class='wpdreamsThemeChooser'>
       <fieldset style='background:#FAFAFA;padding:0;'>
       <label style='color:#333' for='wpdreamsThemeChooser_" . self::$_instancenumber . "'>" . $this->label . "</label>";
            $decodedData = $this->themes;
            echo "<select id='wpdreamsThemeChooser_" . self::$_instancenumber . "' name='" . $this->name . "'>
          <option value=''>Select</option>";
            foreach ($decodedData as $name => $theme) {
                $selected = $name == $this->selected ? " selected='selected'" : "";
                if ($theme === false)
                    echo "<option value='" . $name . "' disabled>" . $name . "</option>";
                else
                    echo "<option value='" . $name . "' " . $selected.">&nbsp;&nbsp;" . $name . "</option>";
            }
            echo "</select>";
            foreach ($decodedData as $name => $theme) {
                if ($theme === false) continue;
                echo "<div name='" . $name . "' style='display:none;'>";
                echo json_encode($theme);
                echo "</div>";
            }
            echo "
      <span></span>
      <input id='asp_import_theme' class='submit wd_button_red' type='button' value='Import'>
      <input id='asp_export_theme' class='submit wd_button_blue' type='button' value='Export'>
      <p class='descMsg'>" . __('Changes not take effect on the frontend until you save them.', 'ajax-search-pro') . "</p>
      </fieldset>";
      ?>
<div id="wpd_imex_modal_bg" class="wpd-modal-bg"></div>
<div id="wpd_import_modal" class="wpd-modal hiddend">
    <h3 style="font-family: 'Open Sans',sans-serif;text-align: left; margin-top: 0;margin-left: 7px; font-size: 18px; font-weight: 600;"><?php echo __('Import theme', 'ajax-search-pro'); ?></h3>
    <div class="wpd-modal-close"></div>
    <div class="wpd_md_col">
        <p class="descMsg"><?php echo __('Paste the exported theme here', 'ajax-search-pro'); ?></p>
        <textarea></textarea><br>
        <div class="errorMsg hiddend"><?php echo __('Invalid or missing data, please try again!', 'ajax-search-pro'); ?></div>
        <input id='asp_import_theme_btn' class='submit wd_button_red' type='button' value='<?php echo __('Import', 'ajax-search-pro'); ?>'>
    </div>
</div>
<div id="wpd_export_modal" class="wpd-modal hiddend">
    <h3 style="font-family: 'Open Sans',sans-serif;text-align: left; margin-top: 0;margin-left: 7px; font-size: 18px; font-weight: 600;"><?php echo __('Export theme', 'ajax-search-pro'); ?></h3>
    <div class="wpd-modal-close"></div>
    <div class="wpd_md_col">
        <p class="descMsg"><?php echo __('Copy this text and save it on your computer', 'ajax-search-pro'); ?></p>
        <textarea></textarea>
    </div>
</div>
      <?php
      echo "</div>";
        }

        function processData() {
            $this->themes = $this->data['themes'];
            $this->selected = $this->data['value'];
        }

        final function getData() {
            return $this->data;
        }

        final function getSelected() {
            return $this->selected;
        }
    }
}