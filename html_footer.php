		<footer id="help">
			<?php include('help.html'); ?>
		</footer>
		<footer id="inform">
			<span></span>
		</footer>

		<!-- JavaScript at the bottom for fast page loading -->

		<!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="js/libs/jquery-1.6.2.min.js"><\/script>')</script>

		<!-- scripts concatenated and minified via ant build script-->
		<script src="js/mylibs/jquery.tmpl.min.js"></script>
		<script defer src="js/plugins.js?v=<?php echo $pluginTS; ?>"></script>
		<script defer src="js/script.js?v=<?php echo $scriptTS; ?>"></script>
		<!-- end scripts-->

		<!-- mathiasbynens.be/notes/async-analytics-snippet Change UA-XXXXX-X to be your site's ID -->
		<!-- to use on the "demo" pages
		<script>
			var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview'],['_trackPageLoadTime']];
			(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.async=1;
			g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
			s.parentNode.insertBefore(g,s)}(document,'script'));
		</script>
		-->

		<!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
		   chromium.org/developers/how-tos/chrome-frame-getting-started -->
		<!--[if lt IE 7 ]>
		<script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
		<script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
		<![endif]-->

	</body>
</html>