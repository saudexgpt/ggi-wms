<template>
  <div class="">
    <el-row :gutter="10">
      <el-col :xs="24" :sm="8" :md="8">
        <label for="">Select Warehouse</label>
        <el-select v-model="form.warehouse_index" placeholder="Select Warehouse" class="span" filterable @input="getWaybills">
          <el-option v-for="(warehouse, index) in warehouses" :key="index" :value="index" :label="warehouse.name" />

        </el-select>

      </el-col>
      <el-col :xs="24" :sm="6" :md="6">
        <label for="">Filter by: </label>
        <el-select v-model="form.status" placeholder="Select Status" class="span" @input="getWaybills">
          <el-option v-for="(status, index) in waybill_statuses" :key="index" :value="status.code" :label="status.name" />

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
        <h4 class="box-title">{{ title }}</h4>

      </div>
      <div class="box-body">
        <div>
          <label class="radio-label" style="padding-left:0;">Filename: </label>
          <el-input v-model="filename" :placeholder="$t('excel.placeholder')" style="width:340px;" prefix-icon="el-icon-document" />
          <el-button :loading="downloadLoading" style="margin:0 0 20px 20px;" type="primary" icon="document" @click="handleDownload">
            Export Excel
          </el-button>
        </div>
        <v-client-table v-model="waybills" :columns="columns" :options="options">
          <div slot="dispatchers" slot-scope="{row}">
            <span v-for="(vehicle_driver, index) in row.dispatcher.vehicle.vehicle_drivers" :key="index">
              {{ vehicle_driver.driver.user.name }}<br>
            </span>
          </div>
          <div slot="created_at" slot-scope="props">
            {{ moment(props.row.created_at).format('MMMM Do YYYY, h:mm:ss a') }}
          </div>
          <div slot="updated_at" slot-scope="props">
            {{ moment(props.row.updated_at).format('MMMM Do YYYY, h:mm:ss a') }}
          </div>

        </v-client-table>

      </div>
    </div>
    <div v-if="page.option==='waybill_details'">
      <a class="btn btn-danger no-print" @click="page.option='list'">Go Back</a>
      <waybill-details :waybill="waybill" :page="page" :company-name="params.company_name" :company-contact="params.company_contact" :currency="currency" />
    </div>
  </div>
</template>
<script>
import moment from 'moment';
import { parseTime } from '@/utils';
import Resource from '@/api/resource';
import WaybillDetails from '@/app/invoice/partials/WaybillDetails';
const fetchWaybills = new Resource('invoice/waybill');
// const deleteItemInStock = new Resource('stock/items-in-stock/delete');
export default {
  components: { WaybillDetails },
  props: {
    params: {
      type: Object,
      default: () => ([]),
    },
  },
  data() {
    return {
      warehouses: [],
      waybills: [],
      waybill_statuses: [
        { code: 'pending', name: 'Pending' },
        { code: 'in transit', name: 'In Transit' },
        { code: 'delivered', name: 'Delivered' },
      ],
      currency: '',
      columns: ['waybill_no', 'status', 'dispatchers', 'created_at', 'updated_at', 'invoice.invoice_number', 'invoice.status'],

      options: {
        headings: {
          'invoice.invoice_number': 'Invoice Number',
          waybill_no: 'Waybill Number',
          created_at: 'Date Generated',
          updated_at: 'Status Date',
          'invoice.status': 'Invoice Status',
          status: 'Waybill Status',
          'dispatchers': 'Dispatchers',

          // id: 'S/N',
        },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['created_at', 'updated_at'],
        filterable: ['invoice.invoice_number', 'waybill_no', 'created_at', 'updated_at'],
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
      title: '',
      waybill: {},
      selected_row_index: '',
      submitTitle: 'Fetch Report',
      panel: 'month',
      future: false,
      panels: ['range', 'week', 'month', 'quarter', 'year'],
      show_calendar: false,
      table_title: '',
      downloadLoading: false,
      filename: 'Waybills',

    };
  },

  created() {
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
      app.getWaybills();
    },
    getWaybills() {
      const app = this;
      const loader = fetchWaybills.loaderShow();

      const param = app.form;
      param.warehouse_id = app.warehouses[param.warehouse_index].id;
      app.title = param.status.toUpperCase() + ' Waybills in ' + app.warehouses[param.warehouse_index].name + ' from ' + app.form.from + ' to ' + app.form.to;
      fetchWaybills.list(param)
        .then(response => {
          app.waybills = response.waybills;
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
        const tHeader = ['Waybill Number', 'Waybill Status', 'Dispatchers', 'Date Generated', 'Status Date', 'Invoice Number', 'Invoice Status'];
        const filterVal = this.columns;
        const list = this.waybills;
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
        if (j === 'created_at') {
          return parseTime(v[j]);
        }
        if (j === 'updated_at') {
          return parseTime(v[j]);
        }
        if (j === 'invoice.invoice_number') {
          return v['invoice']['invoice_number'];
        }
        if (j === 'invoice.status') {
          return v['invoice']['status'];
        }
        if (j === 'dispatchers') {
          var vehicle_drivers = v['dispatcher']['vehicle']['vehicle_drivers'];
          var drivers = '';
          vehicle_drivers.forEach(element => {
            var name = element.driver.user.name;
            drivers += name + ', ';
          });
          return drivers;
        }
        return v[j];
      }));
    },

  },
};
</script>
