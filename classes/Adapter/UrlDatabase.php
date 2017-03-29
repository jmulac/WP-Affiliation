<?php 

namespace affiliatelinkupd\Adapter;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class UrlDatabase implements UrlAdapterInterface
{
	private static $table_name = "affiliate_url";
	
	public $count_no_limit;
	
	public static function install()
	{
		global $wpdb;
		
		$table_name = $wpdb->prefix . self::$table_name;
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE $table_name (
		id int(9) NOT NULL AUTO_INCREMENT,
		url text NOT NULL,
		url_output text DEFAULT '' NOT NULL,
		state  smallint(2) NOT NULL,
		date_add datetime NULL DEFAULT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		add_option("alu_db_version", "1.0");
	}
	
	public static function uninstall()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table_name;
		
		$sql = "DROP TABLE $table_name;";
		$wpdb->query($sql);
		
		delete_option("alu_db_version");
	}
	
	public function getAll($orderby = null, $index = 0, $limit = 0, $search = null)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table_name;
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM $table_name";
		
		if (!empty($search))
			$sql .= " WHERE url like '%$search%'";
		
		if (!empty($orderby))
			$sql .= " ORDER BY $orderby";
		
		if ($limit > 0)
			$sql .= " LIMIT $index, $limit";
		
		$data = $wpdb->get_results($sql, ARRAY_A);
		
		$this->generate_count_no_limit();
		
		return $data;
	}
	
	protected function generate_count_no_limit()
	{
		global $wpdb;
		
		// Get total results number
		$sql = ' SELECT FOUND_ROWS() AS Count ';
		
		$data = $wpdb->get_row($sql, ARRAY_A);
		$this->count_no_limit = $data['Count'];
	}
	
	public function update($id, $data)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table_name;
		
		$updated = $wpdb->update( $table_name, $data, array('id' => $id) );
		return $updated !== false;
	}
	
	public function updateItems($data)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table_name;
		
		foreach ($data as $d)
		{
			$updated = $wpdb->update( $table_name, $d, array('id' => $d['id']) );
		}
	}
	
	public function insert($data)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table_name;
		
		$sql_data = array(
			'url' => $data['url'],
			'url_output' => isset($data['url_output'])? $data['url_output']: "",
			'date_add' => date('Y-m-d H:i:s'),
			'state' => $data['state'],
		);
		
		$inserted = $wpdb->insert($table_name, $sql_data, array('%s', '%s', '%s', '%d'));
		return $inserted !== false;
	}
	
	public function delete($id)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . self::$table_name;
		return $wpdb->delete($table_name, array('id' => (int)$id));
	}
}
