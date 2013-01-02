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
}
$harvester->work();
