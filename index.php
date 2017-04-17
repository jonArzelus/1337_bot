<?php

/*1337_bot*/

include "db/dbOpen.php";

//config
$website="https://api.telegram.org/bot".$botToken;
date_default_timezone_set('Europe/Madrid');
//$data = date(DATE_RSS, time());

//lortu azken update id from database
$sql="SELECT lastid FROM config";
$emaitza=$db->query($sql);
$offset=-1;
if($emaitza) {
	$offset=$emaitza->fetch_assoc()['lastid'];
} else {
	$offset=-1; //default
}
$offset1=$offset+1;

$update=file_get_contents($website."/getUpdates?offset=".$offset1);
$updateArray=json_decode($update,true);

$chatID=-1;
//trabajar respuesta
for ($i = 0; $i < count($updateArray["result"]); $i++) {
	if($updateArray["result"][$i]["update_id"]>$offset) {
		$offset=$updateArray["result"][$i]["update_id"];
	}
	$chatID=$updateArray["result"][$i]["message"]["chat"]["id"];
	$logged=false;
	$date=date('Y-m-d H:i:s');
	$sql="SELECT * FROM start_table WHERE chat_id='$chatID'";
	$emaitza=$db->query($sql);
	if($emaitza) {
		$logged=true;
		$sql="UPDATE start_table SET last_update='$date' WHERE chat_id='$chatID'"; //first login here
		$emaitza=$db->query($sql);
	} else {
		$sql="INSERT INTO start_table(chat_id, last_update, login_date) VALUES('$chatID', '$date', '$date')"; //first login here
		$emaitza=$db->query($sql);
	}
}

if($offset==-1) {
	file_get_contents($website."/sendMessage?chat_id=".$chatID."&text=No new messages");
} else {
	$offset1=$offset+1;
	$date=date('Y-m-d H:i:s');
	$sql="TRUNCATE TABLE config";
	$db->query($sql);
	$sql="INSERT INTO config VALUES('$offset','$date')";
	$db->query($sql);
	file_get_contents($website."/sendMessage?chat_id=".$chatID."&text=Last update_id is ".$offset.", next update_id to use is ".$offset1);
}

?>