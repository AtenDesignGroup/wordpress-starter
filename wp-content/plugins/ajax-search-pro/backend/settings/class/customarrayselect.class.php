<?php
if (!class_exists("wpdreamsCustomArraySelect")) {
	/**
	 * Class wpdreamsCustomArraySelect
	 *
	 * A customisable drop down UI element, supports multiple select boxes chained.
	 *
	 * @package  WPDreams/OptionsFramework/Classes
	 * @category Class
	 * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
	 * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
	 * @copyright Copyright (c) 2014, Ernest Marcinko
	 */
	class wpdreamsCustomArraySelect extends wpdreamsType {

		private $optionsArr = array();
		private $selectedArr = array();

		function getType() {
			parent::getType();
			$this->processData();

			echo "<div class='wpdreamsCustomArraySelect'>";

			foreach ($this->optionsArr as $k => $options) {

				// label for each box
				if ( is_array($this->label) && $this->label[$k] != '' )
					echo "<label for='" .$k. "wpdreamscustomarrayselect_" . self::$_instancenumber . "'>" . $this->label[$k] . "</label>";

				// this is not the parameter, its just a dummy
				echo "<select class='wpdreamscustomarrayselect' id='" .$k. "wpdreamscustomarrayselect_" . self::$_instancenumber . "' name='dummy-" . $this->name . "'>";

				foreach ($options as $kk => $option) {
					if (($option['value'] . "") == ($this->selectedArr[$k] . ""))
						echo "<option value='" . $option['value'] . "' selected='selected'>" . $option['option'] . "</option>";
					else
						echo "<option value='" . $option['value'] . "'>" . $option['option'] . "</option>";
				}

				echo "</select>";

			}

			echo "
				<input type='hidden' isparam=1 name='" . $this->name . "' value='".$this->data."' />
				<input type='hidden' value='wpdreamsCustomArraySelect' name='classname-" . $this->name . "'>
			</div>";

		}

		function processData() {
			if ( is_array($this->data) ) {
				// invoked on the backend
				$this->optionsArr = $this->data['optionsArr'];
				$this->selectedArr = explode('||', $this->data['value']);
				// change the data to the actual value
				$this->data = $this->data['value'];
			} else {
				// invoked by wpdreams_parse_params(), when saving
				$this->selectedArr = explode('||', $this->data);
			}
		}

		final function getData() {
			return $this->data;
		}

		final function getSelected() {
			return $this->selectedArr;
		}

	}
}