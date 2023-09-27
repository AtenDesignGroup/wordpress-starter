<?php
namespace WPDRMS\ASP\Media;

/* Prevent direct access */

use Exception;
use WPDRMS\ASP\Media\RemoteService\License;
use WPDRMS\ASP\Utils\FileManager;
use WPDRMS\ASP\Utils\Str;

defined( 'ABSPATH' ) or die( "You can't access this file directly." );

class Parser {
	/**
	 * Mime groups array
	 *
	 * @var array
	 */
	private static $mimes = array(
		'pdf'            => array(
			'application/pdf'
		),
		'text'           => array(
			'text/plain',
			'text/csv',
			'text/tab-separated-values',
			'text/calendar',
			'text/css',
			'text/html'
		),
		'richtext'       => array(
			'text/richtext',
			'application/rtf'
		),
		'mso_word'       => array(
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/vnd.ms-word.document.macroEnabled.12',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'application/vnd.ms-word.template.macroEnabled.12',
			'application/vnd.oasis.opendocument.text'
		),
		'mso_excel'      => array(
			'application/vnd.ms-excel',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'application/vnd.ms-excel.sheet.macroEnabled.12',
			'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
			'application/vnd.ms-excel.template.macroEnabled.12',
			'application/vnd.ms-excel.addin.macroEnabled.12',
			'application/vnd.oasis.opendocument.spreadsheet',
			'application/vnd.oasis.opendocument.chart',
			'application/vnd.oasis.opendocument.database',
			'application/vnd.oasis.opendocument.formula'
		),
		'mso_powerpoint' => array(
			'application/vnd.ms-powerpoint',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
			'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
			'application/vnd.openxmlformats-officedocument.presentationml.template',
			'application/vnd.ms-powerpoint.template.macroEnabled.12',
			'application/vnd.ms-powerpoint.addin.macroEnabled.12',
			'application/vnd.openxmlformats-officedocument.presentationml.slide',
			'application/vnd.ms-powerpoint.slide.macroEnabled.12',
			'application/vnd.oasis.opendocument.presentation',
			'application/vnd.oasis.opendocument.graphics'
		)
	);
	private $args, $post, $filepath;


	/**
	 * Constructor.
	 * @param \WP_Post $post
	 * @param array $args
	 */
	function __construct($post, $args) {
		$defaults = array(
			'media_service_send_file' => false,
			'pdf_parser' => 'auto', // auto, smalot, pdf2txt
			'text_content' => true,
			'richtext_content' => true,
			'pdf_content' => true,
			'msword_content' => true,
			'msexcel_content' => true,
			'msppt_content' => true
		);

		$this->post = $post;
		$this->args = wp_parse_args($args, $defaults);
		$this->args = apply_filters('asp_media_parser_args', $this->args, $defaults);
	}

	/**
	 * Parses the file contents
	 *
	 * @return string|\WP_Error
	 */
	function parse( $remote = true ) {
		$this->filepath = get_attached_file( $this->post->ID );
		if ( is_wp_error($this->filepath) || empty($this->filepath) || !file_exists($this->filepath) ) {
			return '';
		}

		$text = '';
		if ( $this->isThisParseEnabled() ) {
			if ( $remote ) {
				$text = $this->parseRemote();
			} else {
				$text = $this->parseLocal();
			}
			/**
			 * In case of PDF there are many cases of gibberish in text when using OCR, such as
			 * "my text a<bc my other text" -> where "<bc" is treated as an opening HTML tag.
			 * Then running strip_tags("my text a<bc my other text") = "my text a" -> will remove everything
			 * after the tag starting bracket, because the "my other text" is considered as tag attributes.
			 * The best course of action here is to simply remove these symbols.
			 */
			if ( !is_wp_error($text) && $this->isThis('pdf') ) {
				$text = str_replace(array('<', '>'), ' ', $text);
			}
		}

		return $text;
	}

	function parseRemote() {
		$text = '';
		$license = License::getInstance();

		if ( $license->active() && $license->valid() ) {
			if ( !in_array('_asp_attachment_text', get_post_custom_keys($this->post->ID)) ) {
				$license_data = $license->getData();
				$license_max_filesize =
					isset($license_data['stats'], $license_data['stats']['max_filesize']) ?
						$license_data['stats']['max_filesize'] : 0;
				$parser = new RemoteService\Parser(
					$this->filepath,
					wp_get_attachment_url($this->post->ID),
					$license->get(),
					$this->args['media_service_send_file'],
					$license_max_filesize
				);
				$text = $parser->request();
				$license->setStats($parser->getStats());

				if ( !is_wp_error($text) ) {
					update_post_meta($this->post->ID, '_asp_attachment_text', $text);
				} else {
					return $text;
				}
			} else {
				$text = get_post_meta($this->post->ID, '_asp_attachment_text', true);
			}
		}

		return $text;
	}

