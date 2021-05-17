<template>
  <div class="app-container">
    <div v-if="page.option==='list'">
      <router-link v-if="checkPermission(['manage transfer request']) && canGenerateNewWaybill" :to="{name:'GenerateTransferWaybill'}" class="btn btn-default"> Generate New Waybill</router-link>

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
          <el-popover
            placement="right"
            trigger="click"
          >
            <date-range-picker :from="$route.query.from" :to="$route.query.to" :panel="panel" :panels="panels" :submit-title="submitTitle" :future="future" @update="setDateRange" />
            <el-button id="pick_date" slot="reference" type="primary">
              <i class="el-icon-date" /> Pick Date Range
            </el-button>
          </el-popover>
        </el-col>

      </el-row>
      <br>
    </div>
    <div v-if="page.option==='list'" class="box">
      <div class="box-header">
        <h4 class="box-title">{{ tableTitle }}</h4>

      </div>
      <div class="box-body">
        <div>
          <label class="radio-label" style="padding-left:0;">Filename: </label>
          <el-input v-model="filename" :placeholder="$t('excel.placeholder')" style="width:340px;" prefix-icon="el-icon-document" />
          <el-button :loading="downloadLoading" style="margin:0 0 20px 20px;" type="primary" icon="document" @click="handleDownload">
            Export Excel
          </el-button>
        </div>
        <el-tabs v-model="activeActivity" type="border-card">
          <el-tab-pane label="Generated Waybills" name="Incoming">
            <v-client-table v-model="waybills" :columns="columns" :options="options">
              <div slot="request_warehouse" slot-scope="{row}">
                {{ row.request_warehouse.name }}
              </div>
              <div slot="supply_warehouse" slot-scope="{row}">
                {{ row.supply_warehouse.name }}
              </div>
              <div slot="transfer_requests" slot-scope="props">
                <div v-if="props.row.transfer_requests.length > 0">
                  <span v-for="(invoice, invoice_index) in props.row.transfer_requests" :key="invoice_index">
                    {{ invoice.request_number }},
                  </span>
                  {{ }}
                </div>
              </div>
              <div slot="created_at" slot-scope="props">
                {{ moment(props.row.created_at).format('MMMM Do YYYY, h:mm:ss a') }}
              </div>
              <div slot="action" slot-scope="props">
                <a class="btn btn-default" @click="waybill=props.row; page.option='waybill_details'"><i class="el-icon-tickets" /></a>

                <a v-if="props.row.status === 'waybill_generated'" class="btn btn-default" @click="waybill=props.row; waybill.index= props.index - 1; page.option='waybill_details'"><i class="el-icon-success" /></a>
                <!-- <el-dropdown class="avatar-container right-menu-item hover-effect" trigger="click">
              <div class="avatar-wrapper" style="color: brown">
                <label style="cursor:pointer"><i class="el-icon-more-outline" /></label>
              </div>
              <el-dropdown-menu slot="dropdown" style="padding: 10px;">
                <el-dropdown-item v-if="props.row.waybill_status === 'pending' && checkPermission(['approve waybill'])">
                  <a @click="approveWaybill(props.index, props.row);">Approve</a>
                </el-dropdown-item>
                <el-dropdown-item v-if="props.row.waybill_status === 'approved' && checkPermission(['approve waybill', 'deliver waybill'])" divided>
                  <a @click="deliverWaybill(props.index, props.row);">Delivered</a>
                </el-dropdown-item>
                <el-dropdown-item v-if="props.row.waybill_status === 'pending' && checkPermission(['cancel waybill'])" divided>
                  <a @click="cancelWaybill(props.index, props.row);">Cancel</a>
                </el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown> -->
              </div>

            </v-client-table>
          </el-tab-pane>
          <el-tab-pane label="My Request Waybills" name="Sent">
            <v-client-table v-model="my_request_waybills" :columns="columns" :options="options">
              <div slot="request_warehouse" slot-scope="{ row }">
                {{ row.request_warehouse.name }}
              </div>

              <div slot="supply_warehouse" slot-scope="{row}">
                {{ row.supply_warehouse.name }}
              </div>
              <div slot="transfer_requests" slot-scope="props">
                <div v-if="props.row.transfer_requests.length > 0">
                  <span v-for="(invoice, invoice_index) in props.row.transfer_requests" :key="invoice_index">
                    {{ invoice.request_number }},
                  </span>
                  {{ }}
                </div>
              </div>
              <div slot="created_at" slot-scope="props">
                {{ moment(props.row.created_at).format('MMMM Do YYYY, h:mm:ss a') }}
              </div>
              <div slot="action" slot-scope="props">
                <a class="btn btn-default" @click="waybill=props.row; page.option='waybill_details'"><i class="el-icon-tickets" /></a>

                <a v-if="props.row.status === 'waybill_generated'" class="btn btn-default" @click="waybill=props.row; waybill.index= props.index - 1; page.option='waybill_details'"><i class="el-icon-success" /></a>
                <!-- <el-dropdown class="avatar-container right-menu-item hover-effect" trigger="click">
              <div class="avatar-wrapper" style="color: brown">
                <label style="cursor:pointer"><i class="el-icon-more-outline" /></label>
              </div>
              <el-dropdown-menu slot="dropdown" style="padding: 10px;">
                <el-dropdown-item v-if="props.row.waybill_status === 'pending' && checkPermission(['approve waybill'])">
                  <a @click="approveWaybill(props.index, props.row);">Approve</a>
                </el-dropdown-item>
                <el-dropdown-item v-if="props.row.waybill_status === 'approved' && checkPermission(['approve waybill', 'deliver waybill'])" divided>
                  <a @click="deliverWaybill(props.index, props.row);">Delivered</a>
                </el-dropdown-item>
                <el-dropdown-item v-if="props.row.waybill_status === 'pending' && checkPermission(['cancel waybill'])" divided>
                  <a @click="cancelWaybill(props.index, props.row);">Cancel</a>
                </el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown> -->
              </div>

            </v-client-table>
          </el-tab-pane>
        </el-tabs>

      </div>
    </div>
    <div v-if="page.option==='waybill_details'">
      <a class="btn btn-danger no-print" @click="page.option='list'">Go Back</a>
      <waybill-details :warehouse-id="form.warehouse_id" :waybill="waybill" :page="page" :company-name="params.company_name" :company-contact="params.company_contact" :currency="currency" :drivers="drivers" />
    </div>
  </div>
