<?php

/*1337_bot*/

include "db/dbOpen.php";

//config
$website="https://api.telegram.org/bot".$botToken;
date_default_timezone_set('Europe/Madrid');

try {
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
	//$update=file_get_contents($website."/getUpdates?offset=".$offset1);
	file_get_contents("php://input");
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
						if(date('H:i')=="13:38") {
							$now = new DateTime();
							$future_date = new DateTime(date('Y-m-d').' 13:37:00');
							$interval = $future_date->diff($now);
							$dif = $interval->format("%s segundos");
							file_get_contents($website."/sendMessage?chat_id=".$chatID."&text=".$nombre." no ha llegado a tiempo al minuto mágico por ".$dif.". ¡Una lástima!");
						} else if(date('H:i')=="13:36") {
							$now = new DateTime();
							$future_date = new DateTime(date('Y-m-d').' 13:38:00');
							$interval = $future_date->diff($now);
							$dif = $interval->format("%s segundos");
							file_get_contents($website."/sendMessage?chat_id=".$chatID."&text=".$nombre." ha llegado antes de tiempo al minuto mágico por ".$dif.". ¡Ten paciencia!");
						} else {
							//consiguiendo la diferencia de tiempo
							$now = new DateTime();
							$future_date = new DateTime(date('Y-m').'-'.(date('d')+1).' 13:37:00');
							$interval = $future_date->diff($now);
							$dif = $interval->format("%h horas, %i minutos y %s segundos");
							file_get_contents($website."/sendMessage?chat_id=".$chatID."&text=Quizás en otro momento ".$nombre."... Faltan ".$dif." hasta el próximo minuto mágico");
						}
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
} catch(Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
}
?>
<html>
	<head></head>
	<body>
	   	<link rel="stylesheet"
	         href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
	         integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
	         crossorigin="anonymous"/>
	   	<!--<div class="container theme-showcase" role="main">-->
	   	<div class="container container-table">
    		<div class="row vertical-center-row">
	   			<div class="col-md-6 col-md-push-3 text-center alert alert-info" role="alert"><h3>Bot working zone...</h3></div>
			</div>
		</div>
	</body>
</html>