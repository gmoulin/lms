<?php
/**
 * class for database interractions shared functions
 *
 * class name is in lowerclass to match table name ("commun" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Commun
 * @category Commun
 */
class commun {
	//db connexion
	public $db;

	//constructor
	public function __construct() {
		$instance = init::getInstance();

		$this->db = $instance->dbh();
	}

	/**
	 * @return array[][]
	 */
	public function columns() {
		try {
			$columns = $this->db->prepare("SHOW COLUMNS FROM ".get_class($this));

			$columns->execute();

			return $columns->fetchAll(PDO::FETCH_ASSOC);

		} catch (PDOException $e) {
			erreur_pdo($e, get_class($this), __FUNCTION__);
		}
	}

	/**
	 * @return integer
	 */
	public function nbResult() {
		try {
			$nbResult = $this->db->prepare("SELECT found_rows()");

			$nbResult->execute();

			$nb = $nbResult->fetch();

			//robustesse en cas de retour vide
			$nb = !empty( $nb ) ? $nb["found_rows()"] : 0;

			return $nb;

		} catch (PDOException $e) {
			erreur_pdo($e, get_class($this), __FUNCTION__);
		}
	}

	/**
	 * format search words for full text queries
	 *
	 * @param string $keywords
	 * return string
	 */
	public function prepareForFullTextQuery( $keywords ){
		$keywords = preg_split('/((^\p{P}+)|(\p{P}*\s+\p{P}*)|(\p{P}+$))/', $keywords, -1, PREG_SPLIT_NO_EMPTY);

		return implode(',', $keywords);
	}

	protected function cleanImageCache($id){
		//clear stash cache
		$stashFileSystem = new StashFileSystem(array('path' => STASH_PATH));
		$stash = new Stash($stashFileSystem);
		$stash->setupKey('covers', get_class($this), $id);
		$stash->clear();
	}
}
?>