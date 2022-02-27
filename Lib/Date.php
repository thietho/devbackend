<?php
namespace Lib;
final class Date
{
    //Kieu ngay chuan: yyyy-mm-dd 00:00:00 - MySQLDate
    //Kieu ngay view: dd-mm-yyyy 00:00:00 - ViewDate
    public $now;
    public $balanc_vntime = 0;

    function __construct()
    {
        $this->now = $this->getVietnamToday();
        $this->balanc_vntime = 7 - round(date('Z') / 3600);
    }

    public function getVietnamToday()
    {
        $currenttime = time();
        $currenttime += ($this->balanc_vntime * 3600);
        return getdate($currenttime);
    }


    //CAC HAM DANH CHO NGAY CHUAN
    function getDay($date) //format yyyy-mm-dd
    {
        return date( 'd' ,$this->timeToInt($date));
    }

    function getMonth($date) //format yyyy-mm-dd
    {
        return date( 'm' ,$this->timeToInt($date));
    }

    function getYear($date) //format yyyy-mm-dd
    {
        return date( 'Y' ,$this->timeToInt($date));
    }

    function getMinute($date)
    {
        return date( 'i' ,$this->timeToInt($date));
    }

    function getHour($date)
    {
        return date( 'H' ,$this->timeToInt($date));
    }

    function getSecond($date)
    {
        return date( 's' ,$this->timeToInt($date));
    }

    function getTime($date)
    {
        return date( 'H:i:s' ,$this->timeToInt($date));
    }

    function getDate($date)
    {
        return date( 'Y-m-d' ,$this->timeToInt($date));
    }
    public function intToTime($int){
        return date('Y-m-d H:i:s',$int);
    }
    function getToday()
    {
        $today = $this->getVietnamToday();

        $time = $today['year'] . '-' . $this->numberFormate($today['mon']) . '-' . $this->numberFormate($today['mday']) . ' ' . $this->numberFormate($today['hours']) . ":" . $this->numberFormate($today['minutes']) . ":" . $this->numberFormate($today['seconds']);
        return $time;
    }

    function getTodayNoTime()
    {
        $today = $this->getVietnamToday();

        $time = $today['year'] . '-' . $this->numberFormate($today['mon']) . '-' . $this->numberFormate($today['mday']);
        return $time;
    }
    function getFistDayOfMonth($date)//fomate yy-mm-dd
    {
        return $this->getYear($date)."-".$this->getMonth($date)."-01";
    }
    function getLastDateOfMonth($mon,$year)
    {
        $date = $year."-".$this->numberFormate($mon + 1)."-01";
        $befordate = $this->addday($date,-1);
        return $this->getDay($befordate);
    }
    function addWorkDay($stringdate, $days) //fomate yy-mm-dd)
    {
        $date = $stringdate;
        while ($days>0){
            $date = $this->addday($date,1);
            $arr = getdate($this->timeToInt($date));
            if($arr['wday']!= 0 && $arr['wday']!= 6){
                $days-=1;
            }

        }

        return $date;
    }
    function addday($stringdate, $days) //fomate yy-mm-dd
    {
        $d = $this->getDay($stringdate);
        $mon = $this->getMonth($stringdate);
        $y = $this->getYear($stringdate);
        $time = mktime(0, 0, 0, intval($mon), intval($d) + $days, intval($y));
        return date("Y-m-d", $time);
    }

    public function addmonth($stringdate, $months) //fomate yy-mm-dd
    {
        $d = $this->getDay($stringdate);
        $mon = $this->getMonth($stringdate);
        $y = $this->getYear($stringdate);
        $time = mktime(0, 0, 0, intval($mon) + $months, intval($d), intval($y));
        return date("Y-m-d", $time);
    }

    function addYear($stringdate, $years) //fomate yy-mm-dd
    {
        $d = $this->getDay($stringdate);
        $mon = $this->getMonth($stringdate);
        $y = $this->getYear($stringdate);
        $time = mktime(0, 0, 0, intval($mon), intval($d), intval($y) + $years);
        return date("Y-m-d", $time);
    }

    function addHour($stringdate, $hour) //fomate yy-mm-dd
    {
        $d = $this->getDay($stringdate);
        $mon = $this->getMonth($stringdate);
        $y = $this->getYear($stringdate);

        $h = (int)$this->getHour($stringdate) + $hour;
        $m = $this->getMinute($stringdate);
        $s = $this->getSecond($stringdate);
        $time = mktime(intval($h), intval($m), intval($s), intval($mon), intval($d), intval($y));
        return date("Y-m-d H:i:s", $time);
    }

