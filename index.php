<?php
session_start();

use Lib\Date;
use Lib\Entity;
use Lib\MySQLi;
use Lib\ObjString;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');
require "Config/db.php";
require "Config/directory.php";
require "Core/Mysqli.php";
$db = new MySQLi(DBHOST, DBUSER, DBPASS, DBNAME, PORT);
require "Core/startup.php";
$resource = new Entity('Core','Resource');
$page = new Entity('Core','Page');
$request = new \Lib\Request();
$response = new \Lib\Response();
$cache = new \Lib\Cache();

$settingModel = new \Lib\Entity('Core', 'Setting');
$dataSetting = $settingModel->getList();
$glSetting = array();
$controller = new \Core\Controller();
$auth = new \Lib\Auth();
$glDate = new Date;
$glString = new ObjString;
$loader = new \Lib\Loader();