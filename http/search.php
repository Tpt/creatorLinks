<?php
include '../include/include.php';
include '../config.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$authorities = array();
if( $search != '' ) {
	$storage = new Storage();
	$authorities = $storage->search( $search );
}
$data = array();
foreach( $authorities as $authority ) {
	$data[] = $authority->toArray();
}
$action = 'search';
include '../templates/search.php';
