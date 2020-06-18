<?php

error_reporting( 0 );
require_once './includes/kilencam.config.php';
new KilenCam( $image_relative_path, $image_filname );

?>


<!DOCTYPE HTML>
<html>
    <head>
        <!-- Tells the browser to refresh this page
             automatically every 20 seconds -->
        <meta http-equiv="refresh" content="20">
        <meta charset="utf-8">
        <title>Kilen webkamera</title>
    </head>

    <body>
        <img src="kilen.jpg" alt="Webcam Image Kilen">
    </body>
</html>
