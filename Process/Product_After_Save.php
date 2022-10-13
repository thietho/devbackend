<?php
$id = isset($context['id']) ? $context['id'] : 0;
if($id){
    $productModel = new \Lib\Entity('Inventory','Product');
    $product = $productModel->getItem($id);
    if($product['productparent'] == 0){
        switch ($product['productgroup']){
            case 'clothes':

                break;
            case 'accessory':

                break;
        }
    }
}
?>