<?php
/**
 * Class for loan managment
 *
 * class name is in lowerclass to match table name ("commun" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Loan
 * @category Loan
 */
class loan extends commun {
	// Constructor
	public function __construct() {
		//for "commun" ($this->db & co)
		parent::__construct();
	}

	/**
	 * @return array[][]
	 */
	public function getLoans() {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			if( empty($params) ) $stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $sql);
			else $stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $sql, $params);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getHolders = $this->db->prepare("
					SELECT DISTINCT(loanHolder)
					FROM loan
					ORDER BY loanHolder
				");

				$getHolders->execute();

				$results = $getHolders->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}


	/**
	 * @param integer $id : loan id
	 * @return array[][]
	 */
	public function getLoanById( $id ) {
		try {
			$getLoanById = $this->db->prepare("
				SELECT loanID, loanHolder, loanDate
				FROM loan
				WHERE loanID = :id
				ORDER BY loanDate, loanHolder
			");

			$getLoanById->execute( array( ':id' => $id ) );

			return $getLoanById->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @return array[]
	 */
	public function getBooksLoansForFilterList(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getBooksLoansForFilterList = $this->db->prepare("
					SELECT loanHolder as value
					FROM books_view
					GROUP BY loanHolder
					ORDER BY loanHolder
				");

				$getBooksLoansForFilterList->execute();

				$results = $getBooksLoansForFilterList->fetchAll();

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
	public function getMoviesLoansForFilterList(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getMoviesLoansForFilterList = $this->db->prepare("
					SELECT loanHolder as value
					FROM movies_view
					GROUP BY loanHolder
					ORDER BY loanHolder
				");

				$getMoviesLoansForFilterList->execute();

				$results = $getMoviesLoansForFilterList->fetchAll();

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
	public function getAlbumsLoansForFilterList(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getAlbumsLoansForFilterList = $this->db->prepare("
					SELECT loanHolder as value
					FROM albums_view
					GROUP BY loanHolder
					ORDER BY loanHolder
				");

				$getAlbumsLoansForFilterList->execute();

				$results = $getAlbumsLoansForFilterList->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * clean the session for the loan related lists
	 */
	private function _cleanCaches(){
		//clear stash cache
		$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
		$stash = new Stash($stashFileSystem);

		//update caches timestamps
		$ts = new list_timestamp();

		$toClean = array('book', 'movie', 'album', 'loan');
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
	public function addLoan( $data ) {
		try {
			$addLoan = $this->db->prepare("
				INSERT INTO loan (loanHolder, loanDate)
				VALUES (:holder, NOW())
			");

			$addLoan->execute(
				array(
					':holder' => $data['holder'],
				)
			);

			$loanID = $this->db->lastInsertId();

			if( $data['for'] == 'book' ) $oFor = new book();
			elseif( $data['for'] == 'movie' ) $oFor = new movie();
			elseif( $data['for'] == 'album' ) $oFor = new album();

			$oFor->addLoan( $data['id'], $loanID );

			$this->_cleanCaches();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @param string $for
	 * @param integer $fk
	 */
	public function delLoan( $id ) {
		try {
			$delLoan = $this->db->prepare("
				DELETE
				FROM loan
				WHERE loanID = :id
			");

			$delLoan->execute( array( ':id' => $id ) );

			//nullify links (not needed if ON DELETE SET NULL ok)

			$this->_cleanCaches();

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
			'action'	=> FILTER_SANITIZE_STRING,
			'id'		=> FILTER_SANITIZE_NUMBER_INT,
			'for'		=> FILTER_SANITIZE_STRING,
			'holder'	=> FILTER_SANITIZE_STRING,
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

			if( $action == 'add' ){
				//holder
				if( is_null($holder) || $holder === false ){
					$errors[] = array('loanHolder', 'Nom incorrect.', 'error');
				} elseif( empty($holder) ){
					$errors[] = array('loanHolder', 'Le nom est requis.', 'required');
				} else {
					$formData['holder'] = trim($holder);
				}

				//for
				if( is_null($for) || $for === false ){
					$errors[] = array('loanHolder', 'Lien incorrect.', 'error');
				} elseif( empty($for) ){
					$errors[] = array('loanHolder', 'Le lien est requis.', 'required');
				} else {
					$formData['for'] = $for;
				}

				//id
				if( empty($errors) ){
					//id
					if( is_null($id) || $id === false ){
						$errors[] = array('loanHolder', 'Identifiant du lien incorrect.', 'error');
					} else {
						$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $id === false ){
							$errors[] = array('loanHolder', 'Identifiant du lien incorrect.', 'error');
						} else {
							if( $formData['for'] == 'book' ) $oFor = new book();
							elseif( $formData['for'] == 'movie' ) $oFor = new movie();
							elseif( $formData['for'] == 'album' ) $oFor = new album();

							//check if id exists in DB
							if( $oFor->exists($id) ){
								$formData['id'] = $id;
							} else {
								$errors[] = array('loanHolder', 'Identifiant du lien inconnu.', 'error');
							}
						}
					}
				}
			}
		}

		$formData['errors'] = $errors;

		return $formData;
	}
}
?>