<?php
class BaseHarvester {
	protected $namespaceId = 0;
	protected $wiki = '';
	protected $api;
	protected $storage;
	protected $interwikiGroup = '';

	public function __construct( $storage = null ) {
		$this->api = new Api( $this->wiki );
		$this->storage = ($storage instanceof Storage) ? $storage : new Storage();
	}

	public function work() {
		$this->scrollPages();
	}

	protected function scrollPages( $prefix = '' ) {
		$params = array( 'list' => 'allpages', 'aplimit' => 500, 'apnamespace' => $this->namespaceId, 'apfilterredir' => 'nonredirects', 'apprefix' => $prefix );
		do {
			$result = $this->api->query($params);
			$continue = $this->api->getContinueParam($result);
			if($continue != null) {
				$params[$continue[0]] = $continue[1];
			}
			$titles = array();
			$i = 0;
			foreach($result['query']['allpages'] as $page) {
				$titles[] = $page['title'];
				$i++;
				if( $i == 50 ) {
					$this->workOnPages( $titles );
					$i = 0;
					$titles = array();
					//exit(); //TODO
				}
			}
			if( $i != 0 ) {
				$this->workOnPages( $titles );
			}
		} while($continue != null);
	}

	protected function workOnPages( $titles ) {
		$pages = $this->getPagesContent( $titles );
		$authorities = array();
		foreach( $pages as $page ) {
			$authorities[$page[0]] = $this->parsePage( $page[0], $page[1] );
		}

		if( $this->interwikiGroup != '' ) {
			$interwiki = $this->getInterwikis( $titles );
			foreach( $interwiki as $title => $links ) {
				if(isset($authorities[$title])) {
					$authorities[$title]->links = array_merge( $links, $authorities[$title]->links );
				}
			}
		}

		$authorities = $this->updateWithWikidata( $authorities );
		$this->save( $authorities );
	}

	protected function parsePage( $title, $content ) {
		$authority = new Authority();
		$authority->name = $this->extractName( $content );
		if( $authority->name == '' ) {
			$parts = explode( ':', $title, 2 );
			if(isset($parts[1])) {
				$authority->name = htmlspecialchars( preg_replace( '/\(.*\)/', '', $parts[1] ) );
			}
		}
		$authority->birthYear = $this->extractYear( 'birth', $content );
		$authority->deathYear = $this->extractYear( 'death', $content );
		$links = $this->validateAuthorityLinks( $this->extractAuthorities( $content ) );
		$authority->links = array_merge( $links, $this->extractWikilinks( $title, $content ) );
		return $authority;
	}

	protected function validateAuthorityLinks( $links ) {
		if(isset($links['viaf']) && !is_numeric($links['viaf'])) {
			unset($links['viaf']);
		}
		if(isset($links['lccn']) && !preg_match('/^[a-z]{1,5}\/[0-9]{2,4}\/[0-9]{1,20}$/', $links['lccn'])) {
			unset($links['lccn']);
		}
		if(isset($links['arc']) && !is_numeric($links['arc'])) {
			unset($links['arc']);
		}
		if(isset($links['bnf']) && !preg_match('/^[0-9a-z]+$/', $links['bnf'])) {
			unset($links['bnf']);
		}
		if(isset($links['nla']) && !is_numeric($links['nla'])) {
			unset($links['nla']);
		}
		if(isset($links['ulan']) && !is_numeric($links['ulan'])) {
			unset($links['ulan']);
		}
		if(isset($links['selibr']) && !is_numeric($links['selibr'])) {
			unset($links['selibr']);
		}
		if(isset($links['isni']) && preg_match('/^[0-9X]+$/', $links['isni'])) {
			unset($links['isni']);
		}
		return $links;
	}

	protected function extractName( $content ) {
		return '';
	}

	protected function extractYear( $type, $content ) {
		return null;
	}

	protected function extractAuthorities( $content ) {
		return array();
	}

	protected function extractWikilinks( $title, $content ) {
		return array();
	}

	protected function getPagesContent( $titles ) {
		$params = array(
			'prop' => 'revisions',
			'titles' => implode( '|', $titles ),
			'rvprop' => 'content'
		);
		$result = $this->api->query( $params );
		$contents = array();
		foreach($result['query']['pages'] as $page) {
			if(isset($page['revisions'][0]['*'])) {
				$contents[] = array( $page['title'], $page['revisions'][0]['*'] );
			}
		}
		return $contents;
	}

