<?php
use Lib\Date;
use Lib\ObjString;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');
require "Config/db.php";
require "Config/directory.php";
require "Core/Mysqli.php";
$db = new \Lib\MySQLi(DBHOST, DBUSER, DBPASS, DBNAME,PORT);
require "Core/startup.php";
$auth = new \Lib\Worker();
$auth->login('workflow','123456789');
$loader = new \Lib\Loader();
$loader->loadModel('Core','Process');
$processModel = new \Model\ProcessModel();
$count = 0;
//while ($count < 60){
//    $count++;
//    $processs = $processModel->getList(" AND active = 'actived' AND trigertype = 'schedule'");
//    echo $count.PHP_EOL;
//    $date = new Date();
//    foreach ($processs as $process){
//        echo $process['processname'].PHP_EOL;
//        $timeout = $process['timeout'];
//        $now = $date->timeToInt($date->getToday());
//        $whenrun = $date->timeToInt($process['whenrun']);
//        $lastrun = $date->timeToInt($process['lastrun']);
//        $runcount = $process['runcount'];
//        echo "Whenrun: ".$process['whenrun']." Timeout: ".$timeout." ". $process['timeunit'].PHP_EOL;
//        echo "Lastrun: ".$process['lastrun'].PHP_EOL;
//        $timerun = 0;
//        $processTraceModel = new \Lib\Entity('Core','ProcessTrace');
//        if($timeout>0){
//            switch ($process['timeunit']){
//                case 'seconds':
//                    $timerun = $timeout;
//                    break;
//                case 'minute':
//                    $timerun = $timeout*60;
//                    break;
//                case 'hours':
//                    $timerun = $timeout*60*60;
//                    break;
//                case 'day':
//                    $timerun = $timeout*60*60*24;
//                    break;
//                case 'month':
//                    $timerun = $timeout*60*60*24*30;
//                    break;
//            }
//            $nextrun = $whenrun + $timerun*$runcount;
//            echo PHP_EOL."Next run: ".$date->intToTime($nextrun);
//            //die();
//            if($now >= $nextrun){
//                try{
//                    ob_start();
/*                    eval("?> ".base64_decode($process['content'])." <?php ");*/
//                    $output = ob_get_contents();
//                    ob_end_clean();
//                    echo $output;
//                }catch (Throwable $e){
//                    $output = $e;
//                }
//                $processTrace_insert = array(
//                    'processid' => $process['id'],
//                    'method' => 'WORKER',
//                    'header' => '',
//                    'input' => '',
//                    'output' => $output,
//                );
//                $processTrace = $processTraceModel->save($processTrace_insert);
//                echo PHP_EOL;
//                $data = $processModel->save(array(
//                        'id' => $process['id'],
//                        'runcount' => $runcount+1,
//                        'lastrun' => $date->formatMySQLDate($date->getToday(),'DMY H:i:s'),
//                        'nextrun' => $date->formatMySQLDate($date->intToTime($nextrun+$timerun),'DMY H:i:s'))
//                );
//                print_r($data);
//            }
//        }else{
//            if($now >= $whenrun){
//                try{
//                    ob_start();
/*                    eval("?> ".base64_decode($process['content'])." <?php ");*/
//                    $output = ob_get_contents();
//                    ob_end_clean();
//                    echo $output;
//                }catch (Throwable $e){
//                    $output = $e;
//                }
//                $processTrace_insert = array(
//                    'processid' => $process['id'],
//                    'method' => 'WORKER',
//                    'header' => '',
//                    'input' => '',
//                    'output' => $output,
//                );
//                $processTrace = $processTraceModel->save($processTrace_insert);
//                $data = $processModel->save(array(
//                    'id' => $process['id'],
//                    'runcount' => $process['runcount']+1,
//                    'lastrun' => $date->formatMySQLDate($date->getToday()). ' ' . $date->getTime($date->getToday()),
//                ));
//            }
//
//        }
//        echo "---------------------------------".PHP_EOL;
//    }
//    sleep(1);
//}
