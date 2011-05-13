<!DOCTYPE html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7 ]><html lang="<?php echo $lang; ?>" class="no-js ie6 ie ielt9 ielt8 ielt7"><![endif]-->
<!--[if IE 7 ]><html lang="<?php echo $lang; ?>" class="no-js ie7 ie ielt9 ielt8"><![endif]-->
<!--[if IE 8 ]><html lang="<?php echo $lang; ?>" class="no-js ie8 ie ielt9"><![endif]-->
<!--[if IE 9 ]><html lang="<?php echo $lang; ?>" class="no-js ie9 ie" manifest="site.manifest"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html lang="<?php echo $lang; ?>" class="no-js"  manifest="site.manifest"><!--<![endif]-->
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

		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
			Remove this if you use the .htaccess -->
		<!-- <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> -->

		<!--  Mobile Viewport Fix
			j.mp/mobileviewport & davidbcalhoun.com/2010/viewport-metatag
			device-width : Occupy full width of the screen in its current orientation
			initial-scale = 1.0 retains dimensions instead of zooming out if page height > device height
			maximum-scale = 1.0 retains dimensions instead of zooming in if page width < device width
		-->
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">

		<!-- Place favicon.ico and apple-touch-icon.png in the root of your domain and delete these references -->
		<link rel="shortcut icon" href="/favicon.ico">
		<link rel="apple-touch-icon" href="/apple-touch-icon.png">

		<!-- CSS: implied media="all" -->
		<link rel="stylesheet" href="css/style.css?v=<?php echo $css; ?>">

		<!-- All JavaScript at the bottom, except for Modernizr which enables HTML5 elements & feature detects -->
		<script src="js/libs/modernizr-1.7.min.js"></script>
	</head>
	<body>

