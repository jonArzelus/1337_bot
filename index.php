<?php

/*1337_bot*/

include "db/dbOpen.php";

//config
$website="https://api.telegram.org/bot".$botToken;
date_default_timezone_set('Europe/Madrid');

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

//eguneraketak lortu
$update=file_get_contents($website."/getUpdates?offset=".$offset1);
$updateArray=json_decode($update,true);

$chatID=-1;
//trabajar respuesta
for ($i = 0; $i < count($updateArray["result"]); $i++) {
	//offset eguneratu lortzeko
	if($updateArray["result"][$i]["update_id"]>$offset) {
		$offset=$updateArray["result"][$i]["update_id"];
	}
	$chatID=$updateArray["result"][$i]["message"]["chat"]["id"];
	$text=$updateArray["result"][$i]["message"]["text"];
	$nombre=$updateArray["result"][$i]["message"]["from"]["first_name"]." ".$updateArray["result"][$i]["message"]["from"]["last_name"];
	$date=date('Y-m-d H:i:s');
	if($text=="/start") {
		$sql="SELECT * FROM start_table WHERE chat_id='$chatID'";
		$emaitza=$db->query($sql);
		if($emaitza->num_rows > 0) {
			$loginData = $emaitza->fetch_assoc()['login_date'];
			$sql="UPDATE start_table SET last_update='$date' WHERE chat_id='$chatID'"; //not first login here
			$emaitza=$db->query($sql);
			file_get_contents($website."/sendMessage?chat_id=".$chatID."&text=Ya empezaste una conversación conmigo en ".$loginData);
		} else {
			$sql="INSERT INTO start_table(chat_id, last_update, login_date) VALUES('$chatID', '$date', '$date')"; //first login here
			$emaitza=$db->query($sql);
			file_get_contents($website."/sendMessage?chat_id=".$chatID."&text=¡De ahora en adelante te avisaré cuando sea el momento mágico! Puedes detener la conversación en cualquier momento escribiendo /stop");
		}
	} else if($text=="/stop") {
		$sql="SELECT * FROM start_table WHERE chat_id='$chatID'";
		$emaitza=$db->query($sql);
		if($emaitza->num_rows > 0) {
			$sql="DELETE FROM start_table WHERE chat_id='$chatID'"; //not first login here
			$emaitza=$db->query($sql);
			file_get_contents($website."/sendMessage?chat_id=".$chatID."&text=¡Espero volver a escribirnos pronto!");
		} else {
			file_get_contents($website."/sendMessage?chat_id=".$chatID."&text=No me consta que hayamos iniciado una conversación... Prueba con /start");
		}
	} else { //resto de comandos
		$sql="SELECT * FROM start_table WHERE chat_id='$chatID'";
		$emaitza=$db->query($sql);
		if($emaitza->num_rows > 0) {
			$sql="UPDATE start_table SET last_update='$date' WHERE chat_id='$chatID'"; //not first login here
			$emaitza=$db->query($sql);
			//realizar el proceso del mensaje
			if($text=="/1337") { //ver si se trata de nuestro amado 1337
				if(date('H:i')=="13:37") {
					file_get_contents($website."/sendMessage?chat_id=".$chatID."&text=Sin duda eres una persona que sabe apreciar lo que es bueno ".$nombre.". Eres una parte muy importante de 1337");
				} else {
					$dif=strtotime(date('H:i:s'))-strtotime("13:38:00"); //TODO
					file_get_contents($website."/sendMessage?chat_id=".$chatID."&text=".$nombre." no ha llegado a tiempo al minuto mágico por ".$dif." segundos. ¡Una lástima!");
				}
			} else {
				file_get_contents($website."/sendMessage?chat_id=".$chatID."&text=Veo que no puedes vivir sin mi. ¿Qué es lo que quieres decir con '".$text."'?");
			}
		} else {
			file_get_contents($website."/sendMessage?chat_id=".$chatID."&text=No me consta que hayamos iniciado una conversación... Prueba con /start");
		}
	}
}

if(($offset+1) == $offset1) {
	file_get_contents($website."/sendMessage?chat_id=".$admchatID."&text=>INFO No new messages");
} else {
	$offset1=$offset+1;
	$date=date('Y-m-d H:i:s');
	$sql="TRUNCATE TABLE config";
	$db->query($sql);
	$sql="INSERT INTO config VALUES('$offset','$date')";
	$db->query($sql);
	file_get_contents($website."/sendMessage?chat_id=".$admchatID."&text=>INFO Last update_id is ".$offset.", next update_id to use is ".$offset1);
}

?>