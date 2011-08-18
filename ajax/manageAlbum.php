<?php
//manage albums related ajax requests
try {
	require_once('../conf.ini.php');

	header('Content-type: application/json');

	$action = filter_has_var(INPUT_POST, 'action');
	if( is_null($action) || $action === false ){
		throw new Exception('Gestion des albums : action manquante.');
	}

	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	if( $action === false ){
		throw new Exception('Gestion des albums : action incorrecte.');
	}

	switch ( $action ){
		case 'add' :
				$oAlbum = new album();

				$formData = $oAlbum->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$id = $oAlbum->addAlbum( $formData );
					$response = 'ok';
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'update' :
				$oAlbum = new album();

				$formData = $oAlbum->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$id = $oAlbum->updAlbum( $formData );
					$response = 'ok';
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'delete' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des albums : identitifant de l\'album manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des albums : identifiant incorrect.');
				}

				$oAlbum = new album();
				$oAlbum->delAlbum( $id );
				$response = 'ok';
			break;
		case 'get' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des albums : identitifant de l\'album manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des albums : identifiant incorrect.');
				}

				if(    isset($_SESSION['albums'])
					&& isset($_SESSION['albums']['list'])
					&& isset($_SESSION['albums']['list'][$id])
					&& !empty($_SESSION['albums']['list'][$id])
				){
					$response = $_SESSION['albums']['list'][$id];

				} else {
					$oAlbum = new album();
					$response = $oAlbum->getAlbumById($id);
				}

				if( empty($response) ){
					throw new Exception('Gestion des albums : identitifant de l\'album incorrect.');
				}
			break;
		case 'list' :
				$type = filter_has_var(INPUT_POST, 'type');
				if( is_null($type) || $type === false ){
					throw new Exception('Gestion des albums : type de recherche manquant.');
				}

				$type = filter_var($_POST['type'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 2));
				if( $type === false ){
					throw new Exception('Gestion des albums : type de recherche incorrect.');
				}

				if( !isset($_SESSION['albums']) || !isset($_SESSION['albums']['page']) ){
					$_SESSION['albums']['page'] = 0;
					$_SESSION['albums']['numPerPage'] = 20;
				}

				if( $type == 0 ){
					$_SESSION['albums']['page'] = ( isset($_SESSION['albums']['page']) ? $_SESSION['albums']['page'] : 0 );
					$_SESSION['albumListFilters'] = array();
				}
				if( $type == 1 ){
					$_SESSION['albums']['page'] = 0;
				}

				if( $type == 1 ){
					$_SESSION['albumListFilters'] = $_POST;
				} else {
					$_POST = $_SESSION['albumListFilters'];
				}

				$oAlbum = new album();
				if( $type == 0 ) $albums = $oAlbum->getAlbums();
				else $albums = $oAlbum->getAlbumsByFullTextSearch();

				//save the list on session for future pagination
				$_SESSION['albums']['list'] = $albums;
				$_SESSION['albums']['total'] = count($albums);

				if( $type == 2 || ( $type == 0 && $_SESSION['albums']['page'] > 0 ) ){
					$albums = array_slice( $albums, 0, $_SESSION['albums']['numPerPage'] * ($_SESSION['albums']['page']+1), false );
				} else {
					$albums = array_slice( $albums, $_SESSION['albums']['numPerPage'] * $_SESSION['albums']['page'], $_SESSION['albums']['numPerPage'], false );
				}

				if( $type == 2 || ( $type == 0 && $_SESSION['albums']['page'] > 0 ) ){
					$nb = count($albums);
				} else {
					$nb = $_SESSION['albums']['numPerPage'] * $_SESSION['albums']['page'] + count($albums);
				}

				if( $nb > $_SESSION['albums']['total'] ) $nb = $_SESSION['albums']['total'];

				$response = array('nb' => $nb, 'total' => $_SESSION['albums']['total'], 'list' => $albums);

			break;
		case 'more':
				if( isset($_SESSION['albums']) ){
					$_SESSION['albums']['page']++;
					$albums = array_slice( $_SESSION['albums']['list'], $_SESSION['albums']['numPerPage'] * $_SESSION['albums']['page'], $_SESSION['albums']['numPerPage'], false );
					$type = 3;
					$nb = $_SESSION['albums']['numPerPage'] * $_SESSION['albums']['page'] + count($albums);

					$response = array('nb' => $nb, 'total' => $_SESSION['albums']['total'], 'list' => $albums);

				} else {
					throw new Exception('Gestion des albums : pagination impossible, liste non disponible.');
				}
			break;
		default:
			throw new Exception('Gestion des albums : action non reconnue.');
	}

	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}
?>