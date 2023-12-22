<?php
namespace WPDRMS\ASP\Asset;

/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

interface GeneratorInterface {
	function generate();
}