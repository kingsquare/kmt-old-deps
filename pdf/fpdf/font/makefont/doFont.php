<?php
$rootPath = dirname(__DIR__).'/../../../../../';
require $rootPath . 'inc/bootstrap.php';
$bootstrap = Bootstrap::__getEngine('cli');
require dirname(__FILE__).'/makefont.php';
MakeFont($rootPath . 'inc/fonts/belta-regular-webfont.ttf', $rootPath . 'inc/fonts/belta-regular-webfont.afm');