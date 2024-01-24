<?php

namespace esp\debugs;

use esp\core\Controller;

class _Base extends Controller
{

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
            if ($f->isDot()) continue;
            if (!$f->isDir()) continue;
            $name = $f->getFilename();
            $nPath = "{$path}/{$name}";
            if (is_dir($nPath)) {
                if ($lev) {
                    $array[$name] = $this->path($nPath, $lev++);
                } else {
                    $array[$name] = $nPath;
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
            if ($f->isDot()) continue;
            $name = $f->getFilename();
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