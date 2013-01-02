<?php
class CreatorHarvester extends BaseHarvester {
	protected $namespaceId = 100;
	protected $wiki = 'commons.wikimedia.org';

	protected function extractYear( $type, $content ) {
		if(preg_match('/' . $type . "date[ \t]*=[ \t]*([0-9]{3,4})/i", $content, $m)) {
			return $m[1];
		}
		if(preg_match('/' . $type . "year[ \t]*=[ \t]*([0-9]{3,4})/i", $content, $m)) {
			return $m[1];
		}
		return null;
	}

	protected function extractAuthorities( $content ) {
		$auth = array();
		if(preg_match("/\{\{Authority control[ \t\n]*\|([^\}]*)\}\}/i", $content, $m)) {
			$parts = explode('|', $m[1]);
			foreach( $parts as $part ) {
				$temp =  explode('=', $part, 2);
				$key = strtolower( trim( $temp[0] ) );
				if(isset($temp[1])) {
					$val = trim( $temp[1] );
					if( $val != '' && in_array( $key, array( 'viaf', 'gnd', 'lccn', 'arc', 'bnf', 'nla', 'ulan', 'selibr', 'isni' ) ) ) {
						$auth[$key] = $val;
					}
				}
			}
		}
		return $auth;
	}

	protected function extractWikilinks( $title, $content ) {
		$links = array();
		$links['commons'] = $title;

		$links = array_merge( $this->parseLinksWithLangSwitch( 'Wikisource', $content, true ), $links );
		$links = array_merge( $this->parseLinksWithLangSwitch( 'Wikiquote', $content, true ), $links );
		$links = array_merge( $this->parseLinksWithLangSwitch( 'Name', $content, false ), $links );

		if(preg_match_all("/\{\{w[ \t]*\|[ \t]*([^\|]*)\|[^\|]*\|([^\}]*)\}\}/i", $content, $m, PREG_SET_ORDER )) {
			foreach( $m as $mp ) {
				$result = $this->parseLink( trim( $mp[2] ) . ':' . trim( $mp[1] ) );;
				if( $result !== null && !isset( $links[$result[0]] ) ) {
					$links[$result[0]] = $result[1];
				}
			}
		}

		if(preg_match("/Wikidata[ \t\n]*=[ \t\n]*(Q[1-9]+)[ \t\n]/is", $content, $m)) {
			$links['wikidata'] = $m[1];
		}

		return $links;
	}

	protected function parseLinksWithLangSwitch( $param, $content, $linkByDefault = false ) {
		$links = array();
		if(preg_match('/' . $param . "[ \t\n]*=[ \t\n]*\{\{LangSwitch[ \t\n]*\|([^\}]*)\}\}/is", $content, $m)) {
			$parts = preg_split("/\|?[ \t\n]*[a-z\-]+[ \t\n]*=/is", $m[1], null, PREG_SPLIT_NO_EMPTY);
			foreach( $parts as $part ) {
				$result = $this->parsePossibleLink( $part, $linkByDefault );
				if( $result !== null ) {
					$links[$result[0]] = $result[1];
				}
			}
		} elseif(preg_match('/' . $param . "[ \t]*=[ \t]*([^\|\{]*)/", $content, $m)) {
			$result = $this->parsePossibleLink( $m[1], $linkByDefault );
			if( $result !== null ) {
				$links[$result[0]] = $result[1];
			}
		}
		return $links;
	}
}
