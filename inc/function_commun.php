<?php
/**
 * Fonction magique de chargement des classes si elle ne l'est pas encore
 *
 * @param string $class_name
 */
function __autoload( $class_name ){
	if( stripos($class_name, 'stash') !== false ){
		StashAutoloader::autoload($class_name);
	} else {
		require_once LMS_PATH . "/inc/class_" . $class_name . ".php";
	}
}

/**
 * Fonction pour afficher les erreurs des blocs try catch
 * finalise la page (die)
 *
 * @param msg : le message de l'erreur
 */
function erreur( $msg ) {
	print "Error!: " . $msg->getMessage() . "<br />";
	die();
}

/**
 * Affiche les erreurs "PDOException"
 *
 * @param string $msg : le message de l'erreur
 * @param string $className : le nom de la classe où l'erreur a été détectée
 * @param string $functionName : le nom de la fonction où l'erreur a été détectée
 */
function erreur_pdo( $msg, $className, $functionName ) {
	print "erreur dans la classe ".$className.", fonction ".$functionName."<br />";
	erreur( $msg );
}

/** PROTECTIONS XSS **/

// from http://nyphp.org/phundamentals/storingretrieving.php
function fix_magic_quotes ($var = NULL, $sybase = NULL){
	// if sybase style quoting isn't specified, use ini setting
	if ( !isset ($sybase) ){
		$sybase = ini_get ('magic_quotes_sybase');
	}

	// if no var is specified, fix all affected superglobals
	if ( !isset ($var) ){
		// if magic quotes is enabled
		if ( get_magic_quotes_gpc () ){
			// workaround because magic_quotes does not change $_SERVER['argv']
			$argv = isset($_SERVER['argv']) ? $_SERVER['argv'] : NULL;

			// fix all affected arrays
			foreach ( array ('_ENV', '_REQUEST', '_GET', '_POST', '_COOKIE', '_SERVER') as $var )
			{
				$GLOBALS[$var] = fix_magic_quotes ($GLOBALS[$var], $sybase);
			}

			$_SERVER['argv'] = $argv;

			// turn off magic quotes, this is so scripts which
			// are sensitive to the setting will work correctly
			ini_set ('magic_quotes_gpc', 0);
		}

		// disable magic_quotes_sybase
		if ( $sybase ){
			ini_set ('magic_quotes_sybase', 0);
		}

		// disable magic_quotes_runtime
		@set_magic_quotes_runtime (0);
		return TRUE;
	}

	// if var is an array, fix each element
	if ( is_array ($var) ){
		foreach ( $var as $key => $val )
		{
			$var[$key] = fix_magic_quotes ($val, $sybase);
		}

		return $var;
	}

	// if var is a string, strip slashes
	if ( is_string ($var) ){
		return $sybase ? str_replace ('\'\'', '\'', $var) : stripslashes ($var);
	}

	// otherwise ignore
	return $var;
}

fix_magic_quotes();

function removeInvalidChars($data, $charset = 'UTF-8', $entities = FALSE){
	# since some variable-length charset are vulnerable to an exploit
	# that ditch the next quote, dont trust the input and reencode the strings
	# reencoding the string with the same encoding will remove all malformed chars
	# supports all php charsets http://www.php.net/manual/en/function.htmlspecialchars.php as of 2008-06-01
	# cf http://ha.ckers.org/weird/variable-width-encoding.cgi for the exploit methodology

	switch($charset)
	{
		case 'UTF-8':
		case 'BIG5':
		case '950':
		case 'GB2312':
		case '936':
		case 'BIG5-HKSCS':
		case 'Shift_JIS':
		case 'SJIS':
		case '932':
		case 'EUC-JP':
		case 'EUCJP':
			return mb_convert_encoding( $data, $charset, $charset );

		case 'ISO-8859-1':
		case 'ISO8859-1':
		case 'ISO-8859-15':
		case 'ISO8859-15':
		case 'cp866':
		case 'ibm866':
		case '866':
		case 'cp1251':
		case 'Windows-1251':
		case 'win-1251':
		case '1251':
		case 'cp1252':
		case 'Windows-1252':
		case '1252':
		case 'KOI8-R':
		case 'KOI8-ru':
		case 'koi8r':
			return $data;

		default:
			if($entities) trigger_error('WRONG CHARSET USED, not supported by php, see http://www.php.net/manual/en/function.htmlspecialchars.php', E_USER_ERROR);
			return $data;
	}
}


function secureXSS($data, $charset = 'UTF-8', $quote_style = ENT_QUOTES, $all_entities = false, $double_encode = true){
	if(version_compare(PHP_VERSION, '5.2.3', '>='))
		if($all_entities) $data = htmlentities($data, $quote_style, $charset, $double_encode);
		else $data = htmlspecialchars($data, $quote_style, $charset, $double_encode);
	else
		if($all_entities) $data = htmlentities($data, $quote_style, $charset);
		else $data = htmlspecialchars($data, $quote_style, $charset);

	# Ok it should have been done on the inputs already
	# but lets be paranoid for just some milliseconds more just in case
	return removeInvalidChars($data, $charset = 'UTF-8', TRUE);
}

/**
 * error handler function
 * used for transaction rollback on any error during add, update or delete
 */
function errorHandler($errno, $errstr, $errfile, $errline){
	if( !(error_reporting() & $errno) ){
		// This error code is not included in error_reporting
		return;
	}

	$error = "Error [$errno] $errstr on line $errline in file $errfile. Aborting...";
	throw new Exception($error);

	/* Don't execute PHP internal error handler */
	return true;
}

// Remplace tous les accents par leur équivalent sans accent.
function stripAccents($string) {
	$string = str_replace(
		array(' ',
			'à', 'â', 'ä', 'á', 'ã', 'å',
			'î', 'ï', 'ì', 'í',
			'ô', 'ö', 'ò', 'ó', 'õ', 'ø',
			'ù', 'û', 'ü', 'ú',
			'é', 'è', 'ê', 'ë',
			'ç', 'ÿ', 'ñ',
			'À', 'Â', 'Ä', 'Á', 'Ã', 'Å',
			'Î', 'Ï', 'Ì', 'Í',
			'Ô', 'Ö', 'Ò', 'Ó', 'Õ', 'Ø',
			'Ù', 'Û', 'Ü', 'Ú',
			'É', 'È', 'Ê', 'Ë',
			'Ç', 'Ÿ', 'Ñ',
		),
		array('',
			'a', 'a', 'a', 'a', 'a', 'a',
			'i', 'i', 'i', 'i',
			'o', 'o', 'o', 'o', 'o', 'o',
			'u', 'u', 'u', 'u',
			'e', 'e', 'e', 'e',
			'c', 'y', 'n',
			'A', 'A', 'A', 'A', 'A', 'A',
			'I', 'I', 'I', 'I',
			'O', 'O', 'O', 'O', 'O', 'O',
			'U', 'U', 'U', 'U',
			'E', 'E', 'E', 'E',
			'C', 'Y', 'N',
		),
		$string
	);

	return str_replace(' ', '_', $string);
}
?>