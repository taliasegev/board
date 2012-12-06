<?php

require 'utils.php';
require 'connection.php';
// tasks fields
//  id  guid    title   cDate   uDate   taskOrder   deadline    bucketId    completed   userId  eDate   eWeek   points  week    description recycle deleted

// user fields
//  userId  guid    cDate   uDate   title   facebookId  facebookLink    facebookUsername    gender  timezone    locale  stars   todayDate   week    weekOneDate

// stats fields
// statsId  guid    newTasks    completedTasks  weeklyStars week    userId

function _genericResponse($ok, $errorCode, $errorText) {
    $errorCode = $errorCode ?: 0;
    
    $response = new stdClass;
    $response->ok = $ok;
    $response->errorCode = $errorCode;
    $response->errorText = $errorText;

    return $response;
}

// returns user object
function _addUser($user) 
{
    $timezone = $user["timezone"];
    $user["todayDate"] = userDate($timezone);
    $user["weekOneDate"] = closestMonday($timezone);

    $sql = "INSERT INTO users(title, facebookId, facebookUsername, facebookLink, timezone, locale, gender, todayDate, weekOneDate, browserName, browserVersion, osName) 
                       VALUES(:title, :facebookId, :facebookUsername, :facebookLink, :timezone, :locale, :gender, :todayDate, :weekOneDate, :browserName, :browserVersion, :osName)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("title", $user["title"]);
        $stmt->bindParam("facebookId", $user["facebookId"]);
        $stmt->bindParam("facebookUsername", $user["facebookUsername"]);
        $stmt->bindParam("facebookLink", $user["facebookLink"]);
        $stmt->bindParam("timezone", $user["timezone"]);
        $stmt->bindParam("locale", $user["locale"]);
        $stmt->bindParam("gender", $user["gender"]);
        $stmt->bindParam("todayDate", $user["todayDate"]);
        $stmt->bindParam("weekOneDate", $user["weekOneDate"]);
        $stmt->bindParam("browserName", $user["browserName"]);
        $stmt->bindParam("browserVersion", $user["browserVersion"]);
        $stmt->bindParam("osName", $user["osName"]);
        $stmt->execute();
        $user["id"] = $db->lastInsertId();
        $db = null;
        return $user;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}



// returns user object
function _loginUser($userParams) {

    $userId= fetchUser($userParams);
    $user = _getUserByFacebookId($userParams["facebookId"]);
    $week = $user["week"];

    setUserId($userId);
    setUserIdMaster($userId);

    setWeek($week);

    return $user;
}

// returns user object
function _loginAs($userGUID) {

    $masterId= getUserIdMaster();

    $user = __getUserByGUID($userGUID);
    if(!$user) return;

    $userId = $user->id;

    $userPermission = _getUserPermission($userId, $masterId);

    if($userPermission!=null || $masterId==$userId) {
       $week = $user->week;

        setUserId($userId);

        setWeek($week);            

        $user->permissionOk = true;
    } else {
        $user->permissionOk = false;
    }

    return $user;
}

