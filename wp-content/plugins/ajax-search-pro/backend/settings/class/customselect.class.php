<?php  /** @noinspection PhpMultipleClassDeclarationsInspection */
if (!class_exists("wpdreamsCustomSelect")) {
    /**
     * Class wpdreamsCustomSelect
     *
     * A customisable dropdown UI element.
     *
     * @package  WPDreams/OptionsFramework/Classes
     * @category Class
     * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
     * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
     * @copyright Copyright (c) 2014, Ernest Marcinko
     */
    class wpdreamsCustomSelect extends wpdreamsType {
        private $selects, $selected, $iconMsg, $icon = 'none';

        public function getType() {
            parent::getType();
            $this->processData();
            echo "<div class='wpdreamsCustomSelect'>";
            echo "<label for='wpdreamscustomselect_" . self::$_instancenumber . "'>" . $this->label . "</label>";
			if ( $this->icon != 'none' ) {
				?>
				<span
					title="<?php echo $this->iconMsg[$this->icon] ?? ''; ?>"
					class="wpd-txt-small-icon wpd-txt-small-icon-<?php echo $this->icon ?>">
                </span>
				<?php
			}
            echo "<select isparam=1 class='wpdreamscustomselect' id='wpdreamscustomselect_" . self::$_instancenumber . "' name='" . $this->name . "'>";
            foreach ($this->selects as $sel) {
                $disabled = isset($sel['disabled']) ? ' disabled' : '';
                if (is_array($sel)) {
                    if (($sel['value'] . "") == ($this->selected . ""))
                        echo "<option value='" . $sel['value'] . "' selected='selected'" . $disabled . ">" . $sel['option'] . "</option>";
                    else
                        echo "<option value='" . $sel['value'] . "'>" . $sel['option'] . "</option>";
                } else {
                    if (($sel . "") == ($this->selected . ""))
                        echo "<option value='" . $sel . "' selected='selected'>" . $sel . "</option>";
                    else
                        echo "<option value='" . $sel . "'>" . $sel . "</option>";
                }
            }
            echo "</select>";
            echo "<div class='triggerer'></div>
      </div>";
        }

        public function processData() {
			$this->iconMsg = array(
				'phone' => __('Phone devices, on 0px to 640px widths', 'ajax-search-pro'),
				'tablet' => __('Tablet devices, on 641px to 1024px widths', 'ajax-search-pro'),
				'desktop' => __('Desktop devices, 1025px width  and higher', 'ajax-search-pro')
			);

            $this->selects = array();
			$this->icon = $this->data['icon'] ?? $this->icon;
            $this->selects = $this->data['selects'];
            $this->selected = $this->data['value'];
        }

        public final function getData() {
            return $this->data;
        }

        public final function getSelected() {
            return $this->selected;
        }

    }
}