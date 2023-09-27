<?php
namespace WPDRMS\ASP\Hooks\Ajax;

use WPDRMS\ASP\Misc\Statistics;
use WPDRMS\ASP\Utils\Ajax;

if (!defined('ABSPATH')) die('-1');


class AddKeyword extends AbstractAjax {
	function handle() {
		if ( isset($_POST['id'], $_POST['keyword']) ) {
			echo (Statistics::addKeyword($_POST['id'] + 0, $_POST['keyword']) === true) ? 1 : 0;
			exit;
		}
		Ajax::prepareHeaders();
		echo 0;
		die();
	}
}