<?php 

namespace affiliatelinkupd;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AffiliateUrl
{
	protected static $_ERROR_STATE = 0;
	protected static $_VALID_STATE = 1;
	
	private $adapter;
	
	public $url;
	public $convertedUrl;
	
	public $state;
	
	public function __construct($url, Adapter\UrlAdapterInterface $adapter)
	{
		$this->adapter = $adapter;
		$this->url = $url;
	}
	
	public function convert()
	{
		$url = $this->url;
		if (!\affiliatelinkupd\UrlUtils::hasProtocol($url)) {
			$url = 'http://' . $url;
		}
		
		$clean_url = $url;
		if (($pos = strpos($clean_url, '#')) !== false) {
			$clean_url = substr($clean_url, 0, $pos);
		}
		
		// TODO : find a secure solution
		$domain = \affiliatelinkupd\UrlUtils::get_domain_from_url($clean_url);
var_dump($domain);

		$this->state = self::$_VALID_STATE;
		switch ($domain)
		{
			case 'www.fnac.com': case 'fnac.com':
				$base_url = "http://clic.reussissonsensemble.fr/click.asp?ref=720439&site=14485&type=text&tnb=3&diurl=http%3A%2F%2Feultech.fnac.com%2Fdynclick%2Ffnac%2F%3Feseg-name%3DaffilieID%26eseg-item%3D%24ref%24%26eaf-publisher%3DAFFILINET%26eaf-name%3DGenerateur_liens%26eaf-creative%3D%24affmt%24%26eaf-creativetype%3D%24affmn%24%26eurl%3D";
				$url = \affiliatelinkupd\UrlUtils::add_params_to_url($clean_url, array('Origin' => 'affilinet720439'/*, 'ectrans' => 1*/));
				$new_url = $base_url . urlencode($url);
				break;
			case 'www.grosbill.com': case 'grosbill.com':
				$base_url = "http://clic.reussissonsensemble.fr/click.asp?ref=720439&site=6387&type=text&tnb=30&diurl=http%3A%2F%2Feulerian.grosbill.com%2Fdynclick%2Fgrosbill%2F%3Feaf-publisher%3Daffilinet%26eaf-name%3Dgrosbill-logo%26eaf-creative%3D120x60%26eaf-creativetype%3D%24ref%24%26eurl%3D";
				$url = \affiliatelinkupd\UrlUtils::add_params_to_url($clean_url, array('utm_source' => 'affilinet', 'utm_medium' => 'cpa', 'utm_campaign' => 'grosbill-moteurliens'/*, 'ectrans' => 1*/));
				$new_url = $base_url . urlencode($url) . '#siteaffilinet';
				break;
			case 'www.cdiscount.com': case 'cdiscount.com':
				$new_url = "https://ad.zanox.com/ppc/?36429178C54756398&ulp=[[".urlencode($clean_url)."?refer=zanoxpb&cid=affil&cm_mmc=zanoxpb-_-userid]]";
				break;
			case 'www.amazon.fr': case 'amazon.fr':
				// Link ID ?
				$base_url = "https://www.amazon.fr//ref=as_li_ss_tl?ie=UTF8&linkCode=ll2&tag=choc0e-21&linkId=e975064ea5a3cefd29ccded6f0363778";
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
		$data = array(
			'url' => $this->url,
			'state' => $this->state,
			'url_output' => $new_url,
		);
		
		return $this->adapter->insert($data);
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