	protected function parseLink( $link, $wiki = 'w', $lang = 'en' ) {
		$link = trim( $link, " \t\n:");
		$parts = explode( ':', $link, 3 );
		if( count($parts) == 3 && in_array($parts[0], array( 'w', 's', 'wikipedia', 'wikisource', 'q', 'wikiquote' ) ) ) {
			$wiki = $parts[0];
			$lang = $parts[1];
			$page = $parts[2];
		} elseif( count($parts) == 2 ){
			if( in_array($parts[0], array( 'w', 's', 'wikipedia', 'wikisource', 'q', 'wikiquote', 'commons', 'meta' ) ) ) { //TODO very hacky
				$wiki = $parts[0];
			} else {
				$lang = $parts[0];
			}
			$page = $parts[1];
		} else {
			$page = $parts[0];
		}
		return array($this->getWikiName( $wiki, $lang ), str_replace( '_', ' ', $page ) );
	}

	protected function getWikiName( $wiki, $lang = 'en' ) {
		$lang = str_replace( '-', '_', $lang );
		switch( $wiki ) {
			case 'w':
			case 'wikipedia':
				return $lang . 'wiki';
			case 's':
			case 'wikisource':
				return $lang . 'source';
			case 'q':
			case 'wikiquote':
				return $lang . 'quote';
			case 'commons':
				return 'commons';
			default:
				return null;
		}
	}

	protected function parsePossibleLink( $val, $linkByDefault = false ) {
		$val = trim( $val );
		if(preg_match('/\[\[([^\|]*)(\|(.*))?\]\]/', $val, $m)) {
			return $this->parseLink( trim( $m[1] ) );
		} elseif( $linkByDefault && $val != '' && preg_match( '/^[^\|\{\[]*$/', $val ) ) {
			return $this->parseLink( trim( $val ) );
		}
		return null;
	}

	public function updateWithWikidata( $authorities ) {
		$authoritiesUsed = array();
		$authoritiesNotUsed = array();
		$wikis = array();
		$titles = array();
		foreach($authorities as $authority) {
			$end = false;
			foreach( $authority->links as $wiki => $title ) {
				if( preg_match( '/^[a-z]{2}wiki$/', $wiki ) ) {
					$wikis[] = $wiki;
					$titles[] = $title;
					$authoritiesUsed[$wiki . '-' . $title] = $authority;
//echo $wiki . '-' . $title . "\n";
					$end = true;
					break;
				}
			}
			if( !$end ) {
				$authoritiesNotUsed[] = $authority;
			}
		}
		if( $wikis == array()) {
			return $authorities;
		}

		$api = new Api( 'wikidata.org' );
		$params = array(
			'action' => 'wbgetentities',
			'sites' => implode('|', $wikis),
			'titles' => implode('|', $titles),
			'props' => 'info|sitelinks'
		);

		$result = $api->request( $params );
		foreach( $result['entities'] as $key => $entity ) {
			if( is_numeric($key) && $key < 0 ) {
				continue;
			}
			$found = false;
			foreach($entity['sitelinks'] as $link) {
				if(isset($authoritiesUsed[$link['site'] . '-' . $link['title']])) {
					$auth = $authoritiesUsed[$link['site'] . '-' . $link['title']];
					$auth->links['wikidata'] = $entity['title'];
					foreach($entity['sitelinks'] as $link2) {
						/*if(isset($auth->links[$link2['site']]) && $auth->links[$link2['site']] !== $link2['title']) { //TODO manage errors
							echo 'error in link for' . $auth->name . ' ' . $auth->links[$link2['site']] . ' <-> ' . $link2['title'] . "\n"; */
						$auth->links[$link2['site']] = $link2['title'];
					}
					$authoritiesUsed[$link['site'] . '-' . $link['title']] = $auth;
					$found = true;
					break;
				}
			}
			if(!$found) {
				echo 'error'. $entity['title'] . "\n";
			}
		}

		foreach($authoritiesUsed as $auth) {
			$authoritiesNotUsed[] = $auth;
		}
		return $authoritiesNotUsed;
	}

