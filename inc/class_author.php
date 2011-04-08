<?php
/**
 * Class for Author management
 *
 * class name is in lowerclass to match table name ("commun" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Author
 * @category Author
 */
class author extends commun {
	// Constructor
	public function __construct() {
		//for "commun" ($this->db & co)
		parent::__construct();
	}

	/**
	 * @return array[][]
	 */
	public function getAuthors() {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getAuthors = $this->db->prepare("
					SELECT authorID, authorFirstName, authorLastName, authorWebSite, authorSearchURL
					FROM author
					ORDER BY authorFirstName, authorLastName
				");

				$getAuthors->execute();

				$results = $getAuthors->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id : author id
	 * @return array[][]
	 */
	public function getAuthorById( $id ) {
		try {
			$getAuthorById = $this->db->prepare("
				SELECT authorID, authorFirstName, authorLastName, authorWebSite, authorSearchURL
				FROM author
				WHERE authorID = :id
			");

			$getAuthorById->execute( array( ':id' => $id ) );

			return $getAuthorById->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @return array[][]
	 */
	public function getAuthorsForDropDownList() {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getAuthors = $this->db->prepare("
					SELECT CONCAT(authorFirstName, ' ', authorLastName) AS value
					FROM author
					ORDER BY value
				");

				$getAuthors->execute();

				$results = $getAuthors->fetchAll();

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
	public function getAuthorsForFilterList(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getAuthorsForFilterList = $this->db->prepare("
					SELECT CONCAT(authorFirstName, ' ', authorLastName) as value
					FROM book_authors_view
					GROUP BY authorID
					ORDER BY authorLastName, authorFirstName
				");

				$getAuthorsForFilterList->execute();

				$results = $getAuthorsForFilterList->fetchAll();

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

		$toClean = array('author', 'book', 'saga', 'storage', 'loan');
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
	public function addAuthor( $data ) {
		try {
			$addAuthor = $this->db->prepare("
				INSERT INTO author (authorFirstName, authorLastName, authorWebSite, authorSearchURL)
				VALUES (:firstName, :lastName, :webSite, :searchURL)
			");

			$addAuthor->execute(
				array(
					':firstName' => $data['firstName'],
					':lastName' => $data['lastName'],
					':webSite' => $data['webSite'],
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
	public function updAuthor( $data ) {
		try {
			$updAuthor = $this->db->prepare("
				UPDATE author
				SET authorFirstName = :firstName,
					authorLastName = :lastName,
					authorWebSite = :webSite,
					authorSearchURL = :searchURL
				WHERE authorID = :id
			");

			$updAuthor->execute(
				array(
					':id' => $data['id'],
					':firstName' => $data['firstName'],
					':lastName' => $data['lastName'],
					':webSite' => $data['webSite'],
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
	 * @return boolean
	 */
	private function isUsedAuthor( $id ) {
		try {
			$verif = false;

			$isUsedAuthor = $this->db->prepare("
				SELECT COUNT(DISTINCT bookFK) AS verif
				FROM books_authors
				WHERE authorFK = :id");

			$isUsedAuthor->execute( array( ':id' => $id ) );

			$result = $isUsedAuthor->fetch();
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
	public function delAuthor( $id ) {
		try {
			$delAuthor = $this->db->prepare("
				DELETE
				FROM author
				WHERE authorID = :id
			");

			$delAuthor->execute( array( ':id' => $id ) );

			//delete book link
			$delLink = $this->db->prepare("
				DELETE
				FROM books_authors
				WHERE authorFK = :id
			");

			$delLink->execute( array( ':id' => $id ) );

			//delete orphan book
			$delLink = $this->db->prepare("
				DELETE
				FROM book
				WHERE bookID NOT IN ( SELECT bookFK FROM books_authors )
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
	public function delAuthorImpact( $id ) {
		try {
			$delAuthorImpact = $this->db->prepare("
				SELECT bookID AS impactID, bookTitle AS impactTitle, 'book' AS type
				FROM book
				INNER JOIN books_authors ON bookFK = bookID
				WHERE authorFK = :authorFK
				AND bookFK NOT IN ( SELECT bookFK FROM books_authors WHERE authorFK != :authorFKsub )
				ORDER BY impactTitle
			");

			$delAuthorImpact->execute( array( ':authorFK' => $id, ':authorFKsub' => $id ) );

			return $delAuthorImpact->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param string $name
	 * @return id or false
	 */
	public function isAuthor( $author ) {
		try {
			$isAuthor = $this->db->prepare('
				SELECT authorID
				FROM author
				WHERE CONCAT(authorFirstName, \' \', authorLastName) = :author
			');

			$isAuthor->execute( array( ':author' => $author ) );

			$result = $isAuthor->fetchAll();
			if( count($result) > 0 ){
				$authorID = $result[0]['authorID'];
			} else $authorID = false;

			return $authorID;

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
				SELECT COUNT(authorID) AS verif
				FROM author
				WHERE authorID = :id
			");

			$exists->execute( array( ':id' => $id ) );

			$result = $exists->fetch();

			if( !empty($result) && $result['verif'] == 1 ){
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
			'webSite'		=> FILTER_SANITIZE_URL,
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

			//author id
			//errors are set to #authorFirstName because #authorID is hidden
			if( $action == 'update' ){
				if( is_null($id) || $id === false ){
					$errors[] = array('authorFirstName', 'Identifiant incorrect.', 'error');
				} else {
					$id = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $id === false ){
						$errors[] = array('authorFirstName', 'Identifiant de l\'auteur incorrect.', 'error');
					} else {
						//check if id exists in DB
						if( $this->exists($id) ){
							$formData['id'] = $id;
						} else {
							$errors[] = array('authorFirstName', 'Identifiant de l\'auteur inconnu.', 'error');
						}
					}
				}
			}

			if( $action == 'update' || $action == 'add' ){
				//first name
				if( is_null($firstName) || $firstName === false ){
					$errors[] = array('authorFirstName', 'Prénom incorrect.', 'error');
				} else {
					$formData['firstName'] = trim($firstName);
				}

				//last name
				if( is_null($lastName) || $lastName === false ){
					$errors[] = array('authorLastName', 'Nom incorrect.', 'error');
				} elseif( empty($lastName) ){
					$errors[] = array('authorLastName', 'Le nom est requis.', 'required');
				} else {
					$formData['lastName'] = trim($lastName);
				}

				//unicity
				if( empty($errors) ){
					$check = $this->isAuthor($firstName.' '.$lastName);
					if( $check ){
						if( $action == 'add' || ($action == 'update' && $formData['id'] != $check) ){
							$errors[] = array('authorFirstName', 'Cet auteur est déjà présent.', 'error');
						}
					}
				}

				//web site
				if( is_null($webSite) || $webSite === false ){
					$errors[] = array('authorWebSite', 'Site incorrect.', 'error');
				} else {
					if( !empty($webSite) ){
						$webSite = filter_var($webSite, FILTER_VALIDATE_URL);
						if( $id === false ){
							$errors[] = array('authorWebSite', 'URL du site invalide.', 'error');
						} else {
							$formData['webSite'] = trim($webSite);
						}
					} else {
						$formData['webSite'] = null;
					}
				}

				//search URL
				if( is_null($searchURL) || $searchURL === false ){
					$errors[] = array('authorSearchURL', 'Recherche incorrect.', 'error');
				} else {
					if( !empty($searchURL) ){
						$searchURL = filter_var($searchURL, FILTER_VALIDATE_URL);
						if( $id === false ){
							$errors[] = array('authorSearchURL', 'URL de recherche invalide.', 'error');
						} else {
							$formData['searchURL'] = trim($searchURL);
						}
					} else {
						$formData['searchURL'] = null;
					}
				}
			}
		}
		$formData['errors'] = $errors;

		return $formData;
	}
}
?>