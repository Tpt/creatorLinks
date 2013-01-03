<?php include 'header.php';
if( $data != array() ) {
	echo '<div class="pagination"><ul>';
	if( $offset > 0 ) {
		echo '<li><a href="' . $basePath . '/search.php?search=' . $search . '&limit=' . $limit . '&offset=' . max( $offset - $limit, 0 ) . '">Prev</a></li>';
	}
	if( count( $data ) >= $limit ) {
		echo '<li><a href="' . $basePath . '/search.php?search=' . $search . '&limit=' . $limit . '&offset=' . ($offset + count( $data )) . '">Next</a></li>';
	}
	echo '</ul></div>';
	echo '<table class="table table-striped">';
	/*<thead>
		<tr>
			<th scope="cols">Name</th>
			<th scope="cols">Title</th>
			</tr>
		</thead> */
		echo '<tbody>';
		foreach( $data as $auth ) {
		echo '<tr><td scope="rows"><a itemprop="url" href="' . $basePath . '/index.php?id=' . $auth['id'] . '">' . htmlspecialchars( $auth['name'] );
		if( $auth['birthYear'] !== null || $auth['deathYear'] !== null ) {
			echo ' (';
			if( $auth['birthYear'] !== null ) {
				echo '<time datetime="' . $auth['birthYear'] . '">' . $auth['birthYear'] . '</time>';
			}
			echo '-';
			if( $auth['deathYear'] !== null ) {
				echo '<time datetime="' . $auth['deathYear'] . '">' . $auth['deathYear'] . '</time>';
			}
			echo ')';
		}
		echo '</a></td>' . "\n";
	}
	echo '</tbody></table>';
	echo '<div class="pagination"><ul>';
	if( $offset > 0 ) {
		echo '<li><a href="' . $basePath . '/search.php?search=' . $search . '&limit=' . $limit . '&offset=' . max( $offset - $limit, 0 ) . '">Prev</a></li>';
	}
	if( count( $data ) >= $limit ) {
		echo '<li><a href="' . $basePath . '/search.php?search=' . $search . '&limit=' . $limit . '&offset=' . ($offset + count( $data )) . '">Next</a></li>';
	}
	echo '</ul></div>';
}
include 'footer.php';
exit(); ?>
