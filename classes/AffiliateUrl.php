<?php 

namespace affiliatelinkupd;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AffiliateUrl
{
	protected static $_ERROR_STATE = 0;
	protected static $_VALID_STATE = 1;
	
	public $url;
	public $convertedUrl;
	
	public $state;
	
	public function __construct($url)
	{
		$this->url = $url;
	}
	
	public function convert()
	{
		// TODO : find a secure solution
		$domain = \affiliatelinkupd\UrlUtils::get_domain_from_url($this->url);
		
		$url = $this->url;
		if (!\affiliatelinkupd\UrlUtils::hasProtocol($url)) {
			$url = 'http://' . $url;
		}
		
		// var_dump(urldecode("http://clic.reussissonsensemble.fr/click.asp?ref=720439&site=14485&type=text&tnb=3&diurl=http%3A%2F%2Feultech.fnac.com%2Fdynclick%2Ffnac%2F%3Feseg-name%3DaffilieID%26eseg-item%3D%24ref%24%26eaf-publisher%3DAFFILINET%26eaf-name%3DGenerateur_liens%26eaf-creative%3D%24affmt%24%26eaf-creativetype%3D%24affmn%24%26eurl%3Dhttp%253A%252F%252Fwww.fnac.com%252F%253FOrigin%253Daffilinet%2524ref%2524"));
		
		var_dump($url, $domain);
		
		$this->state = self::$_VALID_STATE;
		switch ($domain)
		{
			case 'www.fnac.com': case 'fnac.com':
				$new_url = \affiliatelinkupd\UrlUtils::add_params_to_url($url, array('Origin' => 'affilinet720439', 'ectrans' => 1));
				break;
			case 'www.grosbill.com': case 'grosbill.com':
				// https://www.grosbill.com/?utm_source=affilinet&utm_medium=cpa&utm_campaign=grosbill-moteurliens&ectrans=1#siteaffilinet
				// TODO : add #
				$new_url = \affiliatelinkupd\UrlUtils::add_params_to_url($url, array('utm_source' => 'affilinet', 'utm_medium' => 'cpa', 'utm_campaign' => 'grosbill-moteurliens', 'ectrans' => 1));
				break;
			default:
				$this->state = self::$_ERROR_STATE;
				$new_url = $url;
				break;
		}
		var_dump($new_url);
		
		$this->saveConvertion($new_url);
		
		return $new_url;
	}
	
	protected function saveConvertion($new_url)
	{
		//if ($this->isError())
		{
			// TODO : save in DB
		}
	}
	
	public function isLoaded()
	{
		return !empty($this->url);
	}
	
	public function isValid()
	{
		return self::stateIsValid($this->state);
	}
	
	public function isError()
	{
		return self::stateIsError($this->state);
	}
	
	public function getAdminHTMLForm()
	{
		$fields = array(
			'url' => array('label' => "Url", 'value' => !empty($this->url)? $this->url: ""),
		);
		
		$html = "";
		$html .= "<h2>Test URL</h2>";
		
		$html .= '<form method="post">';
		$html .= wp_nonce_field('affiliate-form', 'secu');
		
		$html .= '<table class="form-table"><tbody>';
		
		foreach ($fields as $id => $field)
		{
			$html .= '<tr>
				<th><label for="'.$id.'">'.$field['label'].'</label></th>
				<td><input type="text" name="'.$id.'" id="'.$id.'" value="'.$field['value'].'"></td>
			</tr>';
		}
		
		// Submit button
		$html .= '<tr><th><input type="submit" value="Test" class="button" /></th></tr>';
		
		$html .= '</tbody></table></form>';

		return $html;
	}
	
	public static function stateIsValid($state)
	{
		return $state == self::$_VALID_STATE;
	}
	
	public static function stateIsError($state)
	{
		return $state == self::$_ERROR_STATE;
	}
}
