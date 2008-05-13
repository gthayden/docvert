<?php
session_start();
include_once('core/webpage.php');
define('DOCVERT_CLIENT_TYPE', 'web');
$themes = new Themes;
$themes->drawTheme();
?>