	function parseLocal() {
		if ( $this->isThis('text') && $this->args['text_content'] ) {
			$text = $this->parseTXT();
		} else if ( $this->isThis('richtext') && $this->args['richtext_content'] ) {
			$text = $this->parseRTF();
		} else if ( $this->isThis('pdf') && $this->args['pdf_content'] ) {
			$text = $this->parsePDF();
		} else if ( $this->isThis('mso_word') && $this->args['msword_content'] ) {
			$text = $this->parseMSOWord();
		} else if ( $this->isThis('mso_excel') && $this->args['msexcel_content'] ) {
			$text = $this->parseMSOExcel();
		} else if ( $this->isThis('mso_powerpoint') && $this->args['msppt_content'] ) {
			$text = $this->parseMSOPpt();
		} else {
			$text = '';
		}
		return $text;
	}

	/**
	 * Checks if a mime type belongs to a certain mime group (text, richtext etc..)
	 *
	 * @param string $type
	 * @return bool
	 */
	function isThis($type = 'text') {
		return (isset(self::$mimes[$type]) && in_array($this->post->post_mime_type, self::$mimes[$type]));
	}

	function isThisParseable() {
		return $this->isThis('text') || $this->isThis('richtext') || $this->isThis('pdf') ||
			$this->isThis('mso_word') || $this->isThis('mso_excel') || $this->isThis('mso_powerpoint');
	}

	function isThisParseEnabled() {
		return
			( $this->isThis('text') && $this->args['text_content'] ) ||
			( $this->isThis('richtext') && $this->args['richtext_content'] ) ||
			( $this->isThis('pdf') && $this->args['pdf_content'] ) ||
			( $this->isThis('mso_word') && $this->args['msword_content'] ) ||
			( $this->isThis('mso_excel') && $this->args['msexcel_content'] ) ||
			( $this->isThis('mso_powerpoint') && $this->args['msppt_content'] );
	}

	/**
	 * Gets contents from a text based file
	 * @return string
	 */
	function parseTXT() {
		$contents = FileManager::_o()->read($this->filepath);
		// CSV files often store the values in quotes. We don't need those in this case.
		if ( $this->post->post_mime_type == 'text/csv' ) {
			$contents = str_replace(array('"', "'"), ' ', $contents);
		}

		return $contents;
	}

	/**
	 * Gets contents from a richtext file
	 *
	 * @return string
	 */
	function parseRTF() {
		$rtf = FileManager::_o()->read($this->filepath);
		if ($rtf != '') {
			include_once(ASP_EXTERNALS_PATH . 'class.rtf-html-php.php');
			$reader = new \ASP_RtfReader();
			$reader->Parse($rtf);
			$formatter = new \ASP_RtfHtml();

			return html_entity_decode(strip_tags($formatter->Format($reader->root)));
		}

		return '';
	}

	/**
	 * Gets contents from a PDF file
	 *
	 * @return string
	 */
	function parsePDF() {
		$args = $this->args;
		$contents = '';

		// PDF Parser for php 5.3 and above
		if ($args['pdf_parser'] == 'auto' || $args['pdf_parser'] == 'smalot') {
			if ( version_compare(PHP_VERSION, '5.3', '>=') ) {
				include_once(ASP_EXTERNALS_PATH . 'class.pdfsmalot.php');
				$parser = new \ASP_PDFSmalot();
				$parser = $parser->getObj();
				try {
					$pdf = $parser->parseFile($this->filepath);
					$contents = $pdf->getText();
				} catch (Exception $e) {
					echo 'Caught exception: ',  $e->getMessage(), "\n";
				}
			}
		}

		// Different method maybe?
		if ($args['pdf_parser'] == 'auto' || $args['pdf_parser'] == 'pdf2txt') {
			if ( $contents == '' ) {
				include_once(ASP_EXTERNALS_PATH . 'class.pdf2txt.php');

				$pdfParser = new \ASP_PDF2Text();
				$pdfParser->setFilename($this->filepath);
				$pdfParser->decodePDF();
				$contents = $pdfParser->output();
			}
		}

		return $contents;
	}

