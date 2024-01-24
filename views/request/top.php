<div id="body" class="fixedForm"
     xmlns:v-slot="http://www.w3.org/1999/XSL/Transform"
     xmlns:v-html="http://www.w3.org/1999/XSL/Transform">

    <el-form :inline="true" class="searchForm" @submit="loadBodyData" onsubmit="return !1;">
        <el-form-item>
            <el-radio-group v-model="bodyForm.type" size="small" @change="loadBodyData">
                <el-radio-button label="">今天</el-radio-button>
                <el-radio-button label="1">昨天</el-radio-button>
                <el-radio-button label="2">前天</el-radio-button>
            </el-radio-group>
        </el-form-item>

        <el-form-item>
            <db-input v-model="bodyForm.key" @enter="doInputEnter" placeholder="搜索关键词" clearable></db-input>
        </el-form-item>
        <el-form-item>
            <db-button class="btn primary small" @click="loadBodyData">查询</db-button>
        </el-form-item>
        <el-form-item>
            <db-button class="btn primary small ajax"
                       url="/debugs/request/clearcount">清除三天前数据
            </db-button>
        </el-form-item>
    </el-form>


    <table class="dbTable">
        <thead>
        <tr v-if="bodyData.total">
            <td width="230">mySQL共运行{{bodyData.total}}次；</td>
            <td width="350">缓存命中{{bodyData.hit.sum}}次/{{bodyData.hit.run}}处；占比{{bodyData.hit.pnt}}%;</td>
            <td width="400">select：{{bodyData.select.sum}}次/{{bodyData.select.run}}处，占比{{bodyData.select.pnt}}%；</td>
            <td width="350">update：{{bodyData.update.sum}}次/{{bodyData.update.run}}处，占比{{bodyData.update.pnt}}%；</td>
            <td width="350">insert：{{bodyData.insert.sum}}次/{{bodyData.insert.run}}处，占比{{bodyData.insert.pnt}}%；</td>
            <td>&nbsp;</td>
        </tr>
        </thead>
        <tbody>
        <template v-for="(ln,i) in bodyData.value" v-key="i">
            <tr>
                <td colspan="6">
                    {{ln.sql}}
                    <div>{{i+1}}.Run:<span>{{ln.run}}</span>&nbsp;File:{{ln.file}}({{ln.line}})</div>
                    <div>{{ln.key}}</div>
                </td>
            </tr>

        </template>

        </tbody>
    </table>
</div>
<style>
    td > div {
        clear: both;
        padding: 5px;
        background: #eee;
        color: #aaa;
        font-size: 12px;
        margin-top: 5px;
        border-radius: 3px;
    }

    td > div > span {
        color: #ff0000;
    }
</style>
<script>

    let vm = new Vue({
        el: '#body',
        mixins: [expBodyMixin],
        data() {
            return {
                bodyDataApi: '/debugs/request/top',
            }
        },
        methods: {
            clearCache() {

            }
        }
    });

</script>