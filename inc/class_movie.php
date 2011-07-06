<?php
/**
 * Class for movie management
 *
 * class name is in lowerclass to match table name ("commun" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Movies
 * @category Movies
 */
class movie extends commun {
	private $_sortTypes = array(
		'IF(ISNULL(sagaTitle),1,0), sagaTitle, movieSagaPosition, movieTitle, artistLastName, artistFirstName',
		'IF(ISNULL(sagaTitle),1,0) DESC, sagaTitle DESC, movieSagaPosition, movieTitle, artistLastName, artistFirstName',
		'movieTitle, IF(ISNULL(sagaTitle),1,0), sagaTitle, movieSagaPosition, artistLastName, artistFirstName',
		'movieTitle DESC, IF(ISNULL(sagaTitle),1,0), sagaTitle, movieSagaPosition, artistLastName, artistFirstName',
		'artistLastName, artistFirstName, IF(ISNULL(sagaTitle),1,0), sagaTitle, movieSagaPosition, movieTitle',
		'artistLastName DESC, artistFirstName, IF(ISNULL(sagaTitle),1,0), sagaTitle, movieSagaPosition, movieTitle',
		'storageRoom, storageType, storageColumn, storageLine, IF(ISNULL(sagaTitle),1,0), sagaTitle, movieSagaPosition, movieTitle, artistLastName, artistFirstName',
		'storageRoom DESC, storageType, storageColumn, storageLine, IF(ISNULL(sagaTitle),1,0), sagaTitle, movieSagaPosition, movieTitle, artistLastName, artistFirstName',
		'movieDate, IF(ISNULL(sagaTitle),1,0), sagaTitle, movieSagaPosition, movieTitle, artistLastName, artistFirstName',
		'movieDate DESC, IF(ISNULL(sagaTitle),1,0), sagaTitle, movieSagaPosition, movieTitle, artistLastName, artistFirstName',
	);

	// Constructor
	public function __construct(){
		//for "commun" ($this->db & co)
		parent::__construct();
	}

