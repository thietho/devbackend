<?php
global $request, $response,$glString;
$id = $request->get('id');
$optionsetModel = new \Lib\Entity('Core', 'OptionSet');
$optionset = $optionsetModel->getItem($id);
$response->jsonOutput(array(
    'statuscode' => 1,
    'data' => json_decode($glString->formateJson($optionset['optionsetvalue']),true)
));
?>