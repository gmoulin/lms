<?php
//manage movies related ajax requests
try {
	require_once('../conf.ini.php');

	header('Content-type: application/json');

	$action = filter_has_var(INPUT_POST, 'action');
	if( is_null($action) || $action === false ){
		throw new Exception('Gestion des films : action manquante.');
	}

	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	if( $action === false ){
		throw new Exception('Gestion des films : action incorrecte.');
	}

	switch ( $action ){
		case 'add' :
				$oMovie = new movie();

				$formData = $oMovie->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$id = $oMovie->addMovie( $formData );
					$response = 'ok';
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'update' :
				$oMovie = new movie();

				$formData = $oMovie->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$id = $oMovie->updMovie( $formData );
					$response = 'ok';
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'delete' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des films : identitifant du film manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des films : identifiant incorrect.');
				}

				$oMovie = new movie();
				$oMovie->delMovie( $id );
				$response = 'ok';
			break;
		case 'get' :

				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des films : identitifant du film manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des films : identifiant incorrect.');
				}

				if(    isset($_SESSION['movies'])
					&& isset($_SESSION['movies']['list'])
					&& isset($_SESSION['movies']['list'][$id])
					&& !empty($_SESSION['movies']['list'][$id])
				){
					$response = $_SESSION['movies']['list'][$id];

				} else {
					$oMovie = new movie();
					$response = $oMovie->getMovieById($id);

					if( empty($response) ){
						throw new Exception('Gestion des films : identitifant du film incorrect.');
					}
				}
			break;
		case 'list' :
				$type = filter_has_var(INPUT_POST, 'type');
				if( is_null($type) || $type === false ){
					throw new Exception('Gestion des films : type de recherche manquant.');
				}

				$type = filter_var($_POST['type'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 2));
				if( $type === false ){
					throw new Exception('Gestion des films : type de recherche incorrect.');
				}

				if( !isset($_SESSION['movies']) || !isset($_SESSION['movies']['page']) ){
					$_SESSION['movies']['page'] = 0;
					$_SESSION['movies']['numPerPage'] = 20;
				}

				if( $type == 0 ){
					$_SESSION['movies']['page'] = ( isset($_SESSION['movies']['page']) ? $_SESSION['movies']['page'] : 0 );
					$_SESSION['movieListFilters'] = array();
				}
				if( $type == 1 ){
					$_SESSION['movies']['page'] = 0;
				}

				if( $type == 1 ){
					$_SESSION['movieListFilters'] = $_POST;
				} else {
					$_POST = $_SESSION['movieListFilters'];
				}

				$oMovie = new movie();
				if( $type == 0 ) $movies = $oMovie->getMovies();
				else $movies = $oMovie->getMoviesByFullTextSearch();

				//save the list on session for future pagination
				$_SESSION['movies']['list'] = $movies;
				$_SESSION['movies']['total'] = count($movies);

				if( $type == 2 || ( $type == 0 && $_SESSION['movies']['page'] > 0 ) ){
					$movies = array_slice( $movies, 0, $_SESSION['movies']['numPerPage'] * ($_SESSION['movies']['page']+1), false );
				} else {
					$movies = array_slice( $movies, $_SESSION['movies']['numPerPage'] * $_SESSION['movies']['page'], $_SESSION['movies']['numPerPage'], false );
				}

				if( $type == 2 || ( $type == 0 && $_SESSION['movies']['page'] > 0 ) ){
					$nb = count($movies);
				} else {
					$nb = $_SESSION['movies']['numPerPage'] * $_SESSION['movies']['page'] + count($movies);
				}

				if( $nb > $_SESSION['movies']['total'] ) $nb = $_SESSION['movies']['total'];

				$response = array('nb' => $nb, 'total' => $_SESSION['movies']['total'], 'list' => $movies);

			break;
		case 'more':
				if( isset($_SESSION['movies']) ){
					$_SESSION['movies']['page']++;
					$movies = array_slice( $_SESSION['movies']['list'], $_SESSION['movies']['numPerPage'] * $_SESSION['movies']['page'], $_SESSION['movies']['numPerPage'], false );
					$type = 3;
					$nb = $_SESSION['movies']['numPerPage'] * $_SESSION['movies']['page'] + count($movies);

					$response = array('nb' => $nb, 'total' => $_SESSION['movies']['total'], 'list' => $movies);

				} else {
					throw new Exception('Gestion des films : pagination impossible, liste non disponible.');
				}
			break;
		default:
			throw new Exception('Gestion des films : action non reconnue.');
	}

	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}
?>