<?php
include './include/include.php';
include './config.php';

switch( $_SERVER['argv'][1] ) {
	case 'commons':
		$harvester = new CreatorHarvester();
		break;
	case 'ensource':
		$harvester = new EnWikisourceHarvester();
		break;
	case 'itsource':
		$harvester = new ItWikisourceHarvester();
		break;
}

$prefix = '';
if( isset( $_SERVER['argv'][2] ) ) {
	$prefix = $_SERVER['argv'][2];
}
$harvester->work( $prefix );
