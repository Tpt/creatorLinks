<?php
include '../include/include.php';
include '../config.php';

$id = isset($_GET['id']) ? intval( $_GET['id'] ) : 0;
$format = isset($_GET['format']) ? $_GET['format'] : 'json';

header( 'Content-Type: application/json; charset=utf-8' );
header( 'access-control-allow-origin: *' );
$data = array(
	'id' => $id
);

if( $id == 0 ) {
	header( 'HTTP/1.1 404 Not Found' );
	$data['error'] = 'Invalid Id.';
} else {
	$authority = new Authority;
	$authority->links['viaf'] = $id;
	$storage = new Storage();
	$harvester = new BaseHarvester( $storage );
	$authority = $harvester->updateFromViaf( $authority );
	if( $authority == null ) {
		header( 'HTTP/1.1 404 Not Found' );
		$data['error'] = 'Not found';
	} else {
		$data['id'] = $id;
		$data['links'] = $authority->links;
	}
}

switch( $format ) {
	case 'php':
		header( 'Content-Type: application/vnd.php.serialized; charset=utf-8' );
		echo serialize( $data );
		break;
	case 'json':
	default:
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'access-control-allow-origin: *' );
		if( isset( $_GET['callback'] ) ) {
			echo $_GET['callback'] . '(' . json_encode( $data ) . ')';
		} else {
			echo json_encode( $data );
		}
		break;
}

