<?php
require_once("TGBot.php"); 
require_once('config.php');
require_once('db.php'); 

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$tgBot = new TGBot();

$data = file_get_contents('php://input');
$data = json_decode($data, true);

$chat_id = $data['message']['from']['id'];

TGBot::writeLog($data);
// $tgBot -> setWebhook();

if($data['message']['text'] == '/start')
{
    $arrayQuery = array(
        "chat_id" => "$chat_id",
        "text" => "Выберите вариант", 
        "reply_markup" => json_encode(
            array(
                "inline_keyboard" => array(
                    array(             
                        array(
                            "text" => "Анекдот",
                            "callback_data" => "callback_1",
                        ),
                        array(
                            "text" => "Добавить анекдот",
                            "callback_data" => "callback_2",
                        ),
                    )
                ),
            )
        )
    );

    $arrayQuery = json_encode($arrayQuery);
    $tgBot -> sendQueryTelegram('sendMessage', $arrayQuery);
    $tgBot -> setLastMessage($chat_id, $con, '/start');
}
elseif(isset($data['callback_query']['data']))
{
    switch($data['callback_query']['data']){
        case 'callback_1':
            $query = "SELECT `text` FROM anekdots";
            $result = $con -> query($query);
            $array_anic = [];
            while($row = $result -> fetch_assoc())
            {
                $array_anic[] = $row['text'];
            }
            $anic = $array_anic[array_rand($array_anic)];
            $tgBot -> sendMessage($anic, $data['callback_query']['message']['chat']['id']);
        break;
        case 'callback_2':
            if($data['callback_query']['message']['chat']['id'] == 1959334791)
            {
                $tgBot -> sendMessage('Вам можно', $data['callback_query']['message']['chat']['id']);
                $tgBot -> setLastMessage(1959334791, $con, 'Добавить анекдот');
            }
            else
            {
                $tgBot -> sendMessage('Вам нельзя', $data['callback_query']['message']['chat']['id']);
                $tgBot -> setLastMessage(1959334791, $con, 'Анекдот нельзя');
            }
        break;
    }
}
elseif($tgBot->getLastMessage($chat_id, $con) == 'Добавить анекдот')
{
    $text = $data['message']['text'];
    $query = "INSERT INTO anekdots (`text`) VALUES ('$text')";
    $result = $con -> query($query);

    $tgBot->setLastMessage(1959334791, $con, 'Анекдот отправлен');
    $tgBot -> sendMessage("Анекдот получен", 1959334791);
}

else{
    $tgBot -> sendMessage('Неизвестная команда', $data['message']['from']['id']);
}
    // $message_text = $data['message']['text']; 
    // $chat_id = $data['message']['from']['id']; // id пользователя 
    // $arrayQuery = array(
    //     "chat_id" => "$chat_id",
    //     "message_id" => "$message_id"
    // );
    // $arrayQuery = json_encode($arrayQuery);
    // $tgBot = new TGBot;
    // TGBot::writeLog($data); // запись логов

    // $tgBot -> sendMessage($message_text, $chat_id);
    // $ch = curl_init('https://api.telegram.org/bot'. TG_TOKEN .'/sendMessage?'. http_build_query($arrayQuery));
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // curl_setopt($ch, CURLOPT_HEADER, false);
    // TGBot::writeLog("Запрос инициализирован");
    // $result = curl_exec($ch);
    // TGBot::writeLog("Запрос отправился");
    // curl_close($ch);

?>