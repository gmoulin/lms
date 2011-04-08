<?php
/**
 * class for saga management
 *
 * class name is in lowerclass to match table name ("commun" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) 2009, Guillaume MOULIN
 *
 * @package Saga
 * @category Saga
 */
class saga extends commun {
	// Constructor
	public function __construct() {
		//for "commun" ($this->db & co)
		parent::__construct();
	}

	/**
	 * @return array[][]
	 */
	public function getSagas() {
		try {
			$getSagas = $this->db->prepare("
				SELECT sagaID, sagaTitle, sagaSearchURL
				FROM saga
				ORDER BY sagaTitle
			");

			$getSagas->execute();

			return $getSagas->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id : identifiant de la saga
	 * @return array[][]
	 */
	public function getSagaById( $id ) {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getSagaById = $this->db->prepare("
					SELECT sagaID, sagaTitle, sagaSearchURL
					FROM saga
					WHERE sagaID = :id
				");

				$getSagaById->execute( array( ':id' => $id ) );

				$results = $getSagaById->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param string $title : titre de la saga
	 * @return array[][]
	 */
	public function getBookSagaByTitle( $title ) {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getBookSagaByTitle = $this->db->prepare("
					SELECT sagaSearchURL, bookSagaPosition,
						storageID,
						CONCAT(authorFirstName, ' ', authorLastName) as author
					FROM books_view b
					INNER JOIN book_authors_view ba ON bookID = bookFK
					WHERE sagaTitle = :title
				");

				$getBookSagaByTitle->execute( array( ':title' => $title ) );

				$results = $getBookSagaByTitle->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param string $title : titre de la saga
	 * @return array[][]
	 */
	public function getMovieSagaByTitle( $title ) {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getMovieSagaByTitle = $this->db->prepare("
					SELECT sagaSearchURL, movieSagaPosition,
						storageID,
						CONCAT(artistFirstName, ' ', artistLastName) as artist
					FROM movies_view m
					INNER JOIN movie_artists_view ma ON movieID = movieFK
					WHERE sagaTitle = :title
				");

				$getMovieSagaByTitle->execute( array( ':title' => $title ) );

				$results = $getMovieSagaByTitle->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}


	/**
	 * @return array[][]
	 */
	public function getSagasTitles($linked){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getSagasTitles = $this->db->prepare("
					SELECT sagaTitle as value
					FROM saga
					WHERE sagaID IN (SELECT ".$linked."SagaFK FROM ".$linked.")
					ORDER BY sagaTitle
				");

				$getSagasTitles->execute();

				$results = $getSagasTitles->fetchAll();

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
	public function getSagasForFilterList(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getSagasForFilterList = $this->db->prepare("
					SELECT sagaTitle as value
					FROM saga
					GROUP BY sagaTitle
					ORDER BY sagaTitle
				");

				$getSagasForFilterList->execute();

				$results = $getSagasForFilterList->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @return array[]
	 */
	public function getBooksSagasForFilterList(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getBooksSagasForFilterList = $this->db->prepare("
					SELECT sagaTitle as value
					FROM books_view
					GROUP BY sagaTitle
					ORDER BY sagaTitle
				");

				$getBooksSagasForFilterList->execute();

				$results = $getBooksSagasForFilterList->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @return array[]
	 */
	public function getMoviesSagasForFilterList(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getMoviesSagasForFilterList = $this->db->prepare("
					SELECT sagaTitle as value
					FROM movies_view
					GROUP BY sagaTitle
					ORDER BY sagaTitle
				");

				$getMoviesSagasForFilterList->execute();

				$results = $getMoviesSagasForFilterList->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * clean the caches for the related lists
	 */
	private function _cleanCaches(){
		//clear stash cache
		$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
		$stash = new Stash($stashFileSystem);

		//update caches timestamps
		$ts = new list_timestamp();

		$toClean = array('book', 'movie', 'saga');
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
	public function addSaga( $data ) {
		try {
			$addSaga = $this->db->prepare("
				INSERT INTO saga (sagaTitle, sagaSearchURL)
				VALUES (:title, :searchURL)
			");

			$addSaga->execute(
				array(
					':title' => $data['title'],
					':searchURL' => $data['searchURL'],
				)
			);

			$id = $this->db->lastInsertId();

			$this->_cleanCaches();

			return $id;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param array $data
	 */
	public function updSaga( $data ) {
		try {
			$updSaga = $this->db->prepare("
				UPDATE saga
				SET sagaTitle = :title,
					sagaSearchURL = :searchURL
				WHERE sagaID = :id
			");

			$updSaga->execute(
				array(
					':id' => $data['id'],
					':title' => $data['title'],
					':searchURL' => $data['searchURL'],
				)
			);

			$this->_cleanCaches();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 */
	public function delSaga( $id ) {
		try {
			$delSaga = $this->db->prepare("
				DELETE
				FROM saga
				WHERE sagaID = :id
			");

			$delSaga->execute( array( ':id' => $id ) );

			//cascade deletion for linked books and movies is done by the inoDB foreign key constraints

			$this->_cleanCaches();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return array[][]
	 */
	public function delSagaImpact( $id ) {
		try {
			$delSagaImpact = $this->db->prepare("
				(
					SELECT bookID AS impactID, bookTitle AS impactTitle, 'book' AS type
					FROM book
					WHERE bookSagaFK = :bookSagaFK
				) UNION (
					SELECT movieID AS impactID, movieTitle AS impactTitle, 'movie' AS type
					FROM movie
					WHERE movieSagaFK = :movieSagaFK
				) ORDER BY type, impactTitle
			");

			$delSagaImpact->execute( array( ':bookSagaFK' => $id, ':movieSagaFK' => $id ) );

			return $delSagaImpact->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return array[][]
	 */
	public function moveImpact( $id ) {
		try {
			$moveSagaImpact = $this->db->prepare("
				(
					SELECT bookTitle AS title, 'book' AS type, CONCAT(storageRoom, ' - ', storageType, IF(storageColumn IS NULL, '', ' - '), IFNULL(storageColumn, ''), IFNULL(storageLine, '')) AS currentStorage, bookSagaPosition as position
					FROM books_view
					WHERE sagaID = :bookSagaFK
				) UNION (
					SELECT movieTitle AS title, 'movie' AS type, CONCAT(storageRoom, ' - ', storageType, IF(storageColumn IS NULL, '', ' - '), IFNULL(storageColumn, ''), IFNULL(storageLine, '')) AS currentStorage, movieSagaPosition as position
					FROM movies_view
					WHERE sagaID = :movieSagaFK
				) ORDER BY type, currentStorage, position, title
			");

			$moveSagaImpact->execute( array( ':bookSagaFK' => $id, ':movieSagaFK' => $id ) );

			return $moveSagaImpact->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id : identifiant de la saga
	 * @param integer $storage_book : identifiant du rangement des livres
	 * @param integer $storage_movie : identifiant du rangement des films
	 */
	public function move( $id, $storage_book, $storage_movie ){
		try {
			if( !empty($storage_book) ){
				$moveSaga = $this->db->prepare("
					UPDATE book
					SET bookStorageFK = :storage_book
					WHERE bookSagaFK = :id
				");

				$moveSaga->execute(
					array(
						':id' => $id,
						':storage_book' => $storage_book,
					)
				);
			}

			if( !empty($storage_movie) ){
				$moveSaga = $this->db->prepare("
					UPDATE movie
					SET movieStorageFK = :storage_movie
					WHERE movieSagaFK = :id
				");

				$moveSaga->execute(
					array(
						':id' => $id,
						':storage_movie' => $storage_movie,
					)
				);
			}

			$this->_cleanCaches();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param string $name
	 * @return id or false
	 */
	public function isSaga( $saga ) {
		try {
			$isSaga = $this->db->prepare('
				SELECT sagaID
				FROM saga
				WHERE sagaTitle = :saga
			');

			$isSaga->execute(
				array(
					':saga' => $saga,
				)
			);

			$result = $isSaga->fetchAll();
			if( count($result) > 0 ){
				$sagaID = $result[0]['sagaID'];
			} else $sagaID = false;

			return $sagaID;

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
				SELECT COUNT(sagaID) AS verif
				FROM saga
				WHERE sagaID = :id");

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
			'title'			=> FILTER_SANITIZE_STRING,
			'searchURL'		=> FILTER_SANITIZE_URL,
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

			//saga id
			//errors are set to #sagaTitle because #sagaID is hidden
			if( $action == 'update' ){
				if( is_null($id) || $id === false ){
					$errors[] = array('sagaTitle', 'Identifiant incorrect.', 'error');
				} else {
					$id = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $id === false ){
						$errors[] = array('sagaTitle', 'Identifiant de la saga incorrect.', 'error');
					} else {
						//check if id exists in DB
						if( $this->exists($id) ){
							$formData['id'] = $id;
						} else {
							$errors[] = array('sagaTitle', 'Identifiant de la saga inconnu.', 'error');
						}
					}
				}
			}

			if( $action == 'update' || $action == 'add' ){
				//title
				if( is_null($title) || $title === false ){
					$errors[] = array('sagaTitle', 'Titre incorrect.', 'error');
				} elseif( empty($title) ){
					$errors[] = array('sagaTitle', 'Le titre est requis.', 'required');
				} else {
					$formData['title'] = trim($title);
				}

				//unicity
				if( empty($errors) ){
					$check = $this->isSaga($formData['title']);
					if( $check ){
						if( $action == 'add' || ($action == 'update' && $formData['id'] != $check) ){
							$errors[] = array('sagaTitle', 'Cette saga est déjà présente.', 'error');
						}
					}
				}

				//search URL
				if( is_null($searchURL) || $searchURL === false ){
					$errors[] = array('sagaSearchURL', 'Recherche incorrect.', 'error');
				} else {
					if( !empty($searchURL) ){
						$searchURL = filter_var($searchURL, FILTER_VALIDATE_URL);
						if( $id === false ){
							$errors[] = array('sagaSearchURL', 'URL de recherche invalide.', 'error');
						} else {
							$formData['searchURL'] = trim($searchURL);
						}
					} else {
						$formData['searchURL'] = '';
					}
				}
			}
		}

		$formData['errors'] = $errors;

		return $formData;
	}
}
?>