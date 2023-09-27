<?php  /** @noinspection PhpUnused */
namespace WPDRMS\ASP\Hooks\Actions;

if (!defined('ABSPATH')) die('-1');

class OutputBuffer extends AbstractAction {
	// template_redirect
	function obStart() {
		\WPDRMS\ASP\Misc\OutputBuffer::getInstance()->obStart();
	}

	// shutdown
	function obClose() {
		\WPDRMS\ASP\Misc\OutputBuffer::getInstance()->obClose();
	}

	function handle(){}
}