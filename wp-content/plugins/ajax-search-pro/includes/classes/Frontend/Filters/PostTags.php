<?php
namespace WPDRMS\ASP\Frontend\Filters;

if (!defined('ABSPATH')) die('-1');

class PostTags extends TaxonomyTerm {
	protected $type = 'post_tags';

	public function field(): string {
		return 'post_tag';
	}
}