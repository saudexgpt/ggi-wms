<template>
  <div class="dashboard-editor-container">
    <panel-group v-if="data_summary" :data-summary="data_summary" />

    <el-row>
      <graphical-report :use-carousel="true" />
    </el-row>

    <!-- <el-row :gutter="10">
      <el-col :xs="24" :sm="24" :lg="8">
        <div class="chart-wrapper">
          <raddar-chart />
        </div>
      </el-col>
      <el-col :xs="24" :sm="24" :lg="8">
        <div class="chart-wrapper">
          <pie-chart />
        </div>
      </el-col>
      <el-col :xs="24" :sm="24" :lg="8">
        <div class="chart-wrapper">
          <bar-chart />
        </div>
      </el-col>
    </el-row> -->

    <!-- <el-row :gutter="8">
      <el-col :xs="{span: 24}" :sm="{span: 24}" :md="{span: 24}" :lg="{span: 12}" :xl="{span: 12}" style="padding-right:8px;margin-bottom:30px;">
        <transaction-table />
      </el-col>
      <el-col :xs="{span: 24}" :sm="{span: 12}" :md="{span: 12}" :lg="{span: 6}" :xl="{span: 6}" style="margin-bottom:30px;">
        <todo-list />
      </el-col>
      <el-col :xs="{span: 24}" :sm="{span: 12}" :md="{span: 12}" :lg="{span: 6}" :xl="{span: 6}" style="margin-bottom:30px;">
        <box-card />
      </el-col>
    </el-row> -->
  </div>
</template>

<script>
import PanelGroup from './components/PanelGroup';
import GraphicalReport from '@/app/reports/graphical';
import Resource from '@/api/resource';
const adminDashboard = new Resource('dashboard/admin');

export default {
  name: 'AdminDashboard',
  components: {
    PanelGroup,
    GraphicalReport,
  },
  data() {
    return {
      data_summary: '',
      warehouses: [],
    };
  },
  mounted() {
    this.fetchDashboardDetails();
  },
  methods: {
    fetchDashboardDetails() {
      const app = this;
      adminDashboard.list()
        .then(response => {
          app.data_summary = response.data_summary;
          app.warehouses = response.warehouses;
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
