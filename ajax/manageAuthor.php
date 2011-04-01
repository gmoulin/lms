<?php
//manage authors related ajax requests
try {
	require_once('../conf.ini.php');

	header('Content-type: application/json');

	$action = filter_has_var(INPUT_POST, 'action');
	if( is_null($action) || $action === false ){
		throw new Exception('Gestion des auteurs : action manquante.');
	}

	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	if( $action === false ){
		throw new Exception('Gestion des auteurs : action incorrecte.');
	}

	switch ( $action ){
		case 'add' :
				$oAuthor = new author();

				$formData = $oAuthor->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$oAuthor->addAuthor( $formData );
					$response = 'ok';

					if( isset($_SESSION['authors']) ) unset($_SESSION['authors']['list']);
					if( isset($_SESSION['books']) ) unset($_SESSION['books']['list']);
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'update' :
				$oAuthor = new author();

				$formData = $oAuthor->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$oAuthor->updAuthor( $formData );
					$response = 'ok';

					if( isset($_SESSION['authors']) ) unset($_SESSION['authors']['list']);
					if( isset($_SESSION['books']) ) unset($_SESSION['books']['list']);
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'delete' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des auteurs : identitifant de l\'auteur manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des auteurs : identifiant incorrect.');
				}

				$oAuthor = new author();
				$oAuthor->delAuthor( $id );
				$response = "ok";

				if( isset($_SESSION['authors']) ) unset($_SESSION['authors']['list']);
				if( isset($_SESSION['books']) ) unset($_SESSION['books']['list']);
			break;
		case 'impact' : //on deletion
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des auteurs : identitifant de l\'auteur manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des auteurs : identifiant incorrect.');
				}

				$oAuthor = new author();
				$response = $oAuthor->delAuthorImpact( $id );

				include( LMS_PATH . '/list/impact.php' );
				die;
			break;
		case 'get' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des auteurs : identitifant de l\'auteur manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des auteurs : identifiant incorrect.');
				}

				if(    isset($_SESSION['authors'])
					&& isset($_SESSION['authors']['list'])
					&& isset($_SESSION['authors']['list'][$id])
					&& !empty($_SESSION['authors']['list'][$id])
				){
					$response = $_SESSION['authors']['list'][$id];

				} else {
					$oAuthor = new author();
					$response = $oAuthor->getAuthorById($id);

					if( !is_array($response) || empty($response) ){
						throw new Exception('Gestion des auteurs : identitifant de l\'auteur incorrect.');
					}

					$response = $response[0];
				}
			break;
		case 'list' :
				$type = filter_has_var(INPUT_POST, 'type');
				if( is_null($type) || $type === false ){
					throw new Exception('Gestion des auteurs : type de recherche manquant.');
				}

				$type = filter_var($_POST['type'], FILTER_VALIDATE_INT, array('min_range' => 0, 'max-range' => 2));
				if( $type === false ){
					throw new Exception('Gestion des auteurs : type de recherche incorrect.');
				}

				if( !isset($_SESSION['authors']) || !isset($_SESSION['authors']['page']) ){
					$_SESSION['authors']['page'] = 0;
					$_SESSION['authors']['numPerPage'] = 120;
				}

				if( $type == 0 ){
					$_SESSION['authors']['page'] = ( isset($_SESSION['authors']['page']) ? $_SESSION['authors']['page'] : 0 );
					$_SESSION['authorListFilters'] = array();
				}
				if( $type == 1 ){
					$_SESSION['authors']['page'] = 0;
				}

				if( $type == 1 ){
					$_SESSION['authorListFilters'] = $_POST;
				} else {
					$_POST = $_SESSION['authorListFilters'];
				}

				$oAuthor = new author();
				$response = $oAuthor->getAuthors();
				$authors = array();
				foreach( $response as $a ){
					$authors[$a['authorID']] = $a;
				}

				//save the list on session for future pagination
				$_SESSION['authors']['list'] = $authors;
				$_SESSION['authors']['total'] = count($authors);

				if( $type == 2 || ( $type == 0 && $_SESSION['authors']['page'] > 0 ) ){
					$authors = array_slice( $authors, 0, $_SESSION['authors']['numPerPage'] * ($_SESSION['authors']['page']+1), false );
				} else {
					$authors = array_slice( $authors, $_SESSION['authors']['numPerPage'] * $_SESSION['authors']['page'], $_SESSION['authors']['numPerPage'], false );
				}

				include( LMS_PATH . '/list/author.php' );
				die;
			break;
		case 'more':
				if( isset($_SESSION['authors']) ){
					$_SESSION['authors']['page']++;
					$authors = array_slice( $_SESSION['authors']['list'], $_SESSION['authors']['numPerPage'] * $_SESSION['authors']['page'], $_SESSION['authors']['numPerPage'], false );
					$type = 3;

					include( LMS_PATH . '/list/author.php' );
					die;
				} else {
					throw new Exception('Gestion des auteurs : pagination impossible, liste non disponible.');
				}
			break;
		default:
			throw new Exception('Gestion des auteurs : action non reconnue.');
	}

	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}
?>