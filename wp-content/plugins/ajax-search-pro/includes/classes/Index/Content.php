<?php
namespace WPDRMS\ASP\Index;

defined('ABSPATH') or die("You can't access this file directly.");

if ( !class_exists(__NAMESPACE__ . '\Content') ) {
	class Content {
		public static function arabicRemoveDiacritics( $str ) {
			if ( is_array($str) ) {
				foreach ($str as &$v) {
					$v = self::arabicRemoveDiacritics($v);
				}
				return $str;
			}

			$characters = array(
				"~[\x{0600}-\x{061F}]~u",
				"~[\x{063B}-\x{063F}]~u",
				"~[\x{064B}-\x{065E}]~u",
				"~[\x{066A}-\x{06FF}]~u",
			);
			return preg_replace($characters, "", $str);
		}
		public static function hebrewUnvocalize( $str ) {
			if ( is_array($str) ) {
				foreach ($str as &$v) {
					$v = self::hebrewUnvocalize($v);
				}
				return $str;
			}
			if ( preg_match("/[\x{0591}-\x{05F4}]/u", $str) ) {
				$hebrew_common_ligatures = array(
					'ײַ' => 'ײ',
					'ﬠ' => 'ע',
					'ﬡ' => 'א',
					'ﬢ' => 'ד',
					'ﬣ' => 'ה',
					'ﬤ' => 'כ',
					'ﬥ' => 'ל',
					'ﬦ' => 'ם',
					'ﬧ' => 'ר',
					'ﬨ' => 'ת',
					'שׁ' => 'ש',
					'שׂ' => 'ש',
					'שּׁ' => 'ש',
					'שּׂ' => 'ש',
					'אַ' => 'א',
					'אָ' => 'א',
					'אּ' => 'א',
					'בּ' => 'ב',
					'גּ' => 'ג',
					'דּ' => 'ד',
					'הּ' => 'ה',
					'וּ' => 'ו',
					'זּ' => 'ז',
					'טּ' => 'ט',
					'יּ' => 'י',
					'ךּ' => 'ך',
					'כּ' => 'כ',
					'לּ' => 'ל',
					'מּ' => 'מ',
					'נּ' => 'נ',
					'סּ' => 'ס',
					'ףּ' => 'ף',
					'פּ' => 'פ',
					'צּ' => 'צ',
					'קּ' => 'ק',
					'רּ' => 'ר',
					'שּ' => 'ש',
					'תּ' => 'ת',
					'וֹ' => 'ו',
					'בֿ' => 'ב',
					'כֿ' => 'כ',
					'פֿ' => 'פ',
					'ﭏ' => 'אל'
				);
				$str = trim(preg_replace('/\p{Mn}/u', '', $str));
				foreach ($hebrew_common_ligatures as $word1 => $word2) {
					$str = trim(str_replace($word1, $word2, $str));
				}
			}
			return $str;
		}
	}
}