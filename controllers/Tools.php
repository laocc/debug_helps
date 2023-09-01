<?php

namespace esp\debugs;


use esp\gd\QrCode;

class Tools extends _Base
{

    public function codeGet()
    {
    }

    public function codePost()
    {
        $post = file_get_contents('php://input');
        $json = json_decode($post, true);
        $code = $json['code'] ?? '';
        $type = $json['type'] ?? '';

        switch ($type) {
            case 'QR':
            case 'qr':
                $opt = [];
                $opt['text'] = $code;
                $opt['width'] = 260;
                $opt['save'] = 4;
                $qr = new QrCode();
                $code = $qr->create($opt);
                return ['value' => 'data:image/png;base64,' . $code, 'success' => 1];
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