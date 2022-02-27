<?php
namespace Lib;
final class ObjString
{
    public $imgext = array('jpg','png','gif','jpeg');
    public function getTextLength($str, $from, $len)
    {
        $str = strip_tags($str);
        $words = explode(' ', $str);
        $result = implode(' ', array_slice($words, $from, $len));
        if(count($words) > $len) {
            $result .= "...";
        }
        return $result;
    }
    public function formateJson($strjson){
        $strjson = str_replace('\\"','"',$strjson);
        $strjson = str_replace('"[','[',$strjson);
        $strjson = str_replace(']"',']',$strjson);
        return $strjson;
    }
    public function arrayToString($arr){
        if(empty($arr)){
            return '';
        }
        foreach ($arr as &$val)
        {
            $val = "[$val]";
        }
        return implode(',',$arr);
    }
    public function stringToArray($str){
        $str = str_replace('[','',$str);
        $str = str_replace(']','',$str);
        return explode(',',$str);
    }
    public function numberFormate($num,$n=0)
    {
        $dec_point = '.';
        $thousands_sep = ',';
        return number_format($num, $n, $dec_point, $thousands_sep);
    }

    public function toNumber($str)
    {
        if($str == ''){
            return 0;
        }
        return str_replace(",", "", $str);
    }

    public function numberToString($num, $leng)
    {
        $str = "" . $num;
        $arr = array();
        for ($i = strlen($str) - 1; $i >= 0; $i--) {
            array_push($arr, $str[$i]);
        }

        while (count($arr) < $leng) {
            array_push($arr, 0);
        }
        $s = "";
        while (count($arr)) {
            $s .= array_pop($arr);
        }
        return $s;
    }
    public function toUrlPara($data)
    {
        $str = '';
        foreach($data as $key => $val)
        {
            $str.="&$key=$val";
        }
        return $str;
    }
    public function matrixToArray($data,$col)
    {
        $arr = array();
        if(count($data))
            foreach($data as $item)
                $arr[]=$item[$col];
        return $arr;
    }

    public function array_Filter($data,$col,$value)
    {
        $arr = array();
        foreach($data as $item)
        {
            if($item[$col] == $value)
                $arr[]=$item;
        }
        return $arr;
    }

    function generateRandStr($length)
    {
        $randstr = "";
        for ($i = 0; $i < $length; $i++) {
            $randnum = mt_rand(0, 61);
            if ($randnum < 10) {
                $randstr .= chr($randnum + 48);
            } else {
                if ($randnum < 36) {
                    $randstr .= chr($randnum + 55);
                } else {
                    $randstr .= chr($randnum + 61);
                }
            }
        }
        return $randstr;
    }
    function setLoopStr($str,$numlop)
    {
        $strresult = "";
        for($i=0;$i<$numlop;$i++)
        {
            $strresult .= $str;
        }
        return $strresult;
    }
    public function printDataTable($data)
    {
        $header = "<tr>";
        foreach ($data[0] as $key => $val)
            $header .= "<th>$key</th>";
        $header.="</tr>";
        $body = "";
        foreach ($data as $item)
        {
            $body .= "<tr>";
            foreach ($item as $val)
            {
                if(!is_array($val))
                    $body .= "<td>$val</td>";
                else
                {
                    $body .= "<td>".print_r($val,true)."</td>";
                }
            }

            $body .= "</tr>";
        }
        $table = "<table>$header$body</table>";
        return $table;
    }
    public function groupCol($data,$col)
    {
        $datacol = array();
        foreach ($data as $item)
        {
            $datacol[$item[$col]][] = $item;
        }
        return $datacol;
    }
    function convert_number_to_words($number) {
        $hyphen      = ' ';
        $conjunction = '  ';
        $separator   = ' ';
        $negative    = 'âm ';
        $decimal     = ' phẩy ';
        $dictionary  = array(
            0                   => 'không',
            1                   => 'một',
            2                   => 'hai',
            3                   => 'ba',
            4                   => 'bốn',
            5                   => 'năm',
            6                   => 'sáu',
            7                   => 'bảy',
            8                   => 'tám',
            9                   => 'chín',
            10                  => 'mười',
            11                  => 'mười một',
            12                  => 'mười hai',
            13                  => 'mười ba',
            14                  => 'mười bốn',
            15                  => 'mười năm',
            16                  => 'mười sáu',
            17                  => 'mười bảy',
            18                  => 'mười tám',
            19                  => 'mười chín',
            20                  => 'hai mươi',
            30                  => 'ba mươi',
            40                  => 'bốn mươi',
            50                  => 'năm mươi',
            60                  => 'sáu mươi',
            70                  => 'bảy mươi',
            80                  => 'tám mươi',
            90                  => 'chín mươi',
            100                 => 'trăm',
            1000                => 'nghìn',
            1000000             => 'triệu',
            1000000000          => 'tỷ',
            1000000000000       => 'nghìn tỷ',
            1000000000000000    => 'nghìn triệu triệu',
            1000000000000000000 => 'tỷ tỷ'
        );
        if (!is_numeric($number)) {
            return false;
        }
        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }
        if ($number < 0) {
            return $negative . $this->convert_number_to_words(abs($number));
        }
        $string = $fraction = null;
        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }
        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->convert_number_to_words($remainder);
                }
                break;
        }
        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }
        return $string;
    }
    public function clean($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }
    public function decodePattern($pattern){
        $pattern = str_replace('{yyyy}', date("Y"),$pattern);
        $pattern = str_replace('{mm}', date("m"),$pattern);
        $pattern = str_replace('{dd}', date("d"),$pattern);
        return $pattern;
    }
    public function vn_to_str ($str){
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd'=>'đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
            'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D'=>'Đ',
            'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',

        );
        foreach($unicode as $nonUnicode=>$uni){
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        $str = str_replace(' ','_',$str);

        return $str;

    }
}
?>
