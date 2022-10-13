<?php
namespace Lib;


class SendNotification
{
    private $chat_id;
    private $botToken;
    public function __construct($chat_id,$botToken)
    {
        $this->chat_id = $chat_id;
        $this->botToken = $botToken;
    }

    public function sendToTelegram($content)
    {
        $curl = curl_init();
        $data = [
            "chat_id" => $this->chat_id,
            "text" => $content,
            "parse_mode" => "html"
        ];
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.telegram.org/bot'.$this->botToken.'/sendMessage',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
    public function notification($title,$summary,$content){
        $notificationModel = new Entity('Core','Notification');
        $notification_insert = array(
            'title' => $title,
            'summary' => $summary,
            'content' => $content,
            'status' => 'new'
        );
        $notificationModel->save($notification_insert);
    }
}