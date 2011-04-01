<?php
/**
 * Class for Artist management
 *
 * class name is in lowerclass to match table name ("commun" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Artist
 * @category Artist
 */
class artist extends commun {
	// Constructor
	public function __construct() {
		//for "commun" ($this->db & co)
		parent::__construct();
	}

	/**
	 * @return array[][]
	 */
	public function getArtists() {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getArtists = $this->db->prepare("
					SELECT artistID, artistFirstName, artistLastName, artistPhoto
					FROM artist
					ORDER BY artistFirstName, artistLastName
				");

				$getArtists->execute();

				$results = $getArtists->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}


	/**
	 * @param integer $id : artist id
	 * @return array[][]
	 */
	public function getArtistById( $id ) {
		try {
			$getArtistById = $this->db->prepare("
				SELECT artistID, artistFirstName, artistLastName, artistPhoto
				FROM artist
				WHERE artistID = :id
			");

			$getArtistById->execute( array( ':id' => $id ) );

			return $getArtistById->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @return array[][]
	 */
	public function getArtistsForDropDownList() {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getArtists = $this->db->prepare("
					SELECT CONCAT(artistFirstName, ' ', artistLastName) AS value
					FROM artist
					ORDER BY value
				");

				$getArtists->execute();

				$results = $getArtists->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @return array[]
	 */
	public function getArtistsForFilterList(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getArtistsForFilterList = $this->db->prepare("
					SELECT CONCAT(artistFirstName, ' ', artistLastName) as value
					FROM movie_artists_view
					GROUP BY artistID
					ORDER BY artistLastName, artistFirstName
				");

				$getArtistsForFilterList->execute();

				$results = $getArtistsForFilterList->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * clean the session for the artist related lists
	 */
	private function _cleanCaches(){
		//clear stash cache
		$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
		$stash = new Stash($stashFileSystem);

		//update caches timestamps
		$ts = new list_timestamp();

		$toClean = array('artist', 'movie', 'saga', 'storage', 'loan');
		foreach( $toClean as $t ){
			$stash->setupKey($t);
			$stash->clear();

			$ts->updateByName($t);

			if( isset($_SESSION[$t.'s']) ) unset($_SESSION[$t.'s']['list']);
		}
	}

	/**
	 * @param array $data
	 * @return integer
	 */
	public function addArtist( $data ) {
		try {
			$addArtist = $this->db->prepare("
				INSERT INTO artist (artistFirstName, artistLastName, artistPhoto)
				VALUES (:firstName, :lastName, :photo)
			");

			$addArtist->execute(
				array(
					':firstName' => $data['firstName'],
					':lastName' => $data['lastName'],
					':photo' => $data['photo'],
				)
			);

			$this->_cleanCaches();

			return $this->db->lastInsertId();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param array $data
	 */
	public function updArtist( $data ) {
		try {
			$updArtist = $this->db->prepare("
				UPDATE artist
				SET artistFirstName = :firstName,
					artistLastName = :lastName
					".( isset($data['photo']) ? ", artistPhoto = :photo" : "")."
				WHERE artistID = :id
			");

			$params = array(
				':id' => $data['id'],
				':firstName' => $data['firstName'],
				':lastName' => $data['lastName'],
				':photo' => $data['photo'],
			);

			if( isset($data['photo']) ) $param[':photo'] = $data['photo'];

			$updArtist->execute( $params );

			$this->_cleanCaches();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return boolean
	 */
	private function isUsedArtist( $id ) {
		try {
			$verif = false;

			$isUsedArtist = $this->db->prepare("
				SELECT COUNT(DISTINCT movieFK) AS verif
				FROM movies_artists
				WHERE artistFK = :id");

			$isUsedArtist->execute( array( ':id' => $id ) );

			$result = $isUsedArtist->fetch();
			if( !empty($result) && $result['verif'] == 0 ) {
				$verif = true;
			}

			return $verif;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return string error message
	 */
	public function delArtist( $id ) {
		try {
			$delArtist = $this->db->prepare("
				DELETE
				FROM artist
				WHERE artistID = :id
			");

			$delArtist->execute( array( ':id' => $id ) );

			//delete movie link
			$delLink = $this->db->prepare("
				DELETE
				FROM movies_artists
				WHERE artistFK = :id
			");

			$delLink->execute( array( ':id' => $id ) );

			//delete orphan movie
			$delLink = $this->db->prepare("
				DELETE
				FROM movie
				WHERE movieID NOT IN ( SELECT movieFK FROM movies_artists )
			");

			$delLink->execute( array( ':id' => $id ) );

			$this->_cleanCaches();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return array[][]
	 */
	public function delArtistImpact( $id ) {
		try {
			$delArtistImpact = $this->db->prepare("
				SELECT movieID AS impactID, movieTitle AS impactTitle, 'movie' AS type
				FROM movie
				INNER JOIN movies_artists ON movieFK = movieID
				WHERE artistFK = :artistFK
				AND movieFK NOT IN ( SELECT movieFK FROM movies_artists WHERE artistFK != :artistFKsub )
				ORDER BY impactTitle
			");

			$delArtistImpact->execute( array( ':artistFK' => $id, ':artistFKsub' => $id ) );

			return $delArtistImpact->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param string $name
	 * @return id or false
	 */
	public function isArtist( $artist ) {
		try {
			$isArtist = $this->db->prepare('
				SELECT artistID
				FROM artist
				WHERE CONCAT(artistFirstName, \' \', artistLastName) = :artist
			');

			$isArtist->execute( array( ':artist' => $artist ) );

			$result = $isArtist->fetchAll();
			if( count($result) > 0 ){
				$artistID = $result[0]['artistID'];
			} else $artistID = false;

			return $artistID;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return boolean
	 */
	public function exists( $id ) {
		try {
			$verif = false;

			$exists = $this->db->prepare("
				SELECT COUNT(artistID) AS verif
				FROM artist
				WHERE artistID = :id");

			$exists->execute( array( ':id' => $id ) );

			$result = $exists->fetch();
			if( !empty($result) && $result['verif'] == 1 ) {
				$verif = true;
			}

			return $verif;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * check and parse form data for add or update
	 * errors are returned with form inputs ids as (id, text, type)
	 *
	 * @return array[]
	 */
	public function checkAndPrepareFormData(){
		$formData = array();
		$errors = array();

		$args = array(
			'action'		=> FILTER_SANITIZE_STRING,
			'id'			=> FILTER_SANITIZE_NUMBER_INT,
			'firstName'		=> FILTER_SANITIZE_STRING,
			'lastName'		=> FILTER_SANITIZE_STRING,
			//'photo'			=> FILTER_SANITIZE_STRING,
		);

		foreach( $args as $field => $validation ){
			if( !filter_has_var(INPUT_POST, $field) ){
				$errors[] = array('global', 'Le champ '.$field.' est manquant.', 'error');
			}
		}

		if( empty($errors) ){

			$formData = filter_var_array($_POST, $args);

			foreach( $formData as $field => $value ){
				${$field} = $value;
			}

			//artist id
			//errors are set to #artistFirstName because #artistID is hidden
			if( $action == 'update' ){
				if( is_null($id) || $id === false ){
					$errors[] = array('artistFirstName', 'Identifiant incorrect.', 'error');
				} else {
					$id = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $id === false ){
						$errors[] = array('artistFirstName', 'Identifiant de l\'artiste incorrect.', 'error');
					} else {
						//check if id exists in DB
						if( $this->exists($id) ){
							$formData['id'] = $id;
						} else {
							$errors[] = array('artistFirstName', 'Identifiant de l\'artiste inconnu.', 'error');
						}
					}
				}
			}

			if( $action == 'update' || $action == 'add' ){
				//first name
				if( is_null($firstName) || $firstName === false ){
					$errors[] = array('artistFirstName', 'Prénom incorrect.', 'error');
				} elseif( empty($firstName) ){
					$errors[] = array('artistFirstName', 'Le prénom est requis.', 'required');
				} else {
					$formData['firstName'] = trim($firstName);
				}

				//last name
				if( is_null($lastName) || $lastName === false ){
					$errors[] = array('artistLastName', 'Nom incorrect.', 'error');
				} elseif( empty($lastName) ){
					$errors[] = array('artistLastName', 'Le nom est requis.', 'required');
				} else {
					$formData['lastName'] = trim($lastName);
				}

				//unicity
				if( empty($errors) ){
					$check = $this->isArtist($firstName.' '.$lastName);
					if( $check ){
						if( $action == 'add' || ($action == 'update' && $formData['id'] != $check) ){
							$errors[] = array('authorFirstName', 'Cet artiste est déjà présent.', 'error');
						}
					}
				}
				//photo
				$formData['photo'] = null;
				/*
				if( is_null($photo) || $photo === false ){
					$errors[] = array('artistCoversSatus', 'Photo incorrecte.', 'error');
				} else {
					if( $action == 'add' ){
						if( empty($photo) ){
							$formData['photo'] = '';
						} elseif( $action == 'add' && !file_exists(UPLOAD_COVER_PATH.$photo) ){
							$errors[] = array('artistCoversSatus', 'Couverture non trouvée.', 'error');
						} else {
							$formData['photo'] = chunk_split( base64_encode( file_get_contents( UPLOAD_COVER_PATH.$photo ) ) );
						}
					} else { //update
						if( !empty($photo) ){
							if( !file_exists(UPLOAD_COVER_PATH.$cover) ){
								$errors[] = array('artistCoversSatus', 'Couverture non trouvée.', 'error');
							} else {
								$formData['photo'] = chunk_split( base64_encode( file_get_contents( UPLOAD_COVER_PATH.$photo ) ) );
							}
						}
					}
				}
				*/
			}
		}
		$formData['errors'] = $errors;

		return $formData;
	}
}
?>