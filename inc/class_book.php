<?php
/**
 * Class for book management
 *
 * class name is in lowerclass to match table name ("commun" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Books
 * @category Books
 */
class book extends commun {
	private $_sortTypes = array(
		'IF(ISNULL(sagaTitle),1,0), sagaTitle, bookSagaPosition, bookTitle, authorLastName, authorFirstName',
		'IF(ISNULL(sagaTitle),1,0) DESC, sagaTitle DESC, bookSagaPosition, bookTitle, authorLastName, authorFirstName',
		'bookTitle, IF(ISNULL(sagaTitle),1,0), sagaTitle, bookSagaPosition, authorLastName, authorFirstName',
		'bookTitle DESC, IF(ISNULL(sagaTitle),1,0), sagaTitle, bookSagaPosition, authorLastName, authorFirstName',
		'authorLastName, authorFirstName, IF(ISNULL(sagaTitle),1,0), sagaTitle, bookSagaPosition, bookTitle',
		'authorLastName DESC, authorFirstName, IF(ISNULL(sagaTitle),1,0), sagaTitle, bookSagaPosition, bookTitle',
		'storageRoom, storageType, storageColumn, storageLine, IF(ISNULL(sagaTitle),1,0), sagaTitle, bookSagaPosition, bookTitle, authorLastName, authorFirstName',
		'storageRoom DESC, storageType, storageColumn, storageLine, IF(ISNULL(sagaTitle),1,0), sagaTitle, bookSagaPosition, bookTitle, authorLastName, authorFirstName',
		'bookDate, IF(ISNULL(sagaTitle),1,0), sagaTitle, bookSagaPosition, bookTitle, authorLastName, authorFirstName',
		'bookDate DESC, IF(ISNULL(sagaTitle),1,0), sagaTitle, bookSagaPosition, bookTitle, authorLastName, authorFirstName',
	);

	// Constructor
	public function __construct(){
		//for "commun" ($this->db & co)
		parent::__construct();
	}