function _getUserPermission($userId, $toUserId) 
{

    $sql = "SELECT userGUID, userId, toUserId FROM userPermissions WHERE userId=:userId AND toUserId=:toUserId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("toUserId", $toUserId);
        $stmt->execute();
        $userPermission = $stmt->fetchObject();
        $db = null;
        return $userPermission;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

// returns user object
function _getUser($guid) 
{
    $userId = getUserId();

    $sql = "SELECT guid as id, title,facebookId, stars, week, sampleStateId, weekOneDate, language, themeId, progressAmount, accountType, 
            forest1Title, forest2Title, forest3Title, autoClear, searchInCompleted FROM users WHERE guid=:guid AND userId=:userId";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("guid", $guid);
        $stmt->bindParam("userId", $userId);
        $stmt->execute();
        $user = $stmt->fetchObject();
        $db = null;
        return $user;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

// returns user object
function _getUserByCookie() 
{
    $userId = getUserId();

    $sql = "SELECT guid as id, title,facebookId, stars, week, sampleStateId, weekOneDate, language, themeId, progressAmount, accountType,
            forest1Title, forest2Title, forest3Title, autoClear, searchInCompleted FROM users WHERE userId=:userId";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->execute();
        $user = $stmt->fetchObject();
        $db = null;

        if (!$user) {
            return null;
        }
 
        $week = $user->week;
        setWeek($week);
       
        return $user;
 
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

// WARNING: returns userID - do ***** NOT ***** use for API - only for innerMethods
function __getUserByGUID($guid) 
{
    $sql = "SELECT userId as id, guid, title,facebookId, stars, week, sampleUser, sampleStateId, weekOneDate, language, themeId, progressAmount, accountType,
            forest1Title, forest2Title, forest3Title, autoClear, searchInCompleted FROM users WHERE guid=:guid";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("guid", $guid);
        $stmt->execute();
        $user = $stmt->fetchObject();
        $db = null;

         if (!$user) {
            return null;
        }
       
        return $user;
 
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

// WARNING: returns userID - do ***** NOT ***** use for API - only for innerMethods
function __getUserById($userId) 
{
    $sql = "SELECT userId as id, guid, title,facebookId, stars, week, sampleUser, sampleStateId, weekOneDate, language, themeId, progressAmount, accountType,
            forest1Title, forest2Title, forest3Title, autoClear, searchInCompleted FROM users WHERE userId=:userId";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->execute();
        $user = $stmt->fetchObject();
        $db = null;

       if (!$user) {
            return null;
        }
       
        return $user;
 
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _getUserByFacebookId($facebookId) 
{
    $sql = "SELECT userId, week FROM users WHERE facebookId=:facebookId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("facebookId", $facebookId);
        $stmt->execute();
        $user = $stmt->fetchObject();
        $db = null;
        return $user;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _updateUserStars($stars) 
{
    $userId = getUserId();
 
    $sql = "UPDATE users SET stars=:stars WHERE userId=:userId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("stars", $stars);
        $stmt->execute();
        $db = null;
        return _genericResponse(true, 0, 'ok');
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }

    return $stars;
}


function _updateUserSampleStateId($sampleStateId) 
{
    $userId = getUserId();
 
    $sql = "UPDATE users SET sampleStateId=:sampleStateId WHERE userId=:userId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("sampleStateId", $sampleStateId);
        $stmt->execute();
        $db = null;
        return _genericResponse(true, 0, 'ok');
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
       return null;
    }

    return $sampleStateId;
}


function _updateUser($guid, $userParams) 
{
    $userId = getUserId();

    $autoClear = $userParams["autoClear"] ? 1 : 0;
    $searchInCompleted = $userParams["searchInCompleted"] ? 1 : 0;
 
    $sql = "UPDATE users SET language=:language, themeId=:themeId, progressAmount=:progressAmount, accountType=:accountType, searchInCompleted=:searchInCompleted,
                forest1Title=:forest1Title, forest2Title=:forest2Title, forest3Title=:forest3Title, autoClear=:autoClear  WHERE guid=:guid AND userId=:userId";

    setLanguage($userParams["language"]);
    setTheme($userParams["themeId"]);

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);

        $stmt->bindParam("language", $userParams["language"]);
        $stmt->bindParam("themeId", $userParams["themeId"]);
        $stmt->bindParam("progressAmount", $userParams["progressAmount"]);
        $stmt->bindParam("forest1Title", $userParams["forest1Title"]);
        $stmt->bindParam("forest2Title", $userParams["forest2Title"]);
        $stmt->bindParam("forest3Title", $userParams["forest3Title"]);
        $stmt->bindParam("accountType", $userParams["accountType"]);
        $stmt->bindParam("autoClear", $autoClear);
        $stmt->bindParam("searchInCompleted", $searchInCompleted);

        $stmt->bindParam("guid", $guid);
        $stmt->bindParam("userId", $userId);
        $stmt->execute();
        $db = null;
        return null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }

    return $userParams;
}

function _getBucketsLanguage($language) {
    $path = '../mocks/'.$language.'/buckets.json';
    $template =   file_get_contents($path);
    return $template;
}

function _getBuckets() {

    $userId = getUserId();

    $sql = "SELECT guid as id, title, procrastinateToBucketId, moveBackBucketId, bucketId FROM buckets";
    
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $buckets = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        return $buckets;

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return _genericResponse(false, 1, '_getBuckets failed');
    }
}

function _addTask($task) 
{
  $userId = getUserId();
  $week = getWeek();
  $points = 1;
  $color = $task["color"] ? $task["color"] : 0;

  $stars = _plusNewTask($week, 1);
  $starsTotal = _plusStars($stars);

    $sql = "INSERT INTO tasks(title, codename, taskOrder, bucketId, deadLine, completed, userId, description, recycle, week, points, color) 
                       VALUES(:title,:codename, :taskOrder, :bucketId, :deadline, :completed, :userId, :description, :ongoing, :week, :points, :color)";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);

        $stmt->bindParam("userId", $userId);

        $stmt->bindParam("title", $task["title"]);
        $stmt->bindParam("codename", $task["codename"]);
        $stmt->bindParam("taskOrder", $task["taskOrder"]);
        $stmt->bindParam("bucketId", $task["bucketId"]);
        $stmt->bindParam("deadline", $task["deadline"]);
        $stmt->bindParam("completed", $task["completed"]);
        $stmt->bindParam("description", $task["description"]);
        $stmt->bindParam("ongoing", $task["ongoing"]);
        $stmt->bindParam("week", $week);
        $stmt->bindParam("points", $points);
        $stmt->bindParam("color", $color);

        $stmt->execute();
        $task["id"] = $db->lastInsertId();

        $db = null;

        $task = _getTask($task["id"]); 
        $task->pointsTotal = $starsTotal;

        return $task;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _getTasks()
{
    $sql = "SELECT guid as id, title, description, duration, cDate FROM tasks";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->execute();
        $tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        return $tasks;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _getTasksByBucket($bucketId)
{
    $userId = getUserId();

    $sql = "SELECT guid as id, title, codename, DATE(cDate) as cDate, DATE(eDate) as eDate, taskOrder, deadline, bucketId, completed, description, color, 
            recycle as ongoing, week, eWeek, points, iphoneEventGUID, iphoneEventStartDate, taskTime, isActive FROM tasks WHERE userId=:userId AND deleted=0 AND bucketId=:bucketId";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("bucketId", $bucketId);
        $stmt->execute();
        $tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        return $tasks;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _getTask($taskId)
{
    $userId = getUserId();

    $sql = "SELECT guid as id, title, codename, DATE(cDate) as cDate, DATE(eDate) as eDate, taskOrder, deadline, bucketId, completed, description, color, 
            recycle as ongoing, week, eWeek, points, iphoneEventGUID, iphoneEventStartDate, taskTime, isActive FROM tasks WHERE userId=:userId AND id=:taskId";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("taskId", $taskId);
        $stmt->execute();
        $task = $stmt->fetchObject();
        $db = null;
        return $task;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _getTaskByGUID($taskGUID)
{
    $userId = getUserId();

    $sql = "SELECT guid as id, title, codename, DATE(cDate) as cDate, DATE(eDate) as eDate, taskOrder, deadline, bucketId, completed, description, color, 
            recycle as ongoing, week, eWeek, points, iphoneEventGUID, iphoneEventStartDate, taskTime, isActive FROM tasks WHERE userId=:userId AND guid=:taskGUID";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("taskGUID", $taskGUID);
        $stmt->execute();
        $task = $stmt->fetchObject();
        $db = null;
        return $task;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _doneTaskPoints($guid, $done) 
{
    $week = getWeek();

    $taskData = _getTaskData($guid);

    $eWeek = $taskData->eWeek;
    $completed = $taskData->completed;
    $stars = 0;

    if(!$completed && $done) {
        $stars = _plusCompletedTask($week,1);
    }

    if($completed && !$done) {
        $stars = _plusCompletedTask($eWeek,-1);
    }
    
    $starsTotal = _plusStars($stars);

    return $starsTotal;
}

function _deleteTaskPoints($guid) 
{
    $taskData = _getTaskData($guid);
    $week = $taskData->week;
    $eWeek = $taskData->eWeek;
    $completed = $taskData->completed;

    if($completed!=null) {
        $stars = _plusNewTask($week, -1);
    }else{
        $stars = 0;
    }

    $starsTotal = _plusStars($stars);

    if($completed) {
        $starsTotal = _doneTaskPoints($guid, false);
    }

    return $starsTotal;
}

//  id  guid    title   cDate   uDate   taskOrder   deadline    bucketId    completed   userId  eDate   eWeek   points  week    description recycle deleted
function _doneTask($guid, $task) 
{
    $userId = getUserId();
    $week = getWeek();
    $eDate = userDate(0);
    $points = 6;
    $completed = 1;

    $starsTotal = _doneTaskPoints($guid, true);

    $sql = "UPDATE tasks SET completed=:completed, eDate=:eDate, eWeek=:eWeek, points=:points 
            WHERE guid=:guid AND userId=:userId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam("guid", $guid);
        $stmt->bindParam("userId", $userId);

        $stmt->bindParam("completed", $completed);
        $stmt->bindParam("eDate", $eDate);
        $stmt->bindParam("eWeek", $week);
        $stmt->bindParam("points", $points);
        $stmt->execute();
        $task["id"] = $db->lastInsertId();
        $db = null;

        $task["eWeek"] = $week; 
        $task["eDate"] = $eDate;
        $task["points"] = $points;
        $task["completed"] = $completed;
        $task["pointsTotal"] = $starsTotal;


        return _getTaskByGUID($guid);        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _undoneTask($guid, $task) 
{

    //var_dump($task);

    $userId = getUserId();

    $week = getWeek();
    $eWeekNew = 0;
    
    $points = 1;
    $completed = 0;

    $starsTotal = _doneTaskPoints($guid, false);

    $sql = "UPDATE tasks SET completed=:completed,points=:points 
            WHERE guid=:guid AND userId=:userId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam("guid", $guid);
        $stmt->bindParam("userId", $userId);

        $stmt->bindParam("completed", $completed);
        //$stmt->bindParam("eWeek", $eWeekNew);
        $stmt->bindParam("points", $points);
        $stmt->execute();
        $task["id"] = $db->lastInsertId();
        $db = null;

        //$task["eWeek"] = $eWeekNew;         
        $task["points"] = $points;
        $task["completed"] = $completed;
        $task["pointsTotal"] = $starsTotal;
        

        return _getTaskByGUID($guid);        
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _setIPhoneEvent($guid, $task) 
{
    $userId = getUserId();

    $iphoneEventGUID = $task["iphoneEventGUID"];
    $iphoneEventStartDate = $task["iphoneEventStartDate"];

    $sql = "UPDATE tasks SET iphoneEventGUID=:iphoneEventGUID, iphoneEventStartDate=:iphoneEventStartDate
            WHERE guid=:guid AND userId=:userId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam("guid", $guid);
        $stmt->bindParam("userId", $userId);

        $stmt->bindParam("iphoneEventGUID", $iphoneEventGUID);
        $stmt->bindParam("iphoneEventStartDate", $iphoneEventStartDate);

        $stmt->execute();
        $task["id"] = $db->lastInsertId();
        $db = null;


        return _getTaskByGUID($guid);        

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _updateTask($guid, $task) 
{

    $sql = "UPDATE tasks SET title=:title
            WHERE guid=:guid";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam("guid", $guid);
        $stmt->bindParam("title", $task["title"]);
        $stmt->execute();
        $db = null;

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _deleteTask($guid) 
{
    $userId = getUserId();

    $starsTotal = _deleteTaskPoints($guid);

    $sql = "DELETE FROM tasks WHERE guid=:guid AND userId=:userId";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("guid", $guid);
        $stmt->bindParam("userId", $userId);
        $stmt->execute();
        $db = null;

        return array("pointsTotal"=>$starsTotal);

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _statsSummary()
{
    $week = getWeek();

    $stats = _getAllStats();

    $statsThisWeek = statsByWeek($stats, $week);
    $statsLastWeek = statsByWeek($stats, $week-1);
    $statsTotal = statsTotal($stats);

    $statsSummary = array(
            "lastWeekNewTasks"=>$statsLastWeek->newTasks,
            "lastWeekCompletedTasks"=>$statsLastWeek->completedTasks,
            "lastWeekStars"=>$statsLastWeek->weeklyStars,
            "thisWeekNewTasks"=>$statsThisWeek->newTasks,
            "thisWeekCompletedTasks"=>$statsThisWeek->completedTasks,
            "thisWeekStars"=>$statsThisWeek->weeklyStars,
            "totalNewTasks"=>$statsTotal->newTasks,
            "totalCompletedTasks"=>$statsTotal->completedTasks,
            "totalStars"=>$statsTotal->weeklyStars,
            "week"=>$week
        );

    return $statsSummary;
}

function _getAllStats() 
{

    $userId = getUserId();

    $sql = "SELECT guid as id, completedTasks, newTasks, weeklyStars, week, statsId FROM userStats WHERE userId = :userId";
    
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->execute();
        $stats = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        return $stats;

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _getStatsByWeek($userId, $week) 
{
    $sql = "SELECT statsId FROM userStats WHERE userId=:userId AND week=:week";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("week", $week);
        $stmt->execute();
        $stats = $stmt->fetchObject();
        $db = null;
        return $stats;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _addStats($userId, $week) 
{
    $sql = "INSERT INTO userStats(userId, week) VALUES(:userId, :week)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("week", $week);
        $stmt->execute();
        $db = null;
        return _getStatsByWeek($userId, $week);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _duplicateTasks($fromUserId) 
{
     $userId = getUserId();
     $sampleUsers = getSampleUsers();

     if ($fromUserId < $sampleUsers["sampleMin"] || $fromUserId > $sampleUsers["sampleMax"]) {
        return 0;
     }

    $sql = "INSERT INTO `tasks`(`title`, `cDate`, `uDate`, `taskOrder`, `deadline`, `bucketId`, `completed`, `userId`, `eDate`, `eWeek`, `points`, `week`, `description`, `recycle`, `deleted`, `iphoneEventGUID`, codename, `iphoneEventStartDate`, `sample`)
                SELECT `title`, `cDate`, `uDate`, `taskOrder`, `deadline`, `bucketId`, `completed`, :userId as `userId`, `eDate`, `eWeek`, `points`, `week`, `description`, `recycle`, `deleted`, `iphoneEventGUID`, codename, `iphoneEventStartDate`, 1 as `sample`
                FROM `tasks` 
                WHERE userId=:fromUserId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
 
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("fromUserId", $fromUserId);
        $stmt->execute();
        return $stmt->rowCount();

        $db = null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return 0;
    }
}

function _deleteSampleTasks() 
{
     $userId = getUserId();


    $sql = "DELETE FROM `tasks` 
                WHERE sample=1 AND userId=:userId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
 
        $stmt->bindParam("userId", $userId);
        $stmt->execute();
        $db = null;

        return $stmt->rowCount();

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return 0;
    }
}

function _getOrderRange($bucketId) {
    $userId = getUserId();

   $sql = "SELECT MIN(taskOrder) as minOrder, MAX(taskOrder) as maxOrder, COUNT(*) as taskCount FROM tasks WHERE userId=:userId AND bucketId=:bucketId";
   
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("bucketId", $bucketId);
        $stmt->execute();
        $orderRange = $stmt->fetchObject();


        $db = null;
        return $orderRange;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _bucketMove($fromBucketId, $toBucketId, $last) 
{
    $userId = getUserId();
    $interval = 1;
    $count = 0;
    $firstOrder = 0;

    $fromOrderRange = _getOrderRange($fromBucketId);
    $toOrderRange = _getOrderRange($toBucketId);

    // maxOrder minOrder, taskCount

    if ($last) {
        $order = $toOrderRange->maxOrder ?: 0;
        $interval = 1;
    } else {
        $taskOrder = $toOrderRange->minOrder ?: 0;
        $count = $fromOrderRange->taskCount ?: 0;

        $interval = $taskOrder / ($count + 1);
        $order = 0;
    }

    //echo "order: $order ; interval: $interval ; count: $count";
    
    try {
        $db = getConnection();

        $sql = "SET @start=:start;SET @delta=:delta;UPDATE tasks SET taskTime=99, taskOrder = @start:= (@start+@delta), bucketId=:toBuckedId WHERE bucketId=:fromBucketId AND userId=:userId ORDER BY taskOrder";
        $stmt = $db->prepare($sql);
 

        $stmt->bindParam("start", $order);
        $stmt->bindParam("delta", $interval);
        
        $stmt->bindParam("fromBucketId", $fromBucketId);
        $stmt->bindParam("toBuckedId", $toBucketId);
        $stmt->bindParam("userId", $userId);

        $stmt->execute();

        $db = null;
        return _genericResponse(true, 0, 'ok');
        //return _getTasksByBucket($toBucketId);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return _genericResponse(false, 1, '');
    }
}

function _bucketDistributeTime($bucketId, $startHour, $interval) 
{
    $userId = getUserId();
    
    try {
        $db = getConnection();

        $sql = "SET @start=:start;SET @delta=:delta;UPDATE tasks SET taskTime = @start:= (@start+@delta) WHERE bucketId=:bucketId AND userId=:userId ORDER BY taskOrder";
        $stmt = $db->prepare($sql);
 

        $stmt->bindParam("start", $startHour);
        $stmt->bindParam("delta", $interval);
        
        $stmt->bindParam("bucketId", $bucketId);
        $stmt->bindParam("userId", $userId);

        $stmt->execute();

        $db = null;
        return null;
        //return _getTasksByBucket($toBucketId);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return 0;
    }
}

function _bucketSortByTime($bucketId) 
{
    $userId = getUserId();
    
    try {
        $db = getConnection();

        $sql = "SET @start=0;SET @delta=1;UPDATE tasks SET taskOrder = @start:= (@start+@delta) WHERE bucketId=:bucketId AND userId=:userId ORDER BY taskTime";
        $stmt = $db->prepare($sql);
 

        $stmt->bindParam("bucketId", $bucketId);
        $stmt->bindParam("userId", $userId);

        $stmt->execute();

        $db = null;

        return null;
        //return _getTasksByBucket($bucketId);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return 0;
    }
}

function _bucketPostpone($bucketId, $delta) {
    $userId = getUserId();

   $sql = "UPDATE tasks SET taskTime=taskTime + :delta WHERE userId=:userId AND bucketId=:bucketId AND taskTime!=99";
   
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("bucketId", $bucketId);
        $stmt->bindParam("delta", $delta);
        $stmt->execute();

        $db = null;
        return null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _bucketClearTime($bucketId) {
    $userId = getUserId();

   $sql = "UPDATE tasks SET taskTime=99 WHERE userId=:userId AND bucketId=:bucketId";
   
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("bucketId", $bucketId);
        $stmt->execute();

        $db = null;
        return null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _bucketRevive($bucketId) {
    $userId = getUserId();

   $sql = "UPDATE tasks SET deadline=NOW() + INTERVAL 14 DAY WHERE userId=:userId AND bucketId=:bucketId";
   
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("bucketId", $bucketId);
        $stmt->execute();

        $db = null;
        return null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _bucketClear($bucketId) {
    $userId = getUserId();

   $sql = "UPDATE tasks SET bucketId=99 WHERE userId=:userId AND bucketId=:bucketId AND completed = true";
   
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("bucketId", $bucketId);
        $stmt->execute();

        $db = null;
        return null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _bucketEvent($bucketId, $operationParams)
{
    //var_dump($operationParams);
    $action = $operationParams["action"];
    $bucketId = isset($operationParams["bucketId"]) ? $operationParams["bucketId"] : -1;

    switch ($action) {
        case 'bucketMove':
            //$tasks = $operationParams["bucket"];
            $fromBucketId = $operationParams["fromBucketId"];
            $toBucketId = $operationParams["toBucketId"];
            $last = $operationParams["last"];

            return _bucketMove($fromBucketId, $toBucketId, $last);
            break;

        case 'bucketClear':
            return _bucketClear($bucketId);
            break;

        case 'bucketSortByTime':
            return _bucketSortByTime($bucketId);
            break;
        
        case 'bucketPostponeTime':
            $delta = $operationParams["delta"];

            return _bucketPostpone($bucketId, $delta);
            break;

         case 'bucketDistributeTime':
            $startHour = $operationParams["startHour"];
            $delta = $operationParams["delta"];

            return _bucketDistributeTime($bucketId, $startHour, $delta);
            break;

         case 'bucketClearTime':
            return _bucketClearTime($bucketId);
            break;

        case 'bucketRevive':
            return _bucketRevive($bucketId);
            break;
       
    }
}


function _addStatsBulk($userId) 
{
    $sql = "INSERT INTO userStats(userId, week) VALUES(:userId, :week)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
 
        for($week=1;$week<=50;$week++) {
            $stmt->bindParam("userId", $userId);
            $stmt->bindParam("week", $week);
            $stmt->execute();
        }

        $db = null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function _addStatsBulkByCookie() 
{
    $userId = getUserId();
    _addStatsBulk($userId);
}

function createStats($userId) {
    for($week=1 ; $week <=50 ; $week++) {
        _getStatsByWeek($userId, $week);
    }
}

function _updateStats($guid, $stats)
{
    $userId = getUserId();

    $sql = "UPDATE userStats SET newTasks = :newTasks, completedTasks = :completedTasks, weeklyStars=:weeklyStars WHERE guid=:guid";
        try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam("guid", $guid);

        $stmt->bindParam("newTasks", $stats["newTasks"]);
        $stmt->bindParam("completedTasks", $stats["completedTasks"]);
        $stmt->bindParam("weeklyStars", $stats["weeklyStars"]);
        $stmt->execute();
        $db = null;

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

    return $stats;       
}

function _getUserPermissions()
{
    $userId = getUserIdMaster();


    $sql = "SELECT userPermissions.userGUID, users.title
FROM  `userPermissions` 
LEFT JOIN users ON userPermissions.userId = users.userId WHERE userPermissions.toUserId=:userId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->execute();
        $permissions = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        return $permissions;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _plusNewTask($week, $amount)
{
    $userId = getUserId();
    $stars = $amount *1;

    $sql = "UPDATE userStats SET newTasks = newTasks+(:amount), weeklyStars=weeklyStars+(:stars) WHERE week=:week AND userId=:userId";
        try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("week", $week);
        $stmt->bindParam("amount", $amount);
        $stmt->bindParam("stars", $stars);

        $stmt->execute();
        $db = null;

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

    return $stars;       
}

function _plusCompletedTask($week, $amount)
{
  $userId = getUserId();
  $stars = $amount *5;

    $sql = "UPDATE userStats SET completedTasks = completedTasks+(:amount), weeklyStars=weeklyStars+(:stars) WHERE week=:week AND userId=:userId";
        try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("week", $week);
        $stmt->bindParam("amount", $amount);
        $stmt->bindParam("stars", $stars);

        $stmt->execute();
        $db = null;

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

    return $stars;       
}

function _plusStars($amount)
{
  $userId = getUserId();

    $sql1 = "UPDATE users SET stars = stars+(:amount) WHERE userId=:userId";
    $sql2 = "SELECT stars FROM users WHERE userId=:userId";
        try {
        $db = getConnection();
        $stmt = $db->prepare($sql1);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("amount", $amount);
        $stmt->execute();

        $stmt = $db->prepare($sql2);
        $stmt->bindParam("userId", $userId);
        $stmt->execute();
        $user = $stmt->fetchObject();

        $db = null;

        return $user->stars;

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _getTaskData($guid)
{
  $userId = getUserId();

    $sql = "SELECT eWeek, week, completed FROM tasks WHERE guid=:guid AND userId=:userId";
        try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("guid", $guid);
        $stmt->execute();
        $task = $stmt->fetchObject();

        $db = null;

        return $task;

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function fixStars()
{     
    $userId = getUserId();

    $sql1 = "UPDATE tasks SET points=1  WHERE userId = :userId AND completed=0";
    $sql2 = "UPDATE tasks SET points=6  WHERE userId = :userId AND completed=1";

    try {
        $db = getConnection();
        
        $stmt = $db->prepare($sql1);
        $stmt->bindParam("userId", $userId);
        $stmt->execute();

        $stmt = $db->prepare($sql2);
        $stmt->bindParam("userId", $userId);
        $stmt->execute();
        $db = null;

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }

    $statsAll = _getAllStats();
    foreach ($statsAll as $key => $value) {
        fixStatsByWeek($value->week);
    }

    $stars = _starsByWeek(0);

    _updateUserStars($stars);
}

function moveBetweenBuckets($fromBucketId, $toBucketId, $limit) {

    $userId = getUserId();

    $sql = "UPDATE tasks SET bucketId=:toBucketId  WHERE bucketId=:fromBucketId AND userId = :userId LIMIT :limit";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam("userId", $userId);

        $stmt->bindParam("toBucketId", $toBucketId);
        $stmt->bindParam("fromBucketId", $fromBucketId);
        $stmt->bindValue("limit", (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $db = null;

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function nudgeTasks($days, $amount){

    if($days == 1) {
        moveBetweenBuckets(1,0,300);
        moveBetweenBuckets(2,1,300);
        moveBetweenBuckets(3,2,$amount);
    }else if($days>=2) {
        moveBetweenBuckets(1,0,300);
        moveBetweenBuckets(2,0,300);
        moveBetweenBuckets(3,2,$amount);
    }

    return true;
}

function _getUserWeekData($userId)
{
    $sql = "SELECT week, weekOneDate, todayDate, timezone, progressAmount FROM users WHERE userId = :userId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->execute();
        $user = $stmt->fetchObject();
        $db = null;

        return $user;

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _setUserWeek($userId, $week) 
{
  $userId = getUserId();

    $sql = "UPDATE users SET week=:nowWeek WHERE userId = :userId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam("userId", $userId);

        $stmt->bindParam("nowWeek", $week);
        $stmt->execute();
        $db = null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function _setUserToday($userId, $todayDate) 
{
  $userId = getUserId();

    $sql = "UPDATE users SET todayDate=:todayDate WHERE userId = :userId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam("userId", $userId);

        $stmt->bindParam("todayDate", $todayDate);
        $stmt->execute();
        $db = null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function cycleWeeks($userId)
{

    $user = _getUserWeekData($userId);
    if(!$user) {return;}

    $lastWeek = $user->week;

    $weekOneDate = $user->weekOneDate;

    $timezone = floatval($user->timezone);
   
    $userDate = userDate($timezone);

    $diff = abs(strtotime($userDate) - strtotime($weekOneDate))+10;

    $nowWeek = ceil($diff / (60*60*24*7));

    if($nowWeek == $lastWeek) {
        return;
    }

    _setUserWeek($userId, $nowWeek);
}

function cycleTasks($userId)
{
    $user = _getUserWeekData($userId);

    if(!$user) {return;}

    $lastDate = $user->todayDate;
    $lastDate = date("Y-m-d H:i:s",strtotime($lastDate) + 60 * 60 * 2);

    $timezone = floatval($user->timezone);
    $userDate = userDateFull($timezone);


    $diff = abs(strtotime($userDate) - strtotime($lastDate));
    $days = floor($diff / (60*60*24));

    //echo "lastDate: $lastDate ; timezone: $timezone ; userDate: $userDate ; diff : $diff ; days : $days";

    if($days < 1) {
        return;
    }

    nudgeTasks($days, $user->progressAmount);

    _setUserToday($userId, $userDate);
}

function _getTasksCount($week) 
{
    $userId = getUserId();

    $sql ="SELECT count(*) as taskCount FROM tasks WHERE userId=:userId AND (week=:week OR :week=0)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("week", $week);
        $stmt->execute();
        $taskData = $stmt->fetchObject();
        $db = null;

        return $taskData->taskCount;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return 0;
    }
}

function _getCompletedTasksCount($eWeek) 
{
    $userId = getUserId();

    $sql ="SELECT count(*) as taskCount FROM tasks WHERE userId=:userId AND completed=1 AND (eWeek=:eWeek OR :eWeek=0)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("eWeek", $eWeek);
        $stmt->execute();
        $statsData = $stmt->fetchObject();
        $db = null;

        return $statsData->taskCount;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return 0;
    }
}

function _starsByWeek($week) 
{
    $userId = getUserId();

    // new
    $sql1 ="SELECT count(*) as taskCount FROM tasks WHERE userId=:userId AND (week=:week OR :week=0)";

    // completed
    $sql2 ="SELECT count(*) as taskCount FROM tasks WHERE userId=:userId AND completed=1 AND (eWeek=:week OR :week=0)";
    
    try {
        $stars = 0;

        $db = getConnection();

        $stmt = $db->prepare($sql1);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("week", $week);
        $stmt->execute();
        $statsData = $stmt->fetchObject();
        $stars = $stars + ($statsData->taskCount * 1);

        $stmt = $db->prepare($sql2);
        $stmt->bindParam("userId", $userId);
        $stmt->bindParam("week", $week);
        $stmt->execute();
        $statsData = $stmt->fetchObject();
        $stars = $stars + ($statsData->taskCount * 5);

        $db = null;

        if($stars == null) {
            $stars = 0;
        }

        return $stars;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return 0;
    }
}

function fixStatsByWeek($week) 
{
    $userId = getUserId();

    $newTasks = _getTasksCount($week);
    $completedTasks = _getCompletedTasksCount($week);
    $weeklyStars = ($newTasks * 1) + ($completedTasks * 5);

    //echo "userId: $userId, new: $newTasks, completed: $completedTasks, stars: $weeklyStars , week: $week \n";

     $sql = "UPDATE userStats SET newTasks=:newTasks, completedTasks=:completedTasks, weeklyStars=:weeklyStars WHERE userId = :userId AND week = :week ";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);

        //echo pdo_sql_debug($sql, array('newTasks'=>$newTasks, 'completedTasks'=>$completedTasks, 'weeklyStars'=>$weeklyStars, 'userId'=>$userId, 'week'=>$week));

        $stmt->bindParam("newTasks", $newTasks);
        $stmt->bindParam("completedTasks", $completedTasks);
        $stmt->bindParam("weeklyStars", $weeklyStars);
        
        $stmt->bindParam("userId", $userId);

        $stmt->bindParam("week", $week);
        $stmt->execute();

        $db = null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

// returns userId
function fetchUser($userParams) {

    $userId=0;
    $week = 1;

    $facebookId = $userParams["facebookId"];

    $user = _getUserByFacebookId($facebookId);


    if(!$user) {

        $user = _addUser($userParams);
        $userId = $user["id"];
        $week = $user->week;
        _addStatsBulk($userId);
        setNewUser(true);

    }else{
        $userId = $user->userId;
        $week = $user->week;
        setNewUser(false);
    }


    setUserId($userId);
    setUserIdMaster($userId);
    setWeek($week);

    return $userId;
}

function _setWeek($guid, $week) 
{
    $userId = getUserId();

    $sql = "UPDATE tasks SET week=:week
            WHERE guid=:guid AND userId=:userId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam("guid", $guid);
        $stmt->bindParam("userId", $userId);

        $stmt->bindParam("week", $week);

        $stmt->execute();
        $db = null;

        return $task;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function _setEWeek($guid, $eWeek) 
{
    $userId = getUserId();

    $sql = "UPDATE tasks SET eWeek=:eWeek
            WHERE guid=:guid AND userId=:userId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam("guid", $guid);
        $stmt->bindParam("userId", $userId);

        $stmt->bindParam("eWeek", $eWeek);

        $stmt->execute();
        $db = null;

        return $task;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

function fixEWeeks(){
    $userId = getUserId();

    $tasks = _getTasks();
    $userData = _getUserWeekData($userId);
    $weekOneDate = $userData->weekOneDate;

   //echo $userData->weekOneDate . "<br />";

    foreach($tasks as $task) {

       $diff = abs(strtotime($task->cDate) - strtotime($weekOneDate));

       $week = ceil($diff / (60*60*24*7));
       _setWeek($task->id, $week);


        if($task->completed) 
        {
       $diff = abs(strtotime($task->eDate) - strtotime($weekOneDate));

       $eWeek = ceil($diff / (60*60*24*7));
       _setEWeek($task->id, $eWeek);

        //echo $task->eWeek. " ". $task->eDate." ".$eWeek;
        //echo "<br />"; 
    }
    }

    //echo json_encode($tasks);
}




?>