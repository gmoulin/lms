<?php
//manage prêts related ajax requests
try {
	require_once('../conf.ini.php');

	header('Content-type: application/json');

	$action = filter_has_var(INPUT_POST, 'action');
	if( is_null($action) || $action === false ){
		throw new Exception('Gestion des prêts : action manquante.');
	}

	$action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);
	if( $action === false ){
		throw new Exception('Gestion des prêts : action incorrecte.');
	}

	switch ( $action ){
		case 'add' :
				$oLoan = new loan();

				$formData = $oLoan->checkAndPrepareFormData();

				if ( empty($formData['errors']) ) {
					$oLoan->addLoan( $formData );
					$response = 'ok';

					if( isset($_SESSION['books']) ) unset($_SESSION['books']['list']);
					if( isset($_SESSION['movies']) ) unset($_SESSION['movies']['list']);
					if( isset($_SESSION['albums']) ) unset($_SESSION['albums']['list']);
				} else {
					$response = $formData['errors'];
				}
			break;
		case 'delete' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des prêts : identitifant du prêt manquant.');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des prêts : identifiant incorrect.');
				}

				$oLoan = new loan();
				$oLoan->delLoan( $id );
				$response = 'ok';

				if( isset($_SESSION['books']) ) unset($_SESSION['books']['list']);
				if( isset($_SESSION['movies']) ) unset($_SESSION['movies']['list']);
				if( isset($_SESSION['albums']) ) unset($_SESSION['albums']['list']);
			break;
		case 'get' :
				$id = filter_has_var(INPUT_POST, 'id');
				if( is_null($id) || $id === false ){
					throw new Exception('Gestion des prêts : identitifant du prêt manquant..');
				}

				$id = filter_var($_POST['id'], FILTER_VALIDATE_INT, array('min_range' => 1));
				if( $id === false ){
					throw new Exception('Gestion des prêts : identifiant incorrect.');
				}

				$oLoan = new loan();
				$response = $oLoan->getLoanById($id);

				if( !is_array($response) || empty($response) ){
					throw new Exception('Gestion des prêts : identitifant du prêt incorrect.');
				}

				$response = $response[0];
			break;
		default:
			throw new Exception('Gestion des prêts : action non reconnue.');
	}

	echo json_encode($response);
	die;

} catch (Exception $e) {
	header($_SERVER["SERVER_PROTOCOL"]." 555 Response with exception");
	echo json_encode($e->getMessage());
	die;
}
?>