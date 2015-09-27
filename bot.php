<?php
require 'ZermeloRoosterPHP/custom_autoload.php';
require 'config.php';

date_default_timezone_set("Europe/Amsterdam");
$date = date('d/m/Y', time());
$date1 = date("d/m/Y", strtotime("tomorrow"));

$getUpdates = $website.'getUpdates';

while(1){
	$result = file_get_contents($getUpdates);
	$result = json_decode($result, true);
// 	print_r($result);

	$offset = getOffset($result);
	$chatId = getChatId($result);
	$message = strtolower(getMessage($result));
	$messageId = getMessageId($result);
	$userId = getUserId($result);
	
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
			sendMessage($chatId, "Welkom bij de Telegram bot voor Zermelo! Deze bot werkt ook in groepen.", null);
			sendMessage($chatId, "Gemaakt door Bas van den Wollenberg (@BasvdW), Candea College.", null);
// 			sendMessage($chatId, "'/changelog' Als je notificaties wilt ontvangen over veranderingen/toevoegingen in de bot", null);
			sendMessage($chatId, "Krijg meer info over registreren met /registreer", null);
			sendMessage($chatId, "Na het registreren kun je je rooster opvragen met /rooster", null);
		break;
		
// 		Commands:
		case ($message == "/ping"):
			sendMessage($chatId, "Pong!", null);
		break;
		case $message == "/restart":
			if($userId == "125874268"){
				sendMessage($chatId, "Bot herstarten...", null);
				print_r("Bot herstarten...\n");
				exit(2);
			} else {
				sendMessage($chatId, "Je hebt niet de rechten om de bot te herstarten", null);
			}
		break;
		case $message == "/registreer":
			$content = file($file);
			if ($content[0] == "\n"){
				sendMessage($chatId, "Stuur je leerlingnummer met '/leerlingnummer <leerlingnummer>.'", $messageId);
			} elseif ($content[1] == "\n" || !$content[1]){
				sendMessage($chatId, "Stuur je appcode met '/code <appcode>'.", $messageId);
			} elseif ($content[2] == "\n" || !$content[2]){
				sendMessage($chatId, "Stuur je schoolnaam met '/school <schoolnaam>'.", $messageId);
			} else {
				sendMessage($chatId, "Je bent al volledig geregistreerd! Vraag je rooster op met '/rooster'.", null);
			}
		break;
		
// 		Registratie:

		case 0 === strpos($message, '/leerlingnummer'):
				$content = file($file);
				
				@$leerlingnummer = explode(" ",$message)[1];
				
				if ($leerlingnummer == null){
					sendMessage($chatId, "'/leerlingnummer <leerlingnummer>'", $messageId);
				} elseif ($content[0] == $leerlingnummer || $content[0] == $leerlingnummer."\n"){
					sendMessage($chatId, "Je leerlingnummer is al opgeslagen!", $messageId);
				} else {
					try {
						$fp = fopen($file, "w+");
						$content[0] = $leerlingnummer."\n";
						fwrite($fp, implode($content, ''));
						fclose($fp);
						sendMessage($chatId, "Je leerlingnummer is veranderd naar: ".$leerlingnummer, $messageId);
					} catch (Exception $e) {
						sendMessage($chatId, "Er is iets misgegaan bij het opslaan van je leerlingnummer.", $messageId);
					}
				}
		break;
		
		case 0 === strpos($message, '/code'):
			$content = file($file);
			
			@$code = explode(" ",$message)[1];
			
			if ($code == null){
				sendMessage($chatId, "'/code <appcode>' (Zermelo portal > Koppelingen > Koppel App)", $messageId);
			} elseif ($content[1] == $code || $content[1] == $code."\n"){
				sendMessage($chatId, "Stuur een nieuwe code!", $messageId);
			} else {
				try {
					$fp = fopen($file, "w+");
					$content[1] = $code."\n";
					fwrite($fp, implode($content, ''));
					fclose($fp);
					sendMessage($chatId, "Je appcode is veranderd naar: ".$code, $messageId);
				} catch (Exception $e) {
					sendMessage($chatId, "Er is misgegaan bij het opslaan van je appcode.", $messageId);
				}
			}
		break;
		
		case 0 === strpos($message, '/school'):
			$content = file($file);
			
			@$school = explode(" ",$message)[1];
			
			if ($school == null){
				sendMessage($chatId, "'/school <school>' (Zermelo portal > Koppelingen > Koppel App)", $messageId);
			} elseif ($content[2] == $school || $content[2] == $school."\n"){
				sendMessage($chatId, "Je school is al opgeslagen!", $messageId);
			} else {
				try {
					$fp = fopen($file, "w+");
					$content[2] = $school."\n";
					fwrite($fp, implode($content, ''));
					fclose($fp);
					sendMessage($chatId, "Je school is veranderd naar: ".$school, $messageId);
				} catch (Exception $e) {
					sendMessage($chatId, "Er is iets misgegaan bij het opslaan van je school.", $messageId);
				}
			}
		break;
		
//		Rooster opvragen:

		case $message == "/rooster":
			$content = file($file);
			$leerlingnummer = $content[0];
			if (!$content[0] || !$content[1] || !$content[2]){
				sendMessage($chatId, "Je bent nog niet volledig geregistreerd, meer informatie: /registreer", $messageId);
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
						rooster($leerlingnummer, $school);
					} else {
						rooster($leerlingnummer, $school);
					}
				} catch (Exception $e){
					sendMessage($chatId, "Er is iets migegaan bij het ophalen van je rooster, probeer je leerlingnummer/appcode/school opnieuw in te voeren.", $messageId);
					print_r($e);
				}
			}
		break;
		
