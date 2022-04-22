<?php

namespace esp\debugs;

use esp\helper\library\request\Get;

class Counter extends _Base
{

    public function counterGet()
    {
        $this->assign('vueMixin', $this->vueMixin ?? '');
    }

    public function counterAjax()
    {
        $get = new Get();
        $day = $get->int('type');
        $time = time() - (86400 * $day);
        $method = true;

        $key = $this->config('debug.default.counter');

        if ($time === 0) $time = time();
        $key = "{$key}_counter_" . date('Y_m_d', $time);
        $all = $this->_config->_Redis->hGetAll($key);
        if (empty($all)) return ['data' => [], 'action' => []];

        $data = [];
        foreach ($all as $hs => $hc) {
            $key = explode('/', $hs, 5);
            $hour = (intval($key[0]) + 1);
            $ca = $method ? "{$key[1]}:/{$key[4]}" : "/{$key[4]}";
            $vm = "{$key[2]}.{$key[2]}";
            if (!isset($data[$vm])) $data[$vm] = ['action' => [], 'data' => []];
            if (!isset($data[$vm]['data'][$hour])) $data[$vm]['data'][$hour] = [];
            $data[$vm]['data'][$hour][$ca] = $hc;
            if (!in_array($ca, $data[$vm]['action'])) $data[$vm]['action'][] = $ca;
            sort($data[$vm]['action']);
        }
        return $data;
    }

}