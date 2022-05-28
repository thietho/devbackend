<?php
require "Core/Model.php";
require "Core/Controller.php";
$files = glob('Lib/*.php');
foreach ($files as $file) {
    require($file);
}
$libraryModel = new \Lib\Entity('Core', 'Library');
$where = "AND scope = 'global' AND status = 1";
$librarys = $libraryModel->getList($where);
if (!empty($librarys)) {
    foreach ($librarys as $library) {
        try {
            $code = base64_decode($library['content']);
            $code = str_replace('<?php','',$code);
            $code = str_replace('?>','',$code);
            eval($code);
        } catch (Throwable $e) {
            echo 'Library: '.$library['libraryname'].PHP_EOL;
            echo $e;
            //return 'catch';
        }

    }
}
