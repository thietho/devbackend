<?php
namespace Lib;
class File
{
    public function getFileInfor($file){
        $file_info = array();
        $pathinfo = pathinfo($file);
        $stat = stat($file);
        $file_info['realpath'] = realpath($file);
        $file_info['dirname'] = $pathinfo['dirname'];
        $file_info['basename'] = $pathinfo['basename'];
        $file_info['filename'] = $pathinfo['filename'];
        $file_info['extension'] = $pathinfo['extension'];
        $file_info['size'] = $stat[7];
        $file_info['size_string'] = $this->format_bytes($stat[7]);
        $file_info['atime'] = $stat[8];
        $file_info['mtime'] = $stat[9];
        $file_info['permission'] = substr(sprintf('%o', fileperms($file)), -4);
        $file_info['fileowner'] = getenv('USERNAME');
        return $file_info;
    }
    private function format_bytes(int $size){
        $base = log($size, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');
        return round(pow(1024, $base-floor($base)), 2).''.$suffixes[floor($base)];
    }
    public function getFileName($string){
        if(!empty($string)){
            $arr = explode('/',$string);
            return $arr[count($arr)-1];
        }else{
            return '';
        }

    }
    public function getFile($urlfile,$filename){
        $cache = new Cache();

        if(empty($path)){
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $urlfile,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Accept: */*",
                    "Accept-Encoding: gzip, deflate",
                    "Cache-Control: no-cache",
                    "Connection: keep-alive",
                    "cache-control: no-cache",

                ),
            ));
            $response = curl_exec($curl);
            $cache->create($filename,$response);
        }
        return $cache->getPath($filename);
    }
    public function deleteDir($dirPath) {
        if (is_dir($dirPath)) {
            if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
                $dirPath .= '/';
            }
            $files = glob($dirPath . '*', GLOB_MARK);
            foreach ($files as $file) {
                if (is_dir($file)) {
                    $this->deleteDir($file);
                } else {
                    unlink($file);
                }
            }
            rmdir($dirPath);
        }
    }
}