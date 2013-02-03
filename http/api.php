<?php
include '../include/include.php';
include '../config.php';


$ids = isset($_GET['ids']) ? explode( '|', htmlspecialchars( $_GET['ids'] ) ) : array();
foreach( $ids as $key => $id ) {
	$ids[$key] = intval( $id );
}
$sites = isset($_GET['sites']) ? explode( '|', htmlspecialchars( rawurldecode( $_GET['sites'] ) ) ) : array();
$titles = isset($_GET['titles']) ? explode( '|', htmlspecialchars( rawurldecode( $_GET['titles'] ) ) ) : array(); //TODO
foreach( $titles as $key => $title ) {
	$titles[$key] = str_replace( '_', ' ', $title );
}
$offset = isset($_GET['offset']) ? intval( $_GET['offset'] ) : 0;
$limit = isset($_GET['limit']) ? min( intval( $_GET['limit'] ), 50 ) : 50;
$format = isset($_GET['format']) ? $_GET['format'] : 'json';

$authorities = array();
$storage = new Storage();
$harvester = new BaseHarvester( $storage );
if( $ids == array() ) {
	if( $sites != array() && $title != array() ) {
		foreach( $sites as $i => $site ) {
			if( count( $authorities ) >= 50 ) {
				break;
			}
			if( isset( $titles[$i] ) ) {
				if( $titles[$i] == '*' ) {
					$auths = $storage->getAuthorityWithSite( $site, $offset, $limit - count( $authorities ) );
					foreach( $auths as $auth ) {
						if( !isset( $authorities[$auth->id] ) ) {
							$harvester->update( $auth );
							$authorities[$auth->id] = $auth;
						}
					}
				} else {
					$auth = $storage->getAuthorityFromLink( $site, $title );
					if( $auth != null && !isset( $authorities[$auth->id] ) ) {
						$harvester->update( $auth );
						$authorities[$auth->id] = $auth;
					}
				}
			}
		}
	} else {
		header( 'Content-Type: text/html; charset=utf-8' );
		include '../templates/apihelp.html';
		exit();
	}
} else {
	foreach( $ids as $id ) {
		if( count( $authorities ) >= 50 ) {
			break;
		}
		$auth = $storage->getAuthorityFromId( $id );
		if( $auth != null && !isset( $authorities[$auth->id] ) ) {
			$harvester->update( $auth );
			$authorities[$auth->id] = $auth;
		}
	}
}
$format = isset($_GET['format']) ? $_GET['format'] : 'json';
$data = array( 'entries' => array() );
if( count( $authorities ) >= $limit ) {
	$data['offset'] = count( $authorities ) + $offset;
}
foreach( $authorities as $auth ) {
	$data['entries'][$auth->id] = $auth->toArray();
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

