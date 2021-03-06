<?php
if (!file_exists('ZermeloRoosterPHP'))
{
      exit("Installeer alsjeblieft eerst Zermelo-Telegram-Bot!");
}
require 'ZermeloRoosterPHP/Cache.php';
require 'ZermeloRoosterPHP/Zermelo.php';
require 'config.php';

date_default_timezone_set("Europe/Amsterdam");
$startTime = time();

$getUpdates = $website.'getUpdates';

while (true){
	$result = @file_get_contents($getUpdates);
	if ($result === false) {
	     echo "Updates krijgen mislukt.\n";
	}
	$result = json_decode($result, true);
	$result = $result["result"];
	
	$offset = getOffset($result);
	$chatId = getChatId($result);
	$message = getMessage($result);
	$messageId = getMessageId($result);
	$userId = getUserId($result);
	$firstName = getFirstName($result);
	$lastName = getLastName($result);
	$group = getIfGroup($result);
	
	$file = "gebruikers/".$userId.".txt";
	if (!file_exists($file) && !empty($message)){
		$fp = fopen($file, "w");
		if ($fp != false)
		{
			fwrite($fp, "\n\n\n\n");
			fclose($fp);
			echo "Nieuw bestand aangemaakt voor: '".$userId." (".$firstName." ".$lastName.")'.\n";
		}
	}
	
	$offset++;
	file_get_contents($getUpdates.'?offset='.$offset);
	
	switch(true){
		
// 		Welkomstbericht:

		case $message == "/start":
			sendMessage($chatId, "Welkom bij de Telegram bot voor Zermelo! Deze bot werkt ook in groepen.", null, $group);
			sendMessage($chatId, "Gemaakt door Bas van den Wollenberg (@BasvdW), Candea College.", null, $group);
			sendMessage($chatId, "'/changelog' om notificaties te ontvangen over veranderingen/toevoegingen aan de bot.", null, $group);
			sendMessage($chatId, "Krijg meer info over registreren met '/registreer'.", null, $group);
			sendMessage($chatId, "Na het registreren kun je je rooster opvragen met '/rooster'.", null, $group);
			sendMessage($chatId, "_Deze bot is niet aansprakelijk voor te laat komingen en/of fouten in het rooster._", null, $group);
		break;
		
// 		Commands:

		case $message == "/ping":
			sendMessage($chatId, "_Pong!_", null, $group);
		break;
		case $message == "/restart":
			if($userId == "125874268"){
				sendMessage($chatId, "Bot herstarten...", null, $group);
				print_r("Bot herstarten...\n");
				exit(2);
			} else {
				sendMessage($chatId, "Je hebt niet de rechten om de bot te herstarten.", null, $group);
			}
		break;
		case $message == "/registreer":
			$content = file($file);
			if ($content[0] == "\n" && $content[1] == "\n" && $content[2] == "\n"){
				sendMessage($chatId, "Stuur je leerlingnummer met '/leerlingnummer <leerlingnummer>'.\nStuur je appcode met '/code <appcode>' (Zermelo portal > Koppelingen > Koppel App).\nStuur je schoolnaam met '/school <schoolnaam>' (Zermelo portal > Koppelingen > Koppel App).", $messageId, $group);
			} elseif ($content[0] == "\n"){
				sendMessage($chatId, "Stuur je leerlingnummer met '/leerlingnummer <leerlingnummer>'.", $messageId, $group);
			} elseif ($content[1] == "\n" || !$content[1]){
				sendMessage($chatId, "Stuur je appcode met '/code <appcode>' (Zermelo portal > Koppelingen > Koppel App).", $messageId, $group);
			} elseif ($content[2] == "\n" || !$content[2]){
				sendMessage($chatId, "Stuur je schoolnaam met '/school <schoolnaam>' (Zermelo portal > Koppelingen > Koppel App).", $messageId, $group);
			} else {
				sendMessage($chatId, "Je bent al volledig geregistreerd! Vraag je rooster op met '/rooster'.", $messageId, $group);
			}
		break;
		
// 		Registratie:

		case 0 === strpos($message, '/leerlingnummer'):
				$content = file($file);
				
				@$leerlingnummer = explode(" ",$message)[1];
				
				if ($leerlingnummer == null){
					sendMessage($chatId, "'/leerlingnummer <leerlingnummer>'", $messageId, $group);
				} elseif ($content[0] == $leerlingnummer || $content[0] == $leerlingnummer."\n"){
					sendMessage($chatId, "Je leerlingnummer is al opgeslagen!", $messageId, $group);
				} else {
					try {
						$fp = fopen($file, "w+");
						$content[0] = $leerlingnummer."\n";
						fwrite($fp, implode($content, ''));
						fclose($fp);
						sendMessage($chatId, "Je leerlingnummer is veranderd naar: ".$leerlingnummer, $messageId, $group);
						print_r("Leerlingnummer '".$userId." (".$firstName." ".$lastName.")' veranderd naar '".$leerlingnummer."'.\n");
					} catch (Exception $e) {
						sendMessage($chatId, "Er is iets misgegaan bij het opslaan van je leerlingnummer.", $messageId, $group);
						print_r("Veranderen leerlingnummer van '".$userId." (".$firstName." ".$lastName.")' naar '".$leerlingnummer."' mislukt.\n");
					}
				}
		break;
		
		case 0 === strpos($message, "/code"):
			$content = file($file);
			
			@$code = explode(" ",$message)[1];
			
			if ($code == null){
				sendMessage($chatId, "'/code <appcode>' (Zermelo portal > Koppelingen > Koppel App)", $messageId, $group);
			} elseif ($content[1] == $code || $content[1] == $code."\n"){
				sendMessage($chatId, "Stuur een nieuwe code!", $messageId, $group);
			} else {
				try {
					$fp = fopen($file, "w+");
					$content[1] = $code."\n";
					fwrite($fp, implode($content, ''));
					fclose($fp);
					sendMessage($chatId, "Je appcode is veranderd naar: ".$code, $messageId, $group);
					print_r("Appcode '".$userId." (".$firstName." ".$lastName.")' veranderd naar '".$code."'.\n");
				} catch (Exception $e) {
					sendMessage($chatId, "Er is misgegaan bij het opslaan van je appcode.", $messageId, $group);
					print_r("Veranderen appcode '".$userId." (".$firstName." ".$lastName.")' naar '".$code."' mislukt.\n");
				}
			}
		break;
		
		case 0 === strpos($message, "/school"):
			$content = file($file);
			
			@$school = explode(" ",$message)[1];
			
			if ($school == null){
				sendMessage($chatId, "'/school <school>' (Zermelo portal > Koppelingen > Koppel App)", $messageId, $group);
			} elseif ($content[2] == $school || $content[2] == $school."\n"){
				sendMessage($chatId, "Je school is al opgeslagen!", $messageId, $group);
			} else {
				try {
					$fp = fopen($file, "w+");
					$content[2] = $school."\n";
					fwrite($fp, implode($content, ''));
					fclose($fp);
					sendMessage($chatId, "Je school is veranderd naar: ".$school, $messageId, $group);
					print_r("School '".$userId." (".$firstName." ".$lastName.")' veranderd naar '".$school."'.\n");
				} catch (Exception $e) {
					sendMessage($chatId, "Er is iets misgegaan bij het opslaan van je school.", $messageId, $group);
					print_r("Veranderen school '".$userId." (".$firstName." ".$lastName.")' naar '".$school."' mislukt.\n");
				}
			}
		break;
		
//		Rooster opvragen:

		case $message == "/rooster":
			$content = file($file);
			
			if (!$content[0] || $content[0] == "\n" || !$content[1] || $content[1] == "\n" || $content[2] == "\n" || !$content[2]){
				sendMessage($chatId, "Je bent nog niet volledig geregistreerd, meer informatie: /registreer", $messageId, $group);
			} else {
// 				sendMessage($chatId, "Welke dag van de week?", $messageId.'&reply_markup={"keyboard":[["Vandaag","Morgen"],["Maandag","Dinsdag"],["Woensdag","Donderdag","Vrijdag"]],"one_time_keyboard":true,"selective":true,"resize_keyboard":true}', true);
				sendMessage($chatId, "Welke dag van de week?", $messageId.'&reply_markup={"keyboard":[["Maandag","Dinsdag"],["Woensdag","Donderdag","Vrijdag"]],"one_time_keyboard":true,"selective":true,"resize_keyboard":true}', true);
			}
		break;
		case strtolower($message) == "maandag":
			$content = file($file);
			$leerlingnummer = $content[0];
			$school = $content[2];
			
			$date = getDay("maandag");
			getTimetable($leerlingnummer, $school, $date, "maandag");
		break;
		case strtolower($message) == "dinsdag":
			$content = file($file);
			$leerlingnummer = $content[0];
			$school = $content[2];
			
			$date = getDay("dinsdag");
			getTimetable($leerlingnummer, $school, $date, "dinsdag");
		break;
		case strtolower($message) == "woensdag":
			$content = file($file);
			$leerlingnummer = $content[0];
			$school = $content[2];
			
			$date = getDay("woensdag");
			getTimetable($leerlingnummer, $school, $date, "woensdag");
		break;
		case strtolower($message) == "donderdag":
			$content = file($file);
			$leerlingnummer = $content[0];
			$school = $content[2];
			
			$date = getDay("donderdag");
			getTimetable($leerlingnummer, $school, $date, "donderdag");
		break;
		case strtolower($message) == "vrijdag":
			$content = file($file);
			$leerlingnummer = $content[0];
			$school = $content[2];
			
			$date = getDay("vrijdag");
			getTimetable($leerlingnummer, $school, $date, "vrijdag");
		break;
			
// 			Changelog notificaties:

		case $message == "/changelog":
			sendMessage($chatId, "Het changelog systeem is nu een kanaal:\nhttps://telegram.me/zermelo", $messageId, $group);
/*			$content = file($file);
			if (($changelog = strpos($message, " ")) !== false) {
				$changelog = substr($message, $changelog+1);
			}
			
			if ($userId == "125874268" && $changelog != null){
				$i = 0;
				foreach(glob("gebruikers/*") as $file) {
					if ($file != "gebruikers/geregistreerd.txt")
						$content = file($file);
						if ($content[3] == "true" || $content[3] == "true\n"){
							sendMessage(basename($file, ".txt"), $changelog, $messageId, $group);
 							sendMessage($chatId, "'".$changelog."' succesvol verstuurd naar: '".basename($file, ".txt")."'.", $messageId, $group);
							print_r("'".$changelog."' succesvol verstuurd naar: '".basename($file, ".txt")."'.\n");
							$i++;
						}
				}
				sendMessage($chatId, "'".$changelog."' succesvol verstuurd naar ".$i." mensen.", $messageId, $group);
			} elseif ($changelog != null){
				sendMessage($chatId, "Je hebt niet de rechten om een changelog bericht te sturen.", $messageId, $group);
			} elseif (!$content[3] || $content[3] == "\n"){
				try {
						$fp = fopen($file, "w+");
						$content[3] = "true\n";
						fwrite($fp, implode($content, ''));
						fclose($fp);
						sendMessage($chatId, "Vanaf nu ontvang je notificaties over veranderingen/toevoegingen aan de bot.", $messageId, $group);
						print_r("'".$userId." (".$firstName." ".$lastName.")' heeft zich aangemeld voor de changelog.\n");
					} catch (Exception $e) {
						sendMessage($chatId, "Er is iets misgegaan bij het abonneren op notificaties.", $messageId, $group);
						print_r("Aanmelden van '".$userId." (".$firstName." ".$lastName.")' voor de changelog mislukt.\n");
					}
			} else {
				try {
						$fp = fopen($file, "w+");
						$content[3] = "\n";
						fwrite($fp, implode($content, ''));
						fclose($fp);
						sendMessage($chatId, "Vanaf nu ontvang je *geen* notificaties meer over veranderingen/toevoegingen aan de bot.", $messageId, $group);
						print_r("'".$userId." (".$firstName." ".$lastName.")' heeft zich afgemeld voor de changelog.\n");
					} catch (Exception $e) {
						sendMessage($chatId, "Er is iets misgegaan bij het opzeggen van het ontvangen van notificaties.", $messageId, $group);
						print_r("Afmelden van '".$userId." (".$firstName." ".$lastName.")' voor de changelog mislukt.\n");
					}
			}
		break;
*/
	}
}


