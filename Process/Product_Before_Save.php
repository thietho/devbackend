<?php
global $glDate,$glString;
$productname = isset($context['productname'])?$context['productname']:'';
$description = isset($context['description'])?$context['description']:'';
$description = strip_tags($description);
$keywords = $glString->vn_to_str($productname);
$keywords .= " ".$glString->vn_to_str($description);
$keywords = preg_replace('/[^A-Za-z0-9\-]/', ' ', $keywords); // Removes special chars.
$keywords = str_replace('-',' ',$keywords);
$context['keywords'] = $keywords;
?>