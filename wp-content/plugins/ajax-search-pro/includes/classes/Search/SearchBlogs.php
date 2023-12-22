<?php
namespace WPDRMS\ASP\Search;

use stdClass;

defined('ABSPATH') or die("You can't access this file directly.");


class SearchBlogs extends AbstractSearch {

	/**
	 * The search function
	 *
	 * @return array of results
	 */
	protected function doSearch(): array {
		$args = &$this->args;

		$sd = $args["_sd"] ?? array();

		/* There are no blog images available, return nothing for polaroid mode */
		if ($args['_ajax_search'] && isset($sd['resultstype']) &&
			$sd['resultstype'] == 'polaroid' && $sd['pifnoimage'] == 'removeres') {
			return array();
		}

		$s = $this->s;
		$_s = $this->_s;

		if ( $args['_limit'] > 0 ) {
			$limit = $args['_limit'];
		} else {
			if ( $args['_ajax_search'] )
				$limit = $args['blogs_limit'];
			else
				$limit = $args['blogs_limit_override'];
		}

		if ($limit <= 0)
			return array();

		$blogresults = array();

		$blog_list = wpdreams_get_blog_list(0, 'all');
		foreach ($blog_list as $bk => $blog) {
			if ( in_array($blog['blog_id'], $args['blog_exclude']) )
				unset($blog_list[$bk]);
			$_det = get_blog_details($blog['blog_id']);
			$blog_list[$bk]['name'] = $_det->blogname;
			$blog_list[$bk]['siteurl'] = $_det->siteurl;
			$blog_list[$bk]['match'] = 0;
		}

		if (isset($search)) {
			foreach ($_s as $keyword) {
				foreach ($blog_list as $bk => $blog) {
					if ( $blog['match'] == 1) continue;
					$pos = strpos(strtolower($blog['name']), $keyword);
					if ($pos !== false) $blog_list[$bk]['match'] = 1;
				}
			}
		}
		foreach ($blog_list as $bk => $blog) {
			if ( $blog['match'] == 1) continue;
			$pos = strpos(strtolower($blog['name']), $s);
			if ($pos !== false) $blog_list[$bk]['match'] = 1;
		}
		foreach ($blog_list as $blog) {
			if ( $blog['match'] == 1) {
				$_blogres = new stdClass();
				switch_to_blog($blog['blog_id']);
				$_blogres->title = $blog['name'];
				$_blogres->link = get_bloginfo('url');
				$_blogres->content = get_bloginfo('description');
				$_blogres->author = "";
				$_blogres->date = "";
				$_blogres->content_type = "blog";
				$_blogres->g_content_type = "blogs";
				$_blogres->blogid = $blog['blog_id'];
				$_blogres->id = $blog['blog_id'];
				$blogresults[] = $_blogres;
			}
		}
		if (w_isset_def($sd['blogtitleorderby'], 'desc') == 'asc') {
			$blogresults = array_reverse($blogresults);
		}
		restore_current_blog();

		$this->results_count = count($blogresults);
		/* For non-ajax search, results count needs to be limited to the maximum limit,
		 * as nothing is parsed beyond that */
		if ($args['_ajax_search'] == false && $this->results_count > $limit) {
			$this->results_count = $limit;
		}

		$blogresults = array_slice($blogresults, $args['_call_num'] * $limit, $limit);

		$this->results = $blogresults;
		$this->return_count = count($this->results);

		return $blogresults;
	}

	function postProcess() {}
}