function getOffset($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		if (isset($array[$key]["update_id"])){
			return $array[$key]["update_id"];
		}
	}
}

function getChatId($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		if (isset($array[$key]["message"]["chat"]["id"])){
			return $array[$key]["message"]["chat"]["id"];
		}
	}
}

function getMessage($array){
	global $startTime;
	
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		if (isset($array[$key]["message"]["date"])){
			if ($array[$key]["message"]["date"] > $startTime){
				if (isset($array[$key]["message"]["text"])){
					return str_replace("@ZermeloBot", "", $array[$key]["message"]["text"]);
				}
			}
		}
	}
}

function getMessageId($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		if (isset($array[$key]["message"]["message_id"])){
			return $array[$key]["message"]["message_id"];
		}
	}
}

function getUserId($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		if (isset($array[$key]["message"]["from"]["id"])){
			return $array[$key]["message"]["from"]["id"];
		}
	}
}

function getIfGroup($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		if (isset($array[$key]["message"]["chat"]["type"]) && $array[$key]["message"]["chat"]["type"] == "group"){
			return true;
		} else {
			return false;
		}
	}
}

function getFirstName($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		if (isset($array[$key]["message"]["from"]["first_name"])){
			return $array[$key]["message"]["from"]["first_name"];
		}
	}
}

