<?php

namespace esp\debugs;


class Counter extends _Base
{

    public function indexGet($day)
    {
        $data = $this->counterData($day);
        $this->assign('data', json_encode($data));
    }

    public function counterData($day)
    {
        $time = time() - (86400 * intval($day));
        $method = true;
        $conf = $this->config('counter.default');
        $count = new \esp\debug\Counter($conf, $this->_config->_Redis);
        $data = $count->getCounter($time, $method);
        return $data;
    }

}