    function addMinutes($stringdate, $minutes) //fomate yy-mm-dd
    {
        $d = $this->getDay($stringdate);
        $mon = $this->getMonth($stringdate);
        $y = $this->getYear($stringdate);

        $h = $this->getHour($stringdate);
        $m = $this->getMinute($stringdate);
        $s = $this->getSecond($stringdate);
        $time = mktime(intval($h), intval($m) + $minutes, intval($s), intval($mon), intval($d), intval($y));
        return date("Y-m-d H:i:s", $time);
    }

    function addSecond($stringdate, $sec) //fomate yy-mm-dd
    {
        $d = $this->getDay($stringdate);
        $mon = $this->getMonth($stringdate);
        $y = $this->getYear($stringdate);

        $h = $this->getHour($stringdate);
        $m = $this->getMinute($stringdate);
        $s = $this->getSecond($stringdate);
        $time = mktime(intval($h), intval($m), intval($s) + $sec, intval($mon), intval($d), intval($y) + (int)$y);
        return date("Y-m-d  H:i:s", $time);
    }

    function isDate($year, $mon, $day)
    {
        $time = mktime(0, 0, 0, intval($mon), intval($day), intval($year));
        $da1 = $year . "-" . $mon . "-" . $day;
        $da2 = date("Y-m-d", $time);
        if ($this->_compareDate($da1, $da2) == 0) {
            return true;
        } else {
            return false;
        }
    }

    function isDateNull($date)
    {
        if ($date == "0000-00-00 00:00:00") {
            return true;
        } else {
            return false;
        }
    }

