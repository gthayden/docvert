<?php
session_start();
include('core/webpage.php');
define('DOCVERT_CLIENT_TYPE', 'web');
$themes = new Themes;
$themes->drawTheme();
?>
