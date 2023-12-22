<?php
namespace WPDRMS\ASP\Synonyms;

use WPDRMS\ASP\Patterns\SingletonTrait;
use WPDRMS\ASP\Utils\FileManager;
use WPDRMS\ASP\Utils\MB;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Manager') ) {
	class Manager {
		use SingletonTrait;

		private $db;
		/**
		 * Array of synonyms, set during the init process
		 * @var array
		 */
		private $synonyms = array();
		/**
		 * False until initialized
		 * @var bool
		 */
		private $initialized = false;

		/**
		 * False, until the synonyms as keywords conversion is called
		 * @var bool
		 */
		private $converted = false;

		function __construct() {
			$this->db = new Database();
		}

		/**
		 * Gets keyword synonyms, based on keyword+language. Returns all synonyms when $keyword is empty.
		 *
		 * @param string $keyword
		 * @param string $language
		 * @param bool $refresh
		 * @return array|bool Array of synonyms, all synonyms array or False on failure.
		 */
		public function get(string $keyword = '', string $language = '', bool $refresh = false) {
			if ( $keyword == '' ) {
				if ( $refresh || $this->initialized === false ) {
					$this->init();
				}
				return $this->synonyms; // return all
			} else {
				$keyword = $this->processKeyword($keyword);
				if ( $keyword != '' ) {
					if ( $refresh || $this->initialized === false ) {
						$this->init();
					}
					$lang = $language == '' ? 'default' : $language;
					if ( isset($this->synonyms[$lang], $this->synonyms[$lang][$keyword]) )
						return $this->synonyms[$lang][$keyword];
					else
						return false;
				}
			}

			return false;
		}

		/**
		 * Checks if a keyword+language pair has synonyms.
		 * If $keyword is empty, checks if there are any synonyms are available
		 *
		 * @param string $keyword
		 * @param string $language
		 * @return bool
		 */
		public function exists(string $keyword = '', string $language = ''): bool {
			$keyword = $this->processKeyword($keyword);
			if ( $this->initialized === false ) {
				$this->init();
			}
			$lang = $language == '' ? 'default' : $language;
			if ( $keyword == '' ) {
				return count($this->synonyms) > 0;
			} else {
				return isset($this->synonyms[$lang], $this->synonyms[$lang][$keyword]);
			}
		}

		/**
		 * Adds a keyword and synonyms to the database
		 *
		 * @param string $keyword
		 * @param string|array $synonyms
		 * @param string $language
		 * @return int number of rows inserted
		 */
		public function add(string $keyword, $synonyms, string  $language = ''): int {
			$synonyms_arr = $this->processSynonyms($synonyms);
			$keyword = $this->processKeyword($keyword);
			$language = $this->processKeyword($language);

			if ( count($synonyms_arr) < 1 || $keyword == '' ) {
				return 0;
			} else {
				return $this->db->add($keyword, $synonyms_arr, $language);
			}
		}

		/**
		 * Updates are row, based on $keyword+$lang unique key. If the row does not exist, it is created.
		 *
		 * @param string $keyword
		 * @param string|array $synonyms
		 * @param string $language
		 * @param bool $overwrite_existing
		 * @return int number of affected rows
		 */
		public function update(string $keyword, $synonyms, string $language = '', bool $overwrite_existing = true): int {
			$synonyms_arr = $this->processSynonyms($synonyms);
			$keyword = $this->processKeyword($keyword);
			$language = $this->processKeyword($language);

			if ( count($synonyms_arr) < 1 || $keyword == '' )
				return 0;

			if ( !$this->exists($keyword, $language) ) {
				return $this->add($keyword, $synonyms_arr, $language);
			} else {
				if ( $overwrite_existing ) {
					return $this->db->update($keyword, $synonyms_arr, $language);
				} else {
					return 0;
				}
			}
		}

		/**
		 *  Converts synonyms into keyword => synonym pairs
		 * @noinspection PhpUnused
		 */
		public function synonymsAsKeywords() {
			if ( $this->initialized === false ) {
				$this->init();
			}
			if ( count($this->synonyms) > 0 && $this->converted == false ) {
				foreach ( $this->synonyms as $lang => $synonyms ) {
					$additional_syns = array();
					foreach ( $synonyms as $kw => $syns ) {
						foreach ( $syns as $syn ) {
							if ( !isset($additional_syns[$syn]) ) {
								$additional_syns[$syn] = array_merge(
									array($kw),
									array_diff($syns, array($syn))
								);
							}
						}
					}
					if ( count($additional_syns) > 0 ) {
						$this->synonyms[$lang] = array_merge(
							$this->synonyms[$lang],
							$additional_syns
						);
					}
				}
				$this->converted = true;
			}
		}

		/**
		 * Deletes a row based on keyword+language keys
		 *
		 * @param string $keyword
		 * @param string $language
		 * @return int number of affected rows
		 */
		public function delete(string $keyword, string $language = ''): int {
			$keyword = $this->processKeyword($keyword);
			if ( $keyword != '' ) {
				return $this->db->delete($keyword, $language);
			} else {
				return 0;
			}
		}

		/**
		 * Deletes a row by ID
		 *
		 * @param $id
		 * @return int number of affected rows
		 * @noinspection PhpUnused
		 */
		public function deleteByID($id): int {
			$id = (int)$id;
			if ( $id != 0 ) {
				return $this->db->deleteByID($id);
			} else {
				return 0;
			}
		}

		/**
		 * Deletes all rows from the database table
		 */
		public function wipe() {
			$this->db->wipe();
		}


		/**
		 * Looks for a synonym based on keyword and language. If keyword is empty, lists all results from the language.
		 *
		 * @param string $keyword
		 * @param string $language When set to 'any', looks in all languages. Empty '' value is the default language.
		 * @param int $limit
		 * @param bool $exact
		 * @return array Results
		 */
		public function find(string $keyword = '', string $language = '', int $limit = 30, bool $exact = false): array {
			return $this->db->find($this->processKeyword($keyword), $language, $limit, $exact);
		}

		/**
		 * Generates an export file to the upload directory.
		 *
		 * @return int|string -1 on error, 0 when no rows to export, URL of file on success
		 */
		public function export() {
			if ( $this->exists() === false )
				return 0;

			$res = $this->db->export();

			if ( count($res) == 0 )
				return 0; // Nothing to export

			/** @noinspection PhpComposerExtensionStubsInspection */
			$contents = json_encode($res);
			$filename = 'asp_synonyms_export.json';

			if ( FileManager::_o()->write( wd_asp()->upload_path . $filename , $contents) !== false )
				return wd_asp()->upload_url . $filename;
			else
				return -1;
		}

		/**
		 * Imports synonyms from an export file.
		 *
		 * @param $path
		 * @return int Number of affected rows. -2 on file IO errors, -1 on file content errors
		 */
		public function import($path): int {
			$att = attachment_url_to_postid($path);
			if ( $att != 0 ) {
				$att = get_attached_file($att);
				$contents = FileManager::_o()->read($att);
			} else {
				$contents = FileManager::_o()->read($path);
			}

			if ( !empty($contents) ) {
				$type = wp_check_filetype($path);
				if ( $type['ext'] == 'csv' ) {
					if ( is_string($att) && $att != '' ) {
						$fp = fopen($att, "r");
						$csv = array();
						$contents = array();
						while (($data = fgetcsv($fp)) !== FALSE) {
							$csv[] = $data;
						}
						fclose($fp);
						foreach ($csv as $row) {
							$new = array();
							$new['keyword'] = $row[0];
							$new['synonyms'] = array_slice($row, 1);
							$new['synonyms'] = array_filter($new['synonyms'], 'strlen');
							$new['lang'] = '';
							if ( !empty($new['synonyms']) ) {
								$new['synonyms'] = implode(',', $new['synonyms']);
								$contents[] = $new;
							}
						}
					}
				} else {
					$contents = str_replace(array("\r", "\n"), '', trim($contents));
					/** @noinspection PhpComposerExtensionStubsInspection */
					$contents = json_decode($contents, true);
				}

				if ( is_array($contents) ) {
					$contents = array_map(function($synonym){
						$synonym['keyword'] = $this->processKeyword($synonym['keyword']);
						$synonym['synonyms'] = $this->processKeyword($synonym['synonyms']);
						$synonym['lang'] = $this->processKeyword($synonym['lang']);
						return $synonym;
					}, $contents);
					return $this->db->import($contents);
				} else {
					return -1; // Invalid content?
				}
			} else {
				return -2; // Something went wrong
			}
		}

		/**
		 * Creates the synonyms table and the constraints.
		 *
		 * @param string $table_name
		 * @return array queries
		 */
		public function createTable(string $table_name = ''): array {
			return $this->db->createTable($table_name);
		}

		/**
		 * Initializes the synonyms variable
		 */
		private function init() {
			$res = $this->db->select();
			if ( !is_wp_error($res) && count($res) > 0 ) {
				$this->synonyms = array();
				foreach( $res as $row ) {
					$lang = $row['lang'] == '' ? 'default' : $row['lang'];
					if ( !isset($this->synonyms[$lang]) )
						$this->synonyms[$lang] = array();
					$this->synonyms[$lang][$row['keyword']] = wpd_comma_separated_to_array($row['synonyms']);
				}
			}   // else $this->synonyms stays false

			$this->initialized = true;
		}

		/**
		 * Clears the synonyms array before the DB processing
		 *
		 * @param $synonyms
		 * @return array
		 */
		private function processSynonyms($synonyms): array {
			$synonyms_arr = is_array($synonyms) ?
				wpd_comma_separated_to_array(implode(',',$synonyms)) : wpd_comma_separated_to_array($synonyms);

			foreach ( $synonyms_arr as &$w ) {
				$w = MB::strtolower($w);
				$w = trim(preg_replace('/\s+/', ' ', $w));
			}

			return $synonyms_arr;
		}

		/**
		 * Clears the keyword before the DB processing
		 *
		 * @param $keyword
		 * @return string
		 */
		private function processKeyword($keyword): string {
			return preg_replace('/\s+/', ' ', MB::strtolower(trim($keyword)));
		}
	}
}