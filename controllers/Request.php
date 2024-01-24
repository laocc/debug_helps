<?php

namespace esp\debugs;

use esp\debug\Counter;
use esp\helper\library\request\Get;
use esp\helper\library\Result;
use function esp\helper\rnd;

class Request extends _Base
{

    public function clearcountAjax()
    {
        $keys = $this->_config->_Redis->keys('*');
        $sum = 0;
        $now = time();
        foreach ($keys as $key) {
            if (preg_match('/(\d{4})_(\d{2})_(\d{2})/', $key, $mc)) {
                $time = strtotime("{$mc[1]}-{$mc[2]}-{$mc[3]}");
                if ($now - $time > 86400 * 4) {
                    $sum += $this->_config->_Redis->del($key);
                }
            }
        }

        return ['success' => 1, 'message' => "清除了{$sum}条数据"];
    }

    public function topGet()
    {
//        $this->setViewPath('@' . dirname(__DIR__) . '/views');
//        $this->setLayout('/home/chain/application/admin/views/layout.php');
        $this->setViewPath('@' . dirname(__DIR__) . '/views');
    }

    public function topAjax()
    {
        $get = new Get();
        $day = $get->int('type');
        $time = time() - (86400 * $day);
        $conf = $this->config('counter.default');
        $count = new Counter($conf, $this->_config->_Redis);
        $value = $count->getTopMysql($time, 0, 2);
        $hit = $select = $update = $insert = ['run' => 0, 'sum' => 0];
        $run = array_sum(array_column($value, 'run'));
        foreach ($value as $sql) {
            if (substr($sql['sql'], 0, 3) === 'Hit') {
                $hit['run']++;
                $hit['sum'] += $sql['run'];
            } else if (substr($sql['sql'], 0, 6) === 'SELECT') {
                $select['run']++;
                $select['sum'] += $sql['run'];
            } else if (substr($sql['sql'], 0, 6) === 'INSERT') {
                $insert['run']++;
                $insert['sum'] += $sql['run'];
            } else {
                $update['run']++;
                $update['sum'] += $sql['run'];
            }
        }
        $hit['pnt'] = rnd($hit['sum'] / ($run + 0.001) * 100, 2);
        $select['pnt'] = rnd($select['sum'] / ($run + 0.001) * 100, 2);
        $update['pnt'] = rnd($update['sum'] / ($run + 0.001) * 100, 2);
        $insert['pnt'] = rnd($insert['sum'] / ($run + 0.001) * 100, 2);

        $result = new Result();
        return $result->data(
            [
                'value' => $value,
                'total' => $run,
                'insert' => $insert,
                'update' => $update,
                'select' => $select,
                'hit' => $hit
            ]);
    }

    public function mysqlGet()
    {
        $get = new Get();
        $day = $get->int('type');
        $time = time() - (86400 * $day);
        $conf = $this->config('counter.default');
        $count = new Counter($conf, $this->_config->_Redis);
        $value = $count->getMysql($time, 16);

        $this->assign('labels', json_encode($value['label'] ?? [], 320));
        $this->assign('minute', json_encode($value['average'] ?? [], 320));
        $this->assign('top', json_encode($value['maximum'] ?? [], 320));
        $this->assign('max', ($value['max'] ?? 0) + 5);
        $this->setViewPath('@' . dirname(__DIR__) . '/views');
    }

    public function peakedGet()
    {
        $get = new Get();
        $type = $get->int('type');
        $time = time() - (86400 * $type);
        $day = date('Y-m-d', $time);
        $dayTime = strtotime($day);
        $key = $d05 = $d10 = $d15 = [];//192

        if (!is_readable(_RUNTIME . "/cpu/{$day}.txt")) goto end;

        $txt = file_get_contents(_RUNTIME . "/cpu/{$day}.txt");
        foreach (explode("\n", $txt) as $line) {
            $p = preg_match('/([\d\:]+);\s([\d\.]+);([\d\.]+);([\d\.]+)/', $line, $mch);
            if (!$p) continue;
            $h = substr($mch[1], 0, 4) . '0';
            if (!isset($d05[$h])) $d05[$h] = $d10[$h] = $d15[$h] = 0;
            $d05[$h] += intval($mch[2] * 100);
            $d10[$h] += intval($mch[3] * 100);
            $d15[$h] += intval($mch[4] * 100);
        }
        for ($i = 0; $i < 1440; $i += 10) {
            $h = date('H:i', $dayTime + ($i * 60));
            if (!isset($d05[$h])) {
                $d05[$h] = 0;
                $d10[$h] = 0;
                $d15[$h] = 0;
            }
        }
        ksort($d05);
        ksort($d10);
        ksort($d15);
        foreach ($d05 as $h => $hv) $d05[$h] = intval($hv / 120);
        foreach ($d10 as $h => $hv) $d10[$h] = intval($hv / 120);
        foreach ($d15 as $h => $hv) $d15[$h] = intval($hv / 120);
        end:
        $this->assign('key', json_encode(array_keys($d05), 320));
        $this->assign('d05', json_encode(array_values($d05), 320));
        $this->assign('d10', json_encode(array_values($d10), 320));
        $this->assign('d15', json_encode(array_values($d15), 320));
    }

    /**
     * 并发统计
     */
    public function concurrentGet()
    {
        $get = new Get();
        $day = $get->int('type');
        $time = time() - (86400 * $day);
        $conf = $this->config('counter.default');
        $count = new Counter($conf, $this->_config->_Redis);
        $value = $count->getConcurrent($time, 16);

        $this->assign('labels', json_encode($value['label'], 320));
        $this->assign('minute', json_encode($value['average'], 320));
        $this->assign('top', json_encode($value['maximum'], 320));
        $this->assign('max', $value['max'] + 5);

        $this->setViewPath('@' . dirname(__DIR__) . '/views');
//        $this->setLayout(false);

    }

}