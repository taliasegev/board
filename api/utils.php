<?php
function setWeek($week){

    $userId = getUserId();

    //if(session_id() == '') {

    if(!isset($_SESSION)) {
        session_start();
    }

    $_SESSION['week'] = $week;
}

function setUserId($userId){
    //if(session_id() == '') {

    if(!isset($_SESSION)) {
        session_start();
    }

    $_SESSION['userId'] = $userId;
}

function getUserId(){

    if(!isset($_SESSION)) {
        session_start();
    }

    if(isset($_SESSION['userId'])){
        $userId = $_SESSION['userId'];
    }else{
        $userId = 0;
    }

    return $userId;
}

function getLanguage(){

   
    if(isset($_COOKIE['language'])){
        $language = $_COOKIE['language'];
    }else{
        $language = "en";
    }

    return $language;
}

function setLanguage($language){

    setcookie("language", $language, time() + 60 * 60 * 24 * 365, "/");

}

function getNewUser(){

   
    if(isset($_COOKIE['newUser'])){
        $newUser = $_COOKIE['newUser'];
    }else{
        $newUser = false;
    }

    return $newUser;
}

function setNewUser($newUser){

    setcookie("newUser", $newUser, time() + 60 * 60 * 24 * 365, "/");

}

function getTheme(){
    if(isset($_COOKIE['themeId'])){
        $themeId = $_COOKIE['themeId'];
    }else{
        $themeId = 1;
    }

    return $themeId;
}

function setTheme($themeId){

    setcookie('themeId', $themeId, time() + 60 * 60 * 24 * 365, "/");
}


function setUserIdMaster($userId){
    //if(session_id() == '') {

    if(!isset($_SESSION)) {
        session_start();
    }

    $_SESSION['userIdMaster'] = $userId;
}

function getUserIdMaster(){

    if(!isset($_SESSION)) {
        session_start();
    }

    if(isset($_SESSION['userIdMaster'])){
        $userId = $_SESSION['userIdMaster'];
    }else{
        $userId = 0;
    }

    return $userId;
}

function setLogoutUrl($logoutUrl){

    //if(session_id() == '') {

    if(!isset($_SESSION)) {
        session_start();
    }

    $_SESSION['logoutUrl'] = $logoutUrl;
}


function getWeek(){
    if(!isset($_SESSION)) {
        session_start();
    }

    if(isset($_SESSION['week'])){
        $week = $_SESSION['week'];
    }else{
        $week = 0;
    }

    return $week;
}

function getSession(){
    $session = array(
          "userId"=>getUserId(),
          "week"=>getWeek()
      );

    return $session;
}

function getLogoutUrl(){

    //if(session_id() == '') {

    if(!isset($_SESSION)) {
        session_start();
    }

    
    if(isset($_SESSION['logoutUrl'])){
        $logoutUrl = $_SESSION['logoutUrl'];
    }else{
        $logoutUrl = '';
    }

    return $logoutUrl;
}

function userDateFull($timezone) {
    $now  = date("Y-m-d H:i:s");
    $nowTime = time($now); //Change date into time

    $userTime = $nowTime+$timezone * 60*60;

    $userDate = date("Y-m-d H:i:s",$userTime);
    //echo $userDate;
    //echo ' '.strftime('%H:%M',$userTime);

    return $userDate;
}

function userDate($timezone) {
    $now  = date("Y-m-d H:i:s");
    $nowTime = time($now); //Change date into time

    $userTime = $nowTime+$timezone * 60*60;

    $userDate = date("Y-m-d",$userTime);
    //echo $userDate;
    //echo ' '.strftime('%H:%M',$userTime);

    return $userDate;
}

function userTime($timezone) {
    $now  = date("Y-m-d H:i:s");
    $nowTime = time($now); //Change date into time

    $userTime = $nowTime + ($timezone * 60 * 60);

    return strftime('%H:%M',$userTime);
}

function dayDifference($dateLater, $date)
{
    $diff = abs(strtotime($dateLater) - strtotime($date));
    $days = floor($diff / (60*60*24));

    return $days;
}

function minutesDifference($dateLater, $date)
{
    $diff = abs(strtotime($dateLater) - strtotime($date));
    $minutes = floor($diff / (60));

    return $minutes;
}

function closestSunday($timezone){
  $now = userDate($timezone);

  $dayOfWeek = date("w");
  $closestSunday = date('Y-m-d', strtotime($now)-24*60*60*$dayOfWeek); 

  return $closestSunday;
}

function closestMonday($timezone){
  $now = userDate($timezone);

  $dayOfWeek = date("w");
  $closestMonday = date('Y-m-d', strtotime($now)-24*60*60* ($dayOfWeek - 1)); 

  return $closestMonday;
}

function pdo_sql_debug($sql,$placeholders){
    foreach($placeholders as $k => $v){
        $sql = preg_replace('/:'.$k.'/',"'".$v."'",$sql);
    }
    return $sql;
}

function statsByWeek($allStats, $week) {

    foreach($allStats as $k => $v){
        if($v->week==$week) {
            return $v;
        }
    }

    $output = new stdClass;
    $output->newTasks = 0;
    $output->completedTasks = 0;
    $output->weeklyStars = 0;

    return $output;
}

function statsTotal($allStats) {

    $total = new stdClass;
    $total->weeklyStars = 0;
    $total->newTasks = 0;
    $total->completedTasks = 0;
   
    foreach($allStats as $k => $v){
        $total->weeklyStars += intval($v->weeklyStars);
        $total->newTasks += intval($v->newTasks);
        $total->completedTasks += intval($v->completedTasks);
    }

    return $total;
}

function objectToArray($d) {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }
 
        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return array_map(__FUNCTION__, $d);
        }
        else {
            // Return array
            return $d;
        }
    }

?>