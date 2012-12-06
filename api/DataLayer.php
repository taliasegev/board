<?
require 'utils.php';
require 'connection.php';

// Get all tasks objects
function _getTasks()
{
    $sql = "SELECT   Tasks.guid as id, Tasks.cDate, Tasks.title, Tasks.colorId FROM  `Tasks`";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);

        $stmt->execute();
        $tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        return $tasks;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

// Update task object
function _updateTask($guid, $task)
{

    $sql = "UPDATE Tasks SET title=:title
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

// returns task object
function _insertTask($task)
{

    $sql = "INSERT INTO Tasks (title, colorId) VALUES(:title, :colorId)";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);

        $stmt->bindParam("title", $task["title"]);
        $stmt->bindParam("colorId", $task["colorId"]);

        $stmt->execute();
        $task["id"] = $db->lastInsertId();
        $db = null;
        return $task;

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return null;
    }
}

?>
