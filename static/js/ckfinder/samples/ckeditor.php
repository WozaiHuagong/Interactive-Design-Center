<!DOCTYPE html>
<!--
Copyright (c) 2007-2016, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://cksource.com/ckfinder/license
-->
<html>
<head>
	<meta charset="utf-8">
	<title>CKFinder 3 Samples</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--[if lt IE 9]>
	<script src="js/html5shiv.min.js"></script>
	<![endif]-->
	<link href="css/sample.css" rel="stylesheet">
</head>
<body>
<div id="editor1"></div>
<script src="./js/sf.js"></script>
<script src="./js/tree-a.js"></script>
<script src="./js/ckeditor/ckeditor.js"></script>
<script src="../ckfinder.js"></script>
<script>
	if ( typeof CKEDITOR !== 'undefined' ) {
		CKEDITOR.addCss( 'img {max-width:100%; height: auto;}' );
		var editor = CKEDITOR.replace( 'editor1', {
			extraPlugins: 'uploadimage,image2,ckeditor_wiris',
			removePlugins: 'image',
			height:350
		} );
		CKFinder.setupCKEditor( editor );
	} else {
		document.getElementById( 'editor1' ).innerHTML = '<div class="tip-a tip-a-alert">This sample requires working Internet connection to load CKEditor from CDN.</div>'
	}
</script>
</body>
</html>
