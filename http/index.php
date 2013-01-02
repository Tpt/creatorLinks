<?php
include '../include/include.php';
include '../config.php';

$id = isset($_GET['id']) ? intval( $_GET['id'] ) : 0;
$site = isset($_GET['site']) ? rawurldecode( $_GET['site'] ) : '';
$title = isset($_GET['title']) ? str_replace( '_', ' ', rawurldecode( $_GET['title'] ) ) : ''; //TODO
$format = isset($_GET['format']) ? $_GET['format'] : 'html';

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
			header( 'Location: ' . $basePath . '/index.php?id=' . $authority->id . '&format=' . $format );
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
	$updated = false;
	if( ( isset( $authority->links['viaf'] ) || isset( $authority->links['enwiki'] ) ) && !isset( $authority->links['isni'] ) ) {
		$authority = $harvester->updateFromViaf( $authority );
		$updated = true;
	}
	if( ( isset( $authority->links['enwiki'] ) || isset( $authority->links['dewiki'] ) || isset( $authority->links['frwiki'] ) ) && !isset( $authority->links['wikidata'] ) ) {
		$authority = reset( $harvester->updateWithWikidata( array( $authority ) ) );
		$updated = true;
	}
	if( $updated ) {
		$storage->saveAuthority( $authority );
	}

	$data = $authority->toArray();
	switch( $format ) {
		case 'json':
			header( 'Content-Type: application/json; charset=utf-8' );
			echo json_encode( $data );
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

