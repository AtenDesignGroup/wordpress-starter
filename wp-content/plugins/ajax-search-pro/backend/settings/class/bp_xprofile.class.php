<?php
if (!class_exists("wpdreamsBP_XProfileFields")) {
	/**
	 * Class wpdreamsBP_XProfileFields
	 *
	 * Creates a BuddyPress profile fields selector UI element.
	 *
	 * @package  WPDreams/OptionsFramework/Classes
	 * @category Class
	 * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
	 * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
	 * @copyright Copyright (c) 2014, Ernest Marcinko
	 */
	class wpdreamsBP_XProfileFields extends wpdreamsType {
		private $selected;

		function getType() {

			$profile_fields = $this->getProfileFields();

			$pf_array = array();
			foreach ($profile_fields as $pf)
				$pf_array[$pf->id] = $pf;

			parent::getType();
			$this->processData();

			$this->types = $profile_fields;
			echo "
      <div class='wpdreamsBP_XProfileFields' id='wpdreamsBP_XProfileFields-" . self::$_instancenumber . "'>
        <fieldset>
          <legend>" . $this->label . "</legend>";
			echo '<div class="sortablecontainer" id="sortablecontainer' . self::$_instancenumber . '">
                  <div class="arrow-all-left"></div>
                  <div class="arrow-all-right"></div>
                ' . __('Available profile fields', 'ajax-search-pro') . '<ul id="sortable' . self::$_instancenumber . '" class="connectedSortable">';
			if ($this->types != null && is_array($this->types)) {
				foreach ($this->types as $k => $v) {
					if ($this->selected == null || !in_array($v->id, $this->selected)) {
						echo '<li class="ui-state-default" bid="' . $v->id . '">' . $v->name . '</li>';
					}
				}
			}
			echo "</ul></div>";
			echo '<div class="sortablecontainer">' . __('Drag here the fields you want to search!', 'ajax-search-pro') . '<ul id="sortable_conn' . self::$_instancenumber . '" class="connectedSortable">';
			if ($this->selected != null && is_array($this->selected)) {
				foreach ($this->selected as $k => $v) {
					if ( isset($pf_array[$v]) ) {
						echo '<li class="ui-state-default" bid="' . $pf_array[$v]->id . '">' . $pf_array[$v]->name . '</li>';
					}
				}
			}
			echo "</ul></div>";
			echo "
         <input isparam=1 type='hidden' value='" . $this->data . "' name='" . $this->name . "'>";
			echo "
         <input type='hidden' value='wpdreamsBP_XProfileFields' name='classname-" . $this->name . "'>";
			echo "
        </fieldset>
      </div>";
		}

		function getProfileFields() {
			global $wpdb;
			$table_name = $wpdb->base_prefix . "bp_xprofile_fields";

			if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name )
				return $wpdb->get_results(
					"SELECT * FROM $table_name LIMIT 200"
				);
			else
				return array();
		}

		function processData() {
			$this->data = str_replace("\n", "", $this->data);
			if ($this->data != "")
				$this->selected = explode("|", $this->data);
			else
				$this->selected = null;
		}

		final function getData() {
			return $this->data;
		}

		final function getSelected() {
			return $this->selected;
		}
	}
}