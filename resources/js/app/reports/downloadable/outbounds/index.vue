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
          <!-- <div slot="child_row" slot-scope="props">
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
                  {{ moment(row.updated_at).format('MMM D, YYYY') }}
                </div>
              </v-client-table>
            </aside>

          </div> -->
          <!-- <div slot="batches" slot-scope="props">
            <div v-for="(invoice_batch, batch_index) in props.row.batches" :key="batch_index">
              <div v-if="invoice_batch.item_stock_batch">
                {{ invoice_batch.item_stock_batch.batch_no }}
              </div>, <br>
            </div>
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
            {{ moment(props.row.invoice.invoice_date).format('MMM D, YYYY') }}
          </div>
          <div slot="created_at" slot-scope="props">
            {{ moment(props.row.created_at).format('MMM D, YYYY') }}
          </div>
          <div slot="updated_at" slot-scope="props">
            {{ (props.row.delivery_status === 'delivered') ? moment(props.row.updated_at).format('MMM D, YYYY') : 'Pending' }}
          </div> -->
          <div slot="amount" slot-scope="props">
            {{ currency + Number(props.row.amount).toLocaleString() }}
          </div>
          <div slot="date" slot-scope="props">
            {{ moment(props.row.date).format('MMM D, YYYY') }}
          </div>
          <div slot="transit_date" slot-scope="props">
            {{ (props.row.transit_date != 'Pending') ? moment(props.row.transit_date).format('MMM D, YYYY') : 'Pending' }}
          </div>
          <div slot="delivery_date" slot-scope="props">
            {{ (props.row.status === 'delivered') ? moment(props.row.delivery_date).format('MMM D, YYYY') : 'Pending' }}
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
      columns: ['dispatcher', 'invoice_no', 'customer', 'product', 'batch_nos', 'amount', 'quantity', 'supplied', 'balance', 'date', 'status', 'transit_date', 'delivery_date'],

      options: {
        headings: {
          // 'invoice.customer.user.name': 'Customer',
          // 'invoice.invoice_number': 'Invoice',
          // 'batches': 'Batch Nos.',
          // 'item.name': 'Product',
          // 'quantity_supplied': 'Supplied',
          // 'invoice.invoice_date': 'Invoice Date',
          // 'created_at': 'Date Saved',
          // 'delivery_status': 'Status',
          // 'updated_at': 'Delivery Date',

          // id: 'S/N',
        },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['invoice_no', 'customer', 'date', 'created_at', 'status', 'transit_date', 'delivery_date'],
        filterable: ['invoice_no', 'customer', 'date', 'created_at', 'status', 'transit_date', 'delivery_date'],
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
      filename: 'Outbounds',

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
      app.form.warehouse_index = 0;
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
          app.invoice_items = response.outbounds;
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
        const multiHeader = [[this.tableTitle, '', '', '', '', '', '', '', '', '', '', '', '', '']];
        const tHeader = ['Dispatcher', 'Invoice No.', 'Customer', 'Product', 'Batch No.', 'Amount', 'Quantity', 'Supplied', 'Balance', 'Date', 'Status', 'Transit Date', 'Delivery Date'];
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
        if (j === 'date') {
          return parseTime(v['date']);
        }
        if (j === 'transit_date') {
          if (v['transit_date'] !== 'Pending') {
            return parseTime(v['transit_date']);
          } else {
            return 'Pending';
          }
        }
        if (j === 'delivery_date') {
          if (v['status'] === 'delivered') {
            return parseTime(v['delivery_date']);
          } else {
            return 'Pending';
          }
        }
        // if (j === 'updated_at') {
        //   if (v['delivery_status'] === 'delivered') {
        //     return parseTime(v['updated_at']);
        //   } else {
        //     return 'Pending';
        //   }
        // }
        // if (j === 'invoice.customer.user.name') {
        //   return v['invoice']['customer']['user']['name'];
        // }
        // if (j === 'amount') {
        //   return v['invoice']['amount'];
        // }
        // if (j === 'invoice.invoice_number') {
        //   return v['invoice']['invoice_number'];
        // }
        // if (j === 'item.name') {
        //   return v['item']['name'];
        // }
        // if (j === 'balance') {
        //   var balance = v['quantity'] - v['quantity_supplied'] + ' ' + v['type'];
        //   return balance;
        // }
        // if (j === 'batches') {
        //   var batches = v['batches'];
        //   var batch_no = '';
        //   if (batches.length > 0) {
        //     batches.forEach(invoice_batch => {
        //       var num = invoice_batch.item_stock_batch.batch_no;
        //       batch_no += num + ', ';
        //     });
        //   }
        //   return batch_no;
        // }
        return v[j];
      }));
    },
  },
};
</script>
