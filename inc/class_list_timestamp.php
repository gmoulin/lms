<?php
/**
 * Class for list timestamps
 * used for cache with 304 Not Modified and 'Last-Modified' / 'If-Modified-Since' header pair
 *
 * class name is in lowerclass to match table name ("common" class __construct) and file name (__autoload function)
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package Payement
 * @category Evolutions
 */
class list_timestamp extends commun {
	// Constructor
	public function __construct() {
		//for "commun" ($this->db & co)
		parent::__construct();
	}

	public function getByName( $name ){
		try {
			$getByName = $this->db->prepare("
				SELECT name, stamp
				FROM list_timestamp
				WHERE name = :n
			");

			$getByName->execute(array(':n' => $name));

			return $getByName->fetch();

		} catch ( Exception $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}

	/**
	 * search for timestamp of list having a name like $name
	 * for each found, update the timestamp (REPLACE)
	 * @params string $name: partial name of the lists to update
	 */
	public function updateByName( $name ){
		try {
			//timestamp already present ?
			$getByList = $this->db->prepare("
				SELECT name FROM list_timestamp WHERE `name` LIKE :n
			");

			$getByList->execute(array(':n' => '%'.$name.'%'));

			$lists = $getByList->fetchAll();

			//update the timestamp for found ones or add a new one
			$update = $this->db->prepare("
				REPLACE INTO list_timestamp (name, stamp) VALUES (:name, NOW())
			");

			if( !empty($lists) ){
				foreach( $lists as $l ){
					$update->execute(array(':name' => $l['name']));
					$update->closeCursor();
				}
			} else {
				$update->execute(array(':name' => $name));
			}

		} catch ( Exception $e ){
			erreur_pdo( $e, get_class( $this ), __FUNCTION__ );
		}
	}
}
?>