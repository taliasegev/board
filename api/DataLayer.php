<?
require 'utils.php';
require 'connection.php';

function _getTasksForTeam($teamId)
{
    $sql = "SELECT tasks.taskId, tasks.cDate, tasks.title, tasks.description, tasks.duration, tasks.guid, tasks.traitId1,
            (SELECT title FROM traits WHERE tasks.traitId1 = traits.traitId) AS traitTitle1,
            tasks.traitPoints1,
            tasks.traitId2,
            (SELECT title FROM traits WHERE tasks.traitId2 = traits.traitId) AS traitTitle2,
             tasks.traitPoints2,
            tasks.traitId3,
            (SELECT title FROM traits WHERE tasks.traitId3 = traits.traitId) AS traitTitle3,
            tasks.traitPoints3,
            tasks.coins,
            tasks.completed,
            taskTypes.typeTitle,
            taskTypes.colorCharacter,
            taskTypes.colorIndex,
            taskTypes.color,
            tasks.userId
            FROM  `tasks`  INNER JOIN `taskTypes`
            ON tasks.typeId = taskTypes.typeId
            where tasks.teamId=:teamId";

    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);

        $stmt->bindParam("teamId", $teamId);

        $stmt->execute();
        $tasks = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        return $tasks;
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

?>
