<?php
class PlWikisourceHarvester extends BaseHarvester {
	protected $namespaceId = 104;
	protected $wiki = 'pl.wikisource.org';
	protected $interwikiGroup = 'source';

	protected function extractYear( $type, $content ) {
		return null;
	}

	protected function extractAuthorities( $content ) {
		return array();
	}

	protected function extractWikilinks( $title, $content ) {
		$links = array();
		$links['plsource'] = $title;

		$result = $this->parseLinksInTemplate( 'Biogram', $content, true );
		if( $result !== null ) {
			$links['plwiki'] = $result[1];
		}
		$result = $this->parseLinksInTemplate( 'Cytat', $content, true );
		if( $result !== null ) {
			$links['plquote'] = $result[1];
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