// 		Wat simpele reacties toegevoegd op verzoek van wat vrienden:

		case ($message == "mondo"):
			sendMessage($chatId, "Oowada", $messageId);
		break;
		case stripos($message, "panda") !== false:
			sendMessage($chatId, "\xF0\x9F\x90\xBC", $messageId);
		break;
		case stripos($message, "ezio") !== false:
			sendMessage($chatId, "Requiscat in pace.", $messageId);
		break;
		case $message == "ulquiorra":
			sendMessage($chatId, "Hot", $messageId);
		break;
		case $message == "ulquihime" || $message == "ishimondo":
			sendMessage($chatId, "Otp", $messageId);
		break;
		case $message == "ikkaku":
			sendMessage($chatId, "Kale kop", $messageId);
		break;
		case $message == "bankai":
			sendMessage($chatId, "Epic", $messageId);
		break;
		case $message == "pokemon":
			sendMessage($chatId, "Gotta catch 'em all!", $messageId);
		break;
		case $message == "yumichika":
			sendMessage($chatId, "Fabulous", $messageId);
		break;
		case $message == "killua":
			sendMessage($chatId, "Assassin", $messageId);
		break;
		case $message == "muramasa":
			sendSticker($chatId, "BQADBAADBAoAApesNQABuh8VOZKFxWMC", $messageId);
		break;
	}
}


function getOffset(&$array){
	if ($array != null){
		$offset = end($array);
		$offset = end($offset);
		return $offset['update_id'];
	}
}

function getChatId(&$array){
	if ($array != null){
		$chatId = end($array);
		$chatId = end($chatId);
		return $chatId['message']['chat']['id'];
	}
}

function getMessage(&$array){
	if ($array != null){
		$message = end($array);
		$message = end($message);
		if(isset($message['message']['text'])){
			return $message['message']['text'];
		}
	}
}

function getMessageId($array){
	if ($array != null){
		$messageId = end($array);
		$messageId = end($messageId);
		if(isset($messageId['message']['message_id'])){
			return $messageId['message']['message_id'];
		}
	}
}

function getUserId($array){
	if ($array != null){
		$userId = end($array);
		$userId = end($userId);
		return $userId['message']['from']['id'];
	}
}

function getIfGroup($array){
	if ($array != null){
		$group = end($array);
		$group = end($group);
		if(isset($group['message']['chat']['title'])){
			return true;
		} else {
			return false;
		}
	}
}

function rooster($leerlingnummer, $school){
	global $date, $date1, $chatId, $messageId;
	
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
	sendMessage($chatId, "Jouw rooster van vandaag:\n".$today, $messageId);
	sendMessage($chatId, "Jouw rooster voor morgen:\n".$tomorrow, $messageId);
}

function sendMessage($chatId, $message, $messageId){
	global $website;
	file_get_contents($website."sendChatAction?chat_id=".$chatId."&action=typing");
	file_get_contents($sendMessage = $website."sendMessage?chat_id=".$chatId."&text=".urlencode($message)."&reply_to_message_id=".$messageId);
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