</template>
<script>
import moment from 'moment';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';
import { parseTime } from '@/utils';
import Resource from '@/api/resource';
import WaybillDetails from './partials/WaybillDetails';
const necessaryParams = new Resource('fetch-necessary-params');
const fetchWaybills = new Resource('transfers/waybill');
// const deleteItemInStock = new Resource('stock/items-in-stock/delete');
export default {
  components: { WaybillDetails },
  props: {
    canGenerateNewWaybill: {
      type: Boolean,
      default: () => (true),
    },
  },
  data() {
    return {
      activeActivity: 'Incoming',
      warehouses: [],
      waybills: [],
      my_request_waybills: [],
      waybill_statuses: [
        { code: 'pending', name: 'Pending' },
        { code: 'in transit', name: 'In Transit' },
        { code: 'delivered', name: 'Delivered' },
      ],
      currency: '',
      columns: ['action', 'request_warehouse', 'supply_warehouse', 'transfer_request_waybill_no', 'transfer_requests', 'created_at', 'status'],

      options: {
        headings: {
          request_warehouse: 'Request By',
          supply_warehouse: 'Supplied By',
          transfer_request_waybill_no: 'Waybill Number',
          transfer_requests: 'Invoices',
          created_at: 'Request Date',
          status: 'Waybill Status',

          // id: 'S/N',
        },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['created_at'],
        filterable: ['transfer_request.request_number', 'transfer_requests', 'transfer_request_waybill_no', 'created_at'],
      },
      page: {
        option: 'list',
      },
      params: {},
      form: {
        warehouse_index: '',
        warehouse_id: '',
        from: '',
        to: '',
        panel: '',
        status: 'pending',
      },
      tableTitle: '',
      waybill: {},
      selected_row_index: '',
      submitTitle: 'Fetch',
      panel: 'month',
      future: false,
      panels: ['range', 'week', 'month', 'quarter', 'year'],
      show_calendar: false,
      downloadLoading: false,
      filename: 'Waybills',
      drivers: [],

    };
  },

  created() {
    this.fetchNecessaryParams();
  },
  beforeDestroy() {

  },
  methods: {
    moment,
    checkPermission,
    checkRole,
    showCalendar(){
      this.show_calendar = !this.show_calendar;
    },
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list()
        .then(response => {
          app.params = response.params;
          app.warehouses = response.params.warehouses;
          app.currency = response.params.currency;
          if (app.warehouses.length > 0) {
            app.form.warehouse_id = app.warehouses[0];
            app.form.warehouse_index = 0;
            app.getWaybills();
          }
        });
    },
    format(date) {
      var month = date.toLocaleString('en-US', { month: 'short' });
      return month + ' ' + date.getDate() + ', ' + date.getFullYear();
    },
    setDateRange(values){
      const app = this;
      document.getElementById('pick_date').click();
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
      var extra_tableTitle = '';
      if (app.form.from !== '' && app.form.to !== '') {
        extra_tableTitle = ' from ' + app.form.from + ' to ' + app.form.to;
      }
      app.tableTitle = param.status.toUpperCase() + ' Waybills in ' + app.warehouses[param.warehouse_index].name + extra_tableTitle;
      fetchWaybills.list(param)
        .then(response => {
          app.waybills = response.waybills;
          app.my_request_waybills = response.my_request_waybills;
          app.drivers = response.drivers;
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
        const multiHeader = [[this.tableTitle, '', '', '', '', '', '']];
        const tHeader = ['WAYBILL NUMBER', 'DISPATCHERS', 'DATE GENERATED', 'WAYBILL STATUS', 'STATUS DATE'];
        const filterVal = ['waybill_no', 'dispatchers', 'created_at', 'status', 'updated_at'];
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
