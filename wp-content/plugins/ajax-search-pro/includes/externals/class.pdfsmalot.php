<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("ASP_PDFSmalot")) {
    class ASP_PDFSmalot {
        function __construct() {
            include_once( ASP_EXTERNALS_PATH . '/pdf-smalot/autoload.php' );
        }
        public function getObj() {
            return new \Smalot\PdfParser\Parser();
        }
    }
}