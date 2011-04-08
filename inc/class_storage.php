<?php
/**
 * class for storage management
 *
 * class name is in lowerclass to match table name ("commun" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Storage
 * @category Storage
 */
class storage extends commun {
	// Constructor
	public function __construct() {
		//for "commun" ($this->db & co)
		parent::__construct();
	}

	/**
	 * @return array[][]
	 */
	public function getStorages() {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getStorages = $this->db->prepare("
					SELECT storageID, storageRoom, storageType, storageColumn, storageLine
					FROM storage
					ORDER BY storageRoom, storageType, storageColumn, storageLine
				");

				$getStorages->execute();

				$results = $getStorages->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}


	/**
	 * @param integer $id : identifiant du rangement
	 * @return array[][]
	 */
	public function getStorageById( $id ) {
		try {
			$getStorageById = $this->db->prepare("
				SELECT storageID, storageRoom, storageType, storageColumn, storageLine
				FROM storage
				WHERE storageID = :id
			");

			$getStorageById->execute( array( ':id' => $id ) );

			return $getStorageById->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}


	/**
	 * @return array[][]
	 */
	public function getStoragesForDropDownList() {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getStorages = $this->db->prepare("
					SELECT storageID as id, CONCAT(storageRoom, ' - ', storageType, IF(storageColumn IS NULL, '', ' - '), IFNULL(storageColumn, ''), IFNULL(storageLine, '')) AS value
					FROM storage
					ORDER BY storageRoom, storageType, storageColumn, storageLine
				");

				$getStorages->execute();

				$results = $getStorages->fetchAll();

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
	public function getStoragesRoomsList() {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getStoragesRoomsList = $this->db->prepare("
					SELECT storageRoom AS value
					FROM storage
					GROUP BY storageRoom
					ORDER BY storageRoom
				");

				$getStoragesRoomsList->execute();

				$results = $getStoragesRoomsList->fetchAll();

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
	public function getStoragesTypesList() {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getStoragesTypesList = $this->db->prepare("
					SELECT storageType AS value
					FROM storage
					GROUP BY storageType
					ORDER BY storageType
				");

				$getStoragesTypesList->execute();

				$results = $getStoragesRoomsList->fetchAll();

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
	public function getStoragesForFilterList() {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getStoragesForFilterList = $this->db->prepare("
					SELECT storageID AS id, CONCAT(storageRoom, ' ', storageType, ' ', IFNULL(storageColumn, ''), IFNULL(storageLine, '')) AS value
					FROM storage
					GROUP BY storageID
					ORDER BY storageRoom, storageType, storageColumn, storageLine
				");

				$getStoragesForFilterList->execute();

				$results = $getStoragesForFilterList->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ) {
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

		$toClean = array('book', 'album', 'movie', 'storage');
		foreach( $toClean as $t ){
			$stash->setupKey($t);
			$stash->clear();

			$ts->updateByName($t);

			if( isset($_SESSION[$t.'s']) ) unset($_SESSION[$t.'s']['list']);
		}
	}

	/**
	 * @param array $data
	 */
	public function addStorage( $data ) {
		try {
			$addStorage = $this->db->prepare("
				INSERT INTO storage (storageRoom, storageType, storageColumn, storageLine)
				VALUES (:room, :type, :column, :line)
			");

			$addStorage->execute(
				array(
					':room' => $data['room'],
					':type' => $data['type'],
					':column' => ( !empty($data['column']) ? $data['column'] : null ),
					':line' => ( !empty($data['line']) ? $data['line'] : null ),
				)
			);

			//update the name of the storage image
			if( file_exists(UPLOAD_STORAGE_PATH.'tmp_storage.png') ){
				$filename = stripAccents($data['room'].'_'.$data['type'].( !empty($data['column']) || !empty($data['line']) ? '_' : '' ).$data['column'].$data['line'].'.png');
				rename(UPLOAD_STORAGE_PATH.'tmp_storage.png', UPLOAD_STORAGE_PATH.$filename);
			}

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
	public function updStorage( $data ) {
		try {
			//delete old storage image if necessary
			$check = $this->getStorageById($data['id']);
			if( empty($check) ){
				throw new PDOException('Rangement inconnu.');
			}
			$check = $check[0];

			$isDifferent = false;
			if( $check['storageRoom'] != $data['room'] ) $isDifferent = true;
			elseif( $check['storageType'] != $data['type'] ) $isDifferent = true;
			elseif( $check['storageColumn'] != $data['column'] ) $isDifferent = true;
			elseif( $check['storageLine'] != $data['line'] ) $isDifferent = true;

			if( $isDifferent ){
				$filename = stripAccents($check['storageRoom'].'_'.$check['storageType'].( !empty($check['storageColumn']) || !empty($check['storageLine']) ? '_' : '' ).$check['storageColumn'].$check['storageLine'].'.png');
				unlink(UPLOAD_STORAGE_PATH.$filename);
			}

			$updStorage = $this->db->prepare("
				UPDATE storage
				SET storageRoom = :room,
					storageType = :type,
					storageColumn = :column,
					storageLine = :line
				WHERE storageID = :id
			");

			$updStorage->execute(
				array(
					':id' => $data['id'],
					':room' => $data['room'],
					':type' => $data['type'],
					':column' => $data['column'],
					':line' => $data['line'],
				)
			);

			//update the name of the storage image
			if( file_exists(UPLOAD_STORAGE_PATH.'tmp_storage.png') ){
				$filename = stripAccents($data['room'].'_'.$data['type'].( !empty($data['column']) || !empty($data['line']) ? '_' : '' ).$data['column'].$data['line'].'.png');
				rename(UPLOAD_STORAGE_PATH.'tmp_storage.png', UPLOAD_STORAGE_PATH.$filename);
			}

			$this->_cleanCaches();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 */
	public function delStorage( $id ) {
		try {
			$check = $this->getStorageById($id);
			if( empty($check) ){
				throw new PDOException('Rangement inconnu.');
			}
			$check = $check[0];

			$delStorage = $this->db->prepare("
				DELETE
				FROM storage
				WHERE storageID = :id
			");

			$delStorage->execute( array( ':id' => $id ) );

			//nullify links (not needed if ON DELETE SET NULL is working, see database model)
			$updateLinkedBooks = $this->db->prepare("
				UPDATE book
				SET bookStorageFK = NULL
				WHERE bookStorageFK = :id
			");
			$updateLinkedBooks->execute( array( ':id' => $id ) );

			$updateLinkedMovies = $this->db->prepare("
				UPDATE movie
				SET movieStorageFK = NULL
				WHERE movieStorageFK = :id
			");
			$updateLinkedMovies->execute( array( ':id' => $id ) );

			$updateLinkedAlbums = $this->db->prepare("
				UPDATE album
				SET albumStorageFK = NULL
				WHERE albumStorageFK = :id
			");
			$updateLinkedAlbums->execute( array( ':id' => $id ) );

			//delete the storage image
			$filename = stripAccents($check['storageRoom'].'_'.$check['storageType'].( !empty($check['storageColumn']) || !empty($check['storageLine']) ? '_' : '' ).$check['storageColumn'].$check['storageLine'].'.png');
			unlink(UPLOAD_STORAGE_PATH.$filename);

			$this->_cleanCaches();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return array[][]
	 */
	public function delStorageImpact( $id ) {
		try {
			$delStorageImpact = $this->db->prepare("
				(
					SELECT bookID AS impactID, bookTitle AS impactTitle, 'book' AS type
					FROM book
					WHERE bookStorageFK = :bookStorageFK
				) UNION (
					SELECT movieID AS impactID, movieTitle AS impactTitle, 'movie' AS type
					FROM movie
					WHERE movieStorageFK = :movieStorageFK
				) UNION (
					SELECT albumID AS impactID, albumTitle AS impactTitle, 'album' AS type
					FROM album
					WHERE albumStorageFK = :albumStorageFK
				) ORDER BY type, impactTitle
			");

			$delStorageImpact->execute( array( ':bookStorageFK' => $id, ':movieStorageFK' => $id, ':albumStorageFK' => $id ) );

			return $delStorageImpact->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return array[][]
	 */
	public function relocateStorage( $formData ) {
		try {
			$id = filter_var($formData['storage'], FILTER_SANITIZE_NUMBER_INT);
			$id = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));

			if( $id && isset($formData['bookID']) && !empty($formData['bookID']) ){
				$length = count($formData['bookID']);
				for( $i = 0; $i < $length; $i++ ){
					$formData['bookID'][$i] = filter_var($formData['bookID'][$i], FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $formData['bookID'][$i] == false ) unset($formData['bookID'][$i]);
				}

				$relocateStorage = $this->db->prepare("
					UPDATE book SET bookStorageFK = :id WHERE bookID IN (".implode(',', $formData['bookID']).")
				");
				$relocateStorage->execute( array( ':id' => $id ) );
			}

			if( $id && isset($formData['movieID']) && !empty($formData['movieID']) ){
				$length = count($formData['movieID']);
				for( $i = 0; $i < $length; $i++ ){
					$formData['movieID'][$i] = filter_var($formData['movieID'][$i], FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $formData['movieID'][$i] == false ) unset($formData['movieID'][$i]);
				}

				$relocateStorage = $this->db->prepare("
					UPDATE movie SET movieStorageFK = :id WHERE movieID IN (".implode(',', $formData['movieID']).")
				");
				$relocateStorage->execute( array( ':id' => $id ) );
			}

			if( $id && isset($formData['albumID']) && !empty($formData['albumID']) ){
				$length = count($formData['albumID']);
				for( $i = 0; $i < $length; $i++ ){
					$formData['albumID'][$i] = filter_var($formData['albumID'][$i], FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $formData['albumID'][$i] == false ) unset($formData['albumID'][$i]);
				}

				$relocateStorage = $this->db->prepare("
					UPDATE album SET albumStorageFK = :id WHERE albumID IN (".implode(',', $formData['albumID']).")
				");
				$relocateStorage->execute( array( ':id' => $id ) );
			}

			$this->_cleanCaches( true );

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
				SELECT COUNT(storageID) AS verif
				FROM storage
				WHERE storageID = :id");

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
	 * @param integer $id
	 * @return boolean
	 */
	public function storageUnicityCheck( $data ) {
		try {
			$storageUnicityCheck = $this->db->prepare("
				SELECT storageID
				FROM storage
				WHERE storageRoom = :room
				AND storageType = :type
				AND storageColumn = :column
				AND storageLine = :line
			");

			$storageUnicityCheck->execute( array(
				':room' => $data['room'],
				':type' => $data['type'],
				':column' => $data['column'],
				':line' => $data['line'],
			) );

			$result = $storageUnicityCheck->fetch();
			if( empty($result) ) {
				return true;
			} elseif( $data['id'] ){
				return $result[0]['storageID'] == $data['id'];
			}

			return false;

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
			'room'			=> FILTER_SANITIZE_STRING,
			'type'			=> FILTER_SANITIZE_STRING,
			'column'		=> FILTER_SANITIZE_STRING,
			'line'			=> FILTER_SANITIZE_NUMBER_INT,
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
			//errors are set to #storageRoom because #storageID is hidden
			if( $action == 'update' ){
				if( is_null($id) || $id === false ){
					$errors[] = array('storageRoom', 'Identifiant incorrect.', 'error');
				} else {
					$id = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $id === false ){
						$errors[] = array('storageRoom', 'Identifiant du rangement incorrect.', 'error');
					} else {
						//check if id exists in DB
						if( $this->exists($id) ){
							$formData['id'] = $id;
						} else {
							$errors[] = array('storageRoom', 'Identifiant du rangement inconnu.', 'error');
						}
					}
				}
			}

			if( $action == 'update' || $action == 'add' ){
				//room
				if( is_null($room) || $room === false ){
					$errors[] = array('storageRoom', 'Pièce incorrecte.', 'error');
				} elseif( empty($room) ){
					$errors[] = array('storageRoom', 'La pièce est requise.', 'required');
				} else {
					$formData['room'] = trim($room);
				}

				//type
				if( is_null($type) || $type === false ){
					$errors[] = array('storageType', 'Type incorrect.', 'error');
				} elseif( empty($type) ){
					$errors[] = array('storageType', 'Le type est requis.', 'required');
				} else {
					$formData['type'] = trim($type);
				}

				//column
				if( is_null($column) || $column === false ){
					$errors[] = array('storageColumn', 'Colonne incorrecte.', 'error');
				} elseif( empty($column) ){
					$formData['column'] = '';
				} else {
					$formData['column'] = trim($column);
				}

				//line
				if( is_null($line) || $line === false ){
					$errors[] = array('storageLine', 'Ligne incorrecte.', 'error');
				} elseif( empty($line) ){
					$formData['line'] = trim($line);
				} else {
					$line = filter_var($line, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $line === false ){
						$errors[] = array('storageLine', 'La ligne doit être un chiffre entier supérieur ou égal à 1.', 'error');
					}

					$formData['line'] = trim($line);
				}

				//unicity check
				if( empty($errors) && $action == 'add' ){
					if( !$this->storageUnicityCheck($formData) ){
						$errors[] = array('storageRoom', 'Rangement déjà présent.', 'error');
					}
				}
			}
		}

		$formData['errors'] = $errors;

		return $formData;
	}
}
?>