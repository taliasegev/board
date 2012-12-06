<?php
/**
 * Step 1: Require the Slim PHP 5 Framework
 *
 * If using the default file layout, the `Slim/` directory
 * will already be on your include path. If you move the `Slim/`
 * directory elsewhere, ensure that it is added to your include path
 * or update this file path as needed.
 */
require 'Slim/Slim.php';

require 'DataLayer.php';
require 'QA.php';

//sleep(5);

ob_start('ob_gzhandler');

date_default_timezone_set("UTC");
header('Content-Type: text/html; charset=utf-8');
/**
 * Step 2: Instantiate the Slim application
 *
 * Here we instantiate the Slim application with its default settings.
 * However, we could also pass a key-value array of settings.
 * Refer to the online documentation for available settings.
 */
$app = new Slim();
$app->add(new Slim_Middleware_ContentTypes());

// tasks
$app->get('/teams/:teamId/tasks/', 'getTasks');
$app->post('/tasks/', 'addTask');
$app->put('/tasks/:guid', 'updateTask');
$app->delete('/tasks/:guid', 'deleteTask');


$app->get('/teams/:teamId/tasks', 'getTasksForTeam');

$app->run();
 
function getTasks($teamId) {
    $tasks = _getTasksForTeam($teamId);
    echo json_encode($tasks);
}

function addTask()
{
//    $request = Slim::getInstance()->request();
//    $taskParams = $request->getBody();
//    $task = _addTask($taskParams);
//    echo json_encode($task);
}

function updateTask($guid) 
{
//    $request = Slim::getInstance()->request();
//    $taskParams = $request->getBody();
//    $task = _updateTask($guid, $taskParams);
//    echo json_encode($task);
}

function deleteTask($guid){
//    _deleteTask($guid);
}

function getTasksForTeam($teamId)
{
    $tasks = _getTasksForTeam($teamId);
    echo json_encode($tasks);
}

ob_end_flush();

