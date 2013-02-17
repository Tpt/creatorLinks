<?php
class ItWikisourceHarvester extends BaseHarvester {
	protected $namespaceId = 102;
	protected $wiki = 'it.wikisource.org';
	protected $interwikiGroup = 'source';

	protected function extractYear( $type, $content ) {
		$title = ($type == 'birth') ? 'Anno di nascita' : 'Anno di morte';
		$val = intval( $this->parseSection( $title, $content ) );
		if( $val != 0 ) {
			return $val;
		}
		return null;
	}

	protected function extractAuthorities( $content ) {
		return array();
	}

	protected function extractWikilinks( $title, $content ) {
		$links = array();
		$links['itsource'] = $title;

		$result = $this->parseSection( 'Nome della pagina su Wikipedia', $content );
		if( $result !== null ) {
			$links['itwiki'] = $result;
		}
		$result = $this->parseSection( 'Nome della pagina su Wikiquote', $content );
		if( $result !== null ) {
			$links['itquote'] = $result;
		}

		return $links;
	}

	protected function parseSection( $title, $content ) {
		if(preg_match('/\<section begin\="' . preg_quote( $title ) . '"\/\>(.{1,})\<section end\="' . preg_quote( $title ) . '"\/\>/i', $content, $m)) {
			return $m[1];
		}
		return null;
	}
}