	protected function getInterwikis( $titles ) {
		$params = array( 'titles' => implode( '|', $titles ), 'prop' => 'langlinks', 'lllimit' => '500' );
		$links = array();
		do {
			$result = $this->api->query($params);
			$continue = $this->api->getContinueParam($result);
			if($continue != null) {
				$params[$continue[0]] = $continue[1];
			}
			$titles = array();
			$i = 0;
			foreach($result['query']['pages'] as $page) {
				if (isset( $page['langlinks'] ) ) {
					if( isset( $links[$page['title']] ) ) {
						$pageLinks = $links[$page['title']];
					} else {
						$pageLinks = array();
					}
					foreach( $page['langlinks'] as $link ) {
						$pageLinks[str_replace( '-', '_', $link['lang'] ) . $this->interwikiGroup] = $link['*'];
					}
					$links[$page['title']] = $pageLinks;
				}
			}
		} while($continue != null);
		return $links;
	}

	public function updateFromViaf( $auth ) { //TODO improve
		if( !isset( $auth->links['viaf'] ) && !isset( $auth->links['enwiki'] ) ) {
			return $auth;
		}

		try {
			if( isset( $auth->links['viaf'] ) ) {
				$url = 'http://viaf.org/viaf/' . $auth->links['viaf'] . '/justlinks.json';
			} else {
				$url = 'viaf.org/viaf/sourceId/WKP|' . str_replace( ' ', '_', $auth->links['enwiki'] ) . '/justlinks.json';
			}
			$result = Api::get( $url );
			if( preg_match( "/301\nhttp:\/\/viaf\.org\/viaf\/([0-9]+)/", $result, $m ) ) {
				$result = Api::get( 'http://www.viaf.org/viaf/' . $m[1] . '/justlinks.json' );
			}
			$result = json_decode( $result, true );
			if( $result !== null ) {
				$auth->links['viaf'] = $result['viafID'];
				if(!isset($auth->links['isni']) && isset($result['ISNI'][0])) {
					$auth->links['isni'] = $result['ISNI'][0];
				}
				if(!isset($auth->links['lccn']) && isset($result['LC'][0]) && preg_match('/^([a-z]{1,5})([0-9]{2,4})([0-9]{6})$/', $result['LC'][0], $m ) ) {
					$auth->links['lccn'] = $m[1] . '/' . $m[2] . '/' . intval( $m[3] );
				}
				if( !isset($auth->links['gnd']) && isset($result['DNB'][0]) && preg_match( '/^http:\/\/d-nb\.info\/gnd\/([0-9]+)$/', $result['DNB'][0], $m ) ) {
					$auth->links['gnd'] = $m[1];
				}
				if( !isset($auth->links['nla']) && isset($result['NLA'][0]) ) {
					$auth->links['nla'] = intval( $result['NLA'][0] );
				}
				if( !isset($auth->links['bnf']) && isset($result['BNF'][0]) && preg_match( '/ark:\/12148\/([a-z0-9]+)$/', $result['BNF'][0], $m ) ) {
					$auth->links['bnf'] = $m[1];
				}
				if( !isset($auth->links['selibr']) && isset($result['SELIBR'][0]) ) {
					$auth->links['selibr'] = $result['SELIBR'][0];
				}
				if( !isset($auth->links['ulan']) && isset($result['JPG'][0]) ) {
					$auth->links['ulan'] = $result['JPG'][0];
				}
				if( !isset($auth->links['enwiki']) && isset($result['WKP'][0]) ) {
					$auth->links['enwiki'] = str_replace( '_', ' ', $result['WKP'][0]);
				}
			}
		} catch( Exception $e ) {
		}
		return $auth;
	}

	protected function save( $authorities ) {
//print_r($authorities);
		foreach( $authorities as $authority ) {
			$saved = false;
			foreach( $authority->links as $site => $title ) {
				$test = $this->storage->getAuthorityFromLink( $site, $title );
				if( $test != null ) {
					$test->mergeWith( $authority );
					$this->storage->saveAuthority( $test );
					$saved = true;
					break;
				}
			}
			if( !$saved ) {
				$this->storage->createAuthority( $authority );
			}
		}
	}
}
