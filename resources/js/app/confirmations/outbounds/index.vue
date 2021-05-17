<template>
  <div class="">
    <el-row :gutter="10">
      <el-col :xs="24" :sm="8" :md="8">
        <label for="">Select Warehouse</label>
        <el-select v-model="form.warehouse_index" placeholder="Select Warehouse" class="span" filterable>
          <el-option v-for="(warehouse, index) in warehouses" :key="index" :value="index" :label="warehouse.name" />

        </el-select>

      </el-col>
      <el-col :xs="24" :sm="10" :md="10">
        <br>
        <el-popover
          placement="right"
          trigger="click"
        >
          <date-range-picker :from="$route.query.from" :to="$route.query.to" :panel="panel" :panels="panels" :submit-title="submitTitle" :future="future" @update="setDateRange" />
          <el-button id="pick_outbound_date" slot="reference" type="success">
            <i class="el-icon-date" /> Pick Date Range
          </el-button>
        </el-popover>
      </el-col>

    </el-row>
    <br>
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
        <v-client-table v-model="invoice_items" :columns="columns" :options="options">
          <div slot="child_row" slot-scope="props">
            <aside>
              <legend>Delivery Details for Invoice No.: {{ props.row.invoice.invoice_number }}</legend>
              <v-client-table v-model="props.row.waybill_items" :columns="['waybill_no', 'quantity', 'supply_status', 'dispatchers', 'supply_date']">
                <div slot="waybill_no" slot-scope="{row}">
                  {{ row.waybill.waybill_no }}

                </div>
                <div slot="quantity" slot-scope="{row}">
                  {{ row.quantity }} {{ row.type }}

                </div>
                <div slot="supply_status" slot-scope="{row}">
                  {{ row.waybill.status }}

                </div>
                <div slot="dispatchers" slot-scope="{row}">
                  <div v-if="row.waybill.dispatcher">
                    <span v-for="(vehicle_driver, index) in row.waybill.dispatcher.vehicle.vehicle_drivers" :key="index">
                      {{ vehicle_driver.driver.user.name }}<br>
                    </span>
                  </div>
                  <div v-else>
                    Not assigned
                  </div>

                </div>
                <div slot="supply_date" slot-scope="{row}">
                  {{ moment(row.updated_at).format('MMMM Do YYYY') }}
                </div>
              </v-client-table>
            </aside>

          </div>
          <div slot="amount" slot-scope="props">
            {{ currency + Number(props.row.invoice.amount).toLocaleString() }}
          </div>
          <div slot="quantity" slot-scope="props">
            {{ props.row.quantity + ' ' + props.row.type }}
          </div>
          <div slot="quantity_supplied" slot-scope="props">
            {{ props.row.quantity_supplied + ' ' + props.row.type }}
          </div>
          <div slot="balance" slot-scope="props">
            {{ props.row.quantity - props.row.quantity_supplied + ' ' + props.row.type }}
          </div>
          <div slot="invoice.invoice_date" slot-scope="props">
            {{ moment(props.row.invoice.invoice_date).format('MMMM Do YYYY') }}
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
const outboundReport = new Resource('reports/tabular/outbounds');
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
      invoice_items: [],
      invoice_statuses: [],
      currency: '',
      columns: ['invoice.invoice_number', 'invoice.customer.user.name', 'item.name', 'amount', 'quantity', 'quantity_supplied', 'balance', 'invoice.invoice_date', 'invoice.status'],

      options: {
        headings: {
          'invoice.customer.user.name': 'Customer',
          'invoice.invoice_number': 'Invoice',
          'item.name': 'Product',
          'quantity_supplied': 'Supplied',
          'invoice.invoice_date': 'Invoice Date',
          'invoice.status': 'Status',

          // id: 'S/N',
        },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['invoice.invoice_number', 'invoice.customer.user.name', 'invoice.invoice_date', 'invoice.status'],
        filterable: ['invoice.invoice_number', 'invoice.customer.user.name', 'invoice.invoice_date', 'invoice.status'],
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
      document.getElementById('pick_outbound_date').click();
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
      const loader = outboundReport.loaderShow();

      const param = app.form;
      param.warehouse_id = app.warehouses[param.warehouse_index].id;
      outboundReport.list(param)
        .then(response => {
          app.invoice_items = response.invoice_items;
          app.table_title = 'Outbounds  in ' + app.warehouses[param.warehouse_index].name + ' from: ' + app.form.from + ' to: ' + app.form.to;
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
        const tHeader = ['Invoice', 'Customer', 'Product', 'Amount', 'Quantity', 'Supplied', 'Balance', 'Invoice Date', 'Status'];
        const filterVal = this.columns;
        const list = this.invoice_items;
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
