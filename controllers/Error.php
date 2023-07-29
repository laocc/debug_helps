<?php

namespace esp\debugs;

use esp\helper\library\ext\Markdown;
use function esp\helper\root;

class Error extends _Base
{

    public function error_matchAction($warn)
    {
        $path = $warn ? $this->_warnPath : $this->_errorPath;
        $dir = new \DirectoryIterator($path);
        $value = [];
        $client = [];
        foreach ($dir as $f) {
            if ($f->isDot()) continue;
            $name = $f->getFilename();
            $val = [];
            if ($f->isFile()) {
                $val['fn'] = $f->getPathname();
                $text = file_get_contents($val['fn']);
                if (preg_match('/"HTTP_COOKIE": "(.+)",/', $text, $mch)) {
                    $val['ck'] = $mch[1];
//                    if (in_array($mch[1], $client)) continue;//过滤相同用户
                    $client[] = $mch[1];
                }
                if (preg_match('/"HTTP_USER_AGENT": "(.+)",/', $text, $mch)) {
                    $val['ua'] = $mch[1];
                }

                if (preg_match('/"error": "(.+)"/i', $text, $mch)) {
                    $val['er'] = $mch[1];
                } else if (preg_match('/"message": "(.+)"/i', $text, $mch)) {
                    $val['er'] = $mch[1];
//                } else if (preg_match('/"Error": \[[\s.]+"(.+?)"\n/i', $text, $mch)) {
                } else if (preg_match('/"Error": \[\s+.+?,\s+\"(.+?)"/is', $text, $mch)) {
                    $val['er'] = $mch[1];
                }

                if (preg_match('/"file": "(.+)"/i', $text, $mch)) {
                    $val['fl'] = $mch[1];
                }
                if (preg_match('/"line": (\d+),/i', $text, $mch)) {
                    if (isset($val['fl'])) {
                        $val['fl'] = "{$val['fl']}({$mch[1]})";
                    } else {
                        $val['ln'] = $mch[1];
                    }
                }
                if ($warn) {
                    preg_match('/ip=(.+?)\&/i', $text, $url);
                    preg_match('/REAL_IP": "(.+?)",/i', $text, $sev);
                    preg_match('/REMOTE_ADDR": "(.+?)",/i', $text, $rem);
                    $ia = ($url[1] ?? '');
                    $ib = ($sev[1] ?? ($rem[1] ?? ''));
                    if ($ia === $ib) {
                        $val['ip'] = "<em class='red'>{$ia} ~ {$ib}</em>";
                    } else {
                        $val['ip'] = "{$ia} ~ {$ib}";
                    }
                }
            }
            $value[$name] = $val;
        }
        ksort($value);
        $this->assign('value', $value);
        $this->assign('warn', intval($warn));
    }

    public function indexGet()
    {
        $files = [];
        $path = $this->_errorPath;
        if (!is_readable($path)) goto end;
        $file = $this->file($path, ['md', 'json']);
        foreach ($file as $i => $fil) {
            $time = strtotime(substr($fil, 0, 14));
            $files[$time . ($i + 1000)] = $fil;
        }
        ksort($files);
        end:
        $this->assign('path', $path);
        $this->assign('file', $files);
    }

    public function warnAction($fd)
    {
        $files = $folder = [];
        $path = $this->_warnPath;
        if (!is_readable($path)) goto end;
        if (empty($fd)) {
            $dir = new \DirectoryIterator($path);
            foreach ($dir as $f) {
                if ($f->isDot()) continue;
                if ($f->isDir()) {
                    $folder[] = $f->getFilename();
                }
            }
        } else {
            $file = $this->file("{$path}/{$fd}", ['md', 'json']);
            foreach ($file as $i => $fil) {
                $time = strtotime(substr($fil, 0, 14));
                $files[$time . ($i + 1000)] = "{$fd}/{$fil}";
            }
            ksort($files);
        }

        end:
        $this->assign('folder', $folder);
        $this->assign('path', $path);
        $this->assign('file', $files);
    }

    /**
     * 字符串里只有一组数字
     *
     * @param string $str
     * @return string
     */
    private function hideOneNumber(string $str): string
    {
        $a = preg_match('/([a-z\x20\'\"]+)([\d\.]+)([a-z\x20\'\"]+)/i', $str, $mt);
        if (!$a) return $str;
        return $mt[1] . $mt[3];
    }

    /**
     * @param string $a
     * @param string $b
     * @return bool
     */
    private function isLikeStr(string $a, string $b): bool
    {
        if ($a === $b) return true;
        return $this->hideOneNumber($a) === $this->hideOneNumber($b);
    }

    /**
     * 批量删除
     * @param $error
     * @param $fl
     * @param $warn
     * @return array
     */
    public function error_deleteAjax($error, $fl, $warn)
    {
        $error = urldecode($error);
        if ($error === 'null') $error = null;
        $path = $warn ? $this->_warnPath : $this->_errorPath;
        $dir = new \DirectoryIterator($path);
        $c = 0;
        foreach ($dir as $f) {
            if ($f->isDot()) continue;
            if ($f->isFile()) {
                $fn = $f->getPathname();
                $text = file_get_contents($fn);
                if (preg_match('/"error": "(.+)"/i', $text, $mch)) {
                    if ($this->isLikeStr($error, $mch[1])) {
                        unlink($fn);
                        $c++;
                    }
                } else if (preg_match('/"message": "(.+)"/i', $text, $mch)) {
                    if ($this->isLikeStr($error, $mch[1])) {
                        unlink($fn);
                        $c++;
                    }
                } else if (preg_match('/"Error": \[\s+.+?,\s+\"(.+?)"/is', $text, $mch)) {
                    if ($this->isLikeStr($error, $mch[1])) {
                        unlink($fn);
                        $c++;
                    }
                }
            }
        }

        return ['success' => 1, 'message' => "共删除了{$c}个相同错误信息"];
    }

    /**
     * @param $file
     * @param $warn
     * @return array
     */
    public function error_delAjax($file, $warn): array
    {
        $file = urldecode($file);
        $path = $warn ? $this->_warnPath : $this->_errorPath;
        $filename = root($path . "/{$file}");
        if (!is_readable($filename)) return ['success' => 0, 'message' => "{$file} not exists."];
        if (stripos($filename, $path) !== 0) return ['success' => 0, 'message' => "非Debug目录禁止删除"];
        unlink($filename);
        return ['success' => 1, 'message' => '删除成功'];
    }

    public function error_viewAction($file, $warn)
    {
        $path = $warn ? $this->_warnPath : $this->_errorPath;
        $file = urldecode($file);
        $filename = root($path . "/{$file}");
        if (!is_readable($filename)) $this->exit("{$file} not exists.");
        $this->css('/public/vui/css/markdown.css');
        $error = file_get_contents($filename);
        $json = json_decode($error, true);
        $debug = '';
        if ($json) {
            $debug = $json['Debug'] ?? '';
            $error = print_r($json, true);
            $error = substr($error, 7, -2);
        }
        $html = Markdown::html($error, false);
        $this->assign('file', $file);
        $this->assign('html', $html);
        $this->assign('warn', $warn);
        $this->assign('debug', $debug);
    }


}