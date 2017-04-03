<?php

namespace affiliatelinkupd;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class UrlTable extends \WP_List_Table
{
	private static $items_per_page = 25;
	
	public function get_columns()
	{
  		$columns = array(
    		'url' 			=> 'URL',
    		'url_output'	=> 'Output URL',
  			'state' 		=> 'State',
			'post_id' 			=> 'Post',
  			'date_add'		=> 'Date Add',
  		);
  		
  		return $columns;
	}
	
	public function prepare_items()
	{
		$columns = $this->get_columns();
		
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		$adapter = new Adapter\UrlDatabase();
		
		// If no sort, default to title
		$orderby = !empty($_GET['orderby'])? $_GET['orderby'] : 'url';
		// If no order, default to asc
		$order = !empty($_GET['order'])? $_GET['order'] : 'asc';
		
		$orderby .= ' ' . $order;

		$current_page = $this->get_pagenum();
		$index = ($current_page - 1) * self::$items_per_page;
		
		$search = isset($_POST['s'])? trim($_POST['s']): null;
		
		$this->items = $adapter->getAll($orderby, $index, self::$items_per_page, $search);

		$total_items = $adapter->count_no_limit;
		
		$this->set_pagination_args( array(
			'total_items' => $adapter->count_no_limit,
			'per_page'    => self::$items_per_page
		) );
	}
	
	public function column_default($item, $column_name)
	{
		if (isset($item[$column_name]))
			return $item[$column_name];
		else
			return "" ; // print_r( $item, true ) ; // Show the whole array for troubleshooting purposes
	}
	
	public function get_sortable_columns()
	{
		$sortable_columns = array(
			'url'  => array('url', true),
			'url_output' => array('url_output',true),
			'state'   => array('state',false),
			'post_id'   => array('post_id',false),
			'date_add'   => array('date_add', false),
		);
		
		return $sortable_columns;
	}
	
	public function column_post_id($item)
	{
		if (isset($item['post_id']) && $item['post_id'] > 0)
			return $item['post_id'];
		else 
			return "- [URL Tester]";
	}
	
	public function column_url($item)
	{
		if (!empty($item['url']))
			return '<a target="_blank" href="'.$item['url'].'">' . $item['url'] . '</a>';
		else 
			return "";
	}
	
	public function column_url_output($item)
	{
		if (!empty($item['url_output']))
			return '<a target="_blank" href="'.$item['url_output'].'" title="'.$item['url_output'].'">' . StringUtils::cut_string($item['url_output'], 150) . '</a>';
		else 
			return "";
	}
	
	public function column_date_add($item)
	{
		if (isset($item['date_add']))
			return date('d M Y H:i:s', strtotime($item['date_add']));
		else 
			return "";
	}
	
	public function column_state($item)
	{
		if (isset($item['state']))
		{
			if (AffiliateUrl::stateIsValid($item['state']))
				return '<strong style="color: Green;">Converted</strong>';
			elseif (AffiliateUrl::stateIsError($item['state']))
				return '<strong style="color: Red;">-</strong>';
		}
		
		return "";
	}
}
