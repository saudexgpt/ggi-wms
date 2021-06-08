<template>
  <div class="app-container">
    <div v-if="page.option==='list'">
      <router-link v-if="checkPermission(['generate waybill']) && canGenerateNewWaybill" :to="{name:'GenerateWaybill'}" class="btn btn-default"> Generate New Waybill</router-link>

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
        <el-row :gutter="20">
          <el-col :xs="24" :sm="12" :md="12">
            <el-input
              v-model="form.keyword"
              placeholder="Search"
              style="width: 200px"
              class="filter-item"
              @input="handleFilter"
            />
          </el-col>
        </el-row>
        <v-client-table v-model="waybills" :columns="columns" :options="options">
          <div slot="invoices" slot-scope="props">
            <div v-if="props.row.invoices.length > 0">
              <span v-for="(invoice, invoice_index) in props.row.invoices" :key="invoice_index">
                {{ invoice.invoice_number }},
              </span>
            </div>
          </div>
          <div slot="dispatchers" slot-scope="{row}">
            <div v-if="row.dispatcher">
              <span v-for="(vehicle_driver, index) in row.dispatcher.vehicle.vehicle_drivers" :key="index">
                {{ (vehicle_driver.driver) ? vehicle_driver.driver.user.name : '' }}<br>
              </span>
            </div>
            <div v-else>
              Not assigned
            </div>
          </div>

          <div slot="trip_no" slot-scope="props">
            {{ (props.row.trips.length > 0) ? props.row.trips[0].trip_no : '' }}
          </div>
          <div slot="created_at" slot-scope="props">
            {{ moment(props.row.created_at).format('MMMM Do YYYY, h:mm:ss a') }}
          </div>
          <div slot="updated_at" slot-scope="props">
            {{ moment(props.row.updated_at).format('MMMM Do YYYY, h:mm:ss a') }}
          </div>
          <div slot="action" slot-scope="props">
            <a class="btn btn-default" @click="waybill=props.row; page.option='waybill_details'"><i class="el-icon-tickets" /></a>
            <a v-if="checkPermission(['generate waybill']) && props.row.confirmed_by===null" class="btn btn-warning" @click="waybill=props.row; page.option='edit_waybill'"><i class="el-icon-edit" /></a>
            <a v-if="props.row.dispatch_products.length < 1 && checkPermission(['delete pending waybill'])" class="btn btn-danger" @click="deleteWaybill(props.index, props.row)"><i class="el-icon-delete" /></a>

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

      </div>
      <el-row :gutter="20">
        <pagination
          v-show="total > 0"
          :total="total"
          :page.sync="form.page"
          :limit.sync="form.limit"
          @pagination="getWaybills"
        />
      </el-row>
    </div>
    <div v-if="page.option !=='list'">
      <a class="btn btn-danger no-print" @click="page.option='list'">Go Back</a>
      <waybill-details v-if="page.option==='waybill_details'" :waybill="waybill" :page="page" :company-name="params.company_name" :company-contact="params.company_contact" :currency="currency" />

      <edit-waybill v-if="page.option==='edit_waybill'" :waybill="waybill" :page="page" :params="params" :currency="currency" @update="updateTable" />
    </div>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import moment from 'moment';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';
