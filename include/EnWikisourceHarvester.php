<?php
class EnWikisourceHarvester extends BaseHarvester {
	protected $namespaceId = 102;
	protected $wiki = 'en.wikisource.org';
	protected $interwikiGroup = 'source';

	protected function extractYear( $type, $content ) {
		if(preg_match('/' . $type . "year[ \t]*=[ \t]*([0-9]{3,4})/i", $content, $m)) {
			return $m[1];
		}
		return null;
	}

	protected function extractAuthorities( $content ) {
		$auth = array();
		if(preg_match("/\{\{Authority control[ \t]*\|([^\}]*)\}\}/i", $content, $m)) {
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
		$links['ensource'] = $title;

		$result = $this->parseLinksInTemplate( 'wikipedia', $content, true );
		if( $result !== null ) {
			$links['enwiki'] = $result[1];
		}
		$result = $this->parseLinksInTemplate( 'wikiquote', $content, true );
		if( $result !== null ) {
			$links['enquote'] = $result[1];
		}

		return $links;
	}

	protected function parseLinksInTemplate( $param, $content, $linkByDefault = false ) {
		if(preg_match('/' . $param . "[ \t]*=[ \t]*([^\|\{]*)/", $content, $m)) {
			return $this->parsePossibleLink( $m[1], $linkByDefault );
		}
		return null;
	}
}
