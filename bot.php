<?php
include 'config.php';
include 'ZermeloRoosterPHP/rooster.php';

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
				sendMessage($chatId, "Je hebt niet de rechten om de bot te herstarten", $messageId);
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
		case stripos($message, "merkoot") !== false:
			sendPhoto($chatId, "AgADBAADr6cxG1ywgAfKzkLkAurv70a0YzAABPe7ZrflteT_CWcBAAEC", null, $messageId);
		break;
		case $message == "muramasa":
			sendSticker($chatId, "BQADBAADBAoAApesNQABuh8VOZKFxWMC", $messageId);
		break;
	}
}


function getOffset(&$array){
	$offset = end($array);
	$offset = end($offset);
	return $offset['update_id'];
}

function getChatId(&$array){
	$chatId = end($array);
	$chatId = end($chatId);
	return $chatId['message']['chat']['id'];
}

function getMessage(&$array){
	$message = end($array);
	$message = end($message);
	if(isset($message['message']['text'])){
		return $message['message']['text'];
	}
}

function getMessageId($array){
	$messageId = end($array);
	$messageId = end($messageId);
	if(isset($messageId['message']['message_id'])){
		return $messageId['message']['message_id'];
	}
}

function getUserId($array){
	$userId = end($array);
	$userId = end($userId);
	return $userId['message']['from']['id'];
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
