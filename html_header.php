<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" class="no-js"  manifest="site.manifest">
<head>
	<title>Gestionnaire de Médiathèque</title>
	<!--<base href="" />-->
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Script-Type" content="text/javascript" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<meta name="identifier-url" content="http://<?php echo $_SERVER['SERVER_NAME']; ?>" />
	<meta name="Description" content="<?php echo strip_tags( $metadata['description'] ); ?>" />
	<meta name="Keywords" content="<?php echo strip_tags( $metadata['motscles'] ); ?>" />
	<meta name="robots" content="index, follow, noarchive" />
	<meta name="author" content="Guillaume Moulin" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta name="distribution" content="global" />
	<meta name="revisit-after" content="1 days" />
	<link href="favicon.ico" rel="shortcut icon" type="images/x-icon" />

  <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

  <!-- CSS: implied media=all -->
  <!-- CSS concatenated and minified via ant build script-->
  <link rel="stylesheet" href="css/style.css?v=<?php echo $cssTS; ?>">
  <!-- end CSS-->

  <!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

  <!-- All JavaScript at the bottom, except for Modernizr / Respond.
       Modernizr enables HTML5 elements & feature detects; Respond is a polyfill for min/max-width CSS3 Media Queries
       For optimal performance, use a custom Modernizr build: www.modernizr.com/download/ -->
  <script src="js/libs/modernizr-2.0.6.custom.min.js"></script>
</head>
<body>