<?php

namespace esp\debugs;


class Cache extends _Base
{

    public function indexGet()
    {
        $data = $this->getConfig()->allConfig();
        unset($data['mime'], $data['state']);
        $this->assign('data', $data);
    }

    public function phpinfoGet()
    {

    }

    public function opcacheGet()
    {

    }

    public function resourceAjax()
    {
        $this->_redis->set(_UNIQUE_KEY . '_RESOURCE_RAND_', time() + mt_rand());
        return ['success' => 1, 'message' => "重置成功"];
    }

    public function flushAjax($all)
    {
        $this->getConfig()->flush(intval($all));
        return ['success' => 1, 'message' => "清空成功"];
    }

}