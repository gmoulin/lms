<?php
/**
 * application initialization class
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Init
 * @category Commun
 */
class init {
	private static $_instance = null;

	//initialization data array
	private $_lms_infos;

	//database connexion handler
	private $_dbh;

	//mysql connexion data
	private $_host;
	private $_dbname;
	private $_user;
	private $_pass;

	//constructor
	private function __construct(){
		try {
			$this->initialize();

			$this->_dbh = null;
			$this->_dbh = new DBI( 'mysql:host='.$this->_host.';dbname='.$this->_dbname, $this->_user, $this->_pass );

			$this->_dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$this->_dbh->exec( 'SET CHARACTER SET utf8' );

		} catch (PDOException $e) {
			die( 'Connection failed or database cannot be selected : ' . $e->getMessage() );
		}
	}

	public function __destruct(){
		$this->_dbh = null;
	}

	/**
	 * @return singleton
	 */
	public static function getInstance(){

		if( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * @return database handler
	 */
	public function dbh(){
		return $this->_dbh;
	}

	/**
	 * ini file parsing
	 */
	private function initialize() {
		$this->_lms_infos = parse_ini_file( LMS_PATH . "/inc/lms.ini", true );

		$this->_host	= $this->_lms_infos['database']['host'];
		$this->_dbname	= $this->_lms_infos['database']['dbname'];
		$this->_user	= $this->_lms_infos['database']['user'];
		$this->_pass	= $this->_lms_infos['database']['pass'];
	}
}

