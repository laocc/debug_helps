<div id="body" class="fixedForm"
     xmlns:v-slot="http://www.w3.org/1999/XSL/Transform"
     xmlns:v-html="http://www.w3.org/1999/XSL/Transform">


    <el-form :inline="true" class="searchForm" onsubmit="return !1;">
        <el-form-item>
            <db-button class="btn" type="link" url="<?= $linkPath ?>/counter/index/0">今天</db-button>
            <db-button class="btn ml5" type="link" url="<?= $linkPath ?>/counter/index/1">昨天</db-button>
            <db-button class="btn ml5" type="link" url="<?= $linkPath ?>/counter/index/2">前天</db-button>
            <db-button class="btn ml5" type="link" url="<?= $linkPath ?>/counter/index/3">前3天</db-button>
        </el-form-item>
    </el-form>


    <table class="dbTable">
        <thead>
        <tr>
            <th width="90">Action</th>
            <th v-for="h in 24" style="font-size: 12px;">{{h-1}}-{{h}}</th>
        </tr>
        </thead>
        <tbody>
        <template v-for="(day,vt) in bodyData">
            <tr>
                <td colspan="25" style="text-align: center;background: #2d5dc7;color: #fff;">{{vt}}</td>
            </tr>
            <tr v-for="act in day.action">
                <td>{{act}}</td>
                <td v-for="h in 24">{{day.data[h]?day.data[h][act]:''}}</td>
            </tr>

        </template>

        </tbody>
    </table>

</div>
<script>

    let vm = new Vue({
        el: '#body',
        data() {
            return {
                bodyData: <?=$data?>,
            }
        }
    });

</script>