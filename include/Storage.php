<?php
class Storage {
	protected $db;

	public function __construct() {
		global $baseName, $user, $password;
		$this->db = new PDO('mysql:host=localhost;dbname=' . $baseName, $user, $password);
		$this->db->query('SET NAMES UTF8');
	}

	public function getAuthorityFromLink( $site, $title ) {
		$result = $this->db->query( 'SELECT id, name, birthYear, deathYear FROM entry, link WHERE link.entry_id = entry.id AND link.site = ' . $this->db->quote( $site ) . ' AND link.title LIKE ' . $this->db->quote( $title ) );
		if( !$result ) {
			return null;
		}
		while( $data = $result->fetch() ) {
			$auth = new Authority();
			$auth->id = $data['id'];
			$auth->name = $data['name'];
			$auth->birthYear = $data['birthYear'];
			$auth->deathYear = $data['deathYear'];
			$auth->links = $this->getLinksForId( $auth->id );
			return $auth;
		}
	}

	public function getAuthorityFromId( $id ) {
		$result = $this->db->query( 'SELECT id, name, birthYear, deathYear FROM entry WHERE id = ' . $this->db->quote( $id ) );
		if( !$result ) {
			return null;
		}
		while( $data = $result->fetch() ) {
			$auth = new Authority();
			$auth->id = $data['id'];
			$auth->name = $data['name'];
			$auth->birthYear = $data['birthYear'];
			$auth->deathYear = $data['deathYear'];
			$auth->links = $this->getLinksForId( $auth->id );
			return $auth;
		}
	}

	protected function getLinksForId( $id ) {
		$links = array();
		$result = $this->db->query( 'SELECT * FROM link WHERE link.entry_id = ' . $this->db->quote( $id ) );
		if( !$result ) {
			return $links;
		}
		while( $link = $result->fetch()) {
			$links[$link['site']] = $link['title'];
		}
		return $links;
	}

	public function createAuthority( $authority ) {
		$req = $this->db->prepare( 'INSERT INTO entry(name, birthYear, deathYear) VALUES (:name, :birthYear, :deathYear)' );
		$req->execute( array(
			'name' => $authority->name,
			'birthYear' => $authority->birthYear,
			'deathYear' => $authority->deathYear
		) );
		$authority->id = $this->db->lastInsertId();
		$req = $this->db->prepare( 'INSERT INTO link(entry_id, site, title) VALUES (:id, :site, :title)' ); //REPLACE
		foreach( $authority->links as $site => $title ) {
			$req->execute( array(
				'id' => $authority->id,
				'site' => $site,
				'title' => $title
			) );
		}
	}

	public function saveAuthority( $authority ) {
		$req = $this->db->prepare( 'UPDATE INTO entry SET name = :name, birthYear = :birthYear, deathYear = :deathYear WHERE id = :id' );
		$req->execute( array(
			'name' => $authority->name,
			'birthYear' => $authority->birthYear,
			'deathYear' => $authority->deathYear,
			'id' => $authority->id
		) );
		$req = $this->db->prepare( 'REPLACE INTO link(entry_id, site, title) VALUES (:id, :site, :title)' );
		foreach( $authority->links as $site => $title ) {
			$req->execute( array(
				'id' => $authority->id,
				'site' => $site,
				'title' => $title
			) );
		}
	}

	public function search( $search ) {
		$result = $this->db->query( 'SELECT id, name, birthYear, deathYear FROM entry, link WHERE link.entry_id = entry.id AND link.title LIKE ' . $this->db->quote( '%' . $search . '%' ) . ' LIMIT 100');
		$auths = array();
		if( !$result ) {
			return array();
		}
		while( $data = $result->fetch() ) {
			if( isset( $auths[$data['id']] ) ) {
				continue;
			}
			$auth = new Authority();
			$auth->id = $data['id'];
			$auth->name = $data['name'];
			$auth->birthYear = $data['birthYear'];
			$auth->deathYear = $data['deathYear'];
			$auth->links = $this->getLinksForId( $auth->id );
			$auths[$data['id']] = $auth;
		}
		return $auths;
	}
}
