<?php
declare(strict_types=1);

namespace esp\debugs;

use esp\helper\library\ext\Markdown;

class Debug extends _Base
{


    public function ordAction($path)
    {
        $key = $_GET['key'] ?? '';
        $this->assign('path', $path);
        $this->assign('key', $key);
        $path = urldecode($path);
        $key = urldecode($key);
        $rnd = date('YmdHis');

        $ord = '';
        if ($key) {
            $name = "{$path}/{$key}-{$rnd}";
            $ord = "touch {$name}.txt";
            $ord .= " && find {$path} -type f -name \"*.md\" | xargs grep \"{$key}\" -l > {$name}.txt";
            $ord .= " \n zip -r {$name}.zip `cat {$name}.txt` ";
            $ord .= " \n sz {$name}.zip \n";
        }
        $this->assign('order', $ord);
    }

    public function indexGet($path)
    {
        if (empty($path)) {
            $pathT = $_GET['path'] ?? '';
            if (empty($pathT)) $pathT = $this->_rootPath . '/' . date('Y_m_d');
        } else {
            $pathT = urldecode($path);
        }

        $path = realpath($pathT);
        if (strpos($path, $this->_rootPath) !== 0) $this->exit("无权限查看该目录:" . var_export($pathT, true));

        if (is_file($path)) $path = dirname($path);

        if (!is_string($path) or !is_readable($path)) $this->exit('empty:' . var_export($pathT, true));
        $file = $this->folder($path);
        ksort($file[0]);
        ksort($file[1]);
//        ksort($file);
        $this->assign('allDir', $file);
        $this->assign('path', substr($path, strlen($this->_rootPath)));
        $this->assign('debug', $this->_rootPath);
    }

    public function o_indexAction()
    {
        if (!is_readable($this->_rootPath)) $this->exit('empty');
        $file = $this->path($this->_rootPath);
        krsort($file);
        $this->assign('allDir', $file);
    }


    public function filesAction(string $path, $ext = 'md')
    {
        $path = urldecode($path);
        if (!is_readable($path)) $this->exit('empty');
        if (!$ext) $ext = 'md';
        $file = $this->file($path, $ext);
        $this->assign('path', $path);
        $this->assign('file', $file);
        if ($ext === 'json') {
            $this->setView('debug/json.php');
        }
    }

    public function fileAction($file)
    {
        if (!$file) $file = $_GET['file'] ?? '';
        $path = realpath(urldecode($file));
        if ($path === false) $this->exit("文件不存在：" . urldecode($file));
        if (!is_readable($path)) $this->exit($path . '文件不存在');

        $this->css('/public/vui/css/markdown.css');
        $html = Markdown::html(file_get_contents($path), false);
        $this->assign('html', $html);
    }


    public function delAjax($path)
    {
        $path = urldecode($path);

        if (stripos($path, $this->_rootPath) !== 0) return '非Debug目录禁止删除';

        $unlink = 0;
        D:
        foreach ($this->path($path, 0) as $p) {
//            echo "P:{$p}\n";
            $pfile = 0;
            foreach ($this->file($p, []) as $f) {
//                echo "F:{$p}/{$f}\n";
                unlink("{$p}/{$f}");
                $unlink++;
                $pfile++;
            }
            if ($pfile > 0) rmdir($p);
            else {
                $path = $p;
                goto D;
            }
        }

        return ['success' => 1, 'message' => "删除了{$unlink}个文档"];
    }


}