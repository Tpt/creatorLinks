<?php include 'header.php';

function outputList( $links, $siteLabel ) {
	echo '<table class="table table-striped">
		<thead>
			<tr>
				<th scope="cols">' . $siteLabel . '</th>
				<th scope="cols">Title</th>
			</tr>
		</thead>
		<tbody>';
	foreach( $links as $link ) {
		echo '<tr><th scope="rows">' . str_replace( '_', '-', htmlspecialchars( $link['label'] ) ) . '</th><td><a itemprop="url" href="' . $link['uri'] . '">' . htmlspecialchars( $link['title'] ) . '</a></td>' . "\n";
	}
	echo '</tbody></table>';
}

$wikipedia = array();
$wikitsource = array();
$wikiquote = array();
$other = array();

foreach( $data['links'] as $link ) {
	if( preg_match( '/^(.*)wiki$/', $link['site'], $m ) ) {
		$link['label'] = $m[1];
		$wikipedia[$m[1]] = $link;
	} elseif( preg_match( '/^(.*)source$/', $link['site'], $m ) ) {
		$link['label'] = $m[1];
		$wikisource[$m[1]] = $link;
	} elseif( preg_match( '/^(.*)quote$/', $link['site'], $m ) ) {
		$link['label'] = $m[1];
		$wikiquote[$m[1]] = $link;
	} elseif( $link['site'] == 'isni' ) {
		$isni = $link['title'];
	} else {
		$link['label'] = $link['site'];
		$other[$link['site']] = $link;
	}
}

?>
<article itemscope="itemscope" itemtype="http://schema.org/Person">
	<hgroup style="text-align:center;">
	<h2 itemprop="name"><?php echo $data['name'] ?></h2>
	<?php if( $data['birthYear'] !== null || $data['deathYear'] !== null ) {
		echo '<h4>(';
		if( $data['birthYear'] !== null ) {
			echo '<time itemprop="birthDate" datetime="' . $data['birthYear'] . '">' . $data['birthYear'] . '</time>';
		}
		echo '-';
		if( $data['deathYear'] !== null ) {
			echo '<time itemprop="deathDate" datetime="' . $data['deathYear'] . '">' . $data['deathYear'] . '</time>';
		}
		echo ')</h4>';
	}
	if( isset( $isni ) ) {
		echo '<h4>ISNI ' . $isni . '</h4>';
	} ?>
	</hgroup>
<?php
if( !empty( $wikipedia ) ) {
	echo '<h3>Wikipedia</h3>';
	outputList( $wikipedia, 'Language code' );
}
if( !empty( $wikisource ) ) {
	echo '<h3>Wikisource</h3>';
	outputList( $wikisource, 'Language code' );
}
if( !empty( $wikiquote ) ) {
	echo '<h3>Wikiquote</h3>';
	outputList( $wikiquote, 'Language code' );
}
if( !empty( $other ) ) {
	echo '<h3>Other Websites</h3>';
	outputList( $other, 'Site' );
}
?>
</article>
<?php include 'footer.php';
exit(); ?>
