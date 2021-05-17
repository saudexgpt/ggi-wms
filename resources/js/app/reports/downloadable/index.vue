<template>
  <div class="dashboard-editor-container">
    <el-card>
      <el-tabs v-model="activeActivity">
        <el-tab-pane label="Instant Balances" name="InstantBalances">
          <keep-alive>
            <instant-balances v-if="params != null && activeActivity=='InstantBalances'" :params="params" />
          </keep-alive>
        </el-tab-pane>
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
        <el-tab-pane label="Products" name="Products">
          <keep-alive>
            <products v-if="activeActivity=='Products'" :can-create-new-product="false" />
          </keep-alive>
        </el-tab-pane>
        <el-tab-pane label="Fleet" name="Fleet">
          <keep-alive>
            <fleets v-if="activeActivity=='Fleet'" :can-add-new="false" />
          </keep-alive>
        </el-tab-pane>
        <el-tab-pane label="Users" name="Users">
          <keep-alive>
            <users v-if="activeActivity=='Users'" :can-add-new="false" />
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
import InstantBalances from './InstantBalances';
import Products from '@/app/stock/item/ManageItem';
import Fleets from './fleets';
import Users from './users/List';
const necessaryParams = new Resource('fetch-necessary-params');
export default {
  name: 'DownloadReports',
  components: {
    Inbounds, Outbounds, InstantBalances, Products, Fleets, Users,
  },
  data() {
    return {
      activeActivity: 'InstantBalances',
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
