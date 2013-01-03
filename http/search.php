<?php
include '../include/include.php';
include '../config.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$offset = isset($_GET['offset']) ? intval( $_GET['offset'] ) : 0;
$limit = isset($_GET['limit']) ? min( intval( $_GET['limit'] ), 100 ) : 100;

$authorities = array();
if( $search != '' ) {
	$storage = new Storage();
	$authorities = $storage->search( $search, $offset, $limit );
}
$data = array();
foreach( $authorities as $authority ) {
	$data[] = $authority->toArray();
}
$action = 'search';
include '../templates/search.php';
