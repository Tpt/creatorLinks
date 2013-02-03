<?php
include '../include/include.php';
include '../config.php';

$id = isset($_GET['id']) ? intval( $_GET['id'] ) : 0;
$site = isset($_GET['site']) ? htmlspecialchars( rawurldecode( $_GET['site'] ) ) : '';
$title = isset($_GET['title']) ? str_replace( '_', ' ', htmlspecialchars( rawurldecode( $_GET['title'] ) ) ) : ''; //TODO
$format = isset($_GET['format']) ? htmlspecialchars( $_GET['format'] ) : 'html';

if( $id == 0 ) {
	if( $site == '' || $title == '' ) {
		$action = 'home';
		include '../templates/home.php';
	} else {
		$storage = new Storage();
		$authority = $storage->getAuthorityFromLink( $site, $title );
		if( $authority == null ) {
			header( 'HTTP/1.1 404 Not Found' );
			$error = 'Page not found for title ' . $title . ' of site ' . $site;
			$action = 'error';
			include '../templates/home.php';
		} else {
			header( 'HTTP/1.1 303 See Other' );
			$url = $basePath . '/index.php?id=' . $authority->id . '&format=' . $format;
			if( isset( $_GET['callback'] ) ) {
				$url .= '&callback=' . $_GET['callback'];
			}
			header( 'Location: ' . $url );
		}
	}
} else {
	$storage = new Storage();
	$authority = $storage->getAuthorityFromId( $id );
	if( $authority == null ) {
		header( 'HTTP/1.1 404 Not Found' );
		$error = 'Page not found for id ' . $id;
		$action = 'error';
		include '../templates/home.php';
		exit();
	}
	$harvester = new BaseHarvester( $storage );
	$harvester->update( $authority );

	$data = $authority->toArray();
	switch( $format ) {
		case 'json':
			header( 'Content-Type: application/json; charset=utf-8' );
			header( 'access-control-allow-origin: *' );
			if( isset( $_GET['callback'] ) ) {
				echo $_GET['callback'] . '(' . json_encode( $data ) . ')';
			} else {
				echo json_encode( $data );
			}
			break;
		case 'php':
			header( 'Content-Type: application/vnd.php.serialized; charset=utf-8' );
			echo serialize( $data );
			break;
		case 'html':
		default:
			header( 'Content-Type: text/html; charset=utf-8' );
			$action = 'view';
			include '../templates/view.php';
			break;
	}
}

