<template>
  <div class="app-container">
    <div class="no-print">
      <el-row :gutter="10">
        <el-col :xs="24" :sm="8" :md="8">
          <label for="">Select Warehouse</label>
          <el-select v-model="form.warehouse_index" placeholder="Select Warehouse" class="span" filterable @input="getBinCard">
            <el-option v-for="(warehouse, index) in warehouses" :key="index" :value="index" :label="warehouse.name" />

          </el-select>

        </el-col>
        <el-col :xs="24" :sm="8" :md="8">
          <label for="">Select Product</label>
          <el-select v-model="form.item_index" placeholder="Select Product" class="span" filterable @input="getBinCard">
            <el-option v-for="(item, index) in items" :key="index" :value="index" :label="item.name" />

          </el-select>
        </el-col>
        <el-col :xs="24" :sm="8" :md="8">
          <br>
          <el-popover
            placement="right"
            trigger="click"
          >
            <date-range-picker :from="$route.query.from" :to="$route.query.to" :panel="panel" :panels="panels" :submit-title="submitTitle" :future="future" @update="setDateRange" />
            <el-button id="pick_bincard_date" slot="reference" type="success">
              <i class="el-icon-date" /> Pick Date Range
            </el-button>
          </el-popover>
        </el-col>

      </el-row>
    </div>

    <div v-if="bincards.length > 0">
      <div class="no-print">
        <div class="box-header">
          <span class="pull-right">
            <el-button round :loading="downloadLoading" type="primary" icon="document" @click="handleDownload">
              Export Excel
            </el-button>
            <a class="btn btn-warning" @click="printCard()">
              <i class="el-icon-printer" /> Print
            </a>
          </span>
        </div>
      </div>
      <div class="print-padded">
        <div class="col-xs-12 page-header" align="center">
          <img src="/svg/logo.png" alt="Company Logo" width="50">
          <span>
            <label>{{ params.company_name }}</label>
          </span>
          <h3><div class="label label-primary">BIN / STOCK CARD</div></h3>
          <label>{{ table_title }}</label>
        </div>
        <div class="invoice-info">
          Description of Article: <label>{{ product_name }}</label>
        </div>
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>DATE</th>
              <th>INVOICE NO.</th>
              <th>WAYBILL/DELIVERY</th>
              <th>IN</th>
              <th>SOLD OUT</th>
              <th>BALANCE (in {{ packaging }})</th>
              <th>PHYSICAL QUANTITY</th>
              <th>SIGN</th>
            </tr>
          </thead>
          <tbody>

            <tr v-for="(bincard, index) in bincards" :key="index">
              <td>{{ moment(bincard.date).format('ll') }}</td>
              <td>{{ bincard.invoice_no }}</td>
              <td>{{ bincard.waybill_grn }}</td>
              <td>{{ bincard.in }}</td>
              <td>{{ bincard.out }}</td>
              <td>{{ bincard.balance }}</td>
              <td>{{ bincard.physical_quantity }}</td>
              <td>{{ bincard.sign }}</td>
            </tr>
          </tbody>
        </table>

      </div>

    </div>
  </div>
