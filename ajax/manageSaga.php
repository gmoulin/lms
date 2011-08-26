<?php
//manage sagas related ajax requests
//albums have no saga
try {
	require_once('../conf.ini.php');

	header('Content-type: application/json');

	$action = filter_has_var(INPUT_POST, 'action');
	if( is_null($action) || $action === false ){
		throw new Exception('Gestion des sagas : action manquante.');
	}

	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	if( $action === false ){
		throw new Exception('Gestion des sagas : action incorrecte.');
	}

	switch ( $action ){
		case 'add' :
				$oSaga = new saga();

				$formData = $oSaga->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$oSaga->addSaga( $formData );
					$response = 'ok';
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'update' :
				$oSaga = new saga();

				$formData = $oSaga->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$oSaga->updSaga( $formData );
					$response = 'ok';
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'delete' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des sagas : identitifant de la saga manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des sagas : identifiant incorrect.');
				}

				$oSaga = new saga();
				$oSaga->delSaga( $id );
				$response = 'ok';
			break;
		case 'updateLastCheckDate' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des sagas : identitifant de la saga manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des sagas : identifiant incorrect.');
				}

				$oSaga = new saga();
				$oSaga->updSagaLastCheckDate( $id );
				$response = "ok";
			break;
		case 'updateRating' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des sagas : identitifant de la saga manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des sagas : identifiant incorrect.');
				}

				$rating = filter_has_var(INPUT_POST, 'rating');
				if( is_null($rating) || $rating === false ){
					throw new Exception('Gestion des sagas : note de la saga manquante.');
				}

				$rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT, array('min_range' => 1, 'max_range' => 5));
				if( $rating === false ){
					throw new Exception('Gestion des sagas : note incorrecte.');
				}

				$oSaga = new saga();
				$oSaga->updRating( $id, $rating );
				$response = "ok";
			break;
		case 'impact' : //on deletion
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des sagas : identitifant de la saga manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des sagas : identifiant incorrect.');
				}

				$oSaga = new saga();
				$response = $oSaga->delSagaImpact( $id );

				include( LMS_PATH . '/list/impact.php' );
				die;
			break;
		case 'moveImpact' : //on storage change
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des sagas : identitifant de la saga manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des sagas : identifiant incorrect.');
				}

				$oSaga = new saga();
				$response = $oSaga->moveImpact( $id );

				include( LMS_PATH . '/list/moveSaga.php' );
				die;
			break;
		case 'move' : //save storage change
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des sagas : identitifant de la saga manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des sagas : identifiant de la saga incorrect.');
				}

				$storage_book = filter_has_var(INPUT_POST, 'storage_book');
				if( !is_null($storage_book) && !$storage_book === false ){
					$storage_book = filter_var($_POST['storage_book'], FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $storage_book === false ){
						throw new Exception('Gestion des sagas : identifiant du rangement des livres incorrect.');
					}
				}

				$storage_movie = filter_has_var(INPUT_POST, 'storage_movie');
				if( !is_null($storage_movie) && !$storage_movie === false ){
					$storage_movie = filter_var($_POST['storage_movie'], FILTER_VALIDATE_INT, array('min_range' => 1));
					if( $storage_movie === false ){
						throw new Exception('Gestion des sagas : identifiant du rangement des films incorrect.');
					}
				}

				$oSaga = new saga();
				$oSaga->move( $id, $storage_book, $storage_movie );
				$response = 'ok';
			break;
		case 'get' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des sagas : identitifant de la saga manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des sagas : identifiant incorrect.');
				}

				if(    isset($_SESSION['sagas'])
					&& isset($_SESSION['sagas']['list'])
					&& isset($_SESSION['sagas']['list']['id'.$id])
					&& !empty($_SESSION['sagas']['list']['id'.$id])
				){
					$response = $_SESSION['sagas']['list']['id'.$id];

				} else {
					$oSaga = new saga();
					$response = $oSaga->getSagaById($id);

					if( !is_array($response) || empty($response) ){
						throw new Exception('Gestion des sagas : identitifant de la saga incorrect.');
					}

					$response = $response[0];
				}
			break;
		case 'getByTitleForBook' :
				$title = filter_has_var(INPUT_POST, 'title');
				if( is_null($title) || $title === false ){
					throw new Exception('Gestion des sagas : titre de la saga manquant.');
				}

				$title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
				if( $title === false ){
					throw new Exception('Gestion des sagas : titre incorrect.');
				}

				$oSaga = new saga();
				$response = $oSaga->getBookSagaByTitle($title);
				if( is_array($response) ){
					$max = 0;
					$tmp = array();
					foreach( $response as $r ){
						$tmp['sagaSearchURL'] = $r['sagaSearchURL'];
						$max = ( $r['bookSagaPosition'] > $max ? $r['bookSagaPosition'] : $max );
						$tmp['storageID'] = $r['storageID'];
						$tmp['authors'][] = $r['author'];
					}
					$tmp['authors'] = array_unique($tmp['authors'], SORT_STRING);
					$tmp['position'] = $max + 1;
					$response = $tmp;
				}
			break;
		case 'getByTitleForMovie' :
				$title = filter_has_var(INPUT_POST, 'title');
				if( is_null($title) || $title === false ){
					throw new Exception('Gestion des sagas : titre de la saga manquant.');
				}

				$title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
				if( $title === false ){
					throw new Exception('Gestion des sagas : titre incorrect.');
				}

				$oSaga = new saga();
				$response = $oSaga->getMovieSagaByTitle($title);
				if( is_array($response) ){
					$max = 0;
					$tmp = array();
					foreach( $response as $r ){
						$tmp['sagaSearchURL'] = $r['sagaSearchURL'];
						$max = ( $r['movieSagaPosition'] > $max ? $r['movieSagaPosition'] : $max );
						$tmp['storageID'] = $r['storageID'];
						$tmp['artists'][] = $r['artist'];
					}
					$tmp['artists'] = array_unique($tmp['artists'], SORT_STRING);
					$tmp['position'] = $max + 1;
					$response = $tmp;
				}
			break;
		case 'list' :
				$type = filter_has_var(INPUT_POST, 'type');
				if( is_null($type) || $type === false ){
					throw new Exception('Gestion des sagas : type de recherche manquant.');
				}

				$type = filter_var($_POST['type'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 2));
				if( $type === false ){
					throw new Exception('Gestion des sagas : type de recherche incorrect.');
				}

				if( !isset($_SESSION['sagas']) || !isset($_SESSION['sagas']['page']) ){
					$_SESSION['sagas']['page'] = 0;
					$_SESSION['sagas']['numPerPage'] = 120;
				}

				if( $type == 0 ){
					$_SESSION['sagas']['page'] = ( isset($_SESSION['sagas']['page']) ? $_SESSION['sagas']['page'] : 0 );
					$_SESSION['sagaListFilters'] = array();
				}
				if( $type == 1 ){
					$_SESSION['sagas']['page'] = 0;
				}

				if( $type == 1 ){
					$_SESSION['sagaListFilters'] = $_POST;
				} else {
					$_POST = $_SESSION['sagaListFilters'];
				}

				$oSaga = new saga();
				$response = $oSaga->getSagas();
				$sagas = array();
				if( is_array($response) ){
					foreach( $response as $s ){
						$sagas[$s['sagaID']] = $s;
					}
				}

				//save the list on session for future pagination
				$_SESSION['sagas']['list'] = $response;
				$_SESSION['sagas']['total'] = count($response);

				if( $type == 2 || ( $type == 0 && $_SESSION['sagas']['page'] > 0 ) ){
					$sagas = array_slice( $sagas, 0, $_SESSION['sagas']['numPerPage'] * ($_SESSION['sagas']['page']+1), false );
				} else {
					$sagas = array_slice( $sagas, $_SESSION['sagas']['numPerPage'] * $_SESSION['sagas']['page'], $_SESSION['sagas']['numPerPage'], false );
				}

				if( $type == 2 || ( $type == 0 && $_SESSION['sagas']['page'] > 0 ) ){
					$nb = count($sagas);
				} else {
					$nb = $_SESSION['sagas']['numPerPage'] * $_SESSION['sagas']['page'] + count($sagas);
				}

				if( $nb > $_SESSION['sagas']['total'] ) $nb = $_SESSION['sagas']['total'];

				$response = array('nb' => $nb, 'total' => $_SESSION['sagas']['total'], 'list' => $sagas);

			break;
		case 'more':
				if( isset($_SESSION['sagas']) ){
					$_SESSION['sagas']['page']++;
					$sagas = array_slice( $_SESSION['sagas']['list'], $_SESSION['sagas']['numPerPage'] * $_SESSION['sagas']['page'], $_SESSION['sagas']['numPerPage'], false );
					$type = 3;
					$nb = $_SESSION['sagas']['numPerPage'] * $_SESSION['sagas']['page'] + count($sagas);

					$response = array('nb' => $nb, 'total' => $_SESSION['sagas']['total'], 'list' => $sagas);

				} else {
					throw new Exception('Gestion des sagas : pagination impossible, liste non disponible.');
				}
			break;
		default:
			throw new Exception('Gestion des sagas : action non reconnue.');
	}

	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}
?>
