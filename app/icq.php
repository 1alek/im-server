<?php

/**
 * IM Notifications Server
 *
 * @author Alek.su via a@alek.su
 */


defined('APP_ROOT') or exit;

use GuzzleHttp\Client;

class IcqCore
{
    const URL_START_SESSION = 'https://api.icq.net/aim/startSession';

    protected $sessionKey = '';
    protected $k = '';
    protected $a = '';
    protected $c = 'WebIM.jscb_tmp_c38690';
    protected $f = 'json';
    protected $aimsid;

    protected function request($method, $uri, $data = [])
    {
        $client = new Client();
        if ($data) {
            if ($method == "POST") {
                $data = [
                    'form_params' => $data,
                ];
            } else {
                $uri .= "?" . http_build_query($data);
            }
        }
        return $client->request($method, $uri, $data);
    }


    public function encodeURIComponent($str)
    {
        return rawurlencode($str);
    }

    public function makeSignedUrl($uri, $params, $sessionKey, $method = "GET")
    {
        ksort($params);
        $sortedParams = [];
        foreach ($params as $k => $v) {
            $sortedParams[] = "$k=" . $this->encodeURIComponent($v);
        }
        if ($sortedParams = join("&", $sortedParams)) {
            $i = $method . "&" . $this->encodeURIComponent($uri) . "&" . $this->encodeURIComponent($sortedParams);
            $sigSha256 = base64_encode(hash_hmac('sha256', $i, $sessionKey, true));
            $sortedParams .= "&sig_sha256=" . $this->encodeURIComponent($sigSha256);
        }
        return $uri . (strpos($uri, '?') !== false ? "&" : "?") . $sortedParams;
    }

    public function startSession()
    {
        $params = [
            'a'                     => $this->a,
            'c'                     => $this->c,
            'clientName'            => 'SiteIM',
            'events'                => 'myInfo,presence,buddylist,typing,hiddenChat,hist,mchat,sentIM,imState,dataIM,offlineIM,userAddedToBuddyList,service,lifestream',
            'f'                     => 'json',
            'includePresenceFields' => 'aimId,displayId,friendly,friendlyName,state,userType,statusMsg,statusTime,lastseen,ssl,moodTitle,moodIcon,buddyIcon,mute,abContactName,official',
            'k'                     => $this->k,
            'language'              => 'ru',
            'ts'                    => time(),
            'view'                  => 'online'
        ];
        $url = $this->makeSignedUrl(self::URL_START_SESSION, $params, $this->sessionKey);
        $res = $this->request('GET', $url);
        $result = json_decode(trim(substr((string)$res->getBody(), strlen($this->c)), '()'), true);
        $this->aimsid = $result['response']['data']['aimsid'];
    }
 }

 class Icq extends IcqCore
 {
    const URL_SEND    = 'https://api.icq.net/im/sendIM';
    const URL_CONNECT = 'https://icq.com/siteim/icqbar/php/proxy_jsonp_connect.php';
    const FILE_TOKEN  = '/tmp/icq-tokens.tmp';

    public function saveTokens($answer)
    {
	// check input data
	// ...
	// save
	file_put_contents(self::FILE_TOKEN, $answer);
    }

    public function loadTokens()
    {
	// check file existance
	// ...
	// load session
	$tokens = json_decode(file_get_contents(self::FILE_TOKEN), true);
        $this->sessionKey = $tokens['sessionKey'];
        $this->k = $tokens['k'];
        $this->a = $tokens['a'];
        $this->startSession();
    }

    public function login($login, $password)
    {
        $post = [
            'username' => $login,
            'password' => $password,
            'language' => 'ru',
            'time'     => time(),
            'remember' => 1,
        ];
        $res = $this->request('POST', self::URL_CONNECT, $post);

	$this->saveTokens($res->getBody());
    }

    public function send($uin, $message)
    {
	$this->loadTokens();
        $data = [
            "aimsid"  => $this->aimsid,
            "c"       => $this->c,
            "f"       => $this->f,
            "message" => $message,
            "t"       => $uin
        ];
        return $this->request('GET', self::URL_SEND, $data);
    }
 }