</template>
<script>
import moment from 'moment';
import Resource from '@/api/resource';
const bincardReport = new Resource('reports/bin-card');
const necessaryParams = new Resource('fetch-necessary-params');
// const deleteItemInStock = new Resource('stock/items-in-stock/delete');
export default {
  name: 'BinCard',
  data() {
    return {
      running_balance: 0,
      brought_forward: 0,
      warehouses: [],
      items: [],
      bincards: [],
      currency: '',
      params: {
        company_name: '',
      },
      columns: ['date', 'invoice_no', 'waybill_grn', 'in', 'out', 'balance', 'physical_quantity', 'sign'],

      product_name: '',
      page: {
        option: 'list',
      },
      form: {
        warehouse_index: '',
        warehouse_id: '',
        item_index: '',
        item_id: '',
        from: '',
        to: '',
        panel: '',
      },
      submitTitle: 'Fetch Report',
      panel: 'month',
      future: false,
      panels: ['month'/*, 'quarter', 'year'*/],
      show_calendar: false,
      table_title: '',
      in_warehouse: '',
      selected_row_index: '',
      downloadLoading: false,
      running_total_array: [],
      packaging: '',
    };
  },

  mounted() {
    this.fetchNecessaryParams();
  },
  beforeDestroy() {

  },
  methods: {
    moment,
    printCard(){
      window.print();
    },
    showCalendar(){
      this.show_calendar = !this.show_calendar;
    },
    setRunningBal(quantity_transacted, type, index) {
      const app = this;
      // var running_balance = app.running_balance;
      if (type === 'in_bound') {
        app.running_balance += parseInt(quantity_transacted);
      } else {
        app.running_balance -= parseInt(quantity_transacted);
      }
      app.bincards[index].balance = app.running_balance;
    },
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list().then((response) => {
        const params = response.params;
        app.params = params;
        app.warehouses = params.warehouses;
        app.form.warehouse_id = params.warehouses[0].id;
        app.form.warehouse_index = 0;
        app.items = params.items;
        app.currency = params.currency;
      });
    },
    format(date) {
      var month = date.toLocaleString('en-US', { month: 'short' });
      return month + ' ' + date.getDate() + ', ' + date.getFullYear();
    },
    setDateRange(values){
      const app = this;
      document.getElementById('pick_bincard_date').click();
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
      app.getBinCard();
    },
    getBinCard() {
      const app = this;
      const loader = bincardReport.loaderShow();

      const param = app.form;
      param.warehouse_id = app.warehouses[param.warehouse_index].id;
      param.item_id = app.items[param.item_index].id;
      app.product_name = app.items[param.item_index].name;
      app.packaging = app.items[param.item_index].package_type;
      bincardReport.list(param)
        .then(response => {
          app.bincards = response.bincards;
          app.brought_forward = response.brought_forward;
          app.running_balance = app.brought_forward;
          app.form.from = response.date_from_formatted;
          app.form.to = response.date_to_formatted;
          app.table_title = app.warehouses[param.warehouse_index].name + ' FROM ' + app.moment(app.form.from).format('ll') + ' TO ' + app.moment(app.form.to).format('ll');
          loader.hide();
          app.setRunningTotalArray(app.bincards);
        })
        .catch(error => {
          loader.hide();
          console.log(error.message);
        });
    },
    setRunningTotalArray(bincards){
      const app = this;
      let running_balance = app.running_balance;
      for (let index = 0; index < bincards.length; index++) {
        const each_entry = bincards[index];
        if (each_entry.type === 'in_bound') {
          running_balance += each_entry.quantity_transacted;
        } else {
          running_balance -= each_entry.quantity_transacted;
        }
        each_entry.balance = running_balance;
      }
      app.bincards.unshift(
        {
          'type': 'out_bound',
          'date': app.moment(app.form.from).format('ll'),
          'invoice_no': '',
          'waybill_grn': '',
          'in': 'B/F',
          'out': '',
          'balance': app.brought_forward, // initially set to zero
          'packaging': '',
          'physical_quantity': '',
          'sign': '',
        },
      );
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const multiHeader = [['Bincard for ' + this.table_title, '', '', '', '', '', '', '', '']];
        const tHeader = ['DATE', 'INVOICE NO.', 'WAYBILL/DELIVERY', 'IN', 'SOLD OUT', 'BALANCE (in ' + this.packaging + ')', 'PHYSICAL QUANTITY', 'SIGN'];
        const filterVal = this.columns;
        const list = this.bincards;
        const data = this.formatJson(filterVal, list);
        excel.export_json_to_excel({
          multiHeader,
          header: tHeader,
          data,
          filename: 'Bincard for ' + this.table_title,
          autoWidth: true,
          bookType: 'xlsx',
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => {
        if (j === 'date') {
          return moment(v['date']).format('ll');
        }
        return v[j];
      }));
    },
  },
};
</script>
<style>
  @media print {
    .print-padded{
      margin-top: -100px;

    }
    .table > thead > tr > th,
    .table > tbody > tr > th,
    .table > tfoot > tr > th,
    .table > thead > tr > td,
    .table > tbody > tr > td,
    .table > tfoot > tr > td {
      font-size: 1.1rem !important;
    }
  }
</style>
