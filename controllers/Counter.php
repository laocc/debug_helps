<?php

namespace esp\debugs;

use \esp\debug\Counter as aCounter;

class Counter extends _Base
{

    public function indexGet($day)
    {
        $data = $this->counterData($day);
        $this->assign('data', json_encode($data));
    }

    public function counterData($day): array
    {
        $time = time() - (86400 * intval($day));
        $method = true;
        $conf = $this->config('counter.default');
        $count = new aCounter($conf, $this->_config->_Redis, $this->_dispatcher->_request);
        return $count->getCounter($time, $method);
    }

}