<?php

namespace affiliatelinkupd;

class UrlUtils
{
	/**
	 * Add param to URL
	 * @param string $url
	 * @param array $params
	 * @return string
	 */
	public static function add_params_to_url($url, $params)
	{
		if (empty($url))
			return false;

		if (empty($params))
			return $url;
		
		$pos = strpos($url, '?');
		if ($pos)
		{
			foreach ($params as $param_name => $param_value)
				$url .= "&$param_name=$param_value";
		} elseif (!empty($params)) {
			$url .= '?';
			$i = 0;
			foreach ($params as $param_name => $param_value)
			{
				if ($i > 0)
					$url .= "&";
				
				$url .= "$param_name=$param_value";
				$i++;
			}
		}
		
		return $url;
	}
	
	public static function get_current_url()
	{
		$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
		$protocol = self::strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s;
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
		return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
	}
	
	public static function strleft($s1, $s2)
	{
		return substr($s1, 0, strpos($s1, $s2));
	}
	
	/**
	 * Function to format all url in a text
	 * Search all url in a text, and add the html code <a>
	 * Example : 
	 * Param : toto http://www.toto.com
	 * Return : toto <a href="http://www.toto.com">http://www.toto.com</a>
	 * @param string $text
	 * @return string
	 */
	public static function getUrlInText(&$text, $is_external = false)
	{
		$replace_to = '<a rel="nofollow" href="';
		if ($is_external)
			$replace_to = '<a rel="nofollow external" href="';
		
		$text = preg_replace('$(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)$i', $replace_to . '\\1">\\1</a>', $text);
		
		$text = preg_replace('$(((f|ht){1}tps://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)$i', $replace_to . '\\1">\\1</a>', $text);
		
		$text = preg_replace('$([[:space:]()[{}])?(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)$i', '\\1' . $replace_to . 'http://\\2">\\2</a>', $text);
		
		$text = preg_replace('$([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})$i', $replace_to . 'mailto:\\1">\\1</a>', $text);
	}
	
	
	/**
	 * Check if the url is in the domain
	 * With this function, we can check for example
	 * if a user just comes in our site or not
	 * @param string $url
	 * @param string $domain
	 * @return boolean
	 */
	public static function checkDomain($url, $domain){
		return (parse_url($url, PHP_URL_HOST) == $domain);
	}
	
	public static function hasProtocol($url){
		$res = self::breakUrl($url);
		return ($res['protocol'] != "");
	}
	
	public static function breakUrl($url){
		$result = array();
		$regex = '#^(.*?//)*([\w\.\-\d]*)(:(\d+))*(/*)(.*)$#';
		$matches = array();
		
		preg_match($regex, $url, $matches);
		
		$result['protocol'] = $matches[1];
		$result['port'] = $matches[4];
		$result['site'] = $matches[2];
		$result['resource'] = $matches[6];
		
		$result['site'] = preg_replace('#/$#', '', $result['site']);
		
		$result['protocol'] = preg_replace('#://$#', '', $result['protocol']);
		
		return $result;
	}
	
	/**
	 * Get a domain from an url.
	 * @param string $url
	 * @return string domain
	 */
	public static function get_domain_from_url($url)
	{
		preg_match('/^(http:\/\/|https:\/\/|ftp:\/\/)?(www\.)?([^\/^\?^:]+)/i', $url, $matches);
		$strBaseUrl  = '';
		
		if($matches)
		{
			$strBaseUrl = $matches[3];
		}

		if(!$strBaseUrl)
		{
			$strBaseUrl = $url;
		}

		$matches = preg_split('/\.+/',$strBaseUrl);
		$n = count($matches);

		if($matches[$n-2] == "com")
		{
			$strBaseUrl = $matches[$n-3].".".$matches[$n-2].".".$matches[$n-1];
		}
		else
		{
			$extension = $matches[$n-2].".".$matches[$n-1];

			switch($extension)
			{
				case "co.uk":
					$strBaseUrl = $matches[$n-3].".".$matches[$n-2].".".$matches[$n-1];
					break;
				case "org.uk":
					$strBaseUrl = $matches[$n-3].".".$matches[$n-2].".".$matches[$n-1];
					break;
				default:
					$strBaseUrl = $matches[$n-2].".".$matches[$n-1];
					break;
			}
		}
		return $strBaseUrl;
	}
	
	public static function get_response_code($url)
	{
		if (!($url = @parse_url($url))) 
			return false;
		
		if (!($fp = fsockopen($url['host'], 80, $errno, $errstr, 30)))
			return false;

		if (isset($url['query'])) 
			$url['path'].= '?' . $url['query'];
	
		fputs($fp, "HEAD " . $url['path'] . " HTTP/1.1\r\nHost: " . $url['host'] . "\r\n\r\n");
		$response_code = preg_replace("/.*(\d{3}).*/", "$1", array(fread($fp, 12)));
		fclose($fp);
		return $response_code[0];
	}
	
	public static function isAlive($url)
	{
		$header = self::get_response_code($url);
		return $header == '200' || $header == '302' || $header == '301';
	}
	
	public static function url_exists($url)
	{
		if(@file_get_contents($url, 0, NULL, 0, 1))
			return true;
		else
			return false;
	} 
}
