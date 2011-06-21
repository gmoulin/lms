<?php
/**
 * Class for album management
 *
 * class name is in lowerclass to match table name ("commun" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Albums
 * @category Albums
 */
class album extends commun {
	private $_sortTypes = array(
		'albumTitle, bandName',
		'albumTitle DESC, bandName',
		'bandName, albumTitle',
		'bandName DESC, albumTitle',
		'storageRoom, storageType, storageColumn, storageLine, albumTitle, bandName',
		'storageRoom DESC, storageType, storageColumn, storageLine, albumTitle, bandName',
		'albumDate, albumTitle, bandName',
		'albumDate DESC, albumTitle, bandName',
	);

	// Constructor
	public function __construct(){
		//for "commun" ($this->db & co)
		parent::__construct();
	}

	/**
	 * @return array[][]
	 */
	public function getAlbums(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getAlbums = $this->db->prepare("
					SELECT  albumID, albumTitle, albumType, albumDate,
							storageID, storageRoom, storageType, storageColumn, storageLine,
							loanID, loanHolder, loanDate,
							bandID, bandName, bandGenre, bandWebSite, bandLastCheckDate
					FROM albums_view
					INNER JOIN album_bands_view ON albumID = albumFK
					ORDER BY ".$this->_sortTypes[0]."
				");

				$getAlbums->execute();

				$results = $this->_merge($getAlbums->fetchAll());

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * dupplicate the albums_view table joinned with album_bands_view into a myisam temporary table for full text search
	 * @param array $filters
	 * @return array[][]
	 */
	public function getAlbumsByFullTextSearch(){
		try {
			//sanitize the form data
			$args = array(
				'albumSearch'			=> FILTER_SANITIZE_STRING,
				'albumTitleFilter'		=> FILTER_SANITIZE_STRING,
				'albumBandFilter'		=> FILTER_SANITIZE_STRING,
				'albumLoanFilter'		=> FILTER_SANITIZE_STRING,
				'albumStorageFilter'	=> FILTER_SANITIZE_NUMBER_INT,
				'albumSortType'			=> FILTER_SANITIZE_NUMBER_INT,
			);
			$filters = filter_var_array($_POST, $args);

			$filters['albumStorageFilter'] = filter_var($filters['albumStorageFilter'], FILTER_VALIDATE_INT, array('min_range' => 1));
			$filters['albumSortType'] = filter_var($filters['albumSortType'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 7));
			if( $filters['albumSortType'] === false ) $filters['albumSortType'] = 0;

			//construct the query
			$sql = " SELECT *";

			$sqlSelect = array();
			$sqlWhere = array();
			$sqlOrder = 'score DESC, ';
			$params = array();
			if( !empty($filters['albumSearch']) ){
				$sqlSelect = array(
					"MATCH(albumTitle) AGAINST (:searchS)",
					"MATCH(baft.bandName) AGAINST (:searchS)",
					"MATCH(loanHolder) AGAINST (:searchS)",
				);
				$sqlWhere = array(
					"MATCH(albumTitle) AGAINST (:searchW)",
					"MATCH(baft.bandName) AGAINST (:searchW)",
					"MATCH(loanHolder) AGAINST (:searchW)",
				);
				$params[':searchS'] = $this->prepareForFullTextQuery($filters['albumSearch']);
				$params[':searchW'] = $params[':searchS'];
			}
			if( !empty($filters['albumTitleFilter']) ){
				$sqlSelect[] = "MATCH(albumTitle) AGAINST (:albumTitleS)";
				$sqlWhere[] = "MATCH(albumTitle) AGAINST (:albumTitleW)";
				$params[':albumTitleS'] = $this->prepareForFullTextQuery($filters['albumTitleFilter']);
				$params[':albumTitleW'] = $params[':albumTitleS'];
			}
			if( !empty($filters['albumBandFilter']) ){
				$sqlSelect[] = "MATCH(baft.bandName) AGAINST (:bandS)";
				$sqlWhere[] = "MATCH(baft.bandName) AGAINST (:bandW)";
				$params[':bandS'] = $this->prepareForFullTextQuery($filters['albumBandFilter']);
				$params[':bandW'] = $params[':bandS'];
			}
			if( !empty($filters['albumLoanFilter']) ){
				$sqlSelect[] = "MATCH(loanHolder) AGAINST (:loanS)";
				$sqlWhere[] = "MATCH(loanHolder) AGAINST (:loanW)";
				$params[':loanS'] = $this->prepareForFullTextQuery($filters['albumLoanFilter']);
				$params[':loanW'] = $params[':loanS'];
			}
			if( !empty($filters['albumStorageFilter']) ){
				$sqlWhere[] = "storageID = :storageID";
				$params[':storageID'] = $filters['albumStorageFilter'];
			}

			$sql = " SELECT bft.*, ba.*"
				  .( !empty($sqlSelect) ? ', '.implode(' + ', $sqlSelect).' AS score' : '')
				  ." FROM albums_view_ft bft"
				  ." INNER JOIN album_bands_view_ft baft ON albumID = baft.albumFK "
				  ." LEFT JOIN album_bands_view ba ON albumID = ba.albumFK "
				  ." WHERE 1 "
				  .( !empty($sqlWhere) ? ' AND '.implode(' AND ', $sqlWhere) : '')
				  ." ORDER BY "
				  .( !empty($sqlSelect) ? $sqlOrder : '')
				  .$this->_sortTypes[$filters['albumSortType']];

			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			if( empty($params) ) $stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $sql);
			else $stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $sql, serialize($params));
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them

				//drop the temporary table if it exists
				$destroyTmpTable = $this->db->prepare("DROP TEMPORARY TABLE IF EXISTS albums_view_ft");
				$destroyTmpTable->execute();
				$destroyTmpTable = $this->db->prepare("DROP TEMPORARY TABLE IF EXISTS album_bands_view_ft");
				$destroyTmpTable->execute();

				//create the temporary table
				$tmpTable = $this->db->prepare("
					CREATE TEMPORARY TABLE albums_view_ft AS
					SELECT  albumID, albumTitle, albumType, albumDate,
							storageID, storageRoom, storageType, storageColumn, storageLine,
							loanID, loanHolder, loanDate
					FROM albums_view
				");
				$tmpTable->execute();

				//add the fulltext index
				$indexTmpTable = $this->db->prepare("
					ALTER TABLE albums_view_ft ENGINE = MyISAM,
					ADD FULLTEXT INDEX albumFT (albumTitle),
					ADD FULLTEXT INDEX loanFT (loanHolder),
					ADD INDEX storageID (storageID),
					ADD INDEX albumID (albumID)
				");
				$indexTmpTable->execute();

				//create the temporary table
				$tmpTable = $this->db->prepare("
					CREATE TEMPORARY TABLE album_bands_view_ft AS
					SELECT  albumFK, bandID, bandName
					FROM album_bands_view
				");
				$tmpTable->execute();

				//add the fulltext index
				$indexTmpTable = $this->db->prepare("
					ALTER TABLE album_bands_view_ft ENGINE = MyISAM,
					ADD FULLTEXT INDEX bandFT (bandName),
					ADD INDEX albumFK (albumFK)
				");
				$indexTmpTable->execute();


				$getAlbums = $this->db->prepare($sql);

				$getAlbums->execute( $params );

				$results = $this->_merge($getAlbums->fetchAll());

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * merged multiple lines into one with a sub array
	 * @params array $results
	 */
	private function _merge($results){
		if( !empty($results) ){
			$currentId = null;
			$merged = array();
			foreach( $results as $r ){

				if( $currentId != $r['albumID'] && !isset($merged[$r['albumID']]) ){
					$currentId = $r['albumID'];

					$merged[$r['albumID']]['albumID'] = $r['albumID'];
					$merged[$r['albumID']]['albumTitle'] = $r['albumTitle'];
					$merged[$r['albumID']]['albumType'] = $r['albumType'];
					$merged[$r['albumID']]['storageID'] = $r['storageID'];
					$merged[$r['albumID']]['storageRoom'] = $r['storageRoom'];
					$merged[$r['albumID']]['storageType'] = $r['storageType'];
					$merged[$r['albumID']]['storageColumn'] = $r['storageColumn'];
					$merged[$r['albumID']]['storageLine'] = $r['storageLine'];
					$merged[$r['albumID']]['loanID'] = $r['loanID'];
					$merged[$r['albumID']]['loanHolder'] = $r['loanHolder'];
					$merged[$r['albumID']]['loanDate'] = $r['loanDate'];
					$merged[$r['albumID']]['bands'] = array();
				}

				$merged[$r['albumID']]['bands'][$r['bandID']] = array(
					'bandID' => $r['bandID'],
					'bandName' => $r['bandName'],
					'bandGenre' => $r['bandGenre'],
					'bandWebSite' => $r['bandWebSite'],
				);
			}

			$results = $merged;
		}

		return $results;
	}

	/**
	 * @param integer $id
	 * @return array[][]
	 */
	public function getAlbumDateById( $id ){
		try {
			$getAlbumDateById = $this->db->prepare("
				SELECT albumDate AS lastModified
				FROM album
				WHERE albumID = :id
			");

			$getAlbumDateById->execute( array( ':id' => $id ) );

			$results = $getAlbumDateById->fetch();
			if( !empty($results) ) $results = $results['lastModified'];

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return array[][]
	 */
	public function getAlbumCoverById( $id ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler('covers', $stashFileSystem);
			$stash = StashBox::getCache('covers', get_class($this), $id);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getAlbumCoverById = $this->db->prepare("
					SELECT albumCover AS cover
					FROM album
					WHERE albumID = :id
				");

				$getAlbumCoverById->execute( array( ':id' => $id ) );

				$results = $getAlbumCoverById->fetch();
				if( !empty($results) ){
					$results = base64_decode($results['cover']);
					$stash->store($results, STASH_EXPIRE);
				}
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return array[]
	 */
	public function getAlbumById( $id ){
		try {
			$getAlbumById = $this->db->prepare("
				SELECT  albumID, albumTitle, albumType,
						storageID, storageRoom, storageType, storageColumn, storageLine,
						loanID, loanHolder, loanDate,
						bandID, bandName, bandGenre, bandWebSite, bandLastCheckDate
				FROM albums_view
				INNER JOIN album_bands_view ON albumID = albumFK
				WHERE albumID = :id
			");

			$getAlbumById->execute( array( ':id' => $id ) );

			$results = $this->_merge($getAlbumById->fetchAll());

			return $results[$id];

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param boolean $returnTs : flag for the function to return the list and the ts or only the list
	 * @param boolean $tsOnly : flag for the function to return the cache creation date timestamp only
	 * @return array[]
	 */
	public function getAlbumsTypes( $returnTs = false, $tsOnly = false ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);

			if( $tsOnly ){
				$ts = $stash->getTimestamp();
				if( $stash->isMiss() ){
					return null;
				} else {
					return $ts;
				}
			}

			$results = $stash->get();
			$ts = null;
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getAlbumsTypes = $this->db->prepare("
					SELECT albumType as value
					FROM album
					GROUP BY albumType
					ORDER BY albumType
				");

				$getAlbumsTypes->execute();

				$results = $getAlbumsTypes->fetchAll();

				if( !empty($results) ){
					$stash->store($results, STASH_EXPIRE);
					$ts = $stash->getTimestamp();
				}
			}

			if( $returnTs ){
				return array($ts, $results);
			} else {
				return $results;
			}

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param boolean $returnTs : flag for the function to return the list and the ts or only the list
	 * @param boolean $tsOnly : flag for the function to return the cache creation date timestamp only
	 * @return array[]
	 */
	public function getAlbumsTitleForFilterList( $returnTs = false, $tsOnly = false ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);

			if( $tsOnly ){
				$ts = $stash->getTimestamp();
				if( $stash->isMiss() ){
					return null;
				} else {
					return $ts;
				}
			}

			$results = $stash->get();
			$ts = null;
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getAlbumsTitleForFilterList = $this->db->prepare("
					SELECT albumTitle as value
					FROM album
					GROUP BY albumTitle
					ORDER BY albumTitle
				");

				$getAlbumsTitleForFilterList->execute();

				$results = $getAlbumsTitleForFilterList->fetchAll();

				if( !empty($results) ){
					$stash->store($results, STASH_EXPIRE);
					$ts = $stash->getTimestamp();
				}
			}

			if( $returnTs ){
				return array($ts, $results);
			} else {
				return $results;
			}

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param array $data
	 * @param integer $albumID
	 */
	private function _manageBandLink( $data, $albumID ){
		//band link deletion
		if( isset($data['id']) ){
			$delBandsLinks = $this->db->prepare("
				DELETE
				FROM albums_bands
				WHERE albumFK = :id
			");

			$delBandsLinks->execute( array( ':id' => $data['id'] ) );
		}

		$addBandLink = $this->db->prepare("
			INSERT INTO albums_bands (albumFK, bandFK)
			VALUES (:albumID, :bandID)
		");

		$oBand = new band();
		foreach ( $data['bands'] as $band ){
			//checking if band already exists
			$bandID = $oBand->isBand($band);
			if( $bandID === false ){
				//get first and last name
				$fullName = explode(' ', $band);
				$lastName = array_pop($fullName);
				$firstName = implode(' ', $fullName);

				$bandID = $oBand->addBand( array(
					'firstName' => trim($firstName),
					'lastName' => trim($lastName),
					'webSite' => null,
					'searchURL' => null,
				) );
			}
			if( empty($bandID) ) throw new PDOException('Bad band id for '.$band.'.');

			$addBandLink->execute(
				array(
					':albumID' => $albumID,
					':bandID' => $bandID,
				)
			);
		}
	}

	/**
	 * clean the caches for the related lists
	 */
	private function _cleanCaches(){
		//clear stash cache
		$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
		$stash = new Stash($stashFileSystem);

		$toClean = array('album', 'band', 'loan', 'storage');
		foreach( $toClean as $t ){
			$stash->setupKey($t);
			$stash->clear();

			if( isset($_SESSION[$t.'s']) ) unset($_SESSION[$t.'s']['list']);
		}
	}

	/**
	 * @param array $data
	 * @return integer
	 */
	public function addAlbum( $data ){
		try {
			set_error_handler("errorHandler"); //handle any error and throw exception, forcing transaction rollback

			$this->db->beginTransaction(); //needed for rollback

			//album
			$addAlbum = $this->db->prepare("
				INSERT INTO album (albumTitle, albumCover, albumType, albumStorageFK, albumLoanFK, albumDate)
				VALUES (:title, :cover, :type, :storage, NULL, NOW())
			");

			$addAlbum->execute(
				array(
					':title' => $data['title'],
					':cover' => $data['cover'],
					':type' => $data['type'],
					':storage' => $data['storage'],
				)
			);

			$albumID = $this->db->lastInsertId();

			if( empty($albumID) ) throw new PDOException('Bad album id.');

			//band(s)
			$this->_manageBandLink( $data, $albumID );

			$this->db->commit(); //transaction validation

			restore_error_handler();

			$this->_cleanCaches();

			//create stash cache
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			$stash = new Stash($stashFileSystem);
			$stash->setupKey('covers', get_class($this), $albumID);
			$stash->store(base64_decode($data['cover']), STASH_EXPIRE);

			return $albumID;

		} catch ( Exception $e ){
			restore_error_handler();
			$this->db->rollBack(); //cancel transaction
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param array $data
	 * @return integer
	 */
	public function addLoan( $id, $fk ){
		try {
			$addLoan = $this->db->prepare("
				UPDATE album SET albumLoanFK = :fk WHERE albumID = :id
			");

			$addLoan->execute(
				array(
					':id' => $id,
					':fk' => $fk,
				)
			);

			$this->_cleanCaches();

		} catch ( Exception $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param array $data
	 */
	public function updAlbum( $data ){
		try {
			set_error_handler("errorHandler"); //handle any error and throw exception, forcing transaction rollback

			$this->db->beginTransaction(); //needed for rollback

			//album
			$updAlbum = $this->db->prepare("
				UPDATE album
				SET albumTitle = :title,
					".( isset($data['cover']) && !empty($data['cover']) ? "albumCover = :cover," : "")."
					albumType = :type,
					albumStorageFK = :storage,
					albumDate = NOW()
				WHERE albumID = :id
			");

			$params = array(
				':id' => $data['id'],
				':title' => $data['title'],
				':type' => $data['type'],
				':storage' => $data['storage']
			);

			if( isset($data['cover']) && !empty($data['cover']) ){
				$params[':cover'] = $data['cover'];

				//update stash cache
				$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
				$stash = new Stash($stashFileSystem);
				$stash->setupKey('covers', get_class($this), $data['id']);
				$stash->store(base64_decode($data['cover']), STASH_EXPIRE);
			}

			$updAlbum->execute( $params );

			//band(s)
			$this->_manageBandLink( $data, $data['id'] );

			$this->db->commit(); //transaction validation

			restore_error_handler();

			$this->_cleanCaches();

		} catch ( Exception $e ){
			restore_error_handler();
			$this->db->rollBack(); //cancel transaction
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 */
	public function delAlbum( $id ){
		try {
			set_error_handler("errorHandler"); //handle any error and throw exception, forcing transaction rollback

			$this->db->beginTransaction(); //needed for rollback

			//loan deletion
			$this->_delLinkedLoan( $id, true );

			//album deletion
			$delAlbum = $this->db->prepare("
				DELETE
				FROM album
				WHERE albumID = :id
			");

			$delAlbum->execute( array( ':id' => $id ) );

			if( isset($_SESSION['images']['albums'][$id]) ) unset($_SESSION['images']['albums'][$id]);

			//band link deletion
			$delBandsLinks = $this->db->prepare("
				DELETE
				FROM albums_bands
				WHERE albumFK = :id
			");

			$delBandsLinks->execute( array( ':id' => $id ) );

			$this->db->commit(); //transaction validation

			restore_error_handler();

			$this->_cleanCaches();
			$this->cleanImageCache($data['id']);

		} catch ( Exception $e ){
			restore_error_handler();
			$this->db->rollBack(); //cancel transaction
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 */
	private function _delLinkedLoan( $id, $reThrow = false ){
		try {
			$delLinkedLoan = $this->db->prepare("
				DELETE
				FROM loan
				WHERE loanID = (SELECT albumID FROM album WHERE albumID = :id)
			");

			$delLinkedLoan->execute( array( ':id' => $id ) );

		} catch ( PDOException $e ){
			//delAlbum() transaction need to know if there was an error for transaction rollback
			if( !$reThrow ) erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
			else throw new PDOException($e);
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
				SELECT COUNT(albumID) AS verif
				FROM album
				WHERE albumID = :id
			");

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
	public function albumUnicityCheck( $data ) {
		try {
			$albumTitleCheck = $this->db->prepare("
				SELECT albumID
				FROM album
				WHERE albumTitle = :title
				AND albumType = :type
			");

			$params = array(
				':title' => $data['title'],
				':type' => $data['type'],
			);

			$albumTitleCheck->execute( $params );

			$result = $albumTitleCheck->fetchAll();

			if( empty($result) ) {
				return true;
			} elseif( isset($data['id']) ){
				return $result[0]['albumID'] == $data['id'];
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
		$bandFields = array();

		$args = array(
			'action'	=> FILTER_SANITIZE_STRING,
			'id'		=> FILTER_SANITIZE_NUMBER_INT,
			'title'		=> FILTER_SANITIZE_STRING,
			'type'		=> FILTER_SANITIZE_STRING,
			'cover'		=> FILTER_SANITIZE_STRING,
			'storage'	=> FILTER_SANITIZE_NUMBER_INT,
		);

		foreach( $args as $field => $validation ){
			if( !filter_has_var(INPUT_POST, $field) ){
				$errors[] = array('global', 'Le champ '.$field.' est manquant.', 'error');
			}
		}

		//bands with variable name save
		$indice = null;
		foreach( $_POST as $key => $val ){
			if( strpos($key, 'band_') !== false ){
				$indice = substr($key, strpos($key, '_') + 1);

				$args['band_'.$indice] = FILTER_SANITIZE_STRING;
				$bandFields[] = 'band_'.$indice;
			}
		}

		if( empty($errors) ){

			$formData = filter_var_array($_POST, $args);

			foreach( $formData as $field => $value ){
				${$field} = $value;
			}

			//album id
			//errors are set to #albumTitle because #albumID is hidden
			if( $action == 'update' ){
				if( is_null($id) || $id === false ){
					$errors[] = array('albumTitle', 'Identifiant incorrect.', 'error');
				} else {
					$id = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $id === false ){
						$errors[] = array('albumTitle', 'Identifiant de l\'album incorrect.', 'error');
					} else {
						//check if id exists in DB
						if( $this->exists($id) ){
							$formData['id'] = $id;
						} else {
							$errors[] = array('albumTitle', 'Identifiant de l\'album inconnu.', 'error');
						}
					}
				}
			}

			if( $action == 'update' || $action == 'add' ){
				//title
				if( is_null($title) || $title === false ){
					$errors[] = array('albumTitle', 'Titre incorrect.', 'error');
				} elseif( empty($title) ){
					$errors[] = array('albumTitle', 'Le titre est requis.', 'required');
				} else {
					$formData['title'] = trim($title);
				}

				//type
				if( is_null($type) || $type === false ){
					$errors[] = array('albumType', 'Type incorrect.', 'error');
				} elseif( empty($type) ){
					$errors[] = array('albumType', 'Le type est requis.', 'required');
				} else {
					$formData['type'] = trim($type);
				}

				//cover
				if( is_null($cover) || $cover === false ){
					$errors[] = array('albumCoversStatus', 'Couverture incorrecte.', 'error');
				} elseif( !empty($cover) ){
					if( !file_exists(UPLOAD_COVER_PATH.$cover) ){
						$errors[] = array('albumCoversStatus', 'Couverture non trouvée.', 'error');
					} else {
						$formData['cover'] = chunk_split( base64_encode( file_get_contents( UPLOAD_COVER_PATH.$cover ) ) );
					}
				} else {
					$formData['cover'] = null;
				}

				//unicity check for title + type
				if( empty($errors) && $action == 'add' ){
					if( !$this->albumUnicityCheck($formData) ){
						$errors[] = array('albumTitle', 'Album déjà présent. (unicité sur titre + type)', 'error');
					}
				}

				//storage
				if( empty($storage) ){
					$errors[] = array('albumStorage', 'Le rangement est requis.', 'required');
				}
				if( is_null($storage) || $storage === false ){
					$errors[] = array('albumStorage', 'Rangement incorrect.', 'error');
				} elseif( empty($storage) ){
					$errors[] = array('albumStorage', 'Le rangement est requis.', 'required');
				} else {
					$formData['storage'] = trim($storage);
				}

				//band
				$bands = array();
				$atLeastOneBand = false;
				foreach( $bandFields as $field ){
					if( is_null(${$field}) || ${$field} === false ){
						$errors[] = array($field, 'Groupe incorrect.', 'error');
					} elseif( !empty(${$field}) ){
						$bands[] = trim(${$field});
						$atLeastOneBand = true;
					}
				}

				if( !$atLeastOneBand ){
					$errors[] = array('albumBands_1', 'Au moins un groupe est requis.', 'required');
				} else {
					$formData['bands'] = $bands;
				}
			}
		}
		$formData['errors'] = $errors;

		return $formData;
	}
}
?>