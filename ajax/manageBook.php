<?php
//manage books related ajax requests
try {
	require_once('../conf.ini.php');

	$action = filter_has_var(INPUT_POST, 'action');
	if( is_null($action) || $action === false ){
		throw new Exception('Gestion des livres : action manquante.');
	}

	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	if( $action === false ){
		throw new Exception('Gestion des livres : action incorrecte.');
	}

	switch ( $action ){
		case 'add' :
				$oBook = new book();

				$formData = $oBook->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$id = $oBook->addBook( $formData );
					$response = 'ok';
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'update' :
				$oBook = new book();

				$formData = $oBook->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$id = $oBook->updBook( $formData );
					$response = 'ok';
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'delete' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des livres : identitifant du livre manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des livres : identifiant incorrect.');
				}

				$oBook = new book();
				$oBook->delBook( $id );
				$response = 'ok';
			break;
		case 'get' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des livres : identitifant du livre manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des livres : identifiant incorrect.');
				}

				if(    isset($_SESSION['books'])
					&& isset($_SESSION['books']['list'])
					&& isset($_SESSION['books']['list'][$id])
					&& !empty($_SESSION['books']['list'][$id])
				){
					$response = $_SESSION['books']['list'][$id];

				} else {

					$oBook = new book();
					$response = $oBook->getBookById($id);

					if( empty($response) ){
						throw new Exception('Gestion des livres : identitifant du livre incorrect.');
					}
				}
			break;
		case 'list' :
				$type = filter_has_var(INPUT_POST, 'type');
				if( is_null($type) || $type === false ){
					throw new Exception('Gestion des livres : type de recherche manquant.');
				}

				$type = filter_var($_POST['type'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 2));
				if( $type === false ){
					throw new Exception('Gestion des livres : type de recherche incorrect.');
				}

				if( !isset($_SESSION['books']) || !isset($_SESSION['books']['page']) ){
					$_SESSION['books']['page'] = 0;
					$_SESSION['books']['numPerPage'] = 20;
				}

				if( $type == 0 ){
					$_SESSION['books']['page'] = ( isset($_SESSION['books']['page']) ? $_SESSION['books']['page'] : 0 );
					$_SESSION['bookListFilters'] = array();
				}
				if( $type == 1 ){
					$_SESSION['books']['page'] = 0;
				}

				if( $type == 1 ){
					$_SESSION['bookListFilters'] = $_POST;
				} else {
					$_POST = $_SESSION['bookListFilters'];
				}

				$oBook = new book();
				if( $type == 0 ) $books = $oBook->getBooks();
				else $books = $oBook->getBooksByFullTextSearch();

				//save the list on session for future pagination
				$_SESSION['books']['list'] = $books;
				$_SESSION['books']['total'] = count($books);

				if( $type == 2 || ( $type == 0 && $_SESSION['books']['page'] > 0 ) ){
					$books = array_slice( $books, 0, $_SESSION['books']['numPerPage'] * ($_SESSION['books']['page']+1), false );
				} else {
					$books = array_slice( $books, $_SESSION['books']['numPerPage'] * $_SESSION['books']['page'], $_SESSION['books']['numPerPage'], false );
				}

				if( $type == 2 || ( $type == 0 && $_SESSION['books']['page'] > 0 ) ){
					$nb = count($books);
				} else {
					$nb = $_SESSION['books']['numPerPage'] * $_SESSION['books']['page'] + count($books);
				}

				if( $nb > $_SESSION['books']['total'] ) $nb = $_SESSION['books']['total'];

				$response = array('nb' => $nb, 'total' => $_SESSION['books']['total'], 'list' => $books);

			break;
		case 'more':
				if( isset($_SESSION['books']) ){
					$_SESSION['books']['page']++;
					$books = array_slice( $_SESSION['books']['list'], $_SESSION['books']['numPerPage'] * $_SESSION['books']['page'], $_SESSION['books']['numPerPage'], false );
					$type = 3;
					$nb = $_SESSION['books']['numPerPage'] * $_SESSION['books']['page'] + count($books);

					$response = array('nb' => $nb, 'total' => $_SESSION['books']['total'], 'list' => $books);

				} else {
					throw new Exception('Gestion des livres : pagination impossible, liste non disponible.');
				}
			break;
		default:
			throw new Exception('Gestion des livres : action non reconnue.');
	}

	header('Content-type: application/json');
	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}
?>