<?php

use App\Utils\Email;
use Dcat\Admin\Admin;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


/**
 * 檢查是否為手機
 * @return number
 */
function checkMobile(){
    $regex_match="/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";
    $regex_match.="htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";
    $regex_match.="blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";
    $regex_match.="symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";
    $regex_match.="jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220";
    $regex_match.=")/i";
    return preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT']));
}

function getSelfCount($userId){
    $query =DB::select(
            "select count(1) count from projects p , participants pt where p.user_id=? and p.id = pt.project_id
             and pt.group = 'Self'", [$userId]);

    return $query[0]->count;
}

function vaildSelfCount(){
    //查詢目前self 數量
    $selfCount = getSelfCount(Admin::user()->id);

    //如果user的quota 已經大於 self數量 就不能新增
    return $selfCount >= Admin::user()->quota;
}

function gtmTime($time_zone =0, $time = null,  $dateFormat="Y-m-d H:i:s"  ){

    if ($time == null) {
        $time = time();
    } else {
        $time = strtotime($time);
    }


    $offset= $time_zone *60*60; //converting 4 hours to seconds.
    $timeNdate=gmdate($dateFormat, $time + $offset); //get GMT date - 4

    return $timeNdate;
}

function lang($tw, $en) {
    return App::isLocale('zh-TW') ? $tw:$en;
}


 /**
  * 產生時間序號
  * @param String $symbol 代碼
  * @return string
  */
function uuid($symbol){
    list($usec, $sec) = explode(" ", microtime());
    $date = date("YmdHisx",$sec);
    return  $symbol.str_replace('x', substr($usec,2,3), $date);
}

/**
 * 寄送Email
 * @param  to 寄件者email
 * @param  subject 主旨
 * @param  view view 名稱
 * @param  datas 資料
 */
 function sendEmailView($to , $subject, $view, $datas){
    $email = new Email();
    $email->to($to);
    $email->subject($subject);
    $email->setView($view , $datas);
   // $email->from(env("MAIL_FROM_ADDRESS"),env("MAIL_FROM_NAME"));

    Mail::queue($email);

    return $email;
}

function format($num , $floatNum =2 ){
    return  number_format($num, $floatNum, '.', ',');
}

function formatNA($num , $floatNum =2 ){
    return is_string($num) && $num =="NA" ?"N.A." : number_format($num, $floatNum, '.', ',');
}

function formatNADisplay($num ){

    return is_string($num) && $num =="NA" ?'style="display:none;"' : "";
}

function  sendEmail($to , $subject, $html){
    $email = new Email();
    $email->bcc($to);
    $email->subject($subject);
    $email->html($html);
    $email->from(env("MAIL_FROM_ADDRESS"),env("MAIL_FROM_NAME"));
    Mail::queue($email);
    return $email;
}
?>