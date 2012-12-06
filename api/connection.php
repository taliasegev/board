<?php

function getConnection() {

    $dbhost="localhost"; // 127.0.0.1
    $dbuser="root";
    $dbpass="";          // secretpasswor
    $dbname="board";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}

function getSampleUsers() {
	return array(
		"sampleMin"=>5,
		"sampleMax"=>7
		);
}

?>