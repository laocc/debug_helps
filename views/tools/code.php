<div id="body" class="editBody">
    <el-form ref="form" class="editForm" style="margin:1em;width:1200px;">

        <el-tabs type="border-card">
            <el-tab-pane label="时间转换">

                <el-form-item label="时间">
                    <el-input v-model="time.datetime">
                        <span slot="append">{{time.datetime|timestamp}}</span>
                    </el-input>
                </el-form-item>

                <el-form-item label="时间戳">
                    <el-input v-model="time.timestamp">
                        <span slot="append">{{time.timestamp|datetime}}</span>
                    </el-input>
                </el-form-item>

                <el-form-item label="时间加减">
                    <el-input v-model="add.time" style="flex:1"></el-input>
                    <el-input v-model="add.num" style="flex:3;" placeholder="+/- n ymdhis" class="ml5">
                        <span slot="append">{{watchVal.add}}</span>
                    </el-input>
                </el-form-item>

                <el-form-item label="时间间隔">
                    <el-input v-model="diff.time1" style="flex:1"></el-input>
                    <el-input v-model="diff.time2" style="flex:3;" class="ml5">
                        <span slot="append">{{watchVal.diff}}</span>
                    </el-input>
                </el-form-item>

            </el-tab-pane>
            <el-tab-pane label="编码转换">

                <el-form-item label="原始内容">
                    <el-input type="textarea" v-model="code.real"></el-input>
                </el-form-item>

                <el-form-item label="转码方式" pane>
                    <db-radio :data="type" v-model="code.type" type="button"></db-radio>
                </el-form-item>

                <el-form-item label="转换内容">
                    <el-input type="textarea" v-model="code.value" :rows="6"></el-input>
                </el-form-item>

                <el-form-item label=" " v-if="code.type==='qr'">
                    <img :src="code.value" style="width: 260px;">
                </el-form-item>

            </el-tab-pane>


        </el-tabs>

        <div class="submit">
        </div>

    </el-form>
</div>


<script>
    let now = '<?=date('Y-m-d H:i:s')?>';

    let vm = new Vue({
        el: '#body',
        data() {
            return {
                time: {
                    timestamp: <?=time()?>,
                    datetime: now,
                },
                watchVal: {
                    add: now,
                    diff: '时间相等'
                },
                add: {
                    time: now,
                    num: '',
                },
                diff: {
                    time1: now,
                    time2: now,
                },
                type: ['qr', 'md5', 'sha1', 'sha256', '2power',
                    'url_encode', 'url_decode',
                    'htmlentities', 'html_decode',
                    'base64_encode', 'base64_decode',
                ],
                code: {
                    real: '',
                    type: '',
                    value: ''
                }
            }
        },
        watch: {
            add: {
                deep: true,
                handler: function (a, b) {
                    if (!a.num) return;
                    let mch = String(a.num.trim()).match(/^([+-])\s?(\d+)\s?(\w+)$/);
                    if (!mch) return;
                    let now = new Date(a.time);
                    let y = now.getFullYear();
                    let m = now.getMonth();
                    let d = now.getDate();
                    let h = now.getHours();
                    let i = now.getMinutes();
                    let s = now.getSeconds();
                    let num = parseInt(mch[2]) * ((mch[1] === '+') ? 1 : -1);
                    switch (mch[3].toLowerCase()) {
                        case 'y':
                        case 'year':
                            y = y + num;
                            break;
                        case 'm':
                        case 'month':
                            m = m + num;
                            break;
                        case 'd':
                        case 'day':
                            d = d + num;
                            break;
                        case 'h':
                        case 'hour':
                            h = h + num;
                            break;
                        case 'i':
                        case 'minute':
                            i = i + num;
                            break;
                        case 's':
                        case 'second':
                            s = s + num;
                            break;
                    }
                    this.watchVal.add = new Date(y, m, d, h, i, s).format('YYYY-MM-DD HH:ii:ss');
                }
            },
            diff: {
                deep: true,
                handler: function (a, b) {
                    let d = parseInt((new Date(a.time2) - new Date(a.time1)) / 1000);
                    if (d === 0) {
                        this.watchVal.diff = '时间相等';
                        return;
                    }
                    let val = '';
                    if (d < 0) {
                        val = '-';
                        d = 0 - d;
                    }
                    if (d > 86400) {
                        val += parseInt(d / 86400) + '天';
                        d = d % 86400;
                    }
                    if (d > 3600) {
                        val += parseInt(d / 3600) + '小时';
                        d = d % 3600;
                    }
                    if (d > 60) {
                        val += parseInt(d / 60) + '分钟';
                        d = d % 60;
                    }
                    val += d + '秒';
                    this.watchVal.diff = val;
                }
            },
            'code.real': function () {
                this.code.type = '';
            },
            'code.type': function (a, b) {
                if (!a) return;
                if (!this.code.real) return;

                this.$post('/tools/code', {code: this.code.real, type: a}).then(
                    res => {
                        // console.log(res);
                        if (!res.success) return;
                        this.code.value = res.value;
                    },
                    err => {
                    }
                );
            }
        },
        filters: {
            datetime: function (tms) {
                return (new Date(Number(tms) * 1000)).format('YYYY-MM-DD HH:ii:ss');
            },
            timestamp: function (tm) {
                return parseInt(Number(new Date(tm)) / 1000);
            },
        },
        methods: {}

    });
</script>

