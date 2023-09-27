<?php
if (!class_exists("wpdreamsBlogselect")) {
	/**
	 * Class wpdreamsBlogselect
	 *
	 * Creates a blog selection drag and drop UI element.
	 *
	 * @package  WPDreams/OptionsFramework/Classes
	 * @category Class
	 * @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
	 * @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
	 * @copyright Copyright (c) 2012, Ernest Marcinko
	 */
	class wpdreamsBlogselect extends wpdreamsType {
		private $useall = "";
		private $blogs, $selected;

		function getType() {
			parent::getType();
			$this->processData();
			if ( function_exists( 'get_sites' ) ) {
				$this->blogs = get_sites(array('number' => 1000));
			} else {
				$this->blogs = array();
			}
			echo "
      <div class='wpdreamsBlogselect' id='wpdreamsBlogselect" . self::$_instancenumber . "'>
        <fieldset>
          <legend>" . $this->label . "</legend>";
			echo "<div style='text-align:left; margin: 10px 38px;'><label>" . __('Use all blogs?', 'ajax-search-pro') . " <input type='checkbox' class='use-all-blogs' ".$this->useall."></label></div>";
			echo "<div class='bs-cont'>";
			if ($this->useall != "")
				echo "<div class='bs-overlay'></div>";
			else
				echo "<div class='bs-overlay hiddend'></div>";
			echo '<div class="sortablecontainer"><p>' . __('Available blogs', 'ajax-search-pro') . '</p><ul id="sortable' . self::$_instancenumber . '" class="connectedSortable">';
			if ( !is_wp_error($this->blogs) ) {
				foreach ($this->blogs as $k => $blog) {
					if ( $this->selected == null || !in_array($blog->id, $this->selected) ) {
						echo '<li class="ui-state-default" bid="' . $blog->id . '">' . $blog->blogname . '</li>';
					}
				}
			}
			echo "</ul></div>";
			echo '<div class="sortablecontainer"><p>' . __('Drag here the blogs you want to use!', 'ajax-search-pro') . '</p><ul id="sortable_conn' . self::$_instancenumber . '" class="connectedSortable">';
			if ( $this->selected != null && count($this->selected)>0 && function_exists('get_blog_details') ) {
				foreach ($this->selected as $k => $v) {
					$blog = get_blog_details($v);
					if ( !is_wp_error($blog) && isset($blog->blogname) ) {
						echo '<li class="ui-state-default" bid="' . $v . '">' . $blog->blogname . '</li>';
					}
				}
			}
			echo "</ul></div><div class='clear'></div>";
			echo "</div>";
			echo "
         <input isparam=1 type='hidden' value='" . $this->data . "' name='" . $this->name . "'>";
			echo "
         <input type='hidden' value='wpdreamsBlogselect' name='classname-" . $this->name . "'>";
			?>
			<script>
				(function ($) {
					$(document).ready(function () {
						var selector = "#sortable<?php echo self::$_instancenumber ?>, #sortable_conn<?php echo self::$_instancenumber ?>";

						$(selector).sortable({
							connectWith: ".connectedSortable"
						}, {
							update: function (event, ui) {
							}
						}).disableSelection();

						$(selector).on('sortupdate', function(event, ui) {
							if (typeof ui !== 'undefined')
								parent = $(ui.item).parent();
							else
								parent = $(event.target);
							while ( !parent.hasClass('wpdreamsBlogselect') ) {
								parent = $(parent).parent();
							}
							var items = $('ul[id*=sortable_conn] li', parent);
							var hidden = $('input[name=<?php echo $this->name; ?>]', parent);
							var checkbox = $('.use-all-blogs', parent);
							var val = "";
							items.each(function () {
								val += "|" + $(this).attr('bid');
							});
							if (checkbox.prop('checked')) {
								val = val.substring(1) + "xxx1";
								$('.bs-overlay', parent).removeClass('hiddend');
							} else {
								val = val.substring(1);
								$('.bs-overlay', parent).addClass('hiddend');
							}
							hidden.val(val);
						});

						$("#wpdreamsBlogselect<?php echo self::$_instancenumber ?> input.use-all-blogs").on('click', function(){
							$(selector).trigger("sortupdate");
						});
					});
				}(jQuery));
			</script>
			<?php
			echo "
        </fieldset>
      </div>";
		}

		function processData() {
			$this->data = str_replace("\n", "", $this->data);
			$this->selected = null;

			if ($this->data != "") {
				// Check for the use-all checkbox
				$tmp = explode( "xxx", $this->data );
				if (isset($tmp[1]))
					$this->useall = "checked='checked'";

				// Get the selected IDs
				if ($tmp[0] != "")
					$this->selected = explode( "|", $tmp[0] );
			} else {
				$this->useall = "";
			}
		}

		final function getData() {
			return $this->data;
		}

		final function getSelected() {
			if ($this->useall != '')
				return "all";
			return $this->selected;
		}
	}
}