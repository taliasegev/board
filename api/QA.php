<?php

function _updateUserKey($key, $value) 
{
    $userId = getUserId();
 
    $sql = "UPDATE users SET $key=:value WHERE userId=:userId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("value", $value);
        $stmt->execute();
        $db = null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return false;
    }
    return true;
}

function _updateWeek($week)
{
    $value = date("Y-m-j");
    $day = (date('w') - 1) + ($week * 7);
    $newdateNum = strtotime ( "-$day day" , strtotime ( $value ) ) ;

    $newdate = date ( 'Y-m-j' , $newdateNum);

    _updateUserKey("week", $week);
    _updateUserKey("weekOneDate", $newdate);

    return true;
}


function _getUserWeekDataQA($userId, $timezone)
{
    $user = _getUserWeekData($userId);

    if (!$user) {
        return;
    }

    $serverNow  = date("Y-m-d g:i a");
    $serverDate = date("Y-m-d");
    $serverTime = time($serverNow); //Change date into time
    $serverTimezone = date('O');

    $timezone = $timezone ? $timezone : $user->timezone;

    $userNow = userDateFull($timezone);
    $userTimezone = floatval($timezone);
    $userDate = userDate($userTimezone);
    $userTime = userTime($userTimezone);

    $user->serverDate = $serverDate;
    $user->serverTime = userTime(0);;
    $user->serverTimezone = $serverTimezone;
    $user->userDate = $userDate;
    $user->userTime = $userTime;
    $user->userTimezone = $user->timezone;

    $user->weekOneDateDOW = date('l', strtotime($user->weekOneDate));

    $nextWeekStartDate = date("Y-m-d g:i a", strtotime($user->weekOneDate) + ($user->week) * 7 * 24 * 60 * 60);
    $diff = minutesDifference($nextWeekStartDate, $userNow);
    $user->nextWeekCountdown = $diff;
    //echo "nextWeekStartDate: $nextWeekStartDate ; userNow: $userNow ; difference: $diff";

    // changes on 2:00am
    $time = strtotime($userTime); 
    $two = strtotime("02:00"); // 2am measured in seconds since Unix Epoch

    $diff = ($two - $time) / (60);

    if ($diff < 0) {
        $diff = $diff + 24 * 60;
    }
    
    //echo "time: $time ; two: $two ; difference: $diff";
    $user->nextDayCountdown = $diff; 

    return $user;
}

function _qaStats()
{
    $userId = getUserId();
    $user = _getUserWeekDataQA($userId, null);

    return $user;
}

function _qaResetTimezone()
{
    $userId = getUserId();
    _updateUserKey("timezone", 2);
    cycleWeeks($userId);

    return true;
}

function _qaAlmostDay()
{
    _qaResetTimezone();
    $userId = getUserId();
    $user = _getUserWeekDataQA($userId, +2);

    $diff = $user->nextDayCountdown;
    $timezone = $user->userTimezone;

    // gives the QA a minute to wait
    $diff = $diff + 5;

    $timezone = $timezone + ($diff / 60);

    while ($timezone < 24) {
        $timezone = $timezone + 24;
    }

    while ($timezone > 24) {
        $timezone = $timezone - 24;
    }

    return _updateUserKey("timezone", $timezone);
}

function _qaAlmostWeek()
{
    _qaResetTimezone();
    $userId = getUserId();
    $user = _getUserWeekDataQA($userId, +2);

    $diff = $user->nextWeekCountdown;
    $timezone = $user->userTimezone;

    // gives the QA a minute to wait
    $diff = $diff + 5;

    $timezone = $timezone + ($diff / 60);

    // while ($timezone < 0) {
    //     $timezone = $timezone + 24 * 7;
    // }

    return _updateUserKey("timezone", $timezone);
}


function qaValue($verb, $value) {
    switch ($verb) {
        case "facebookId":
            $ok = _updateUserKey("facebookId", 0);
            echo "current user's facebookId was cleared. ".($ok ? "OK" : "FALSE").".";
            break;
         case "clearUserCookie":
            $ok = true;
            setUserId(-1);
            echo "current user's facebookId was cleared. ".($ok ? "OK" : "FALSE").".";
            break;
        case "timezone":
            if ($value == "almostDay") {
                $ok = _qaAlmostDay();
                echo "current user's timezone was set to $value. ".($ok ? "OK" : "FALSE").".";
            } else if ($value == "almostWeek") {
                $ok = _qaAlmostWeek();
                echo "current user's timezone was set to $value. ".($ok ? "OK" : "FALSE").".";
            } else if ($value == "reset") {
                $ok = _qaResetTimezone();
                echo "current user's timezone was set to $value. ".($ok ? "OK" : "FALSE").".";
            }
            break;
        case "week":
            $ok = _updateWeek($value);
            echo "current user's week was set to $value. ".($ok ? "OK" : "FALSE").".";
            break;
        case "sampleState":
            $ok = _updateUserKey("sampleStateId", 0);
            echo "current user's sampleState was reseted. ".($ok ? "OK" : "FALSE").".";
            break;
        case "yesterday":
            $userId = getUserId();
            $user = _getUserWeekDataQA($userId, +2);

            $lastDate = $user->todayDate;
            $newDate = date("Y-m-j",strtotime($lastDate) - 60 * 60 * 24);

            //echo "lastDate: $lastDate ; newDate: $newDate";
            //$value = date("Y-m-j");
            //$newDate = strtotime ( '-1 day' , strtotime ( $value ) ) ;
            //$newDate = date ( 'Y-m-j' , $newDate);
            $ok = _updateUserKey("todayDate", $newDate);
            echo "current user's todayDate was set to yesterday. ".($ok ? "OK" : "FALSE").".";
            break;
        case "nudgeTasks":
            $userId = getUserId();
            $user = _getUserWeekData($userId);
            $progressAmount = $user->progressAmount;
            $ok = nudgeTasks($value, $progressAmount);
            echo "current user's tasks were nudged by $value days. $progressAmount tasks were moved from future list. ".($ok ? "OK" : "FALSE").".";
            break;
    }
}
?>