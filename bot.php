<?php
require 'ZermeloRoosterPHP/custom_autoload.php';
require 'config.php';

date_default_timezone_set("Europe/Amsterdam");
$date = date('d/m/Y', time());
$date1 = date("d/m/Y", strtotime("tomorrow"));

$getUpdates = $website.'getUpdates';

while (true){
	$result = file_get_contents($getUpdates);
	$result = json_decode($result, true);

	if (isset($result["result"])){
		$result = array_slice($result["result"], -10, 10, true);
	}
	
	$offset = getOffset($result);
	$chatId = getChatId($result);
	$message = strtolower(getMessage($result));
	$messageId = getMessageId($result);
	$userId = getUserId($result);
	$firstName = getFirstName($result);
	$lastName = getLastName($result);
	
	$file = "gebruikers/".$userId.".txt";
	if (!file_exists($file) && !empty($message)){
		$fp = fopen($file, "w");
		fwrite($fp, "\n\n\n");
		fclose($fp);
		print_r("Nieuw bestand aangemaakt voor: ".$userId."\n");
	}
	
	$offset++;
	file_get_contents($getUpdates.'?offset='.$offset);
	
	switch(true){
// 		Welkomstbericht:
		case $message == "/start":
			sendMessage($chatId, "Welkom bij de Telegram bot voor Zermelo, ".$firstName."! Deze bot werkt ook in groepen.", null);
			sendMessage($chatId, "Gemaakt door Bas van den Wollenberg (@BasvdW), Candea College.", null);
// 			sendMessage($chatId, "'/changelog' Als je notificaties wilt ontvangen over veranderingen/toevoegingen in de bot", null);
			sendMessage($chatId, "Krijg meer info over registreren met /registreer", null);
			sendMessage($chatId, "Na het registreren kun je je rooster opvragen met /rooster", null);
		break;
		
// 		Commands:
		case ($message == "/ping"):
			sendMessage($chatId, "_Pong!_", null);
		break;
		case $message == "/restart":
			if($userId == "125874268"){
				sendMessage($chatId, "Bot herstarten...", null);
				print_r("Bot herstarten...\n");
				exit(2);
			} else {
				sendMessage($chatId, "Je hebt niet de rechten om de bot te herstarten, ".$firstName.".", null);
			}
		break;
		case $message == "/registreer":
			$content = file($file);
			if ($content[0] == "\n" && $content[1] == "\n" && $content[2] == "\n"){
				sendMessage($chatId, "Stuur je leerlingnummer met '/leerlingnummer <leerlingnummer>'.\nStuur je appcode met '/code <appcode>' (Zermelo portal > Koppelingen > Koppel App).\nStuur je schoolnaam met '/school <schoolnaam>' (Zermelo portal > Koppelingen > Koppel App).", null);
			} elseif ($content[0] == "\n"){
				sendMessage($chatId, "Stuur je leerlingnummer met '/leerlingnummer <leerlingnummer>'.", null);
			} elseif ($content[1] == "\n" || !$content[1]){
				sendMessage($chatId, "Stuur je appcode met '/code <appcode>' (Zermelo portal > Koppelingen > Koppel App).", null);
			} elseif ($content[2] == "\n" || !$content[2]){
				sendMessage($chatId, "Stuur je schoolnaam met '/school <schoolnaam>' (Zermelo portal > Koppelingen > Koppel App).", null);
			} else {
				sendMessage($chatId, "Je bent al volledig geregistreerd, ".$firstName."! Vraag je rooster op met '/rooster'.", null);
			}
		break;
		
// 		Registratie:

		case 0 === strpos($message, '/leerlingnummer'):
				$content = file($file);
				
				@$leerlingnummer = explode(" ",$message)[1];
				
				if ($leerlingnummer == null){
					sendMessage($chatId, "'/leerlingnummer <leerlingnummer>'", null);
				} elseif ($content[0] == $leerlingnummer || $content[0] == $leerlingnummer."\n"){
					sendMessage($chatId, "Je leerlingnummer is al opgeslagen, ".$firstName."!", null);
				} else {
					try {
						$fp = fopen($file, "w+");
						$content[0] = $leerlingnummer."\n";
						fwrite($fp, implode($content, ''));
						fclose($fp);
						sendMessage($chatId, "Je leerlingnummer is veranderd naar: ".$leerlingnummer, null);
						print_r("Leerlingnummer '".$userId." (".$firstName." ".$lastName.")' veranderd naar '".$leerlingnummer."'.\n");
					} catch (Exception $e) {
						sendMessage($chatId, "Er is iets misgegaan bij het opslaan van je leerlingnummer, ".$firstName."Probeer het opnieuw.", null);
						print_r("Veranderen leerlingnummer van '".$userId." (".$firstName." ".$lastName.")' naar '".$leerlingnummer."' mislukt.\n");
					}
				}
		break;
		
		case 0 === strpos($message, '/code'):
			$content = file($file);
			
			@$code = explode(" ",$message)[1];
			
			if ($code == null){
				sendMessage($chatId, "'/code <appcode>' (Zermelo portal > Koppelingen > Koppel App)", null);
			} elseif ($content[1] == $code || $content[1] == $code."\n"){
				sendMessage($chatId, "Stuur een nieuwe code, ".$firstName."!", null);
			} else {
				try {
					$fp = fopen($file, "w+");
					$content[1] = $code."\n";
					fwrite($fp, implode($content, ''));
					fclose($fp);
					sendMessage($chatId, "Je appcode is veranderd naar: ".$code, null);
					print_r("Appcode '".$userId." (".$firstName." ".$lastName.")' veranderd naar '".$code."'.\n");
				} catch (Exception $e) {
					sendMessage($chatId, "Er is misgegaan bij het opslaan van je appcode, ".$firstName.". Stuur een nieuwe code.", null);
					print_r("Veranderen appcode '".$userId." (".$firstName." ".$lastName.")' naar '".$code."' mislukt.\n");
				}
			}
		break;
		
		case 0 === strpos($message, '/school'):
			$content = file($file);
			
			@$school = explode(" ",$message)[1];
			
			if ($school == null){
				sendMessage($chatId, "'/school <school>' (Zermelo portal > Koppelingen > Koppel App)", null);
			} elseif ($content[2] == $school || $content[2] == $school."\n"){
				sendMessage($chatId, "Je school is al opgeslagen, ".$firstName."!", null);
			} else {
				try {
					$fp = fopen($file, "w+");
					$content[2] = $school."\n";
					fwrite($fp, implode($content, ''));
					fclose($fp);
					sendMessage($chatId, "Je school is veranderd naar: ".$school, null);
					print_r("School '".$userId." (".$firstName." ".$lastName.")' veranderd naar '".$school."'.\n");
				} catch (Exception $e) {
					sendMessage($chatId, "Er is iets misgegaan bij het opslaan van je school, ".$firstName.". Probeer het opnieuw.", null);
					print_r("Veranderen school '".$userId." (".$firstName." ".$lastName.")' naar '".$school."' mislukt.\n");
				}
			}
		break;
		
//		Rooster opvragen:

		case $message == "/rooster":
			$content = file($file);
			if (!$content[0] || $content[0] == "\n" || !$content[1] || $content[1] == "\n" || $content[2] == "\n" || !$content[2]){
				sendMessage($chatId, "Je bent nog niet volledig geregistreerd ".$firstName."! Meer informatie: '/registreer'.", null);
			} else {
				sendMessage($chatId, "Wil je je rooster van vandaag of morgen, ".$firstName."?", null.'&reply_markup={"keyboard": [["1. Vandaag","2. Morgen"]],"one_time_keyboard": true,"selective": true,"resize_keyboard": true}');
			}
		break;
		case $message == "1. vandaag":
			$content = file($file);
			$leerlingnummer = $content[0];
			if (!$content[0] || $content[0] == "\n" || !$content[1] || $content[1] == "\n" || $content[2] == "\n" || !$content[2]){
				sendMessage($chatId, "Je bent nog niet volledig geregistreerd ".$firstName."! Meer informatie: '/registreer'.", null);
			} else {
				$leerlingnummer = substr($content[0], 0, -1);
				$code = substr($content[1], 0, -1);
				$school = substr($content[2], 0, -1);
		
				try {
					if (strpos(file_get_contents("gebruikers/geregistreerd.txt"), $leerlingnummer) === false) {
						try {
							register_zermelo_api();
							$zermelo = new ZermeloAPI($school);
								
							$zermelo->grabAccessToken($leerlingnummer, $code);
			
							file_put_contents("gebruikers/geregistreerd.txt", $leerlingnummer.PHP_EOL , FILE_APPEND);
						} catch (Exception $e){
							sendMessage($chatId, "Er is iets migegaan bij het ophalen van de token van Zermelo, stuur je leerlingnummer/school en/of een nieuwe appcode van het Zermelo portaal opnieuw, ".$firstName.".", null);
							print_r("Ophalen rooster '".$userId." (".$firstName." ".$lastName.")' mislukt.\n");
						}
						rooster($leerlingnummer, $school, "vandaag");
					} else {
						rooster($leerlingnummer, $school, "vandaag");
					}
					print_r("Rooster van '".$userId." (".$firstName." ".$lastName.")' succesvol opgehaald.\n");
				} catch (Exception $e){
					sendMessage($chatId, "Er is iets migegaan bij het ophalen van je rooster, stuur je leerlingnummer/school en/of een nieuwe appcode van het Zermelo portaal opnieuw, ".$firstName.".", null);
					print_r("Ophalen rooster '".$userId." (".$firstName." ".$lastName.")' mislukt.\n");
				}
			}
			break;
			case $message == "2. morgen":
				$content = file($file);
				$leerlingnummer = $content[0];
				if (!$content[0] || $content[0] == "\n" || !$content[1] || $content[1] == "\n" || $content[2] == "\n" || !$content[2]){
					sendMessage($chatId, "Je bent nog niet volledig geregistreerd ".$firstName."! Meer informatie: /registreer", null);
				} else {
					$leerlingnummer = substr($content[0], 0, -1);
					$code = substr($content[1], 0, -1);
					$school = substr($content[2], 0, -1);
			
					try {
						if (strpos(file_get_contents("gebruikers/geregistreerd.txt"), $leerlingnummer) === false) {
							register_zermelo_api();
							$zermelo = new ZermeloAPI($school);
								
							$zermelo->grabAccessToken($leerlingnummer, $code);
			
							file_put_contents("gebruikers/geregistreerd.txt", $leerlingnummer.PHP_EOL , FILE_APPEND);
							rooster($leerlingnummer, $school, "morgen");
						} else {
							rooster($leerlingnummer, $school, "morgen");
						}
						print_r("Rooster van '".$userId." (".$firstName." ".$lastName.")' succesvol opgehaald.\n");
					} catch (Exception $e){
						sendMessage($chatId, "Er is iets migegaan bij het ophalen van je rooster, probeer je leerlingnummer/appcode/school opnieuw in te voeren, ".$firstName.".", null);
						print_r("Ophalen rooster '".$userId." (".$firstName." ".$lastName.")' mislukt.\n");
					}
				}
			break;
	}
}