import { parseTime } from '@/utils';
import Resource from '@/api/resource';
import WaybillDetails from './partials/WaybillDetails';
import EditWaybill from './partials/EditWaybill';
const necessaryParams = new Resource('fetch-necessary-params');
const fetchWaybills = new Resource('invoice/waybill');
const deleteWaybill = new Resource('invoice/waybill/delete');
export default {
  // name: 'Waybills',
  components: { WaybillDetails, EditWaybill, Pagination },
  props: {
    canGenerateNewWaybill: {
      type: Boolean,
      default: () => (true),
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
      columns: ['action', 'waybill_no', 'invoices', 'dispatchers', 'trip_no', 'created_at', 'status', 'updated_at'],

      options: {
        headings: {
          waybill_no: 'Waybill Number',
          created_at: 'Date Generated',
          updated_at: 'Status Date',
          status: 'Waybill Status',
          dispatchers: 'Dispatchers',
          trip_no: 'Trip No.',

          // id: 'S/N',
        },
        pagination: {
          dropdown: true,
          chunk: 10,
        },
        perPage: 10,
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['created_at', 'updated_at'],
        filterable: false,
        // filterable: ['invoice.invoice_number', 'invoices', 'waybill_no', 'trip_no', 'created_at', 'updated_at'],
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
        page: 1,
        limit: 10,
        keyword: '',
      },
      total: 0,
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
    handleFilter() {
      this.form.page = 1;
      this.getWaybills();
    },
    getWaybills() {
      const app = this;
      const loader = fetchWaybills.loaderShow();
      const { limit, page } = app.form;
      app.options.perPage = limit;
      const param = app.form;
      param.warehouse_id = app.warehouses[param.warehouse_index].id;
      var extra_tableTitle = '';
      if (app.form.from !== '' && app.form.to !== '') {
        extra_tableTitle = ' from ' + app.form.from + ' to ' + app.form.to;
      }
      app.tableTitle = param.status.toUpperCase() + ' Waybills in ' + app.warehouses[param.warehouse_index].name + extra_tableTitle;
      fetchWaybills.list(param)
        .then(response => {
          // app.waybills = response.waybills;
          app.waybills = response.waybills.data;
          app.waybills.forEach((element, index) => {
            element['index'] = (page - 1) * limit + index + 1;
          });
          app.total = response.waybills.total;
          loader.hide();
        })
        .catch(error => {
          loader.hide();
          console.log(error.message);
        });
    },
    updateTable(updated_row) {
      const app = this;
      app.getWaybills();
      app.page.option = 'list';
    },
    deleteWaybill(index, waybill) {
      const app = this;
      this.$confirm(
        'You are about to delete waybill no. ' + waybill.waybill_no + '. This cannot be undone. Click YES to confirm.',
        'Warning',
        {
          confirmButtonText: 'YES',
          cancelButtonText: 'Cancel',
          type: 'warning',
        },
      )
        .then(() => {
          const loader = deleteWaybill.loaderShow();
          deleteWaybill
            .destroy(waybill.id)
            .then((response) => {
              this.$message({
                message: 'Waybill deleted successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              app.waybills.splice(index - 1, 1);
              loader.hide();
            })
            .catch((error) => {
              loader.hide();
              console.log(error.message);
              this.disabled = false;
            })
            .finally(() => {
              loader.hide();
              this.creatingWaybill = false;
              this.disabled = false;
            });
        });
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const multiHeader = [[this.tableTitle, '', '', '', '', '', '', '', '']];
        const tHeader = ['WAYBILL NUMBER', 'INVOICES', 'DISPATCHERS', 'TRIP NO.', 'DATE GENERATED', 'WAYBILL STATUS', 'STATUS DATE'];
        const filterVal = ['waybill_no', 'invoices', 'dispatchers', 'trip_no', 'created_at', 'status', 'updated_at'];
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
        if (j === 'trip_no') {
          if (v['trips'].length > 0) {
            return v['trips'][0].trip_no;
          }
          return '-';
        }
        if (j === 'dispatchers') {
          if (v['dispatcher']) {
            var drivers = '';
            var vehicle_drivers = v['dispatcher']['vehicle']['vehicle_drivers'];

            vehicle_drivers.forEach(element => {
              if (element.driver) {
                var name = element.driver.user.name;
                drivers += name + ', ';
              } else {
                return '-';
              }
            });
            return drivers;
          } else {
            return '-';
          }
        }
        if (j === 'invoices') {
          var invoices = v['invoices'];
          var numbers = '';
          invoices.forEach(element => {
            var invoice_number = element.invoice_number;
            numbers += invoice_number + ', ';
          });
          return numbers;
        }
        return v[j];
      }));
    },

  },
};
</script>