function getLastName($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		if (isset($array[$key]["message"]["from"]["last_name"])){
			return $array[$key]["message"]["from"]["last_name"];
		}
	}
}

function getDay($day){
	switch($day){
		case "maandag":
			return date("d/m/Y", strtotime("monday this week"));
		break;
		case "dinsdag":
			return date("d/m/Y", strtotime("tuesday this week"));
		break;
		case "woensdag":
			return date("d/m/Y", strtotime("wednesday this week"));
		break;
		case "donderdag":
			return date("d/m/Y", strtotime("thursday this week"));
		break;
		case "vrijdag":
			return date("d/m/Y", strtotime("friday this week"));
		break;
	}
}

function getTimetable($leerlingnummer, $school, $date, $day){
	global $file, $chatId, $messageId, $group, $userId, $firstName, $lastName;
	
	$content = file($file);
	$leerlingnummer = $content[0];
	
	if (!$content[0] || $content[0] == "\n" || !$content[1] || $content[1] == "\n" || $content[2] == "\n" || !$content[2]){
		sendMessage($chatId, "Je bent nog niet volledig geregistreerd, meer informatie: /registreer", $messageId, $group);
	} else {
		$leerlingnummer = substr($content[0], 0, -1);
		$code = substr($content[1], 0, -1);
		$school = substr($content[2], 0, -1);
	
		try {
			if (strpos(file_get_contents("gebruikers/geregistreerd.txt"), $leerlingnummer) === false) {
				try {
					// register_zermelo_api();
					$zermelo = new ZermeloAPI($school);
	
					$zermelo->grabAccessToken($leerlingnummer, $code);
	
					file_put_contents("gebruikers/geregistreerd.txt", $leerlingnummer.PHP_EOL , FILE_APPEND);
				} catch (Exception $e){
					sendMessage($chatId, "Er is iets migegaan bij het ophalen van de token voor Zermelo, stuur je leerlingnummer/school en/of een nieuwe appcode van het Zermelo portaal opnieuw.", $messageId, $group);
					print_r("Ophalen rooster '".$userId." (".$firstName." ".$lastName.")' mislukt. Fout:\n".$e->getMessage());
				}
			}
			// register_zermelo_api();
			$zermelo = new ZermeloAPI($school);
			
			$rooster = $zermelo->getStudentGrid($leerlingnummer);
			
			$subjectss = array();
			$teachers = array();
			$locations = array();
			$start = array();
			$end = array();
			$timetable = array();
			
			foreach($rooster as $subArray) {
				$subjectss[] = implode(" ", $subArray["subjects"]);
				$teachers[] = implode(" ", $subArray["teachers"]);
				$locations[] = implode(" ", $subArray["locations"]);
				$start[] = $subArray["start_date"];
				$end[] = $subArray["end_date"];
				$cancelled[] = $subArray["cancelled"];
			}
			
			$mi = new MultipleIterator();
			$mi->attachIterator(new ArrayIterator($subjectss));
			$mi->attachIterator(new ArrayIterator($teachers));
			$mi->attachIterator(new ArrayIterator($locations));
			$mi->attachIterator(new ArrayIterator($start));
			$mi->attachIterator(new ArrayIterator($end));
			$mi->attachIterator(new ArrayIterator($cancelled));
			
			foreach ($mi as $value) {
				list ($subjects, $teachers, $locations, $start, $end, $cancelled) = $value;
				
				if ($subjects != ""){
					$subjects = strtoupper($subjects);
				}
				if ($teachers != ""){
					$teachers = " - ".strtoupper($teachers);
				}
				if ($locations != ""){
					$locations = " - ".strtoupper($locations);
				}
				
				if (substr($start, 0, 10) == $date){
					if ($cancelled == 1){
						$timetable[] = "_*".$subjects.$teachers.$locations." | ".substr($start, 11, 15)." - ".substr($end, 11, 15)."*_";
					} else {
						$timetable[] = $subjects.$teachers.$locations." | ".substr($start, 11, 15)." - ".substr($end, 11, 15);
					}
				}
			}
			$timetable = implode("\n", $timetable);
			
			sendMessage($chatId, "*Jouw rooster van ".$day.":*\n".$timetable, $messageId, $group);
			print_r("Rooster van '".$userId." (".$firstName." ".$lastName.")' succesvol opgehaald.\n");
		} catch (Exception $e){
			sendMessage($chatId, "Er is iets migegaan bij het ophalen/versturen van je rooster, stuur je leerlingnummer/school en/of een nieuwe appcode van het Zermelo portaal opnieuw.", $messageId, $group);
			print_r("Ophalen/versturen rooster '".$userId." (".$firstName." ".$lastName.")' mislukt.\n");
		}
	}
}

