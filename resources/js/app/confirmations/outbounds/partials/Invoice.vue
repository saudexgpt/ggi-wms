<template>
  <div class="">
    <el-row :gutter="10">
      <el-col :xs="24" :sm="8" :md="8">
        <label for="">Select Warehouse</label>
        <el-select v-model="form.warehouse_index" placeholder="Select Warehouse" class="span" filterable @input="getInvoices">
          <el-option v-for="(warehouse, index) in warehouses" :key="index" :value="index" :label="warehouse.name" />

        </el-select>

      </el-col>
      <el-col :xs="24" :sm="6" :md="6">
        <label for="">Filter by: </label>
        <el-select v-model="form.status" placeholder="Select Status" class="span" @input="getInvoices">
          <el-option v-for="(status, index) in invoice_statuses" :key="index" :value="status.code" :label="status.name" />

        </el-select>

      </el-col>
      <el-col :xs="24" :sm="10" :md="10">
        <br>
        <a @click="showCalendar();"><label align="center" class="bg-gray control-label" style="padding: 10pt; cursor: pointer;">Click to Search by date</label></a>
        <div v-if="show_calendar" style="overflow: auto">
          <date-range-picker :from="$route.query.from" :to="$route.query.to" :panel="panel" :panels="panels" :submit-title="submitTitle" :future="future" @update="setDateRange" />
        </div>

      </el-col>

    </el-row>
    <div class="box">
      <div class="box-header">
        <h4 class="box-title">{{ table_title }}</h4>
      </div>
      <div class="box-body">
        <div>
          <label class="radio-label" style="padding-left:0;">Filename: </label>
          <el-input v-model="filename" :placeholder="$t('excel.placeholder')" style="width:340px;" prefix-icon="el-icon-document" />
          <el-button :loading="downloadLoading" style="margin:0 0 20px 20px;" type="primary" icon="document" @click="handleDownload">
            Export Excel
          </el-button>
        </div>
        <v-client-table v-model="invoices" :columns="columns" :options="options">
          <div slot="amount" slot-scope="props">
            {{ currency + Number(props.row.amount).toLocaleString() }}
          </div>
          <div slot="invoice_date" slot-scope="props">
            {{ moment(props.row.invoice_date).format('MMMM Do YYYY') }}
          </div>
          <div slot="action" slot-scope="props">
            <a class="btn btn-default" @click="invoice=props.row; page.option='invoice_details'"><i class="el-icon-tickets" /></a>
          </div>

        </v-client-table>

      </div>

    </div>
  </div>
</template>
<script>
import moment from 'moment';
import { parseTime } from '@/utils';
import Resource from '@/api/resource';
const fetchInvoices = new Resource('reports/tabular/outbounds');
// const deleteItemInStock = new Resource('stock/items-in-stock/delete');
export default {
  props: {
    params: {
      type: Object,
      default: () => ([]),
    },
  },
  data() {
    return {
      activeTab: 'Invoice',
      warehouses: [],
      invoices: [],
      invoice_statuses: [],
      currency: '',
      columns: ['invoice_number', 'customer.user.name', 'amount', 'invoice_date', 'status'],

      options: {
        headings: {
          'customer.user.name': 'Customer',
          invoice_number: 'Invoice Number',
          amount: 'Amount',
          invoice_date: 'Date',
          status: 'Status',

          // id: 'S/N',
        },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['invoice_number', 'customer.user.name', 'invoice_date', 'status'],
        filterable: ['invoice_number', 'customer.user.name', 'invoice_date', 'status'],
      },
      page: {
        option: 'list',
      },
      form: {
        warehouse_index: '',
        warehouse_id: '',
        from: '',
        to: '',
        panel: '',
        status: 'pending',
      },
      submitTitle: 'Fetch Report',
      panel: 'month',
      future: false,
      panels: ['range', 'week', 'month', 'quarter', 'year'],
      show_calendar: false,
      table_title: '',
      in_warehouse: '',
      invoice: {},
      selected_row_index: '',
      downloadLoading: false,
      filename: 'Invoices',

    };
  },

  mounted() {
    this.fetchNecessaryParams();
  },
  beforeDestroy() {

  },
  methods: {
    moment,
    showCalendar(){
      this.show_calendar = !this.show_calendar;
    },
    fetchNecessaryParams() {
      const app = this;
      app.warehouses = app.params.warehouses;
      app.form.warehouse_id = app.warehouses[0].id;
      app.invoice_statuses = app.params.invoice_statuses;
      app.currency = app.params.currency;
    },
    format(date) {
      var month = date.toLocaleString('en-US', { month: 'short' });
      return month + ' ' + date.getDate() + ', ' + date.getFullYear();
    },
    setDateRange(values){
      const app = this;
      app.show_calendar = false;
      let panel = app.panel;
      let from = app.week_start;
      let to = app.week_end;
      if (values !== '') {
        to = this.format(new Date(values.to));
        from = this.format(new Date(values.from));
        panel = values.panel;
      }
      app.form.from = from;
      app.form.to = to;
      app.form.panel = panel;
      app.getInvoices();
    },
    getInvoices() {
      const app = this;
      const loader = fetchInvoices.loaderShow();

      const param = app.form;
      param.warehouse_id = app.warehouses[param.warehouse_index].id;
      fetchInvoices.list(param)
        .then(response => {
          app.invoices = response.invoices;
          app.table_title = app.form.status.toUpperCase() + ' Invoices  in ' + app.warehouses[param.warehouse_index].name + ' from: ' + app.form.from + ' to: ' + app.form.to;
          loader.hide();
        })
        .catch(error => {
          loader.hide();
          console.log(error.message);
        });
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const multiHeader = [[this.tableTitle, '', '', '', '', '', '', '', '', '']];
        const tHeader = ['Invoice Number', 'Customer', 'Amount', 'Date', 'Status'];
        const filterVal = this.columns;
        const list = this.invoices;
        const data = this.formatJson(filterVal, list);
        excel.export_json_to_excel({
          multiHeader,
          header: tHeader,
          data,
          filename: this.filename,
          autoWidth: true,
          bookType: 'csv',
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => {
        if (j === 'invoice_date') {
          return parseTime(v[j]);
        } else {
          if (j === 'customer.user.name') {
            return v['customer']['user']['name'];
          }
          return v[j];
        }
      }));
    },
  },
};
</script>
