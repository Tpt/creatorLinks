<?php
class Authority {
	public $name = '';
	public $birthYear;
	public $deathYear;
	public $id = -1;
	public $links = array();

	public function mergeWith( $auth ) {
		if( $this->birthYear == null ) {
			$this->birthYear = $auth->birthYear;
		}
		if( $this->deathYear == null ) {
			$this->deathYear = $auth->deathYear;
		}
		$this->links = array_merge( $auth->links, $this->links );
	}

	public function toArray() {
		$array = array(
			'id' => $this->id,
			'name' => $this->name,
			'birthYear' => $this->birthYear,
			'deathYear' => $this->deathYear,
			'links' => array()
		);
		foreach( $this->links as $site => $title ) {
			$uri = self::getUriFromLink( $site, $title );
			if( $uri != '' ) {
				$array['links'][$site] = array(
					'site' => $site,
					'title' => $title,
					'uri' => $uri
				);
			}
		}
		return $array;
	}

	public static function getUriFromLink( $site, $title ) {
		if( preg_match( '/^(.{2,})wiki$/', $site, $m ) ) {
			return 'http://' . str_replace( '_', '-', $m[1] ) . '.wikipedia.org/wiki/' . rawurlencode( str_replace( ' ', '_', $title ) );
		}
		if( preg_match( '/^(.{2,})source$/', $site, $m ) ) {
			return 'http://' . str_replace( '_', '-', $m[1] ) . '.wikisource.org/wiki/' . rawurlencode( str_replace( ' ', '_', $title ) );
		}
		if( preg_match( '/^(.{2,})quote$/', $site, $m ) ) {
			return 'http://' . str_replace( '_', '-', $m[1] ) . '.wikiquote.org/wiki/' . rawurlencode( str_replace( ' ', '_', $title ) );
		}
		switch( $site ) {
			case 'commons':
				return 'http://commons.wikimedia.org/wiki/' . rawurlencode( str_replace( ' ', '_', $title ) );
			case 'wikidata':
				return 'http://wikidata.org/wiki/' . $title;
			case 'viaf':
				return 'http://viaf.org/viaf/' . $title;
			case 'lccn':
				$parts = explode( '/', $title, 3 );
				if( count( $parts ) != 3 ) {
					return '';
				} else {
					return 'http://id.loc.gov/authorities/names/' . $parts[0] . $parts[1] . str_pad($parts[2], 6, '0', STR_PAD_LEFT);
				}
			case 'gnd':
				return 'http://d-nb.info/gnd/' . $title;
			case 'arc':
				return 'http://arcweb.archives.gov/arc/action/ExternalPersonIdSearch?id=' . $title;
			case 'bnf':
				return 'http://catalogue.bnf.fr/ark:/12148/' . $title;
			case 'nla':
				return 'http://nla.gov.au/anbd.aut-an' . $title;
			case 'ulan':
				return 'http://www.getty.edu/vow/ULANFullDisplay?find=&role=&nation=&subjectid=' . $title;
			case 'selibr':
				return 'http://libris.kb.se/auth/' . $title;
			case 'isni':
				return 'urn:isni:' . $title;
		}
		return '';
	}
}
