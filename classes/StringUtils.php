<?php

namespace affiliatelinkupd;

class StringUtils
{
	public static function cut_string($str, $n, $delim='...')
	{
		// 1 : decode the string (especially for Greece)
		$str = trim(html_entity_decode($str, ENT_QUOTES, 'UTF-8'));
	
		// 2 : cut the string if too long
		$len = mb_strlen($str, 'UTF-8');
		if ($len > $n) {
			return substr($str, 0, $n) . $delim;
		} else {
			return $str;
		}
	}
}
