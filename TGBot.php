<?php
require_once ("config.php");

class TGBot {

    private $token = TG_TOKEN;
    private $site = TG_SITE; 

    public function sendMessage($text, $id_chat)
    {
        $text = urlencode($text);
        $ch = curl_init("https://api.telegram.org/bot". $this->token . "/sendMessage?text=". $text ."&chat_id=". $id_chat);
        $result = curl_exec($ch);
        curl_close($ch);
        self::writeLog(json_decode($result, true));

        return json_decode($result, true);
    }

    public function deleteMessage($arrayQuery)
    {
        $ch = curl_init("https://api.telegram.org/bot". $this->token . "/deleteMessage" ); //инициализация метода
        curl_setopt($ch, CURLOPT_POST, 1); //настройка запроса
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arrayQuery);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $result = curl_exec($ch);
        curl_close($ch);

        self::writeLog(json_decode($result, true));
        return json_decode($result, true);
    }

    public function sendQueryTelegram(string $method, $arrayQuery) //отправка запроса
    {
        $ch = curl_init("https://api.telegram.org/bot". $this->token . "/" . $method .""); //инициализация метода
        curl_setopt($ch, CURLOPT_POST, 1); //настройка запроса
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arrayQuery);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:application/json"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $result = curl_exec($ch); //получение результата
        curl_close($ch);

        //self::writeLog(json_decode($result, true));
        return json_decode($result, true); //возращает в виде ассоц. массива
    }

    public function setWebhook() // установка вебхука
    {
        $getQuery = array(
            'url' => $this->site 
        );

        $ch = curl_init("https://api.telegram.org/bot" . $this->token . "/setWebhook?" . http_build_query($getQuery));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        
        $result = curl_exec($ch);
        return json_decode($result, true); 
    }

    public function deleteWebhook() // удаление вебхука
    {
        $ch = curl_init("https://api.telegram.org/bot" . $this->token. "/deleteWebhook"); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        
        $result = curl_exec($ch);
        return json_decode($result, true);
    }

    public static function writeLog($str) // запись логов, принимает ассоц. массив
    {
        $log_file_name = __DIR__. "/log.txt";
        $now = date("Y-m-d H:i:s");
    
        file_put_contents($log_file_name, $now." ". print_r($str, true)."\r\n", FILE_APPEND);
    }

    public function getLastMessage($id, $con)
    {
        $query = "SELECT last_mess FROM `user` WHERE id_tg = '$id'";
        $last_mess = $con -> query($query);
        if(mysqli_num_rows($last_mess)>0)
        {
            $last_mess = $last_mess -> fetch_assoc();
            return $last_mess['last_mess'];
        }
        else
        {
            return false;
        }
    }

    public function setLastMessage($id, $con, $text)
    {
        $last_mess = self::getLastMessage($id, $con);
        if(!$last_mess)
        {
            $query = "INSERT INTO `user` (`id_tg`, `last_mess`) VALUES ('$id', '$text')";
        }
        else
        {
            $query = "UPDATE user SET `last_mess` = '$text' WHERE `id_tg` = '$id'";
        }
        $result = $con -> query($query);
    }
}
?>