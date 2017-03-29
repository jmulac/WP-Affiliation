<?php

namespace affiliatelinkupd\Adapter;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

interface UrlAdapterInterface
{	
	public function getAll($orderby = null, $index = 0, $limit = 0, $search = null);
	public function update($id, $data);
	public function updateItems($data);
	public function insert($data);
	public function delete($id);
}