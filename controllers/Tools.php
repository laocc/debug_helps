<?php

namespace esp\debugs;

use esp\error\Error;
use esp\gd\BarCode;
use esp\gd\QrCode;
use esp\helper\library\request\Post;
use esp\helper\library\Result;
use esp\http\Http;

class Tools extends _Base
{

    /**
     * @throws Error
     */
    public function postPost()
    {
        $result = new Result();
        $post = new Post();
        $http = new Http();
        $url = $post->string('api');
        $ua = $post->string('ua');
        $encode = $post->string('encode');
        $method = $post->string('method');
        $referer = $post->string('referer');
        $cookies = $post->string('cookies');
        $proxy = $post->string('proxy');
        $auth = $post->string('auth');
        $header = $post->string('header', 0);
        $data = $post->string('data', 0);
        $ret = $post->string('?result');

        $http->timeout(0)->wait(0);

        if ($ua) $http->ua($ua);
        if ($encode) $http->encode($encode);
        if ($method) $http->method($method);
        if ($referer) $http->referer($referer);
        if ($header) $http->headers($header);
        if ($cookies) $http->cookies($cookies);
        if ($proxy) $http->proxy($proxy);
        if ($auth) $http->password($auth);
        if ($data) $http->data($data);
        $request = $http->request($url);
//        print_r($request);

        if ($ret === 'http') return $request;
        if ($ret === 'data') return $request->data();
        if ($ret === 'result') return $result->data($request->data());
        return $request->html();
    }

    public function codeGet()
    {
    }

    public function codePost()
    {
        $post = file_get_contents('php://input');
        $json = json_decode($post, true);
        $code = urldecode($json['code'] ?? '');
        $type = $json['type'] ?? '';

        switch ($type) {
            case 'BarCode':
                $option = [];
                $option['code'] = $code;
                $option['save'] = 4;
                $code1 = new BarCode();
                $val = $code1->create($option);
                return ['value' => 'data:image/png;base64,' . $val, 'success' => 1];
            case 'QR':
            case 'qr':
                $opt = [];
                $opt['text'] = $code;
                $opt['width'] = 260;
                $opt['save'] = 4;
                $qr = new QrCode();
                $val = $qr->create($opt);
                return ['value' => 'data:image/png;base64,' . $val, 'success' => 1];
            case 'md5':
                return ['value' => md5($code), 'success' => 1];
            case 'sha1':
                return ['value' => sha1($code), 'success' => 1];
            case 'sha256':
                return ['value' => hash('sha256', $code), 'success' => 1];
            case '2power':
                $num = \esp\helper\numbers(intval($code));
                if (empty($num)) $num = [];
                $num = implode(',', $num);
                return ['value' => $num, 'success' => 1];
            case 'json':
                $code = str_replace(['\"'], '"', $code);
                $code = json_decode($code, true);
                return ['value' => json_encode($code, 320 + 128), 'success' => 1];
            case 'url_encode':
                return ['value' => urlencode($code), 'success' => 1];
            case 'url_decode':
                return ['value' => urldecode($code), 'success' => 1];
            case 'parse_str':
                parse_str(urldecode($code), $data);
                return ['value' => json_encode($data, 320 + 128), 'success' => 1];
            case 'htmlentities':
                return ['value' => htmlentities($code), 'success' => 1];
            case 'html_decode':
                return ['value' => html_entity_decode($code), 'success' => 1];
            case 'base64_encode':
                return ['value' => base64_encode($code), 'success' => 1];
            case 'base64_decode':
                return ['value' => base64_decode($code), 'success' => 1];
        }
        return [];
    }

}