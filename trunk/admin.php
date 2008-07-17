<?php
session_start();
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'webpage.php');
define('DOCVERT_CLIENT_TYPE', 'web');
$themes = new Themes;
$themes->drawTheme();
?>