    function checkFormatDateDMY($input)
    {
        if (strrchr($input, "-") != "" || strrchr($input, "/") != "") {
            $day = substr($input, 0, 2);
            $month = substr($input, 3, 2);
            $year = substr($input, 6, 4);
            if ($this->isDate($year, $month, $day)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function checkValidDate($input)
    {
        if ($this->checkFormatDateDMY($input)) {
            if (!(preg_match('/^(0[1-9]|[12][0-9]|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/', $input))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function _compareDate($PresentDate, $ExpiredDate)
    {
        return ($this->timeToInt($PresentDate) < $this->timeToInt($ExpiredDate) ? 1 : 0);
    }
    public function isBetween($value,$form,$to)
    {
        if($this->_compareDate($form,$value) && $this->_compareDate($value,$to))
            return true;
        else
            return false;
    }
    function compareDateOperation($presentDate, $expireDate, $operation) {
        if($operation == "less") {
            return ($this->timeToInt($presentDate) < $this->timeToInt($expireDate) ? 1 : 0);
        } else if ($operation == "less_equal") {
            return ($this->timeToInt($presentDate) <= $this->timeToInt($expireDate) ? 1 : 0);
        } else if ($operation == "equal") {
            return ($this->timeToInt($presentDate) == $this->timeToInt($expireDate) ? 1 : 0);
        } else if ($operation == "greater_equal") {
            return ($this->timeToInt($presentDate) >= $this->timeToInt($expireDate) ? 1 : 0);
        }  else if ($operation == "greater") {
            return ($this->timeToInt($presentDate) > $this->timeToInt($expireDate) ? 1 : 0);
        } else {
            return -1;
        }
    }

    function timeToInt($stringdate)
    {
        return strtotime($stringdate);
    }
    function isWeekend($stringdate){
        $day = date('l',$this->timeToInt($stringdate));
        if($day == "Sunday" || $day == "Saturday"){
            return true;
        }else{
            return false;
        }
    }
    public function formatTime($time, $format = "")
    {
        switch ($format) {
            case "":
                if ($time == "") {
                    return "00:00";
                } else {
                    $arr = @explode(":", $time);
                    return $arr[0] . ":" . $arr[1];
                }
            case "longtime":
                if ($time == "") {
                    return "00:00:00";
                } else {
                    return $time;
                }
        }
        return "";
    }

    public function formatMySQLDate($date, $format = 'DMY', $character = '-', $language = 'en')
    {
        if ($date == '' || $date == "0000-00-00 00:00:00" || $date == "0000-00-00") {
            return '';
        }
        switch ($format) {
            case 'MY':
                return $this->getMonth($date) . $character . $this->getYear($date);
            case 'MMY':
                return $this->monthToChar($this->getMonth($date), $language) . $character . $this->getYear($date);
            case 'MDY':
                return $this->getMonth($date) . $character . $this->getDay($date) . $character . $this->getYear($date);
            case 'DMY':
                return $this->getDay($date) . $character . $this->getMonth($date) . $character . $this->getYear($date);
            case 'longdate':
                return  $this->getDay($date) . $character . $this->getMonth($date) . $character . $this->getYear($date) . " " . $this->getHour($date) . ":" . $this->getMinute($date) . ":" . $this->getSecond($date);
            //return $this->getHour($date) . ":" . $this->getMinute($date) . " | " . $this->getDay($date) . $character . $this->getMonth($date) . $character . $this->getYear($date);
            case 'month3letter':
                return $this->monthTo3letter($this->getMonth($date)) . " " . $this->getDay($date) . ", " . $this->getYear($date);
            case 'DMY H:i:s':
                return  $this->getDay($date) . $character . $this->getMonth($date) . $character . $this->getYear($date) . " " . $this->getHour($date) . ":" . $this->getMinute($date) . ":" . $this->getSecond($date);
        }

        return '';
    }
    public function formatViewDate($date, $format = 'DMY', $character = '-')
    {
        if ($date == "") {
            return "";
        }
        switch ($format) {
            case 'MDY':
                $day = substr($date, 3, 2);
                $mon = substr($date, 0, 2);
                $year = substr($date, 6, 4);
                return $year . $character . $mon . $character . $day;
            case 'DMY':
                $day = substr($date, 0, 2);
                $mon = substr($date, 3, 2);
                $year = substr($date, 6, 4);
                return $year . $character . $mon . $character . $day;
        }

        return '';
    }
    public function monthToChar($month, $lang){
        if($lang=='en') {
            switch ($month) {
                case 1:
                    return 'January';
                case 2:
                    return 'February';
                case 3:
                    return 'March';
                case 4:
                    return 'April';
                case 5:
                    return 'May';
                case 6:
                    return 'June';
                case 7:
                    return 'July';
                case 8:
                    return 'August';
                case 9:
                    return 'September';
                case 10:
                    return 'Octtober';
                case 11:
                    return 'November';
                case 12:
                    return 'December';
            }
        } elseif ($lang == 'vn') {
            switch ($month) {
                case 1:
                    return 'Tháng 1';
                case 2:
                    return 'Tháng 2';
                case 3:
                    return 'Tháng 3';
                case 4:
                    return 'Tháng 4';
                case 5:
                    return 'Tháng 5';
                case 6:
                    return 'Tháng 6';
                case 7:
                    return 'Tháng 7';
                case 8:
                    return 'Tháng 8';
                case 9:
                    return 'Tháng 9';
                case 10:
                    return 'Tháng 10';
                case 11:
                    return 'Tháng 11';
                case 12:
                    return 'Tháng 12';
            }
        }
        return '';
    }

    //CAC HAM CHUYEN DOI NGAY CHUAN THANH DANG NGAY KHAC

    function monthTo3letter($month)
    {
        switch ($month) {
            case 1:
                return 'Jan';
            case 2:
                return 'Feb';
            case 3:
                return 'Mar';
            case 4:
                return 'Apr';
            case 5:
                return 'May';
            case 6:
                return 'Jun';
            case 7:
                return 'Jul';
            case 8:
                return 'Aug';
            case 9:
                return 'Sep';
            case 10:
                return 'Oct';
            case 11:
                return 'Nov';
            case 12:
                return 'Dec';
        }
        return '';
    }

    //CAC HAM CHUYEN DOI DANG NGAY KHAC THANH NGAY CHUAN



    public function numberFormate($n)
    {
        if ($n < 10) {
            return "0" . $n;
        } else {
            return $n;
        }
    }
    public function localDateTimeToServer($string){
        $arr = explode('T',$string);
        return $arr[0].' '.$arr[1].':00';
    }
    public function toServerDate($string){
        $fomate = $this->detectDateFormat($string);
        switch ($fomate){
            case 'd-m-Y':
                return $this->formatViewDate($string);
            case 'd-m-Y H:i:s':
                return $this->formatViewDate($string).' '.$this->getTime($string);
            case 'localdatetime':
                return $this->localDateTimeToServer($string);
            default:
                return $string;
        }
    }
    public function detectDateFormat($string){
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $string))
            return 'Y-m-d';

        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $string))
            return 'Y-m-d H:i:s';
        if (preg_match('/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}$/', $string))
            return 'd-m-Y H:i:s';
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $string))
            return 'd-m-Y';
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $string))
            return 'localdatetime';
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{3}$/', $string))
            return 'Y-m-d H:i:s.v';

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $string))
            return 'm/d/Y';

        if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $string))
            return 'd.m.Y';
        return '';
    }
}
