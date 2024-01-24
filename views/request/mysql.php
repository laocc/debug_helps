<style>

    div.canvas {
        clear: both;
        padding: 0;
        box-shadow: 1px 1px 2px #ccc;
        height: 700px;
        width: 99%;
        margin: 10px auto;
        border: 1px solid #cccccc;
        background-color: #ffffff;
    }

</style>
<div id="body" class="fixedForm"
     xmlns:v-slot="http://www.w3.org/1999/XSL/Transform"
     xmlns:v-html="http://www.w3.org/1999/XSL/Transform">

    <div>
        <a href="?type=0">今天</a>
        <a href="?type=1">昨天</a>
        <a href="?type=2">前天</a>
        <a href="?type=3">大前天</a>
    </div>


    <div class="canvas">
        <canvas id="canvas" style="height:100%;width: 100%;"></canvas>
    </div>
</div>

<script src="/public/resource/chart/Chart.min.js"></script>
<script src="/public/resource/chart/Chart.ext.js"></script>

<script>
    var config = {
        type: 'line',
        data: {
            labels: <?=$labels?>,
            datasets: [{
                label: '每秒平均',
                backgroundColor: window.chartColors.red,
                borderColor: window.chartColors.red,
                data: <?=$minute?>,
                fill: false,
            }, {
                label: '并发最高',
                backgroundColor: window.chartColors.blue,
                borderColor: window.chartColors.blue,
                data: <?=$top?>,
                fill: false,
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Mysql并发统计'
            },
            scales: {
                yAxes: [{ticks: {suggestedMin: 1, suggestedMax: <?=$max?>}}]
            }
        }
    };

    window.onload = function () {
        let ctx = document.getElementById('canvas').getContext('2d');
        window.myLine = new Chart(ctx, config);
    };

    let vm = new Vue({
        el: '#body',
        // mixins: [expBodyMixin],
        data() {
            return {
                // bodyDataApi: '/debug/concurrent',
            }
        }
    });

</script>