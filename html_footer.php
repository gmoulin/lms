		<footer id="help">
			<?php include('help.html'); ?>
		</footer>
		<footer id="inform">
			<span></span>
		</footer>
		<!-- Grab local. fall back to Google CDN's jQuery if necessary -->
		<script src="js/libs/jquery-1.6.min.js"></script>
		<script>!window.jQuery && document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.js">\x3C/script>')</script>
		<script src="js/libs/jquery.tmpl.min.js"></script>

		<!-- scripts concatenated and minified via ant build script-->
		<script src="js/plugins.js"></script>
		<script src="js/script.js?v=<?php echo $js; ?>"></script>
		<!-- end scripts-->
	</body>
</html>
