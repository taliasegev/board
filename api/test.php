<?php

/*
$date = new DateTime($today, new DateTimeZone('Europe/Paris')); 
date_default_timezone_set('America/New_York'); 
echo date("Y-m-d h:iA", $date->format('U')); 
*/

require 'DataLayer.php';
require 'Browser.php';

date_default_timezone_set("UTC");

  
//fixEWeeks();
//fixStars();
_bucketMove(1, 2, 1);

function testBrowserVersion()
{
  $browser = new Browser();
echo $browser->getBrowser();
echo $browser->getVersion();
echo $browser->getPlatform();
}

function testLoginAs()
{
  $user = _loginAs("3263a186-fc7b-11e1-8cb7-8fdb9a757b07");
  echo json_encode($user);
}

function testCycleTasks()
{
  $userId = getUserId();
  cycleTasks($userId);
}

function testSampleTasks() 
{
  $rowsDeleted = _deleteSampleTasks();
  echo $rowsDeleted.' deleted <br />';

  $rowsAdded = _duplicateTasks(6);
  echo $rowsAdded.' added <br />';
}

function testDelete()
{
  $task = _deleteTask("ea512242-fac2-11e1-ab4c-0759aa4a715a");

  echo json_encode($task);
}

function testUndoneTask()
{
    $task = array(
        "lastAction"=>"undone",
        "title"=>"Run in the streets",
        "taskOrder"=>10.4,
        "bucketId"=>1,
        "deadline"=>"2012-10-10",
        "completed"=>0,
        "description"=>"do it with short pans",
        "ongoing"=>0
      );

  $task = _updateTask("d03a7272-e4e3-11e1-8881-6e5252a33b40",$task);

  echo json_encode($task);
}

function testDoneTask()
{
    $task = array(
        "lastAction"=>"done",
        "title"=>"Run in the streets",
        "taskOrder"=>10.4,
        "bucketId"=>1,
        "deadline"=>"2012-10-10",
        "completed"=>1,
        "description"=>"do it with short pans",
        "ongoing"=>0
      );

  $task = _updateTask("d03a7272-e4e3-11e1-8881-6e5252a33b40",$task);

  echo json_encode($task);
}

function testAddTask()
{
    $task = array(
        "title"=>"Run in the streets",
        "taskOrder"=>10.4,
        "bucketId"=>1,
        "deadline"=>"2012-10-10",
        "completed"=>0,
        "description"=>"do it with short pans",
        "ongoing"=>0
      );

  $task = _addTask($task);

  echo json_encode($task);
}

function testPlusMethods() {
  $stars = _plusCompletedTask(2,3);
  
  $stars = _plusStars($stars);

  $stars = _plusNewTask(2, 14);

  $stars = _plusStars($stars);

  echo $stars;

}


function testCycle($days)
{
    $userId = getUserId();

    $date = date("Y-m-d");
    $date = strtotime ('-'.$days.' days', strtotime($date) ) ;
    $date = date ('Y-m-d' , $date);

    $sql = "UPDATE users SET todayDate=:todayDate WHERE userId = :userId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        
        $stmt->bindParam("userId", $userId);

        $stmt->bindParam("todayDate", $date);
        $stmt->execute();
        $db = null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function testCreateUser() 
{
  $title = "James Bond";
$facebookId = "10";
$facebookUsername = "James.Bond";
$facebookLink = "http://www.facebook.com/james.bond";
$timezone = 3;
$locale = "en";
$gender = 1;


  $userParams = array(
            "title"=>$title,
            "facebookId"=>$facebookId,
            "facebookUsername"=>$facebookUsername,
            "facebookLink"=>$facebookLink,
            "timezone"=>$timezone,
            "locale"=>$locale,
            "gender"=>$gender
        );

$userId = fetchUser($userParams);

return;
}



?>
&#x2665;