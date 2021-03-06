<?php
//error_reporting(-1);

if (php_sapi_name() === 'cli') die('CLI环境中无法运行');

//if (count(get_included_files()) > 1) die('当前加载文件数较多');

define('_FIX', function_exists('opcache_reset') ? 'opcache_' : '');

if (!_FIX) die('<h4>Opcache未启用，请在PHP.ini中[opcache]组内增加：zend_extension=opcache.so</h4>');

$time = time();

if (!empty($_GET['RESET'])) {
    if (function_exists(_FIX . 'reset')) {
        call_user_func(_FIX . 'reset');
    }
    header('Location: ' . str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));
    exit;
} else if (!empty($_GET['RECHECK'])) {
    if (function_exists(_FIX . 'invalidate')) {
        $recheck = trim($_GET['RECHECK']);
        $files = call_user_func(_FIX . 'get_status');
        if (!empty($files['scripts'])) {
            foreach ($files['scripts'] as $file => $value) {
                if ($recheck === '1' || strpos($file, $recheck) === 0) call_user_func(_FIX . 'invalidate', $file);
            }
        }
        header('Location: ' . str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']));
    } else {
        echo 'Sorry, this feature requires Zend Opcache newer than April 8th 2013';
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <title>opCache</title>
    <style type="text/css">
        body {
            background-color: #fff;
            color: #000;
        }

        body, td, th, h1, h2 {
            font-family: sans-serif;
        }

        pre {
            margin: 0;
            font-family: monospace;
        }

        a:link, a:visited {
            color: #000099;
            text-decoration: none;
        }

        a.b {
            color: red;
        }

        a:hover {
            text-decoration: underline;
        }

        table {
            border-collapse: collapse;
            width: 1000px;
        }

        .center {
            text-align: center;
        }

        .center table {
            margin-left: auto;
            margin-right: auto;
            text-align: left;
        }

        .center th {
            text-align: center !important;
        }

        .middle {
            vertical-align: middle;
        }

        td, th {
            border: 1px solid #000;
            font-size: 75%;
            vertical-align: baseline;
            padding: 3px;
        }

        h1 {
            font-size: 150%;
        }

        h2 {
            font-size: 125%;
        }

        .p {
            text-align: left;
        }

        .e {
            background-color: #ccccff;
            font-weight: bold;
            color: #000;
            width: 50%;
            white-space: nowrap;
        }

        .h {
            background-color: #9999cc;
            font-weight: bold;
            color: #000;
            font-size: 12px;
        }

        .v {
            background-color: #cccccc;
            color: #000;
        }

        .vr {
            background-color: #cccccc;
            text-align: right;
            color: #000;
            white-space: nowrap;
        }

        .b {
            font-weight: bold;
        }

        .white, .white a {
            color: #fff;
        }

        img {
            float: right;
            border: 0;
        }

        hr {
            width: 1000px;
            background: #cccccc;
            border: 0;
            height: 1px;
            color: #000;
        }

        .meta, .small {
            font-size: 75%;
        }

        .meta {
            margin: 2em 0;
        }

        .meta a, th a {
            padding: 10px;
            white-space: nowrap;
        }

        .buttons {
            margin: 0 0 1em;
        }

        .buttons a {
            margin: 0 10px;
            background: #9999cc;
            color: #fff;
            text-decoration: none;
            padding: 1px;
            border: 1px solid #000;
            display: inline-block;
            width: 5em;
            text-align: center;
        }

        #files td.v a {
            font-weight: bold;
            color: #9999cc;
            margin: 0 10px 0 5px;
            text-decoration: none;
            font-size: 120%;
        }

        #files td.v a:hover {
            font-weight: bold;
            color: #ee0000;
        }

        .flexGraph {
            display: flex;
            width: 1000px;
            margin: 0 auto;
        }

        .graph {
            flex: 1;
            display: inline-block;
            border: 1px solid #aaa;
            vertical-align: top;
            margin: 5px;
        }

        .graph table {
            width: 100%;
            height: 150px;
            border: 0;
            padding: 0;
            margin: 5px 0 0 0;
            position: relative;
        }

        .graph td {
            vertical-align: middle;
            border: 0;
            padding: 0 0 0 5px;
        }

        .graph .bar {
            width: 25px;
            text-align: right;
            padding: 0 2px;
            color: #fff;
        }

        .graph .total {
            width: 34px;
            text-align: center;
            padding: 0 5px 0 0;
        }

        .graph .total div {
            border: 1px dashed #888;
            border-right: 0;
            height: 99%;
            width: 12px;
            position: absolute;
            bottom: 0;
            left: 17px;
            z-index: -1;
        }

        .graph .total span {
            background: #fff;
            font-weight: bold;
        }

        .graph .actual {
            text-align: right;
            font-weight: bold;
            padding: 0 5px 0 0;
        }

        .graph .red {
            background: #ee0000;
        }

        .graph .green {
            background: #00cc00;
        }

        .graph .brown {
            background: #8B4513;
        }
    </style>
</head>

<body class="center">

<h1>Opcache Control Panel</h1>

<div class="buttons">
    <a href="?">概要</a>
    <a href="?ALL=1">详细信息</a>
    <a href="?FILES=1&GROUP=4&SORT=4">文件数</a>
    <a href="?RESET=1" onclick="return confirm('确认清空?')">清空</a>
    <?php if (function_exists(_FIX . 'invalidate')) { ?>
        <a href="?RECHECK=1" onclick="return confirm('Recheck all files in the cache ?')">重新检查</a>
    <?php } ?>
    <a href="?" onclick="window.location.reload(true); return false">刷新</a>
</div>

<?php

if (!empty($_GET['FILES'])) {
    echo '<h2>files cached</h2>';
    files_display();
    echo '</div></body></html>';
    exit;
}
if (!(isset($_REQUEST['GRAPHS']) && !$_REQUEST['GRAPHS']) && _FIX == 'opcache_') {
    graphs_display();
    if (!empty($_REQUEST['GRAPHS'])) {
        exit;
    }
}
ob_start();
phpinfo(8);
$phpinfo = ob_get_contents();
ob_end_clean();
if (!preg_match('/module\_Zend.(Optimizer\+|OPcache).+?(\<table[^>]*\>.+?\<\/table\>).+?(\<table[^>]*\>.+?\<\/table\>)/is', $phpinfo, $opcache)) {
    die('未获取到OPcache相关信息');
}
if (function_exists(_FIX . 'get_configuration')) {
    echo '<h2>general</h2>';
    $configuration = call_user_func(_FIX . 'get_configuration');
}
$host = function_exists('gethostname') ? @gethostname() : @php_uname('n');
if (empty($host)) {
    $host = empty($_SERVER['SERVER_NAME']) ? $_SERVER['HOST_NAME'] : $_SERVER['SERVER_NAME'];
}
$version = array('Host' => $host);
$version['PHP Version'] = 'PHP ' . (defined('PHP_VERSION') ? PHP_VERSION : '???') . ' ' . (defined('PHP_SAPI') ? PHP_SAPI : '') . ' ' . (defined('PHP_OS') ? ' ' . PHP_OS : '');
$version['Opcache Version'] = empty($configuration['version']['version']) ? '???' : $configuration['version'][_FIX . 'product_name'] . ' ' . $configuration['version']['version'];
print_table($version);
echo "<br>";
if (!empty($opcache[2])) {
    echo preg_replace('/\<tr\>\<td class\="e"\>[^>]+\<\/td\>\<td class\="v"\>[0-9\,\. ]+\<\/td\>\<\/tr\>/', '', $opcache[2]);
    echo "<br>";
}
if (function_exists(_FIX . 'get_status') && $status = call_user_func(_FIX . 'get_status')) {
    $uptime = array();
    if (!empty($status[_FIX . 'statistics']['start_time'])) {
        $uptime['uptime'] = time_since($time, $status[_FIX . 'statistics']['start_time'], 1, '');
    }
    if (!empty($status[_FIX . 'statistics']['last_restart_time'])) {
        $uptime['last_restart'] = time_since($time, $status[_FIX . 'statistics']['last_restart_time']);
    }
    if (!empty($uptime)) {
        print_table($uptime);
    }

    if (!empty($status['cache_full'])) {
        $status['memory_usage']['cache_full'] = $status['cache_full'];
    }

    echo '<h2 id="memory">memory</h2>';
    print_table($status['memory_usage']);
    unset($status[_FIX . 'statistics']['start_time'], $status[_FIX . 'statistics']['last_restart_time']);
    echo '<h2 id="statistics">statistics</h2>';
    print_table($status[_FIX . 'statistics']);
}
if (empty($_GET['ALL'])) {
    exit;
}

if (!empty($configuration['blacklist'])) {
    echo '<h2 id="blacklist">blacklist</h2>';
    print_table($configuration['blacklist']);
}
if (!empty($opcache[3])) {
    echo '<h2 id="runtime">runtime</h2>';
    echo $opcache[3];
}
$name = 'zend opcache';
$functions = get_extension_funcs($name);
if (!$functions) {
    $name = 'zend optimizer+';
    $functions = get_extension_funcs($name);
} else {
    echo '<h2 id="functions">functions</h2>';
    print_table($functions);
}
$level = trim(_FIX, '_') . '.optimization_level';
if (isset($configuration['directives'][$level])) {
    echo '<h2 id="optimization">optimization levels</h2>';
    $levelset = strrev(base_convert($configuration['directives'][$level], 10, 2));
    $levels = array(
        1 => '<a href="http://wikipedia.org/wiki/Common_subexpression_elimination">Constants subexpressions elimination</a> (CSE) true, false, null, etc.
<br />Optimize series of ADD_STRING / ADD_CHAR
<br />Convert CAST(IS_BOOL,x) into BOOL(x)
<br />Convert <a href="http://www.php.net/manual/internals2.opcodes.init-fcall-by-name.php">INIT_FCALL_BY_NAME</a> + <a href="http://www.php.net/manual/internals2.opcodes.do-fcall-by-name.php">DO_FCALL_BY_NAME</a> into <a href="http://www.php.net/manual/internals2.opcodes.do-fcall.php">DO_FCALL</a>',
        2 => 'Convert constant operands to expected types<br />Convert conditional <a href="http://php.net/manual/internals2.opcodes.jmp.php">JMP</a>  with constant operands<br />Optimize static <a href="http://php.net/manual/internals2.opcodes.brk.php">BRK</a> and <a href="<a href="http://php.net/manual/internals2.opcodes.cont.php">CONT</a>',
        3 => 'Convert $a = $a + expr into $a += expr<br />Convert $a++ into ++$a<br />Optimize series of <a href="http://php.net/manual/internals2.opcodes.jmp.php">JMP</a>',
        4 => 'PRINT and ECHO optimization (<a href="https://github.com/zend-dev/ZendOptimizerPlus/issues/73">defunct</a>)',
        5 => 'Block Optimization - most expensive pass<br />Performs many different optimization patterns based on <a href="http://wikipedia.org/wiki/Control_flow_graph">control flow graph</a> (CFG)',
        9 => 'Optimize <a href="http://wikipedia.org/wiki/Register_allocation">register allocation</a> (allows re-usage of temporary variables)',
        10 => 'Remove NOPs'
    );
    echo '<table width="1000" border="0" cellpadding="3"><tbody><tr class="h"><th>Pass</th><th>Description</th></tr>';
    foreach ($levels as $pass => $description) {
        $disabled = substr($levelset, $pass - 1, 1) !== '1' || $pass == 4 ? ' white' : '';
        echo '<tr><td class="v center middle' . $disabled . '">' . $pass . '</td><td class="v' . $disabled . '">' . $description . '</td></tr>';
    }
    echo '</table>';
}
if (isset($_GET['DUMP'])) {
    if ($name) {
        echo '<h2 id="ini">ini</h2>';
        print_table(ini_get_all($name, true));
    }
    foreach ($configuration as $key => $value) {
        echo '<h2>', $key, '</h2>';
        print_table($configuration[$key]);
    }
    exit;
}


function time_since($time, $original, $extended = 0, $text = 'ago')
{
    $time = $time - $original;
    $day = $extended ? floor($time / 86400) : round($time / 86400, 0);
    $amount = 0;
    $unit = '';
    if ($time < 86400) {
        if ($time < 60) {
            $amount = $time;
            $unit = 'second';
        } elseif ($time < 3600) {
            $amount = floor($time / 60);
            $unit = 'minute';
        } else {
            $amount = floor($time / 3600);
            $unit = 'hour';
        }
    } elseif ($day < 14) {
        $amount = $day;
        $unit = 'day';
    } elseif ($day < 56) {
        $amount = floor($day / 7);
        $unit = 'week';
    } elseif ($day < 672) {
        $amount = floor($day / 30);
        $unit = 'month';
    } else {
        $amount = intval(2 * ($day / 365)) / 2;
        $unit = 'year';
    }

    if ($amount != 1) {
        $unit .= 's';
    }
    if ($extended && $time > 60) {
        $text = ' and ' . time_since($time, $time < 86400 ? ($time < 3600 ? $amount * 60 : $amount * 3600) : $day * 86400, 0, '') . $text;
    }

    return $amount . ' ' . $unit . ' ' . $text;
}

function print_table($array, $headers = false)
{
    if (empty($array) || !is_array($array)) {
        return;
    }
    echo '<table border="0" cellpadding="3" width="1000">';
    if (!empty($headers)) {
        if (!is_array($headers)) {
            $headers = array_keys(reset($array));
        }
        echo '<tr class="h">';
        foreach ($headers as $value) {
            echo '<th>', $value, '</th>';
        }
        echo '</tr>';
    }
    foreach ($array as $key => $value) {
        echo '<tr>';
        if (!is_numeric($key)) {
            $key = ucwords(str_replace('_', ' ', $key));
            echo '<td class="e">', $key, '</td>';
            if (is_numeric($value)) {
                if ($value > 1048576) {
                    $value = round($value / 1048576, 1) . 'M';
                } elseif (is_float($value)) {
                    $value = round($value, 1);
                }
            }
        }
        if (is_array($value)) {
            foreach ($value as $column) {
                echo '<td class="v">', $column, '</td>';
            }
            echo '</tr>';
        } else {
            echo '<td class="v">', $value, '</td></tr>';
        }
    }
    echo '</table>';
}

function files_display()
{
    $status = call_user_func(_FIX . 'get_status');
    if (empty($status['scripts'])) {
        return;
    }
    if (isset($_GET['DUMP'])) {
        print_table($status['scripts']);
        exit;
    }
    $time = time();
    $sort = 0;
    $nogroup = preg_replace('/\&?GROUP\=[\-0-9]+/', '', $_SERVER['REQUEST_URI']);
    $nosort = preg_replace('/\&?SORT\=[\-0-9]+/', '', $_SERVER['REQUEST_URI']);
    $group = empty($_GET['GROUP']) ? 0 : intval($_GET['GROUP']);
    if ($group < 0 || $group > 9) {
        $group = 1;
    }
    $groupset = array_fill(0, 9, '');
    $groupset[$group] = ' class="b" ';

    echo '<div class="meta">';
    for ($l = 0; $l < 9; $l++) {
        echo "<a {$groupset[$l]} href='{$nogroup}&GROUP={$l}'>{$l}级</a> |";
    }
    echo '</div>';

    if (!$group) {
        $files =& $status['scripts'];
    } else {
        $files = array();
        foreach ($status['scripts'] as $data) {
            if (preg_match('@^[/]([^/]+[/]){' . $group . '}@', $data['full_path'], $path)) {
                if (empty($files[$path[0]])) {
                    $files[$path[0]] = array('full_path' => '', 'files' => 0, 'hits' => 0, 'memory_consumption' => 0, 'last_used_timestamp' => '', 'timestamp' => '');
                }
                $files[$path[0]]['full_path'] = $path[0];
                $files[$path[0]]['files']++;
                $files[$path[0]]['memory_consumption'] += $data['memory_consumption'];
                $files[$path[0]]['hits'] += $data['hits'];
                if ($data['last_used_timestamp'] > $files[$path[0]]['last_used_timestamp']) {
                    $files[$path[0]]['last_used_timestamp'] = $data['last_used_timestamp'];
                }
                if ($data['timestamp'] > $files[$path[0]]['timestamp']) {
                    $files[$path[0]]['timestamp'] = $data['timestamp'];
                }
            }
        }
    }

    if (!empty($_GET['SORT'])) {
        $keys = array(
            'full_path' => SORT_STRING,
            'files' => SORT_NUMERIC,
            'memory_consumption' => SORT_NUMERIC,
            'hits' => SORT_NUMERIC,
            'last_used_timestamp' => SORT_NUMERIC,
            'timestamp' => SORT_NUMERIC
        );
        $titles = array('', 'path', $group ? 'files' : '', 'size', 'hits', 'last used', 'created');
        $offsets = array_keys($keys);
        $key = intval($_GET['SORT']);
        $direction = $key > 0 ? 1 : -1;
        $key = abs($key) - 1;
        $key = isset($offsets[$key]) && !($key == 1 && empty($group)) ? $offsets[$key] : reset($offsets);
        $sort = array_search($key, $offsets) + 1;
        $sortflip = range(0, 7);
        $sortflip[$sort] = -$direction * $sort;
        if ($keys[$key] == SORT_STRING) {
            $direction = -$direction;
        }
        $arrow = array_fill(0, 7, '');
        $arrow[$sort] = $direction > 0 ? ' &#x25BC;' : ' &#x25B2;';
        $direction = $direction > 0 ? SORT_DESC : SORT_ASC;
        $column = array();
        foreach ($files as $data) {
            $column[] = $data[$key];
        }
        array_multisort($column, $keys[$key], $direction, $files);
    }
    echo '<table border="0" cellpadding="3" width="960" id="files"><tr class="h">';
    foreach ($titles as $column => $title) {
        if ($title) echo '<th><a href="', $nosort, '&SORT=', $sortflip[$column], '">', $title, $arrow[$column], '</a></th>';
    }
    echo '</tr>';
    foreach ($files as $data) {
        echo '<tr><td class="v" nowrap><a title="recheck" href="?RECHECK=', rawurlencode($data['full_path']), '">x</a>', $data['full_path'], '</td>',
        ($group ? '<td class="vr">' . number_format($data['files']) . '</td>' : ''),
        '<td class="vr">', number_format(round($data['memory_consumption'] / 1024)), 'K</td>',
        '<td class="vr">', number_format($data['hits']), '</td>',
        '<td class="vr">', time_since($time, $data['last_used_timestamp']), '</td>',
        '<td class="vr">', empty($data['timestamp']) ? '' : time_since($time, $data['timestamp']), '</td></tr>';
    }
    echo '</table>';
}

function graphs_display()
{
    $graphs = array();
    $colors = array('green', 'brown', 'red');
    $primes = array(223, 463, 983, 1979, 3907, 7963, 16229, 32531, 65407, 130987);
    $configuration = call_user_func(_FIX . 'get_configuration');
    $status = call_user_func(_FIX . 'get_status');
    $graphs['memory']['title'] = '内存占用';
    $graphs['memory']['total'] = $configuration['directives']['opcache.memory_consumption'];
    $graphs['memory']['free'] = $status['memory_usage']['free_memory'];
    $graphs['memory']['used'] = $status['memory_usage']['used_memory'];
    $graphs['memory']['wasted'] = $status['memory_usage']['wasted_memory'];

    $graphs['keys']['title'] = '记录数';
    $graphs['keys']['total'] = $status[_FIX . 'statistics']['max_cached_keys'];
    foreach ($primes as $prime) {
        if ($prime >= $graphs['keys']['total']) {
            $graphs['keys']['total'] = $prime;
            break;
        }
    }
    $graphs['keys']['free'] = $graphs['keys']['total'] - $status[_FIX . 'statistics']['num_cached_keys'];
    $graphs['keys']['scripts'] = $status[_FIX . 'statistics']['num_cached_scripts'];
    $graphs['keys']['wasted'] = $status[_FIX . 'statistics']['num_cached_keys'] - $status[_FIX . 'statistics']['num_cached_scripts'];

    $graphs['hits']['title'] = '命中率';
    $graphs['hits']['total'] = 0;
    $graphs['hits']['hits'] = $status[_FIX . 'statistics']['hits'];
    $graphs['hits']['misses'] = $status[_FIX . 'statistics']['misses'];
    $graphs['hits']['blacklist'] = $status[_FIX . 'statistics']['blacklist_misses'];
    $graphs['hits']['total'] = array_sum($graphs['hits']);

    $graphs['restarts']['title'] = '重启次数';
    $graphs['restarts']['total'] = 0;
    $graphs['restarts']['manual'] = $status[_FIX . 'statistics']['manual_restarts'];
    $graphs['restarts']['keys'] = $status[_FIX . 'statistics']['hash_restarts'];
    $graphs['restarts']['memory'] = $status[_FIX . 'statistics']['oom_restarts'];
    $graphs['restarts']['total'] = array_sum($graphs['restarts']);
    echo "<div class='flexGraph'>";
    foreach ($graphs as $caption => $graph) {
        echo '<div class="graph"><div class="h">', $graph['title'], '</div><table border="0" cellpadding="0" cellspacing="0">';
        unset($graph['title']);
        foreach ($graph as $label => $value) {
            if ($label == 'total') {
                $key = 0;
                $total = $value;
                $totaldisplay = '<td rowspan="3" class="total"><span>' . ($total > 999999 ? round($total / 1024 / 1024) . 'M' : ($total > 9999 ? round($total / 1024) . 'K' : $total)) . '</span><div></div></td>';
                continue;
            }
            $percent = $total ? floor($value * 100 / $total) : '';
            $percent = !$percent || $percent > 99 ? '' : $percent . '%';
            echo '<tr>', $totaldisplay, '<td class="actual">', ($value > 999999 ? round($value / 1024 / 1024) . 'M' : ($value > 9999 ? round($value / 1024) . 'K' : $value)), '</td><td class="bar ', $colors[$key], '" height="', $percent, '">', $percent, '</td><td>', $label, '</td></tr>';
            $key++;
            $totaldisplay = '';
        }
        echo '</table></div>', "\n";
    }
    echo "</div>";
}

?>

</body>
</html>