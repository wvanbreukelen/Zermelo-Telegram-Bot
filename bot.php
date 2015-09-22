<?php
require 'config.php';
require 'custom_autoload.php';

date_default_timezone_set("Europe/Amsterdam");

register_zermelo_api();
$zermelo = new ZermeloAPI('candea');

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
	$ifGroup = getIfGroup($result);
	$length = strlen((string)$message);
	
	$leerlingnummer = "leerlingnummers/".$userId.".txt";
	
	$offset++;
	file_get_contents($getUpdates.'?offset='.$offset);

	switch(true){
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
			sendMessage($chatId, "Stuur eerst je leerlingnummer (die zal gekoppeld worden aan je Telegram ID) en dan de appcode van Zermelo.", null);
		break;
		
// 		Registratie:
		case (ctype_digit($message) == true && $length == 6):
			if (file_exists("leerlingnummers/".$userId.".txt")){
				$content = file($leerlingnummer);
				if ($content[0] == $message || $content[0] == $message."\n"){
					sendMessage($chatId, "Je bent al geregistreerd!", $messageId);
				} else {
					unlink($leerlingnummer);
					$fp = fopen($leerlingnummer, "w");
					fwrite($fp, $message."\n");
					fclose($fp);
					sendMessage($chatId, "Je leerlingnummer is veranderd, stuur de Zermelo appcode (opnieuw) (Koppelingen > Koppel App).", $messageId);
				}
			} else {
				$fp = fopen($leerlingnummer, "w");
				fwrite($fp, $message."\n");
				fclose($fp);
				sendMessage($chatId, "Je leerlingnummer is succesvol aan je Telegram ID gekoppeld, stuur nu de appcode van Zermelo (Koppelingen > Koppel App). Stuur een ander leerlingnummer mocht je je leerlingnummer willen veranderen.", $messageId);
			}
		break;
		case (ctype_digit($message) == true && $length == 12):
			$content = file($leerlingnummer);
			unset($content[1]);
			$content[1] = $message;
			file_put_contents($leerlingnummer, implode("", $content));
			try {
				$zermelo->grabAccessToken($content[0], $content[1]);
				sendMessage($chatId, "Je leerlingnummer en appcode zijn opgeslagen! Later zul je je eigen rooster op kunnen vragen met /rooster.", $messageId);
			} catch (Exception $e) {
				sendMessage($chatId, "Er is iets fout gegaan, probeer het nog een keer met een nieuwe code.", $messageId);
			}
		break;
		
// 		Wat simpele reacties toegevoegd op verzoek van wat vrienden.
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

function registreer($leerlingnummer){
	
}

function sendMessage($id, $message, $reply){
	global $website;
	file_get_contents($website."sendChatAction?chat_id=".$id."&action=typing");
	file_get_contents($sendMessage = $website."sendMessage?chat_id=".$id."&text=".$message."&reply_to_message_id=".$reply);
}

function sendPhoto($id, $photo, $caption, $reply){
	global $website;
	file_get_contents($website."sendChatAction?chat_id=".$id."&action=typing");
	$sendPhoto = $website."sendPhoto?chat_id=".$id."&photo=".$photo."&caption=".$caption."&reply_to_message_id=".$reply;
	file_get_contents($sendPhoto);
}

function sendSticker($id, $sticker, $reply){
	global $website;
	file_get_contents($website."sendChatAction?chat_id=".$id."&action=typing");
	$sendSticker = $website."sendSticker?chat_id=".$id."&sticker=".$sticker."&reply_to_message_id=".$reply;
	file_get_contents($sendSticker);
}
