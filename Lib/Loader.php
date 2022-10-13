<?php
namespace Lib;
use Controller;
use Model\EntityAttributeModel;
use Model\EntityModel;

class Loader
{
    private $resource;
    private $page;
    private $auth;
    public function __construct()
    {
        global $resource,$page,$auth;
        $this->resource = $resource;
        $this->page = $page;
        $this->auth = $auth;
    }

    public function loadController($path,$classname,$method = '',$data = array()){
        if(file_exists(CONTROLLER.$path."/".$classname."Controller.php")){
            $class = "Controller\\".$classname."Controller";
            if(!class_exists($class)){
                require CONTROLLER.$path."/".$classname."Controller.php";
            }
            $obj = new $class;
        }else{
            $class = "Controller\\ViewController";
            if(!class_exists($class)){
                require CONTROLLER."PageView/ViewController.php";
            }
            $obj = new $class;
        }

        if($method == ''){
            return $obj->index();
        }else{
            if(method_exists($obj,$method)){
                return $obj->{$method}($data);
            }else{
                require CONTROLLER."Page/ErrorController.php";
                $error = new Controller\ErrorController();
                return $error->notfount();
            }

        }

    }
    public function loadControllerByRoute($route,$method = '',$data = array()){
        $path = $route."Controller";
        CONTROLLER.$route."Controller.php";
        if(!class_exists($path)){
            require CONTROLLER.$route."Controller.php";
        }
        $arr = explode('/',$path);
        $class = "Controller\\".$arr[1];
        $obj = new $class;
        if($method == ''){
            return $obj->index();
        }else{
            return $obj->{$method}($data);
        }

    }
    public function loadPage($pagecode,$method = '',$data = array()){
        global $glString;
        $where = "AND pagecode = '$pagecode'";
        $pages = $this->page->getList($where);
        $content = '';
        if(!empty($pages)){
            $page = $pages[0];
            $codes = $glString->stringToArray($page['csscode']);
            foreach ($codes as $codeid){
                $resource = $this->resource->getItem($codeid);
                $content.= '<style>'.PHP_EOL;
                $content .= base64_decode($resource['content']);
                $content.= PHP_EOL.'</style>';
            }
            $content.= PHP_EOL;
            $codes = $glString->stringToArray($page['htmlcode']);
            foreach ($codes as $codeid){
                $resource = $this->resource->getItem($codeid);
                $content .= base64_decode($resource['content']);
            }
            $content.= PHP_EOL;
            $codes = $glString->stringToArray($page['jscode']);
            foreach ($codes as $codeid){
                $resource = $this->resource->getItem($codeid);
                $content.= '<script type="text/javascript">'.PHP_EOL;
                $content.= '//'.$resource['name'].PHP_EOL;
                $content .= base64_decode($resource['content']);
                $content.= PHP_EOL.'</script>';
            }
        }
        return $content;
    }
    public function loadModel($path,$classname){
        $class = "Model\\".$classname."Model";
        if(!class_exists($class)){
            require MODEL.$path."/".$classname."Model.php";
        }
    }
    public function getRelatedValue($valueid,$entityrelated){
        $this->loadModel('Core', "Entity");
        $modelEntity = new EntityModel();
        return $modelEntity->getRelatedValue($valueid,$entityrelated);
    }
    public function getOptionSetValue($key,$optionsetid,$attributeid = 0){
        global $glString;
        $model = new Entity('Core','OptionSet');
        if($attributeid){
            $this->loadModel('Core','EntityAttribute');
            $attributeModel = new EntityAttributeModel();
            $attribute = $attributeModel->getItem($attributeid);
            $optionsetdata = json_decode($glString->formateJson($attribute['optionsetvalue']),true);
        }else{
            if(is_int($optionsetid)){
                $optionset = $model->getItem($optionsetid);
                $optionsetdata = json_decode($glString->formateJson($optionset['optionsetvalue']),true);
            }else{
                $where = " AND ".$model->genCondition('optionsetname','equal',$optionsetid);
                $optionsets = $model->getList($where);
                $optionset = $optionsets[0];
                $optionsetdata = json_decode($glString->formateJson($optionset['optionsetvalue']),true);
            }
        }
        $key = str_replace('[','',$key);
        $key = str_replace(']','',$key);
        $arr = explode(',',$key);
        $arrValue = array();
        foreach ($arr as $k){
            if(isset($optionsetdata[$k])){
                $arrValue[] = $optionsetdata[$k];
            }
        }
        return implode(', ',$arrValue);

    }
    public function loadLibrary($libraryname){
        $libraryModel = new Entity('Core','Library');
        $where = "AND libraryname = '$libraryname'";
        $librarys = $libraryModel->getList($where);
        if(count($librarys)){
            $library = $librarys[0];
            if($library['scope'] == 'local'){
                try {
                    eval("?> " . base64_decode($library['content']) . " <?php ");
                    return array(
                        'statuscode' => 1,
                        'errors' => '',
                        'text' => 'Load '.$libraryname.' done'
                    );
                } catch (Throwable $e) {
                    echo 'Library: '.$library['libraryname'].PHP_EOL;
                    echo $e;
                    return array(
                        'statuscode' => 0,
                        'errors' => 'syntaxerror',
                        'text' => 'Load '.$libraryname.' faild'
                    );
                }
            }else{
                return array(
                    'statuscode' => 0,
                    'errors' => 'notlocal',
                    'text' => $libraryname.' is run globle!'
                );
            }
        }else{
            return array(
                'statuscode' => 0,
                'errors' => 'notexists',
                'text' => $libraryname.' is run globle!'
            );
        }
    }
    public function loadGlobalResource(){
        $resourceModel = new Entity('Core','Resource');
        $where = "AND scope = 'global'";
        $resources = $resourceModel->getList($where);
        return $resources;
    }

    public function getApi($apiname,$data = [],$token = ''){
        $url = HTTPSERVER.$apiname.'.api?'.http_build_query($data);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$token
            )
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function postApi($apiname,$data = []){

        $url = HTTPSERVER.$apiname.'.api?';
        $token = $this->auth->genToken($this->auth->userInfor['id']);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data,JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$token
            )
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}