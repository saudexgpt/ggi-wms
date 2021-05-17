<template>
  <div class="app-container">
    <el-row :gutter="20">
      <el-col :xs="24" :sm="24" :md="24">
        <div class="block">
          <legend>Back Up</legend>
          <el-col :xs="24" :sm="10" :md="10">
            <div>
              <a :href="url">
                <i class="el-icon-download" />
                Click TO Download Database Backup for {{ date }}
              </a>
            </div>
          </el-col>
        </div>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import moment from 'moment';
import Resource from '@/api/resource';
const backUpResource = new Resource('reports/backups');
export default {
  data() {
    return {
      tableKey: 0,
      url: '',
      date: '',
    };
  },
  watch: {
    $route: 'getUser',
  },
  created() {
    this.fetchBackUps();
  },
  methods: {
    moment,
    fetchBackUps() {
      const app = this;
      app.listLoading = true;
      backUpResource.list().then((response) => {
        app.url = response.url;
        app.date = response.date;
        this.listLoading = false;
      });
    },
    format(date) {
      var month = date.toLocaleString('en-US', { month: 'short' });
      return month + ' ' + date.getDate() + ', ' + date.getFullYear();
    },
    setDateRange(values) {
      const app = this;
      document.getElementById('pick_date').click();
      let panel = app.panel;
      let from = app.week_start;
      let to = app.week_end;
      if (values !== '') {
        to = this.format(new Date(values.to));
        from = this.format(new Date(values.from));
        panel = values.panel;
      }
      app.listQuery.from = from;
      app.listQuery.to = to;
      app.listQuery.panel = panel;
      app.fetchAuditTrail();
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
  },
};
</script>
<style rel="stylesheet/scss" lang="scss">
table th {
  font-size: 1.9rem !important;
  font-weight: 400;
}
</style>
