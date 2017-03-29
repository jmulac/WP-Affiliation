<?php 
/*
Plugin Name: Affiliate Link Tester
Description: Affiliate Link Tester
Version:     1
Author:      Julien Mulac
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if (!class_exists('AffiliateLinkUpd')):

class AffiliateLinkUpd
{
	// Import
	private static $import_file = "data/serial.csv";
	private static $serial_key = 1;
	
	public function init()
	{
		$this->includes();
	}
	
	private function includes()
	{
		include_once( 'classes/AffiliateUrl.php' );
		include_once( 'classes/UrlTable.php' );
		include_once( 'classes/Adapter/UrlAdapterInterface.php' );
		include_once( 'classes/Adapter/UrlDatabase.php' );
		include_once( 'classes/UrlUtils.php' );
	}
	
	public static function activate()
	{
		// Install SQL Table
		\affiliatelinkupd\Adapter\UrlDatabase::install();
	}
	
	public static function deactivate()
	{
		\affiliatelinkupd\Adapter\UrlDatabase::uninstall();
	}
	
	/**
	 * ADMIN PART
	 */
	
	public static function my_add_menu_items()
	{
		add_menu_page( 'Affiliation', 'Affiliation', 'activate_plugins', 'affiliate_menu', array('AffiliateLinkUpd', 'render_affiliate_home'));
		add_submenu_page('affiliate_menu', 'Affiliate Link Tester', 'Link Tester', 'activate_plugins', 'affiliate_link_tester', array('AffiliateLinkUpd', 'render_affiliate_link_tester'));
		add_submenu_page('affiliate_menu', 'Affiliate URL List', 'URL List', 'activate_plugins', 'affiliate_url_list', array('AffiliateLinkUpd', 'render_affiliate_url_list'));
	}
	
	public static function render_affiliate_home()
	{
		self::render_affiliate_url_list();
	}
	
	public static function render_affiliate_link_tester()
	{
		$url = isset($_GET['url'])? $_GET['url']: null;
		
		self::showAffiliateLinkTesterForm($url);
	}
	
	public static function render_affiliate_url_list()
	{
		// check action : edit / delete / add
		$action = isset($_GET['action'])? $_GET['action']: null;
		$show_list = true;
		$id = isset($_GET['id'])? (int)$_GET['id']: 0;
		
		switch ($action)
		{
			case 'export':
				self::exportFile();
				$show_list = false;
				break;
		}
		
		if ($show_list)
			self::showURLList();
	}
	
	public static function showURLList()
	{
		$myListTable = new \affiliatelinkupd\UrlTable();
		echo '<div class="wrap"><h2>Affiliate URL List<small> - <a href="'.admin_url( 'admin.php?page=affiliate_url_list&action=export' ).'">Export Data</a></small></h2>';
		$myListTable->prepare_items();
		echo '<form method="post">
		<input type="hidden" name="page" value="affiliate_url_list" />';
		$myListTable->search_box('Recherche', 'search_id');
		echo '</form>';
		$myListTable->display();
		echo '</div>';
	}
	
	public static function showAffiliateLinkTesterForm($url = "")
	{	
		$adapter = new \affiliatelinkupd\Adapter\UrlDatabase();
		$url = new \affiliatelinkupd\AffiliateUrl($url, $adapter);
	
		// if this fails, check_admin_referer() will automatically print a "failed" page and die.
		$new_url = null;
		if ( ! empty( $_POST['url'] ) && check_admin_referer('affiliate-form', 'secu' ))
		{
			$url->url = $_POST['url'];
			$new_url = $url->convert();
			
			if (!empty($new_url))
			{
				echo '<div class="updated"><p>New URL : <a target="_blank" href="'.$new_url.'">'.$new_url.'</a></p></div>';
			}
		}
		
		echo $url->getAdminHTMLForm();
	}
}

endif;

register_activation_hook( __FILE__, array('AffiliateLinkUpd', 'activate'));
register_deactivation_hook( __FILE__, array('AffiliateLinkUpd', 'deactivate'));

add_action( 'admin_menu', array('AffiliateLinkUpd', 'my_add_menu_items') );

$class = new AffiliateLinkUpd();
$class->init();
