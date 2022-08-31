<?php

namespace esp\debugs;

use esp\core\Controller;

class _Base extends Controller
{
    protected $_rootPath;
    protected $_errorPath;
    protected $_warnPath;
    protected $linkPath = '';

    public function _init()
    {
        if (is_null($this->_rootPath)) {
            $this->_rootPath = $this->_dispatcher->_debug->root();
            $this->_rootPath = dirname($this->_rootPath);
        }
        if (is_null($this->_errorPath)) $this->_errorPath = _RUNTIME . '/error';
        if (is_null($this->_warnPath)) $this->_warnPath = _RUNTIME . '/warn';

        $this->_dispatcher->_debug->disable();

        if ($this->_request->isGet()) {
            if (!$this->linkPath and $this->_request->module) $this->linkPath = "/{$this->_request->module}";
            $this->assign('linkPath', $this->linkPath ?? '');
            $this->setViewPath('@' . dirname(__DIR__) . '/views');
            $this->setLayout(dirname(__DIR__) . '/views/layout.php');
        }

    }


    /**
     * 读取文件目录所有文件
     *
     * @param string $path
     * @param int $lev
     * @return array
     */
    protected function path(string $path, int $lev = 0)
    {
        $array = array();
        $dir = new \DirectoryIterator($path);
        foreach ($dir as $f) {
            if ($f->isDir()) {
                $name = $f->getFilename();
                if (in_array($name, ['.', '..'])) continue;
                $nPath = "{$path}/{$name}";
                if (is_dir($nPath)) {
                    if ($lev) {
                        $array[$name] = $this->path($nPath, $lev++);
                    } else {
                        $array[$name] = $nPath;
                    }
                }
            }
        }
        return $array;
    }


    /**
     * 仅列出当前目录里的第1级目录
     * @param string $path
     * @return array
     */
    protected function folder(string $path)
    {
        $folder = array();
        $file = array();
        $dir = new \DirectoryIterator($path);
        foreach ($dir as $f) {
            $name = $f->getFilename();
            if (in_array($name, ['.', '..'])) continue;
            if ($f->isDir()) {
                $folder[$name] = "{$path}/{$name}";
            } elseif ($f->isFile()) {
                $file[$name] = "{$path}/{$name}";
            }
        }
        return [$folder, $file];
    }


    /**
     * 文件
     * @param string $path
     * @param  $ext
     * @return array
     */
    protected function file(string $path, $ext)
    {
        if (!is_dir($path)) return [];
        $array = array();
        $dir = new \DirectoryIterator($path);
        if (empty($ext)) $ext = [];
        if (is_string($ext)) $ext = [$ext];
        foreach ($ext as &$t) $t = trim($t, '.');
        foreach ($dir as $f) {
            if ($f->isFile()) {
                if ($ext) {
                    if (in_array($f->getExtension(), $ext)) $array[] = $f->getFilename();
                } else {
                    $array[] = $f->getFilename();
                }
            }
        }
        return $array;
    }

}