function getOffset($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		return $array[$key]['update_id'];
	}
}

function getChatId($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		return $array[$key]['message']['chat']['id'];
	}
}

function getMessage($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		if (isset($array[$key]['message']['text'])){
			return $array[$key]['message']['text'];
		}
	}
}

function getMessageId($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		if (isset($array[$key]['message']['message_id'])){
			return $array[$key]['message']['message_id'];
		}
	}
}

function getUserId($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		return $array[$key]['message']['from']['id'];
// 		}
	}
}

function getIfGroup($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		if (isset($array[$key]['message']['chat']['title'])){
			return true;
		} else {
			return false;
		}
// 		}
	}
}

function getFirstName($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		if (isset($array[$key]['message']['from']['first_name'])){
			return $array[$key]['message']['from']['first_name'];
		}
	}
}

function getLastName($array){
	if ($array != null){
		$keys = array_keys($array);
		$key = array_shift($keys);
		if (isset($array[$key]['message']['from']['last_name'])){
			return $array[$key]['message']['from']['last_name'];
		}
	}
}

function rooster($leerlingnummer, $school, $day){
	global $date, $date1, $chatId, $messageId, $firstName;
	
	register_zermelo_api();
	$zermelo = new ZermeloAPI($school);
	
	$rooster = $zermelo->getStudentGrid($leerlingnummer);
	
	$subjects = array();
	$teachers = array();
	$locations = array();
	$start = array();
	$end = array();
	$today = array();
	$tomorrow = array();
	
	foreach($rooster as $subArray) {
		$subjects[] = $subArray['subjects'][0];
		$teachers[] = $subArray['teachers'][0];
		$locations[] = $subArray['locations'][0];
		$start[] = $subArray['start_date'];
		$end[] = $subArray['end_date'];
	}
	
	$mi = new MultipleIterator();
	$mi->attachIterator(new ArrayIterator($subjects));
	$mi->attachIterator(new ArrayIterator($teachers));
	$mi->attachIterator(new ArrayIterator($locations));
	$mi->attachIterator(new ArrayIterator($start));
	$mi->attachIterator(new ArrayIterator($end));
	
	foreach ($mi as $value) {
		list($subject, $teacher, $location, $start, $end) = $value;
		if (substr($start, 0, 10) == $date){
			$today[] = strtoupper($subject)." - ".strtoupper($teacher)." - ".$location." | ".substr($start, 11, 15)." - ".substr($end, 11, 15);
		}
	}
	foreach ($mi as $value) {
		list($subject, $teacher, $location, $start, $end) = $value;
		if (substr($start, 0, 10) == $date1){
			$tomorrow[] = strtoupper($subject)." - ".strtoupper($teacher)." - ".$location." | ".substr($start, 11, 15)." - ".substr($end, 11, 15);
		}
	}
	$today = implode("\n", $today);
	$tomorrow = implode("\n", $tomorrow);
	if ($day == "vandaag"){
		sendMessage($chatId, "*Jouw rooster van vandaag, ".$firstName.":*\n".$today, null);
	} elseif ($day == "morgen"){
		sendMessage($chatId, "*Jouw rooster voor morgen, ".$firstName.":*\n".$tomorrow, null);
	}
}

function sendMessage($chatId, $message, $messageId){
	global $website;
	file_get_contents($website."sendChatAction?chat_id=".$chatId."&action=typing");
	file_get_contents($sendMessage = $website."sendMessage?chat_id=".$chatId."&text=".urlencode($message)."&reply_to_message_id=".$messageId."&parse_mode=Markdown");
}

function sendPhoto($chatId, $photo, $caption, $messageId){
	global $website;
	file_get_contents($website."sendChatAction?chat_id=".$chatId."&action=typing");
	$sendPhoto = $website."sendPhoto?chat_id=".$chatId."&photo=".$photo."&caption=".$caption."&reply_to_message_id=".$messageId;
	file_get_contents($sendPhoto);
}

function sendSticker($chatId, $sticker, $messageId){
	global $website;
	file_get_contents($website."sendChatAction?chat_id=".$chatId."&action=typing");
	$sendSticker = $website."sendSticker?chat_id=".$chatId."&sticker=".$sticker."&reply_to_message_id=".$messageId;
	file_get_contents($sendSticker);
}