	/**
	 * Gets contents from an Office Word file
	 *
	 * @return string
	 */
	function parseMSOWord() {
		if ( false !== strpos( $this->post->post_mime_type, 'opendocument' ) ) {
			$o = $this->getFileFromArchive('content.xml', $this->filepath);
		} else {
			$o = $this->getFileFromArchive('word/document.xml', $this->filepath);
		}
		return $o;
	}

	/**
	 * Gets contents from an Office Excel file
	 *
	 * @return string
	 */
	function parseMSOExcel() {
		if ( false !== strpos($this->post->post_mime_type, 'opendocument') ) {
			$o = $this->getFileFromArchive('content.xml', $this->filepath);
		} else if ( substr_compare($this->filepath, '.xls', -strlen('.xls')) === 0 ) {
			$o = '';
			include_once(ASP_EXTERNALS_PATH . 'php-excel/autoload.php');
			$reader = \Asan\PHPExcel\Excel::load($this->filepath, function(\Asan\PHPExcel\Reader\Xls $reader) use ($o) {
				$reader->ignoreEmptyRow(true);
			});
			foreach ( $reader->sheets() as $shk => $sheet ) {
				$reader->setSheetIndex($shk);
				foreach ($reader as $row) {
					$o .= ' ' . Str::anyToString($row);
				}
			}
		} else {
			$o = $this->getFileFromArchive('xl/sharedStrings.xml', $this->filepath);
		}
		return $o;
	}

	/**
	 * Gets contents from an Office Powerpoint file
	 *
	 * @return string
	 */
	function parseMSOPpt() {
		$out = '';
		if ( class_exists( '\\ZipArchive' ) ) {
			if ( false !== strpos($this->post->post_mime_type, 'opendocument') ) {
				$out = $this->getFileFromArchive('content.xml', $this->filepath);
			} else {
				$zip = new \ZipArchive();
				if ( true === $zip->open($this->filepath) ) {
					$slide_num = 1;
					while (false !== ($xml_index = $zip->locateName('ppt/slides/slide' . absint($slide_num) . '.xml'))) {
						$xml = $zip->getFromIndex($xml_index);
						$out .= ' ' . $this->getXMLContent($xml);
						$slide_num++;
					}
					$zip->close();
				} else if ( substr_compare($this->filepath, '.ppt', -strlen('.ppt')) === 0 ) {
					// This approach uses detection of the string "chr(0f).Hex_value.chr(0x00).chr(0x00).chr(0x00)" to find text strings, which are then terminated by another NUL chr(0x00). [1] Get text between delimiters [2]
					$fileHandle = fopen($this->filepath, "r");
					$line = @fread($fileHandle, filesize($this->filepath));
					$lines = explode(chr(0x0f),$line);
					$outtext = '';

					foreach($lines as $thisline) {
						if (strpos($thisline, chr(0x00).chr(0x00).chr(0x00)) == 1 ) {
							$text_line = substr($thisline, 4);
							$end_pos   = strpos($text_line, chr(0x00));
							$text_line = substr($text_line, 0, $end_pos);
							$text_line = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$text_line);
							if (strlen($text_line) > 1) {
								$outtext.= substr($text_line, 0, $end_pos)."\n";
							}
						}
					}
					$out = $outtext;
				}
			}
		}

		return $out;
	}

	/**
	 * Gets the content from an XML string
	 *
	 * @param $xml
	 * @return string
	 */
	private function getXMLContent($xml) {
		if ( class_exists('\\DOMDocument') ) {
			$dom = new \DOMDocument();
			$dom->loadXML($xml, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
			return $dom->saveXML();
		}
		return '';
	}

	/**
	 * Gets a file from an archive, based on the xml file name
	 *
	 * @param $xml
	 * @param $filename
	 * @return string
	 */
	private function getFileFromArchive($xml, $filename) {
		if ( class_exists('\\ZipArchive') && class_exists('\\DOMDocument') ) {
			$output_text = '';
			$zip = new \ZipArchive();

			if (true === $zip->open($filename)) {
				if (false !== ($xml_index = $zip->locateName($xml))) {
					$xml_data = $zip->getFromIndex($xml_index);
					$dom = new \DOMDocument();
					$dom->loadXML( $xml_data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING );
					$output_text = $dom->saveXML();
				}
				$zip->close();
			} else {
				// File open error
				return '';
			}

			return $output_text;
		}

		// The ZipArchive class is missing
		return '';
	}

}