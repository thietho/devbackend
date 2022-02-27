<?php
session_start();
use Lib\Date;
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
$db = new MySQLi(DBHOST, DBUSER, DBPASS, DBNAME,PORT);
require "Core/startup.php";

$request = new \Lib\Request();
$response = new \Lib\Response();
$cache = new \Lib\Cache();
$loader = new \Lib\Loader();
$controller = new \Core\Controller();
$auth = new \Lib\Auth();
$glDate = new Date;
$glString = new ObjString;
$content = '';
$token = $request->getBearerToken();
if(empty($token))
{
    $token = $request->get('token');
}
if($token){
    $reuslt = json_decode($auth->loginByToken($token),true);
    if($reuslt['statuscode']==0 || $reuslt['statuscode'] == 2){
        echo json_encode($reuslt);
        die();
    }
}

if(empty($auth->userInfor) && !in_array($controller->route,$auth->routeallow)){
    $content = $loader->loadControllerByRoute('Page/Login');
}else {
    $iscache = $request->get('iscache');
    if($iscache){
        $str = MD5(json_encode($request->getDataGet())).'.tpl';
        $content = $cache->get($str);
    }
    if($controller->path != 'Process'){
        if($content == ''){
            if ($auth->checkEntityPermission($controller->path, $controller->classname, $controller->method)) {
                $content = $loader->loadController($controller->path, $controller->classname, $controller->method);
            }else{
                $content = $loader->loadController('Core', 'Entity', 'AccessDenied');
            }
            if($iscache) {
                $cache->create($str, $content);
            }
        }
    }else{
        // $action = new \Lib\Action();
        // $content = $action->execute($controller->method);
        $content = $loader->loadController('Process', 'Action', $controller->method);
    }


}

echo $content;