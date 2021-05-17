<template>
  <div class="dashboard-editor-container">
    <el-card>
      <el-tabs v-model="activeActivity">
        <el-tab-pane label="Inbounds" name="Inbounds">
          <keep-alive>
            <inbounds v-if="params != null && activeActivity=='Inbounds'" :params="params" />
          </keep-alive>
        </el-tab-pane>
        <el-tab-pane label="Outbounds" name="Outbounds">
          <keep-alive>
            <outbounds v-if="params != null && activeActivity=='Outbounds'" :params="params" />
          </keep-alive>
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
</template>

<script>
import Resource from '@/api/resource';
import Inbounds from './inbounds';
import Outbounds from './outbounds';
const necessaryParams = new Resource('fetch-necessary-params');
export default {
  name: 'DownloadableReport',
  components: {
    Inbounds, Outbounds,
  },
  data() {
    return {
      activeActivity: 'Inbounds',
      data_summary: '',
      params: null,
    };
  },
  created() {
    this.fetchNecessaryParams();
    // window.Echo.private('App.Laravue.Models.User.' + 1)
    //         .notification((notification) => {
    //           console.log(notification);
    //         });
  },
  methods: {
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list()
        .then(response => {
          app.params = response.params;
        });
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.dashboard-editor-container {
  padding: 10px;
  background-color: rgb(240, 242, 245);
  .chart-wrapper {
    background: #fff;
    padding: 16px 16px 0;
    margin-bottom: 10px;
  }
}
</style>
