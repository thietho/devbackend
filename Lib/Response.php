<?php
namespace Lib;
class Response
{
    public $result;
    public function redirect($url){
        header('Location: '.$url);
    }
    public function jsonOutput($data){
        header('Content-Type: application/json');
        $this->result = json_encode($data);
        echo $this->result;
    }
    public function jsonResult($data){
        header('Content-Type: application/json');
        echo $data;
    }
}