	/**
	 * @return array[][]
	 */
	public function getMovies(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getMovies = $this->db->prepare("
					SELECT  movieID, movieTitle, movieGenre, movieMediaType, movieLength, movieDate,
							sagaID, sagaTitle, movieSagaPosition, movieSagaSize, sagaSearchURL,
							storageID, storageRoom, storageType, storageColumn, storageLine,
							loanID, loanHolder, loanDate,
							artistID, artistFirstName, artistLastName
					FROM movies_view
					INNER JOIN movie_artists_view ON movieID = movieFK
					ORDER BY ".$this->_sortTypes[0]."
				");

				$getMovies->execute();

				$results = $this->_merge($getMovies->fetchAll());

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * dupplicate the movies_view table joinned with movie_artists_view into a myisam temporary table for full text search
	 * @param array $filters
	 * @return array[][]
	 */
	public function getMoviesByFullTextSearch(){
		try {
			//sanitize the form data
			$args = array(
				'movieSearch'			=> FILTER_SANITIZE_STRING,
				'movieTitleFilter'		=> FILTER_SANITIZE_STRING,
				'movieSagaFilter'		=> FILTER_SANITIZE_STRING,
				'movieArtistFilter'		=> FILTER_SANITIZE_STRING,
				'movieLoanFilter'		=> FILTER_SANITIZE_STRING,
				'movieStorageFilter'	=> FILTER_SANITIZE_NUMBER_INT,
				'movieSortType'			=> FILTER_SANITIZE_NUMBER_INT,
			);
			$filters = filter_var_array($_POST, $args);

			$filters['movieStorageFilter'] = filter_var($filters['movieStorageFilter'], FILTER_VALIDATE_INT, array('min_range' => 1));
			$filters['movieSortType'] = filter_var($filters['movieSortType'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 4));
			if( $filters['movieSortType'] === false ) $filters['movieSortType'] = 0;

			//construct the query
			$sql = " SELECT *";

			$sqlSelect = array();
			$sqlWhere = array();
			$sqlOrder = 'score DESC, ';
			$params = array();
			if( !empty($filters['movieSearch']) ){
				$sqlSelect = array(
					"MATCH(movieTitle) AGAINST (:searchS)",
					"MATCH(sagaTitle) AGAINST (:searchS)",
					"MATCH(artistFullName) AGAINST (:searchS)",
					"MATCH(loanHolder) AGAINST (:searchS)",
				);
				$sqlWhere = array(
					"MATCH(movieTitle) AGAINST (:searchW)",
					"MATCH(sagaTitle) AGAINST (:searchW)",
					"MATCH(artistFullName) AGAINST (:searchW)",
					"MATCH(loanHolder) AGAINST (:searchW)",
				);
				$params[':searchS'] = $this->prepareForFullTextQuery($filters['movieSearch']);
				$params[':searchW'] = $params[':searchS'];
			}
			if( !empty($filters['movieTitleFilter']) ){
				$sqlSelect[] = "MATCH(movieTitle) AGAINST (:movieTitleS)";
				$sqlWhere[] = "MATCH(movieTitle) AGAINST (:movieTitleW)";
				$params[':movieTitleS'] = $this->prepareForFullTextQuery($filters['movieTitleFilter']);
				$params[':movieTitleW'] = $params[':movieTitleS'];
			}
			if( !empty($filters['movieSagaFilter']) ){
				$sqlSelect[] = "MATCH(sagaTitle) AGAINST (:sagaTitleS)";
				$sqlWhere[] = "MATCH(sagaTitle) AGAINST (:sagaTitleW)";
				$params[':sagaTitleS'] = $this->prepareForFullTextQuery($filters['movieSagaFilter']);
				$params[':sagaTitleW'] = $params[':sagaTitleS'];
			}
			if( !empty($filters['movieArtistFilter']) ){
				$sqlSelect[] = "MATCH(artistFullName) AGAINST (:artistS)";
				$sqlWhere[] = "MATCH(artistFullName) AGAINST (:artistW)";
				$params[':artistS'] = $this->prepareForFullTextQuery($filters['movieArtistFilter']);
				$params[':artistW'] = $params[':artistS'];
			}
			if( !empty($filters['movieLoanFilter']) ){
				$sqlSelect[] = "MATCH(loanHolder) AGAINST (:loanS)";
				$sqlWhere[] = "MATCH(loanHolder) AGAINST (:loanW)";
				$params[':loanS'] = $this->prepareForFullTextQuery($filters['movieLoanFilter']);
				$params[':loanW'] = $params[':loanS'];
			}
			if( !empty($filters['movieStorageFilter']) ){
				$sqlWhere[] = "storageID = :storageID";
				$params[':storageID'] = $filters['storageID'];
			}

			$sql = " SELECT mft.*, ma.*"
				  .( !empty($sqlSelect) ? ', '.implode(' + ', $sqlSelect).' AS score' : '')
				  ." FROM movies_view_ft mft"
				  ." INNER JOIN movie_artists_view_ft maft ON movieID = maft.movieFK "
				  ." LEFT JOIN movie_artists_view ma ON movieID = ma.movieFK "
				  ." WHERE 1 "
				  .( !empty($sqlWhere) ? ' AND '.implode(' AND ', $sqlWhere) : '')
				  ." ORDER BY "
				  .( !empty($sqlSelect) ? $sqlOrder : '')
				  .$this->_sortTypes[$filters['movieSortType']];

			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			if( empty($params) ) $stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $sql);
			else $stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $sql, serialize($params));
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them

				//drop the temporary table if it exists
				$destroyTmpTable = $this->db->prepare("DROP TEMPORARY TABLE IF EXISTS movies_view_ft");
				$destroyTmpTable->execute();
				$destroyTmpTable = $this->db->prepare("DROP TEMPORARY TABLE IF EXISTS movie_artists_view_ft");
				$destroyTmpTable->execute();

				//create the temporary table
				$tmpTable = $this->db->prepare("
					CREATE TEMPORARY TABLE movies_view_ft AS
					SELECT  movieID, movieTitle, movieGenre, movieMediaType, movieLength, movieDate,
							sagaID, sagaTitle, movieSagaPosition, movieSagaSize, sagaSearchURL,
							storageID, storageRoom, storageType, storageColumn, storageLine,
							loanID, loanHolder, loanDate
					FROM movies_view
				");
				$tmpTable->execute();

				//add the fulltext index
				$indexTmpTable = $this->db->prepare("
					ALTER TABLE movies_view_ft ENGINE = MyISAM,
					ADD FULLTEXT INDEX movieFT (movieTitle),
					ADD FULLTEXT INDEX sagaFT (sagaTitle),
					ADD FULLTEXT INDEX loanFT (loanHolder),
					ADD INDEX storageID (storageID),
					ADD INDEX movieID (movieID)
				");
				$indexTmpTable->execute();

				//create the temporary table
				$tmpTable = $this->db->prepare("
					CREATE TEMPORARY TABLE movie_artists_view_ft AS
					SELECT  movieFK, artistID, CONCAT(artistFirstName, ' ', artistLastName) AS artistFullName
					FROM movie_artists_view
				");
				$tmpTable->execute();

				//add the fulltext index
				$indexTmpTable = $this->db->prepare("
					ALTER TABLE movie_artists_view_ft ENGINE = MyISAM,
					ADD FULLTEXT INDEX artistFT (artistFullName),
					ADD INDEX movieFK (movieFK)
				");
				$indexTmpTable->execute();


				$getMovies = $this->db->prepare($sql);

				$getMovies->execute( $params );

				$results = $this->_merge($getMovies->fetchAll());

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
	private function _merge( $results ){
		if( !empty($results) ){
			$currentId = null;
			$merged = array();
			foreach( $results as $r ){

				if( $currentId != $r['movieID'] && !isset($merged[$r['movieID']]) ){
					$currentId = $r['movieID'];

					$merged[$r['movieID']]['movieID'] = $r['movieID'];
					$merged[$r['movieID']]['movieTitle'] = $r['movieTitle'];
					$merged[$r['movieID']]['movieGenre'] = $r['movieGenre'];
					$merged[$r['movieID']]['movieMediaType'] = $r['movieMediaType'];
					$merged[$r['movieID']]['movieLength'] = $r['movieLength'];
					$merged[$r['movieID']]['sagaID'] = $r['sagaID'];
					$merged[$r['movieID']]['sagaTitle'] = $r['sagaTitle'];
					$merged[$r['movieID']]['movieSagaPosition'] = $r['movieSagaPosition'];
					$merged[$r['movieID']]['movieSagaSize'] = $r['movieSagaSize'];
					$merged[$r['movieID']]['sagaSearchURL'] = $r['sagaSearchURL'];
					$merged[$r['movieID']]['storageID'] = $r['storageID'];
					$merged[$r['movieID']]['storageRoom'] = $r['storageRoom'];
					$merged[$r['movieID']]['storageType'] = $r['storageType'];
					$merged[$r['movieID']]['storageColumn'] = $r['storageColumn'];
					$merged[$r['movieID']]['storageLine'] = $r['storageLine'];
					$merged[$r['movieID']]['loanID'] = $r['loanID'];
					$merged[$r['movieID']]['loanHolder'] = $r['loanHolder'];
					$merged[$r['movieID']]['loanDate'] = $r['loanDate'];
					$merged[$r['movieID']]['artists'] = array();
				}

				$merged[$r['movieID']]['artists'][$r['artistID']] = array(
					'artistID' => $r['artistID'],
					'artistFirstName' => $r['artistFirstName'],
					'artistLastName' => $r['artistLastName'],
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
	public function getMovieDateById( $id ){
		try {
			$getMovieDateById = $this->db->prepare("
				SELECT movieDate AS lastModified
				FROM movie
				WHERE movieID = :id
			");

			$getMovieDateById->execute( array( ':id' => $id ) );

			$results = $getMovieDateById->fetch();
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
	public function getMovieCoverById( $id ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler('covers', $stashFileSystem);
			$stash = StashBox::getCache('covers', get_class($this), $id);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getMovieCoverById = $this->db->prepare("
					SELECT movieCover AS cover
					FROM movie
					WHERE movieID = :id
				");

				$getMovieCoverById->execute( array( ':id' => $id ) );

				$results = $getMovieCoverById->fetch();
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
	public function getMovieById( $id ){
		try {
			$getMovieById = $this->db->prepare("
				SELECT  movieID, movieTitle, movieGenre, movieMediaType, movieLength, movieDate, movieCover,
						sagaID, sagaTitle, movieSagaPosition, movieSagaSize, sagaSearchURL,
						storageID, storageRoom, storageType, storageColumn, storageLine,
						loanID, loanHolder, loanDate,
						artistID, artistFirstName, artistLastName, artistWebSite, artistSearchURL
				FROM movies_view
				INNER JOIN movie_artists_view ON movieID = movieFK
				WHERE movieID = :id
			");

			$getMovieById->execute( array( ':id' => $id ) );

			$results = $this->_merge($getMovieById->fetchAll());

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
	public function getMoviesGenres( $returnTs = false, $tsOnly = false ){
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
				$getMoviesGenres = $this->db->prepare("
					SELECT movieGenre as value
					FROM movie
					GROUP BY movieGenre
					ORDER BY movieGenre
				");

				$getMoviesGenres->execute();

				$results = $getMoviesGenres->fetchAll();

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
	public function getMoviesMediaTypes( $returnTs = false, $tsOnly = false ){
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
				$getMoviesMediaTypes = $this->db->prepare("
					SELECT movieMediaType as value
					FROM movie
					GROUP BY movieMediaType
					ORDER BY movieMediaType
				");

				$getMoviesMediaTypes->execute();

				$results = $getMoviesMediaTypes->fetchAll();

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
	public function getMoviesTitleForFilterList( $returnTs = false, $tsOnly = false ){
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
				$getMoviesTitleForFilterList = $this->db->prepare("
					SELECT movieTitle as value
					FROM movie
					GROUP BY movieTitle
					ORDER BY movieTitle
				");

				$getMoviesTitleForFilterList->execute();

				$results = $getMoviesTitleForFilterList->fetchAll();

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
	 * @return integer or null
	 */
	private function _manageSagaLink( $data ){
		//checking if saga already exists
		if( !is_null($data['saga']) ){
			$oSaga = new saga();
			//checking if saga already exists
			$sagaID = $oSaga->isSaga($data['saga']);
			if( $sagaID === false ){
				$sagaID = $oSaga->addSaga( array(
					'title' => $data['saga'],
					'searchURL' => null,
				) );
			}
		} else {
			$sagaID = null;
		}

		return $sagaID;
	}

	/**
	 * @param array $data
	 * @param integer $movieID
	 */
	private function _manageArtistLink( $data, $movieID ){
		//artist link deletion
		if( isset($data['id']) ){
			$delArtistsLinks = $this->db->prepare("
				DELETE
				FROM movies_artists
				WHERE movieFK = :id
			");

			$delArtistsLinks->execute( array( ':id' => $data['id'] ) );
		}

		$addArtistLink = $this->db->prepare("
			INSERT INTO movies_artists (movieFK, artistFK)
			VALUES (:movieID, :artistID)
		");

		$oArtist = new artist();
		foreach ( $data['artists'] as $artist ){
			//checking if artist already exists
			$artistID = $oArtist->isArtist($artist);
			if( $artistID === false ){
				//get first and last name
				$fullName = explode(' ', $artist);
				$lastName = array_pop($fullName);
				$firstName = implode(' ', $fullName);

				$artistID = $oArtist->addArtist( array(
					'firstName' => trim($firstName),
					'lastName' => trim($lastName),
					'photo' => null,
				) );
			}
			if( empty($artistID) ) throw new PDOException('Bad artist id for '.$artist.'.');

			$addArtistLink->execute(
				array(
					':movieID' => $movieID,
					':artistID' => $artistID,
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

		$toClean = array('movie', 'artist', 'saga', 'storage', 'loan');
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
	public function addMovie( $data ){
		try {
			set_error_handler("errorHandler"); //handle any error and throw exception, forcing transaction rollback

			$this->db->beginTransaction(); //needed for rollback

			//saga
			$sagaID = $this->_manageSagaLink( $data );

			//movie
			$addMovie = $this->db->prepare("
				INSERT INTO movie (movieTitle, movieCover, movieGenre, movieMediaType, movieLength, movieSagaFK, movieSagaPosition, movieStorageFK, movieLoanFK, movieDate)
				VALUES (:title, :cover, :genre, :mediaType, :length, :saga, :position, :storage, NULL, NOW())
			");

			$addMovie->execute(
				array(
					':title' => $data['title'],
					':cover' => $data['cover'],
					':genre' => $data['genre'],
					':mediaType' => $data['mediaType'],
					':length' => $data['length'],
					':saga' => $sagaID,
					':position' => $data['position'],
					':storage' => $data['storage'],
				)
			);

			$movieID = $this->db->lastInsertId();

			if( empty($movieID) ) throw new PDOException('Bad movie id.');

			//artist(s)
			$this->_manageArtistLink( $data, $movieID );

			$this->db->commit(); //transaction validation

			restore_error_handler();

			$this->_cleanCaches();

			//create stash cache
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			$stash = new Stash($stashFileSystem);
			$stash->setupKey('covers', get_class($this), $movieID);
			$stash->store(base64_decode($data['cover']), STASH_EXPIRE);

			return $movieID;

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
				UPDATE movie SET movieLoanFK = :fk WHERE movieID = :id
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
	public function updMovie( $data ){
		try {
			set_error_handler("errorHandler"); //handle any error and throw exception, forcing transaction rollback

			$this->db->beginTransaction(); //needed for rollback

			//saga
			$sagaID = $this->_manageSagaLink( $data );

			//movie
			$updMovie = $this->db->prepare("
				UPDATE movie
				SET movieTitle = :title,
					".( isset($data['cover']) && !empty($data['cover']) ? "movieCover = :cover," : "")."
					movieGenre = :genre,
					movieMediaType = :mediaType,
					movieLength = :length,
					movieSagaFK = :saga,
					movieSagaPosition = :position,
					movieStorageFK = :storage,
					movieDate = NOW()
				WHERE movieID = :id
			");

			$params = array(
				':id' => $data['id'],
				':title' => $data['title'],
				':genre' => $data['genre'],
				':mediaType' => $data['mediaType'],
				':length' => $data['length'],
				':saga' => $sagaID,
				':position' => $data['position'],
				':storage' => $data['storage']
			);

			$cleanCover = false;
			if( isset($data['cover']) && !empty($data['cover']) ){
				$params[':cover'] = $data['cover'];

				//update stash cache
				$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
				$stash = new Stash($stashFileSystem);
				$stash->setupKey('covers', get_class($this), $data['id']);
				$stash->store(base64_decode($data['cover']), STASH_EXPIRE);
			}

			$updMovie->execute( $params );

			//artist(s)
			$this->_manageArtistLink( $data, $data['id'] );

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
	public function delMovie( $id ){
		try {
			set_error_handler("errorHandler"); //handle any error and throw exception, forcing transaction rollback

			$this->db->beginTransaction(); //needed for rollback

			//loan deletion
			$this->_delLinkedLoan( $id, true );

			//movie deletion
			$delMovie = $this->db->prepare("
				DELETE
				FROM movie
				WHERE movieID = :id
			");

			$delMovie->execute( array( ':id' => $id ) );

			if( isset($_SESSION['images']['movies'][$id]) ) unset($_SESSION['images']['movies'][$id]);

			//artist link deletion
			$delArtistsLinks = $this->db->prepare("
				DELETE
				FROM movies_artists
				WHERE movieFK = :id
			");

			$delArtistsLinks->execute( array( ':id' => $id ) );

			$this->db->commit(); //transaction validation

			restore_error_handler();

			$this->_cleanCaches();
			$this->cleanImageCache($id);

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
				WHERE loanID = (SELECT movieID FROM movie WHERE movieID = :id)
			");

			$delLinkedLoan->execute( array( ':id' => $id ) );

		} catch ( PDOException $e ){
			//delMovie() transaction need to know if there was an error for transaction rollback
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
				SELECT COUNT(movieID) AS verif
				FROM movie
				WHERE movieID = :id");

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
	public function movieUnicityCheck( $data ) {
		try {
			$verif = false;

			$movieUnicityCheck = $this->db->prepare("
				SELECT movieID
				FROM movie
				".( !is_null($data['saga']) ? "LEFT JOIN saga ON sagaTitle = :saga" : "")."
				WHERE movieTitle = :title
				AND movieMediaType = :mediaType
				".( !is_null($data['saga']) ? "" : "AND movieSagaFK IS NULL")."
			");

			$params = array(
				':title' => $data['title'],
				':mediaType' => $data['mediaType'],
			);

			if( !is_null($data['saga']) ) $params[':saga'] = $data['saga'];

			$movieUnicityCheck->execute( $params );

			$result = $movieUnicityCheck->fetch();
			if( empty($result) ) {
				return true;
			} elseif( isset($data['id']) ){
				return $result['movieID'] == $data['id'];
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
		$artistFields = array();

		$args = array(
			'action'	=> FILTER_SANITIZE_STRING,
			'id'		=> FILTER_SANITIZE_NUMBER_INT,
			'title'		=> FILTER_SANITIZE_STRING,
			'genre'		=> FILTER_SANITIZE_STRING,
			'mediaType'	=> FILTER_SANITIZE_STRING,
			'length'	=> FILTER_SANITIZE_NUMBER_INT,
			'cover'		=> FILTER_SANITIZE_STRING,
			'saga'		=> FILTER_SANITIZE_STRING,
			'position'	=> FILTER_SANITIZE_STRING,
			'storage'	=> FILTER_SANITIZE_NUMBER_INT,
		);

		foreach( $args as $field => $validation ){
			if( !filter_has_var(INPUT_POST, $field) ){
				$errors[] = array('global', 'Le champ '.$field.' est manquant.', 'error');
			}
		}

		//artists with variable name save
		$indice = null;
		foreach( $_POST as $key => $val ){
			if( strpos($key, 'artist_') !== false ){
				$indice = substr($key, strpos($key, '_') + 1);

				$args['artist_'.$indice] = FILTER_SANITIZE_STRING;
				$artistFields[] = 'artist_'.$indice;
			}
		}

		if( empty($errors) ){

			$formData = filter_var_array($_POST, $args);

			foreach( $formData as $field => $value ){
				${$field} = $value;
			}

			//movie id
			//errors are set to #movieTitle because #movieID is hidden
			if( $action == 'update' ){
				if( is_null($id) || $id === false ){
					$errors[] = array('movieTitle', 'Identifiant incorrect.', 'error');
				} else {
					$id = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $id === false ){
						$errors[] = array('movieTitle', 'Identifiant du livre incorrect.', 'error');
					} else {
						//check if id exists in DB
						if( $this->exists($id) ){
							$formData['id'] = $id;
						} else {
							$errors[] = array('movieTitle', 'Identifiant du livre inconnu.', 'error');
						}
					}
				}
			}

			if( $action == 'update' || $action == 'add' ){
				//title
				if( is_null($title) || $title === false ){
					$errors[] = array('movieTitle', 'Titre incorrect.', 'error');
				} elseif( empty($title) ){
					$errors[] = array('movieTitle', 'Le titre est requis.', 'required');
				} else {
					$formData['title'] = trim($title);
				}

				//genre
				if( is_null($genre) || $genre === false ){
					$errors[] = array('movieGenre', 'Genre incorrect.', 'error');
				} elseif( empty($genre) ){
					$errors[] = array('movieGenre', 'Le genre est requis.', 'required');
				} else {
					$formData['genre'] = str_replace('  ', ' ', str_replace('|', '', trim($genre))); //cleaning imdb format
				}

				//media type
				if( is_null($mediaType) || $mediaType === false ){
					$errors[] = array('movieMediaType', 'Format incorrect.', 'error');
				} elseif( empty($mediaType) ){
					$errors[] = array('movieMediaType', 'Le format est requis.', 'required');
				} else {
					$formData['mediaType'] = trim($mediaType);
				}

				//length
				if( is_null($length) || $length === false ){
					$errors[] = array('movieLength', 'Durée incorrect.', 'error');
				} elseif( empty($length) ){
					$errors[] = array('movieLength', 'La durée est requise.', 'required');
				} else {
					$formData['length'] = trim($length);
				}

				//cover
				if( is_null($cover) || $cover === false ){
					$errors[] = array('movieCoverStatus', 'Couverture incorrecte.', 'error');
				} else {
					if( $action == 'add' ){
						if( empty($cover) ){
							$errors[] = array('movieCoverStatus', 'La couverture est requise.', 'required');
						} elseif( $action == 'add' && !file_exists(UPLOAD_COVER_PATH.$cover) ){
							$errors[] = array('movieCoverStatus', 'Couverture non trouvée.', 'error');
						} else {
							$formData['cover'] = chunk_split( base64_encode( file_get_contents( UPLOAD_COVER_PATH.$cover ) ) );
						}
					} else { //update
						if( !empty($cover) ){
							if( !file_exists(UPLOAD_COVER_PATH.$cover) ){
								$errors[] = array('movieCoverStatus', 'Couverture non trouvée.', 'error');
							} else {
								$formData['cover'] = chunk_split( base64_encode( file_get_contents( UPLOAD_COVER_PATH.$cover ) ) );
							}
						}
					}
				}

				//saga title
				if( is_null($saga) || $saga === false ){
					$errors[] = array('movieSagaTitle', 'Saga incorrecte.', 'error');
				} elseif( !empty($saga) ){
					$formData['saga'] = trim($saga);
				} else {
					$formData['saga'] = null;
				}


				//unicity check for title + mediaType + saga
				if( empty($errors) && $action == 'add' ){
					if( !$this->movieUnicityCheck($formData) ){
						$errors[] = array('movieTitle', 'Film déjà présent. (unicité sur titre + format + saga)', 'error');
					}
				}

				//saga position
				if( isset($formData['saga']) && !is_null($formData['saga']) ){ //requis si une saga est définie
					if( is_null($saga) || $saga === false ){
						$errors[] = array('movieSagaPosition', 'Position incorrecte.', 'error');
					} elseif( empty($position) ){
						$errors[] = array('movieSagaPosition', 'La position est requise.', 'required');
					} else {
						$position = filter_var($position, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $position === false ){
							$errors[] = array('movieSagaPosition', 'La position doit être un chiffre entier supérieur ou égal à 1.', 'error');
						}

						$formData['position'] = trim($position);
					}
				} else {
					$formData['position'] = null;
				}

				//storage
				if( empty($storage) ){
					$errors[] = array('movieStorage', 'Le rangement est requis.', 'required');
				}
				if( is_null($storage) || $storage === false ){
					$errors[] = array('movieStorage', 'Rangement incorrect.', 'error');
				} elseif( empty($storage) ){
					$errors[] = array('movieStorage', 'Le rangement est requis.', 'required');
				} else {
					$formData['storage'] = trim($storage);
				}

				//artist
				$artists = array();
				$atLeastOneArtist = false;
				foreach( $artistFields as $field ){
					if( is_null(${$field}) || ${$field} === false ){
						$errors[] = array($field, 'Auteur incorrect.', 'error');
					} elseif( !empty(${$field}) ){
						$artists[] = trim(${$field});
						$atLeastOneArtist = true;
					}
				}

				if( !$atLeastOneArtist ){
					$errors[] = array('movieArtist_1', 'Au moins un artiste est requis.', 'required');
				} else {
					$formData['artists'] = $artists;
				}
			}
		}
		$formData['errors'] = $errors;

		return $formData;
	}
}
?>