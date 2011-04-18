<?php
/**
 * Class for Band management
 *
 * class name is in lowerclass to match table name ("commun" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Band
 * @category Band
 */
class band extends commun {
	private $_sortTypes = array(
		'bandName',
		'bandName DESC',
		'bandLastCheckDate',
		'bandLastCheckDate DESC',
	);

	// Constructor
	public function __construct() {
		//for "commun" ($this->db & co)
		parent::__construct();
	}

	/**
	 * @return array[][]
	 */
	public function getBands() {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getBands = $this->db->prepare("
					SELECT bandID, bandName, bandGenre, bandWebSite, bandLastCheckDate
					FROM band
					ORDER BY ".$this->_sortTypes[0]."
				");

				$getBands->execute();

				$results = $getBands->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * dupplicate the band table into a myisam temporary table for full text search
	 * @param array $filters
	 * @return array[][]
	 */
	public function getBandsByFullTextSearch(){
		try {
			//sanitize the form data
			$args = array(
				'bandNameFilter'		=> FILTER_SANITIZE_STRING,
				'bandGenreFilter'		=> FILTER_SANITIZE_STRING,
				'bandSortType'			=> FILTER_SANITIZE_NUMBER_INT,
			);
			$filters = filter_var_array($_POST, $args);

			$filters['bandSortType'] = filter_var($filters['bandSortType'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 3));
			if( $filters['bandSortType'] === false ) $filters['bandSortType'] = 0;

			//construct the query
			$sql = " SELECT *";

			$sqlSelect = array();
			$sqlWhere = array();
			$sqlOrder = 'score DESC, ';
			$params = array();
			if( !empty($filters['bandNameFilter']) ){
				$sqlSelect[] = "MATCH(bandName) AGAINST (:bandNameS)";
				$sqlWhere[] = "MATCH(bandName) AGAINST (:bandNameW)";
				$params[':bandNameS'] = $this->prepareForFullTextQuery($filters['bandNameFilter']);
				$params[':bandNameW'] = $params[':bandNameS'];
			}
			if( !empty($filters['bandGenreFilter']) ){
				$sqlSelect[] = "MATCH(bandGenre) AGAINST (:bandGenreS)";
				$sqlWhere[] = "MATCH(bandGenre) AGAINST (:bandGenreW)";
				$params[':bandGenreS'] = $this->prepareForFullTextQuery($filters['bandGenreFilter']);
				$params[':bandGenreW'] = $params[':bandGenreS'];
			}

			$sql = " SELECT bft.*"
				  .( !empty($sqlSelect) ? ', '.implode(' + ', $sqlSelect).' AS score' : '')
				  ." FROM band_ft bft"
				  ." WHERE 1 "
				  .( !empty($sqlWhere) ? ' AND '.implode(' AND ', $sqlWhere) : '')
				  ." ORDER BY "
				  .( !empty($sqlSelect) ? $sqlOrder : '')
				  .$this->_sortTypes[$filters['bandSortType']];


			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			if( empty($params) ) $stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $sql);
			else $stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $sql, serialize($params));
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them

				//drop the temporary table if it exists
				$destroyTmpTable = $this->db->prepare("DROP TEMPORARY TABLE IF EXISTS band_ft");
				$destroyTmpTable->execute();

				//create the temporary table
				$tmpTable = $this->db->prepare("
					CREATE TEMPORARY TABLE band_ft AS
					SELECT  bandID, bandName, bandGenre, bandWebSite, bandLastCheckDate
					FROM band
				");
				$tmpTable->execute();

				//add the fulltext index
				$indexTmpTable = $this->db->prepare("
					ALTER TABLE band_ft ENGINE = MyISAM,
					ADD FULLTEXT INDEX bandNameFT (bandName),
					ADD FULLTEXT INDEX bandGenreFT (bandGenre),
					ADD INDEX bandLastCheckDate (bandLastCheckDate)
				");
				$indexTmpTable->execute();


				$getBands = $this->db->prepare($sql);

				$getBands->execute( $params );

				$results = $getBands->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id : band id
	 * @return array[][]
	 */
	public function getBandById( $id ) {
		try {
			$getBandById = $this->db->prepare("
				SELECT bandID, bandName, bandGenre, bandWebSite, bandLastCheckDate
				FROM band
				WHERE bandID = :id
			");

			$getBandById->execute( array( ':id' => $id ) );

			return $getBandById->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @return array[][]
	 */
	public function getBandsForDropDownList() {
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getBands = $this->db->prepare("
					SELECT bandName AS value
					FROM band
					ORDER BY value
				");

				$getBands->execute();

				$results = $getBands->fetchAll();

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
	public function getBandsForFilterList(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getBandsForFilterList = $this->db->prepare("
					SELECT bandName as value
					FROM album_bands_view
					GROUP BY bandID
					ORDER BY bandGenre, bandName
				");

				$getBandsForFilterList->execute();

				$results = $getBandsForFilterList->fetchAll();

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
	public function getBandsNameForFilterList(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getBandsNameForFilterList = $this->db->prepare("
					SELECT bandName as value
					FROM band
					ORDER BY bandName
				");

				$getBandsNameForFilterList->execute();

				$results = $getBandsNameForFilterList->fetchAll();

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
	public function getBandsGenreForFilterList(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getBandsGenreForFilterList = $this->db->prepare("
					SELECT bandGenre as value
					FROM band
					ORDER BY bandGenre
				");

				$getBandsGenreForFilterList->execute();

				$results = $getBandsGenreForFilterList->fetchAll();

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
	public function getBandsGenres(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getBandsGenres = $this->db->prepare("
					SELECT bandGenre as value
					FROM band
					GROUP BY bandGenre
					ORDER BY bandGenre
				");

				$getBandsGenres->execute();

				$results = $getBandsGenres->fetchAll();

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

		$toClean = array('album', 'band', 'storage', 'loan');
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
	public function addBand( $data ) {
		try {
			$addBand = $this->db->prepare("
				INSERT INTO band (bandName, bandGenre, bandWebSite, bandLastCheckDate)
				VALUES (:name, :genre, :webSite, NULL)
			");

			$addBand->execute(
				array(
					':name' => $data['name'],
					':genre' => $data['genre'],
					':webSite' => $data['webSite'],
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
	public function updBand( $data ) {
		try {
			$updBand = $this->db->prepare("
				UPDATE band
				SET bandName = :name,
					bandGenre = :genre,
					bandWebSite = :webSite
				WHERE bandID = :id
			");

			$updBand->execute(
				array(
					':id' => $data['id'],
					':name' => $data['name'],
					':genre' => $data['genre'],
					':webSite' => $data['webSite'],
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
	public function updBandLastCheckDate( $id ) {
		try {
			$updBandLastCheckDate = $this->db->prepare("
				UPDATE band
				SET bandLastCheckDate = NOW()
				WHERE bandID = :id
			");

			$updBandLastCheckDate->execute( array(':id' => $id) );

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param integer $id
	 * @return boolean
	 */
	private function isUsedBand( $id ) {
		try {
			$verif = false;

			$isUsedBand = $this->db->prepare("
				SELECT COUNT(DISTINCT albumFK) AS verif
				FROM albums_bands
				WHERE bandFK = :id");

			$isUsedBand->execute( array( ':id' => $id ) );

			$result = $isUsedBand->fetch();
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
	public function delBand( $id ) {
		try {
			$delBand = $this->db->prepare("
				DELETE
				FROM band
				WHERE bandID = :id
			");

			$delBand->execute( array( ':id' => $id ) );

			//delete album link
			$delLink = $this->db->prepare("
				DELETE
				FROM albums_bands
				WHERE bandFK = :id
			");

			$delLink->execute( array( ':id' => $id ) );

			//delete orphan album
			$delLink = $this->db->prepare("
				DELETE
				FROM album
				WHERE albumID NOT IN ( SELECT albumFK FROM albums_bands )
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
	public function delBandImpact( $id ) {
		try {
			$delBandImpact = $this->db->prepare("
				SELECT albumID AS impactID, albumTitle AS impactTitle, 'album' AS type
				FROM album
				INNER JOIN albums_bands ON albumFK = albumID
				WHERE bandFK = :bandFK
				AND albumFK NOT IN ( SELECT albumFK FROM albums_bands WHERE bandFK != :bandFKsub )
				ORDER BY impactTitle
			");

			$delBandImpact->execute( array( ':bandFK' => $id, ':bandFKsub' => $id ) );

			return $delBandImpact->fetchAll();

		} catch ( PDOException $e ) {
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @param string $name
	 * @return id or false
	 */
	public function isBand( $band ) {
		try {
			$isBand = $this->db->prepare('
				SELECT bandID
				FROM band
				WHERE bandName = :band
			');

			$isBand->execute( array( ':band' => $band ) );

			$result = $isBand->fetchAll();
			if( count($result) > 0 ){
				$bandID = $result[0]['bandID'];
			} else $bandID = false;

			return $bandID;

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
				SELECT COUNT(bandID) AS verif
				FROM band
				WHERE bandID = :id
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
			'name'			=> FILTER_SANITIZE_STRING,
			'genre'			=> FILTER_SANITIZE_STRING,
			'webSite'		=> FILTER_SANITIZE_URL,
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

			//band id
			//errors are set to #bandName because #bandID is hidden
			if( $action == 'update' ){
				if( is_null($id) || $id === false ){
					$errors[] = array('bandName', 'Identifiant incorrect.', 'error');
				} else {
					$id = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $id === false ){
						$errors[] = array('bandName', 'Identifiant du groupe incorrect.', 'error');
					} else {
						//check if id exists in DB
						if( $this->exists($id) ){
							$formData['id'] = $id;
						} else {
							$errors[] = array('bandName', 'Identifiant du groupe inconnu.', 'error');
						}
					}
				}
			}

			if( $action == 'update' || $action == 'add' ){
				//first name
				if( is_null($name) || $name === false ){
					$errors[] = array('bandName', 'Nom incorrect.', 'error');
				} else {
					$formData['name'] = trim($name);
				}

				//last name
				if( is_null($genre) || $genre === false ){
					$errors[] = array('bandGenre', 'Genre incorrect.', 'error');
				} elseif( empty($genre) ){
					$errors[] = array('bandGenre', 'Le genre est requis.', 'required');
				} else {
					$formData['genre'] = trim($genre);
				}

				//unicity
				if( empty($errors) ){
					$check = $this->isBand($name.' '.$genre);
					if( $check ){
						if( $action == 'add' || ($action == 'update' && $formData['id'] != $check) ){
							$errors[] = array('bandName', 'Ce groupe est déjà présent.', 'error');
						}
					}
				}

				//web site
				if( is_null($webSite) || $webSite === false ){
					$errors[] = array('bandWebSite', 'Site incorrect.', 'error');
				} else {
					if( !empty($webSite) ){
						$webSite = filter_var($webSite, FILTER_VALIDATE_URL);
						if( $id === false ){
							$errors[] = array('bandWebSite', 'URL du site invalide.', 'error');
						} else {
							$formData['webSite'] = trim($webSite);
						}
					} else {
						$formData['webSite'] = null;
					}
				}
			}
		}
		$formData['errors'] = $errors;

		return $formData;
	}
}
?>