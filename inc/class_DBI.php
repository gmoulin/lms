<?php
/**
 * class for database request using PDO library
 *
 * @author Guillaume MOULIN <gmoulin.dev@gmail.com>
 * @copyright Copyright (c) Guillaume MOULIN
 *
 * @package DBI
 * @category Commun
 */
class DBI extends PDO {

	//constructeur
	public function __construct( $dsn, $user=NULL, $password=NULL ){
		parent::__construct( $dsn, $user, $password, array( PDO::ATTR_PERSISTENT => true ) );
	}

	/**
	 * Fonction de préparation des requêtes
	 * rend impossible les injections sql, prends en compte le "formatage" des valeurs recherchées et insérées
	 *
	 * @return PDO SQL Statement
	 */
	public function prepare( $sql, $options=NULL ){
		$statement = parent::prepare($sql);

		if( stripos($sql, 'SELECT') <= 5 ) { //requête "SELECT"
			$statement->setFetchMode( PDO::FETCH_ASSOC );
		}

		return $statement;
	}
}
?>