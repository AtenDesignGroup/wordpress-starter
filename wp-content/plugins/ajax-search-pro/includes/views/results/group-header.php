<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * This is printed as the group header - only works with vertical results
 *
 * !!!IMPORTANT!!!
 * Do not make changes directly to this file! To have permanent changes copy this
 * file to your theme directory under the "asp" folder like so:
 *    wp-content/themes/your-theme-name/asp/group-header.php
 *
 * You can use any WordPress function here.
 * Variables to mention:
 *      String $group_name - the group name (including post count)
 *      Array[]  $s_options - holding the search options
 *
 * You can leave empty lines for better visibility, they are cleared before output.
 *
 * MORE INFO: https://wp-dreams.com/knowledge-base/result-templating/
 *
 * @since: 4.0
 */
?>
<div class="asp_results_group <?php echo $group_class; ?>">
	<div class="asp_group_header"><?php echo $group_name; ?></div>