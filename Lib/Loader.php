<?php
namespace Lib;
use Controller;
use Model\EntityAttributeModel;
use Model\EntityModel;
use Model\OptionSetModel;

class Loader
{
    public function loadController($path,$classname,$method = '',$data = array()){
        $class = "Controller\\".$classname."Controller";
        if(!class_exists($class)){
            require CONTROLLER.$path."/".$classname."Controller.php";
        }
        $obj = new $class;
        if($method == ''){
            return $obj->index();
        }else{
            return $obj->{$method}($data);
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
        if($optionsetid != 0){
            $optionset = $model->getItem($optionsetid);
            $optionsetdata = json_decode($glString->formateJson($optionset['optionsetvalue']),true);
        }else{
            $this->loadModel('Core','EntityAttribute');
            $attributeModel = new EntityAttributeModel();
            $attribute = $attributeModel->getItem($attributeid);
            $optionsetdata = json_decode($glString->formateJson($attribute['optionsetvalue']),true);
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
}