<link rel="stylesheet" href="/public/vui/node_modules/layui-src/dist/css/layui.css" media="all">
<script src="/public/vui/node_modules/layui-src/dist/layui.js"></script>
<script src="/public/vui/extend/layui.extend.js?__RAND__"></script>
<script src="/public/vui/extend/layui.patch.js?__RAND__"></script>

<form action="<?=$_linkPath?>/debug/file" method="get" autocomplete="off" style="width:90%;margin:0 auto;margin-top:1em;">
    <input type="tel" name="file" class="layui-input" style="width:1000px;display: inline-block"
           value="<?= $debug ?>" placeholder="输入日志文件完整路径快速查看">
    <button class="layui-btn">查看</button>
    <a class="layui-btn ajax" title="确认删除该文件？"
       href="<?=$_linkPath?>/error/error_del/<?= urlencode($file) . '/' . $warn ?>">删除</a>
</form>

<article class="markdown" style="width:90%;margin:0 auto;">
    <?= $html ?>
</article>