function sendMessage($chatId, $message, $messageId, $group){
	global $website;
	
	file_get_contents($website."sendChatAction?chat_id=".$chatId."&action=typing");
	
	if ($group == true){
		file_get_contents($sendMessage = $website."sendMessage?chat_id=".$chatId."&text=".urlencode($message)."&reply_to_message_id=".$messageId."&parse_mode=Markdown");
	} else {
		file_get_contents($sendMessage = $website."sendMessage?chat_id=".$chatId."&text=".urlencode($message)."&parse_mode=Markdown");
	}
}

function sendPhoto($chatId, $photo, $caption, $messageId, $group){
	global $website;
	
	file_get_contents($website."sendChatAction?chat_id=".$chatId."&action=typing");
	
	if ($group == true){
		$sendPhoto = $website."sendPhoto?chat_id=".$chatId."&photo=".$photo."&caption=".$caption."&reply_to_message_id=".$messageId;
	} else {
		$sendPhoto = $website."sendPhoto?chat_id=".$chatId."&photo=".$photo."&caption=".$caption;
	}
	file_get_contents($sendPhoto);
}

function sendSticker($chatId, $sticker, $messageId, $group){
	global $website;
	
	file_get_contents($website."sendChatAction?chat_id=".$chatId."&action=typing");
	
	if ($group == true){
		$sendSticker = $website."sendSticker?chat_id=".$chatId."&sticker=".$sticker."&reply_to_message_id=".$messageId;
	} else {
		$sendSticker = $website."sendSticker?chat_id=".$chatId."&sticker=".$sticker;
	}
	file_get_contents($sendSticker);
}