	/**
	 * @return array[][]
	 */
	public function getBooks(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);

			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getBooks = $this->db->prepare("
					SELECT  bookID, bookTitle, bookSize, bookDate,
							sagaID, sagaTitle, bookSagaPosition, bookSagaSize, sagaSearchURL,
							storageID, storageRoom, storageType, storageColumn, storageLine,
							loanID, loanHolder, loanDate,
							authorID, authorFirstName, authorLastName, authorWebSite, authorSearchURL
					FROM books_view
					INNER JOIN book_authors_view ON bookID = bookFK
					ORDER BY ".$this->_sortTypes[0]."
				");

				$getBooks->execute();

				$results = $this->_merge($getBooks->fetchAll());

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * dupplicate the books_view table joinned with book_authors_view into a myisam temporary table for full text search
	 * @param array $filters
	 * @return array[][]
	 */
	public function getBooksByFullTextSearch(){
		try {
			//sanitize the form data
			$args = array(
				'bookSearch'			=> FILTER_SANITIZE_STRING,
				'bookTitleFilter'		=> FILTER_SANITIZE_STRING,
				'bookSagaFilter'		=> FILTER_SANITIZE_STRING,
				'bookAuthorFilter'		=> FILTER_SANITIZE_STRING,
				'bookLoanFilter'		=> FILTER_SANITIZE_STRING,
				'bookStorageFilter'	=> FILTER_SANITIZE_NUMBER_INT,
				'bookSortType'			=> FILTER_SANITIZE_NUMBER_INT,
			);
			$filters = filter_var_array($_POST, $args);

			$filters['bookStorageFilter'] = filter_var($filters['bookStorageFilter'], FILTER_VALIDATE_INT, array('min_range' => 1));
			$filters['bookSortType'] = filter_var($filters['bookSortType'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 4));
			if( $filters['bookSortType'] === false ) $filters['bookSortType'] = 0;

			//construct the query
			$sql = " SELECT *";

			$sqlSelect = array();
			$sqlWhere = array();
			$sqlOrder = 'score DESC, ';
			$params = array();
			if( !empty($filters['bookSearch']) ){
				$sqlSelect = array(
					"MATCH(bookTitle) AGAINST (:searchS)",
					"MATCH(sagaTitle) AGAINST (:searchS)",
					"MATCH(authorFullName) AGAINST (:searchS)",
					"MATCH(loanHolder) AGAINST (:searchS)",
				);
				$sqlWhere = array(
					"MATCH(bookTitle) AGAINST (:searchW)",
					"MATCH(sagaTitle) AGAINST (:searchW)",
					"MATCH(authorFullName) AGAINST (:searchW)",
					"MATCH(loanHolder) AGAINST (:searchW)",
				);
				$params[':searchS'] = $this->prepareForFullTextQuery($filters['bookSearch']);
				$params[':searchW'] = $params[':searchS'];
			}
			if( !empty($filters['bookTitleFilter']) ){
				$sqlSelect[] = "MATCH(bookTitle) AGAINST (:bookTitleS)";
				$sqlWhere[] = "MATCH(bookTitle) AGAINST (:bookTitleW)";
				$params[':bookTitleS'] = $this->prepareForFullTextQuery($filters['bookTitleFilter']);
				$params[':bookTitleW'] = $params[':bookTitleS'];
			}
			if( !empty($filters['bookSagaFilter']) ){
				$sqlSelect[] = "MATCH(sagaTitle) AGAINST (:sagaTitleS)";
				$sqlWhere[] = "MATCH(sagaTitle) AGAINST (:sagaTitleW)";
				$params[':sagaTitleS'] = $this->prepareForFullTextQuery($filters['bookSagaFilter']);
				$params[':sagaTitleW'] = $params[':sagaTitleS'];
			}
			if( !empty($filters['bookAuthorFilter']) ){
				$sqlSelect[] = "MATCH(authorFullName) AGAINST (:authorS)";
				$sqlWhere[] = "MATCH(authorFullName) AGAINST (:authorW)";
				$params[':authorS'] = $this->prepareForFullTextQuery($filters['bookAuthorFilter']);
				$params[':authorW'] = $params[':authorS'];
			}
			if( !empty($filters['bookLoanFilter']) ){
				$sqlSelect[] = "MATCH(loanHolder) AGAINST (:loanS)";
				$sqlWhere[] = "MATCH(loanHolder) AGAINST (:loanW)";
				$params[':loanS'] = $this->prepareForFullTextQuery($filters['bookLoanFilter']);
				$params[':loanW'] = $params[':loanS'];
			}
			if( !empty($filters['bookStorageFilter']) ){
				$sqlWhere[] = "storageID = :storageID";
				$params[':storageID'] = $filters['bookStorageFilter'];
			}

			$sql = " SELECT bft.*, ba.*"
				  .( !empty($sqlSelect) ? ', '.implode(' + ', $sqlSelect).' AS score' : '')
				  ." FROM books_view_ft bft"
				  ." INNER JOIN book_authors_view_ft baft ON bookID = baft.bookFK "
				  ." LEFT JOIN book_authors_view ba ON bookID = ba.bookFK "
				  ." WHERE 1 "
				  .( !empty($sqlWhere) ? ' AND '.implode(' AND ', $sqlWhere) : '')
				  ." ORDER BY "
				  .( !empty($sqlSelect) ? $sqlOrder : '')
				  .$this->_sortTypes[$filters['bookSortType']];

			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			if( empty($params) ) $stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $sql);
			else $stash = StashBox::getCache(get_class( $this ), __FUNCTION__, $sql, $params);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them

				//drop the temporary table if it exists
				$destroyTmpTable = $this->db->prepare("DROP TEMPORARY TABLE IF EXISTS books_view_ft");
				$destroyTmpTable->execute();
				$destroyTmpTable = $this->db->prepare("DROP TEMPORARY TABLE IF EXISTS book_authors_view_ft");
				$destroyTmpTable->execute();

				//create the temporary table
				$tmpTable = $this->db->prepare("
					CREATE TEMPORARY TABLE books_view_ft AS
					SELECT  bookID, bookTitle, bookSize, bookDate,
							sagaID, sagaTitle, bookSagaPosition, bookSagaSize, sagaSearchURL,
							storageID, storageRoom, storageType, storageColumn, storageLine,
							loanID, loanHolder, loanDate
					FROM books_view
				");
				$tmpTable->execute();

				//add the fulltext index
				$indexTmpTable = $this->db->prepare("
					ALTER TABLE books_view_ft ENGINE = MyISAM,
					ADD FULLTEXT INDEX bookFT (bookTitle),
					ADD FULLTEXT INDEX sagaFT (sagaTitle),
					ADD FULLTEXT INDEX loanFT (loanHolder),
					ADD INDEX storageID (storageID),
					ADD INDEX bookID (bookID)
				");
				$indexTmpTable->execute();

				//create the temporary table
				$tmpTable = $this->db->prepare("
					CREATE TEMPORARY TABLE book_authors_view_ft AS
					SELECT  bookFK, authorID, CONCAT(authorFirstName, ' ', authorLastName) AS authorFullName
					FROM book_authors_view
				");
				$tmpTable->execute();

				//add the fulltext index
				$indexTmpTable = $this->db->prepare("
					ALTER TABLE book_authors_view_ft ENGINE = MyISAM,
					ADD FULLTEXT INDEX authorFT (authorFullName),
					ADD INDEX bookFK (bookFK)
				");
				$indexTmpTable->execute();

				$getBooks = $this->db->prepare($sql);

				$getBooks->execute( $params );

				$results = $this->_merge($getBooks->fetchAll());

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

				if( $currentId != $r['bookID'] && !isset($merged[$r['bookID']]) ){
					$currentId = $r['bookID'];

					$merged[$r['bookID']]['bookID'] = $r['bookID'];
					$merged[$r['bookID']]['bookTitle'] = $r['bookTitle'];
					$merged[$r['bookID']]['bookSize'] = $r['bookSize'];
					$merged[$r['bookID']]['sagaID'] = $r['sagaID'];
					$merged[$r['bookID']]['sagaTitle'] = $r['sagaTitle'];
					$merged[$r['bookID']]['bookSagaPosition'] = $r['bookSagaPosition'];
					$merged[$r['bookID']]['bookSagaSize'] = $r['bookSagaSize'];
					$merged[$r['bookID']]['sagaSearchURL'] = $r['sagaSearchURL'];
					$merged[$r['bookID']]['storageID'] = $r['storageID'];
					$merged[$r['bookID']]['storageRoom'] = $r['storageRoom'];
					$merged[$r['bookID']]['storageType'] = $r['storageType'];
					$merged[$r['bookID']]['storageColumn'] = $r['storageColumn'];
					$merged[$r['bookID']]['storageLine'] = $r['storageLine'];
					$merged[$r['bookID']]['loanID'] = $r['loanID'];
					$merged[$r['bookID']]['loanHolder'] = $r['loanHolder'];
					$merged[$r['bookID']]['loanDate'] = $r['loanDate'];
					$merged[$r['bookID']]['authors'] = array();
				}

				$merged[$r['bookID']]['authors'][$r['authorID']] = array(
					'authorID' => $r['authorID'],
					'authorFirstName' => $r['authorFirstName'],
					'authorLastName' => $r['authorLastName'],
					'authorWebSite' => $r['authorWebSite'],
					'authorSearchURL' => $r['authorSearchURL'],
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
	public function getBookDateById( $id ){
		try {
			$getBookDateById = $this->db->prepare("
				SELECT bookDate AS lastModified
				FROM book
				WHERE bookID = :id
			");

			$getBookDateById->execute( array( ':id' => $id ) );

			$results = $getBookDateById->fetch();
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
	public function getBookCoverById( $id ){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler('covers', $stashFileSystem);
			$stash = StashBox::getCache('covers', get_class($this), $id);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getBookCoverById = $this->db->prepare("
					SELECT  bookCover AS cover
					FROM book
					WHERE bookID = :id
				");

				$getBookCoverById->execute( array( ':id' => $id ) );

				$results = $getBookCoverById->fetch();
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
	public function getBookById( $id ){
		try {
			$getBookById = $this->db->prepare("
				SELECT  bookID, bookTitle, bookSize,
						sagaID, sagaTitle, bookSagaPosition, bookSagaSize, sagaSearchURL,
						storageID, storageRoom, storageType, storageColumn, storageLine,
						loanID, loanHolder, loanDate,
						authorID, authorFirstName, authorLastName, authorWebSite, authorSearchURL
				FROM books_view
				INNER JOIN book_authors_view ON bookID = bookFK
				WHERE bookID = :id
			");

			$getBookById->execute( array( ':id' => $id ) );

			$results = $this->_merge($getBookById->fetchAll());

			return $results[$id];

		} catch ( PDOException $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * @return array[]
	 */
	public function getBooksSizes(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getBooksSizes = $this->db->prepare("
					SELECT bookSize as value
					FROM book
					GROUP BY bookSize
					ORDER BY bookSize
				");

				$getBooksSizes->execute();

				$results = $getBooksSizes->fetchAll();

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
	public function getBooksTitleForFilterList(){
		try {
			//stash cache init
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			StashBox::setHandler($stashFileSystem);

			StashManager::setHandler(get_class( $this ), $stashFileSystem);
			$stash = StashBox::getCache(get_class( $this ), __FUNCTION__);
			$results = $stash->get();
			if( $stash->isMiss() ){ //cache not found, retrieve values from database and stash them
				$getBooksTitleForFilterList = $this->db->prepare("
					SELECT bookTitle as value
					FROM book
					GROUP BY bookTitle
					ORDER BY bookTitle
				");

				$getBooksTitleForFilterList->execute();

				$results = $getBooksTitleForFilterList->fetchAll();

				if( !empty($results) ) $stash->store($results, STASH_EXPIRE);
			}

			return $results;

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
	 * @param integer $bookID
	 */
	private function _manageAuthorLink( $data, $bookID ){
		//author link deletion
		if( isset($data['id']) ){
			$delAuthorsLinks = $this->db->prepare("
				DELETE
				FROM books_authors
				WHERE bookFK = :id
			");

			$delAuthorsLinks->execute( array( ':id' => $data['id'] ) );
		}

		$addAuthorLink = $this->db->prepare("
			INSERT INTO books_authors (bookFK, authorFK)
			VALUES (:bookID, :authorID)
		");

		$oAuthor = new author();
		foreach ( $data['authors'] as $author ){
			//checking if author already exists
			$authorID = $oAuthor->isAuthor($author);
			if( $authorID === false ){
				//get first and last name
				$fullName = explode(' ', $author);
				$lastName = array_pop($fullName);
				$firstName = implode(' ', $fullName);

				$authorID = $oAuthor->addAuthor( array(
					'firstName' => trim($firstName),
					'lastName' => trim($lastName),
					'webSite' => null,
					'searchURL' => null,
				) );
			}
			if( empty($authorID) ) throw new PDOException('Bad author id for '.$author.'.');

			$addAuthorLink->execute(
				array(
					':bookID' => $bookID,
					':authorID' => $authorID,
				)
			);
		}
	}

	/**
	 * clean the session for the book related lists
	 */
	private function _cleanCaches(){
		//clear stash cache
		$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
		$stash = new Stash($stashFileSystem);

		//update caches timestamps
		$ts = new list_timestamp();

		$toClean = array('book', 'author', 'saga', 'storage', 'loan');
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
	public function addBook( $data ){
		try {
			set_error_handler("errorHandler"); //handle any error and throw exception, forcing transaction rollback

			$this->db->beginTransaction(); //needed for rollback

			//saga
			$sagaID = $this->_manageSagaLink( $data );

			//book
			$addBook = $this->db->prepare("
				INSERT INTO book (bookTitle, bookCover, bookSize, bookSagaFK, bookSagaPosition, bookStorageFK, bookLoanFK, bookDate)
				VALUES (:title, :cover, :size, :saga, :position, :storage, NULL, NOW())
			");

			$addBook->execute(
				array(
					':title' => $data['title'],
					':cover' => $data['cover'],
					':size' => $data['size'],
					':saga' => $sagaID,
					':position' => $data['position'],
					':storage' => $data['storage'],
				)
			);

			$bookID = $this->db->lastInsertId();

			if( empty($bookID) ) throw new PDOException('Bad book id.');

			//author(s)
			$this->_manageAuthorLink( $data, $bookID );

			$this->db->commit(); //transaction validation

			restore_error_handler();

			$this->_cleanCaches();

			//create stash cache
			$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
			$stash = new Stash($stashFileSystem);
			$stash->setKey('covers', get_class($this), $bookID);
			$stash->store(base64_decode($data['cover']), STASH_EXPIRE);

			return $bookID;

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
				UPDATE book SET bookLoanFK = :fk WHERE bookID = :id
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
	public function updBook( $data ){
		try {
			set_error_handler("errorHandler"); //handle any error and throw exception, forcing transaction rollback

			$this->db->beginTransaction(); //needed for rollback

			//saga
			$sagaID = $this->_manageSagaLink( $data );

			//book
			$updBook = $this->db->prepare("
				UPDATE book
				SET bookTitle = :title,
					".( isset($data['cover']) && !empty($data['cover']) ? "bookCover = :cover," : "")."
					bookSize = :size,
					bookSagaFK = :saga,
					bookSagaPosition = :position,
					bookStorageFK = :storage
				WHERE bookID = :id
			");

			$params = array(
				':id' => $data['id'],
				':title' => $data['title'],
				':size' => $data['size'],
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
				$stash->setKey('covers', get_class($this), $data['id']);
				$stash->store(base64_decode($data['cover']), STASH_EXPIRE);
			}

			$updBook->execute( $params );

			//author(s)
			$this->_manageAuthorLink( $data, $data['id'] );

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
	public function delBook( $id ){
		try {
			set_error_handler("errorHandler"); //handle any error and throw exception, forcing transaction rollback

			$this->db->beginTransaction(); //needed for rollback

			//loan deletion
			$this->_delLinkedLoan( $id, true );

			//book deletion
			$delBook = $this->db->prepare("
				DELETE
				FROM book
				WHERE bookID = :id
			");

			$delBook->execute( array( ':id' => $id ) );

			if( isset($_SESSION['images']['books'][$id]) ) unset($_SESSION['images']['books'][$id]);

			//author link deletion
			$delAuthorsLinks = $this->db->prepare("
				DELETE
				FROM books_authors
				WHERE bookFK = :id
			");

			$delAuthorsLinks->execute( array( ':id' => $id ) );

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
				WHERE loanID = (SELECT bookID FROM book WHERE bookID = :id)
			");

			$delLinkedLoan->execute( array( ':id' => $id ) );

		} catch ( PDOException $e ){
			//delBook() transaction need to know if there was an error for transaction rollback
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
				SELECT COUNT(bookID) AS verif
				FROM book
				WHERE bookID = :id
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
	public function bookUnicityCheck( $data ) {
		try {
			$bookTitleCheck = $this->db->prepare("
				SELECT bookID
				FROM book
				".( !is_null($data['saga']) ? "INNER JOIN saga ON sagaID = bookSagaFK" : "")."
				WHERE bookTitle = :title
				AND bookSize = :size
				".( !is_null($data['saga']) ? "AND sagaTitle = :saga" : "AND bookSagaFK IS NULL")."
			");

			$params = array(
				':title' => $data['title'],
				':size' => $data['size'],
			);

			if( !is_null($data['saga']) ) $params[':saga'] = $data['saga'];

			$bookTitleCheck->execute( $params );

			$result = $bookTitleCheck->fetchAll();

			if( empty($result) ) {
				return true;
			} elseif( isset($data['id']) ){
				return $result[0]['bookID'] == $data['id'];
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
		$authorFields = array();

		$args = array(
			'action'	=> FILTER_SANITIZE_STRING,
			'id'		=> FILTER_SANITIZE_NUMBER_INT,
			'title'		=> FILTER_SANITIZE_STRING,
			'size'		=> FILTER_SANITIZE_STRING,
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

		//authors with variable name save
		$indice = null;
		foreach( $_POST as $key => $val ){
			if( strpos($key, 'author_') !== false ){
				$indice = substr($key, strpos($key, '_') + 1);

				$args['author_'.$indice] = FILTER_SANITIZE_STRING;
				$authorFields[] = 'author_'.$indice;
			}
		}

		if( empty($errors) ){

			$formData = filter_var_array($_POST, $args);

			foreach( $formData as $field => $value ){
				${$field} = $value;
			}

			//book id
			//errors are set to #bookTitle because #bookID is hidden
			if( $action == 'update' ){
				if( is_null($id) || $id === false ){
					$errors[] = array('bookTitle', 'Identifiant incorrect.', 'error');
				} else {
					$id = filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $id === false ){
						$errors[] = array('bookTitle', 'Identifiant du livre incorrect.', 'error');
					} else {
						//check if id exists in DB
						if( $this->exists($id) ){
							$formData['id'] = $id;
						} else {
							$errors[] = array('bookTitle', 'Identifiant du livre inconnu.', 'error');
						}
					}
				}
			}

			if( $action == 'update' || $action == 'add' ){
				//title
				if( is_null($title) || $title === false ){
					$errors[] = array('bookTitle', 'Titre incorrect.', 'error');
				} elseif( empty($title) ){
					$errors[] = array('bookTitle', 'Le titre est requis.', 'required');
				} else {
					$formData['title'] = trim($title);
				}

				//size
				if( is_null($size) || $size === false ){
					$errors[] = array('bookSize', 'Format incorrect.', 'error');
				} elseif( empty($size) ){
					$errors[] = array('bookSize', 'Le format est requis.', 'required');
				} else {
					$formData['size'] = trim($size);
				}

				//cover
				if( is_null($cover) || $cover === false ){
					$errors[] = array('bookCoversStatus', 'Couverture incorrecte.', 'error');
				} elseif( !empty($cover) ){
					if( !file_exists(UPLOAD_COVER_PATH.$cover) ){
						$errors[] = array('bookCoversStatus', 'Couverture non trouvée.', 'error');
					} else {
						$formData['cover'] = chunk_split( base64_encode( file_get_contents( UPLOAD_COVER_PATH.$cover ) ) );
					}
				} else {
					$formData['cover'] = null;
				}

				//saga title
				if( is_null($saga) || $saga === false ){
					$errors[] = array('bookSagaTitle', 'Saga incorrecte.', 'error');
				} elseif( !empty($saga) ){
					$formData['saga'] = trim($saga);
				} else {
					$formData['saga'] = null;
				}


				//unicity check for title + format + saga
				if( empty($errors) && $action == 'add' ){
					if( !$this->bookUnicityCheck($formData) ){
						$errors[] = array('bookTitle', 'Livre déjà présent. (unicité sur titre + format + saga)', 'error');
					}
				}

				//saga position
				if( isset($formData['saga']) && !is_null($formData['saga']) ){ //requis si une saga est définie
					if( is_null($saga) || $saga === false ){
						$errors[] = array('bookSagaPosition', 'Position incorrecte.', 'error');
					} elseif( empty($position) ){
						$errors[] = array('bookSagaPosition', 'La position est requise.', 'required');
					} else {
						$position = filter_var($position, FILTER_VALIDATE_INT, array('min_range' => 1));
						if( $position === false ){
							$errors[] = array('bookSagaPosition', 'La position doit être un chiffre entier supérieur ou égal à 1.', 'error');
						}

						$formData['position'] = trim($position);
					}
				} else {
					$formData['position'] = null;
				}

				//storage
				if( empty($storage) ){
					$errors[] = array('bookStorage', 'Le rangement est requis.', 'required');
				}
				if( is_null($storage) || $storage === false ){
					$errors[] = array('bookStorage', 'Rangement incorrect.', 'error');
				} elseif( empty($storage) ){
					$errors[] = array('bookStorage', 'Le rangement est requis.', 'required');
				} else {
					$formData['storage'] = trim($storage);
				}

				//author
				$authors = array();
				$atLeastOneAuthor = false;
				foreach( $authorFields as $field ){
					if( is_null(${$field}) || ${$field} === false ){
						$errors[] = array($field, 'Auteur incorrect.', 'error');
					} elseif( !empty(${$field}) ){
						$authors[] = trim(${$field});
						$atLeastOneAuthor = true;
					}
				}

				if( !$atLeastOneAuthor ){
					$errors[] = array('bookAuthor_1', 'Au moins un auteur est requis.', 'required');
				} else {
					$formData['authors'] = $authors;
				}
			}
		}
		$formData['errors'] = $errors;

		return $formData;
	}
}
?>