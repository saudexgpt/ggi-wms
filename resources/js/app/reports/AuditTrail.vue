<template>
  <div class="app-container">
    <el-row :gutter="20">
      <el-col :xs="24" :sm="24" :md="24">
        <div class="block">
          <legend>Audit Trail</legend>
          <el-col :xs="24" :sm="10" :md="10">
            <br>
            <el-popover
              placement="right"
              trigger="click"
            >
              <date-range-picker :from="$route.query.from" :to="$route.query.to" :panel="panel" :panels="panels" :submit-title="submitTitle" :future="future" @update="setDateRange" />
              <el-button id="pick_date" slot="reference" type="success">
                <i class="el-icon-date" /> Pick Date Range
              </el-button>
            </el-popover>
            <br><br>
          </el-col>
          <el-table
            :key="tableKey"
            v-loading="listLoading"
            :data="activity_logs"
            border
            fit
            highlight-current-row
            style="width: 100%;"
          >
            <el-table-column label="Action">
              <template slot-scope="{row}">
                <span>{{ row.data.title }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Description">
              <template slot-scope="{row}">
                <span>{{ row.data.description }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Date">
              <template slot-scope="{row}">
                <span>{{ moment(row.created_at).fromNow() }}</span>
              </template>
            </el-table-column>
          </el-table>
          <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="fetchAuditTrail" />
        </div>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import moment from 'moment';
import Pagination from '@/components/Pagination'; // secondary package based on el-pagination
import Resource from '@/api/resource';
const auditTrailResource = new Resource('reports/audit-trails');
export default {
  components: { Pagination },
  data() {
    return {
      tableKey: 0,
      activity_logs: [],
      total: 0,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 20,
        importance: undefined,
        title: undefined,
        type: undefined,
        sort: '+id',
        from: '',
        to: '',
        panel: '',
      },
      submitTitle: 'Fetch Audit Trail',
      panel: 'week',
      future: false,
      panels: ['range', 'week', 'month', 'quarter', 'year'],
      show_calendar: false,
    };
  },
  watch: {
    '$route': 'getUser',
  },
  created() {
    this.fetchAuditTrail();
  },
  methods: {
    moment,
    fetchAuditTrail() {
      const app = this;
      app.listLoading = true;
      auditTrailResource.list(app.listQuery)
        .then(response => {
          app.activity_logs = response.activity_logs.data;
          app.total = response.activity_logs.total;
          this.listLoading = false;
        });
    },
    format(date) {
      var month = date.toLocaleString('en-US', { month: 'short' });
      return month + ' ' + date.getDate() + ', ' + date.getFullYear();
    },
    setDateRange(values){
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
