<?php

namespace Lib;
class Cache
{
    private $dir = FILESERVER . 'cache/';
    private $expire = 24 * 60 * 60;

    public function __construct()
    {
        /*$files = glob($this->dir . '*.*');
        if ($files) {
            foreach ($files as $file) {
                $objFile = new File();
                $info = $objFile->getFileInfor($file);
                if (time() - $info['atime'] > $this->expire) {
                    unlink($file);
                }

            }
        }*/
    }
    public function check($filename)
    {
        if (file_exists($this->dir . $filename)) {
            $objFile = new File();
            $info = $objFile->getFileInfor($this->dir . $filename);
            return true;
        } else {
            return false;
        }
    }
    public function getPath($filename){
        if($this->check($filename)){
            return $this->dir.$filename;
        }else{
            return '';
        }
    }
    public function newVersion(){
        $data = array(
            'updatetime' => time()
        );
        $this->create('version.data',json_encode($data));
    }
    public function create($filename, $content)
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir);
            chmod($this->dir, 0777);
        }
        return file_put_contents($this->dir . $filename, $content);
    }

    public function get($filename, $expire = 0)
    {
        if (file_exists($this->dir . $filename)) {
            $objFile = new File();
            $info = $objFile->getFileInfor($this->dir . $filename);
            if ($expire > 0) {
                if (time() - $info['atime'] > $expire) {
                    unlink($this->dir . $filename);
                    return '';
                }
            } else {
                return file_get_contents($this->dir . $filename);
            }
        } else {
            return '';
        }
    }

    public function delete($filename)
    {
        if(file_exists($filename)){
            unlink($filename);
        }

    }

    public function clear()
    {
        $files = glob($this->dir . '*.*');
        if ($files) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }

    public function clearView()
    {
        $files = glob($this->dir . '*.tpl');
        if ($files) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
        $files = glob($this->dir . '*.json');
        if ($files) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }

    public function clearClass($classname){
        $files = glob($this->dir .$classname. '*');
        if ($files) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }


}