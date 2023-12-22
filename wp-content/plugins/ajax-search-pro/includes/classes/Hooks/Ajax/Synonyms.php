<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace WPDRMS\ASP\Hooks\Ajax;

use WPDRMS\ASP\Synonyms\Manager as SynonymsManager;
use WPDRMS\ASP\Utils\Ajax;

if (!defined('ABSPATH')) die('-1');


class Synonyms extends AbstractAjax {
	function handle() {
		if ( 
			isset($_POST['asp_synonyms_request_nonce']) &&
			wp_verify_nonce( $_POST['asp_synonyms_request_nonce'], 'asp_synonyms_request_nonce' ) &&
			current_user_can( 'administrator' )
		) {
			if ( !isset($_POST['op']) ) {
				print -1;
				die();
			} else if ($_POST['op'] == 'find' || $_POST['op'] == 'findexact') {
				$this->find();
			} else if ($_POST['op'] == 'update') {
				$this->update();
			} else if ($_POST['op'] == 'delete') {
				$this->delete();
			} else if ($_POST['op'] == 'wipe') {
				$this->wipe();
			} else if ($_POST['op'] == 'export') {
				$this->export();
			} else if ($_POST['op'] == 'import') {
				$this->import();
			}
		}
	}

	private function find() {
		$syn = SynonymsManager::getInstance();
		$exact = $_POST['op'] == 'findexact';
		$limit = $exact == true ? 1 : 30;
		if ( isset($_POST['keyword'], $_POST['lang']) )
			$ret = $syn->find($_POST['keyword'], $_POST['lang'], $limit, $exact);
		else
			$ret = -1;
		print '!!!ASP_SYN_START!!!';
		Ajax::prepareHeaders();
		print_r(json_encode($ret));
		print '!!!ASP_SYN_END!!!';
		die();
	}

	private function update() {
		$syn = SynonymsManager::getInstance();
		if ( isset($_POST['keyword'], $_POST['synonyms'], $_POST['lang'], $_POST['overwrite_existing']) )
			$ret = $syn->update($_POST['keyword'], $_POST['synonyms'], $_POST['lang'], $_POST['overwrite_existing']);
		else
			$ret = -1;
		Ajax::prepareHeaders();
		print
			'!!!ASP_SYN_START!!!'.
			$ret.
			'!!!ASP_SYN_END!!!';
		die();
	}

	private function delete() {
		$syn = SynonymsManager::getInstance();
		if ( isset($_POST['id']) )
			$ret = $syn->deleteByID($_POST['id']);
		else
			$ret = -1;
		Ajax::prepareHeaders();
		print
			'!!!ASP_SYN_START!!!'.
			$ret.
			'!!!ASP_SYN_END!!!';
		die();
	}

	private function wipe() {
		$syn = SynonymsManager::getInstance();
		$syn->wipe();
		Ajax::prepareHeaders();
		print
			'!!!ASP_SYN_START!!!0!!!ASP_SYN_END!!!';
		die();
	}

	private function export() {
		$syn = SynonymsManager::getInstance();
		Ajax::prepareHeaders();
		print
			'!!!ASP_SYN_START!!!'.$syn->export().'!!!ASP_SYN_END!!!';
		die();
	}

	private function import() {
		$syn = SynonymsManager::getInstance();
		if ( !isset($_POST['path']) )
			$ret = -1;
		else
			$ret = $syn->import($_POST['path']);
		Ajax::prepareHeaders();
		print
			'!!!ASP_SYN_START!!!'.$ret.'!!!ASP_SYN_END!!!';
		die();
	}
}