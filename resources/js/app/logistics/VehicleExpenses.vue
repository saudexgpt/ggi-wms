<template>
  <div class="app-container">
    <div v-if="page.option === 'list'">
      <a v-if="canAddNew" class="btn btn-info" @click="page.option = 'add_new'"> Add New Request</a>
      <el-row :gutter="10">
        <el-col :xs="24" :sm="8" :md="8">
          <label for="">Select Warehouse</label>
          <el-select v-model="form.warehouse_index" placeholder="Select Warehouse" class="span" filterable @input="fetchVehicleExpenses">
            <el-option v-for="(warehouse, index) in params.warehouses" :key="index" :value="index" :label="warehouse.name" />

          </el-select>

        </el-col>
        <el-col :xs="24" :sm="6" :md="6">
          <label for="">Search by Status</label>
          <el-select v-model="form.status" class="span" filterable @input="fetchVehicleExpenses">
            <el-option value="All" label="All" />
            <el-option v-for="(status, index) in statuses" :key="index" :value="status" :label="status" />

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
        <br><br><br><br>
      </el-row>
    </div>
    <div class="">
      <add-new v-if="page.option === 'add_new'" :params="params" :vehicle-expenses="vehicle_expenses" :page="page" />
      <div v-if="page.option === 'list'" class="box">
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
          <v-client-table v-model="vehicle_expenses" :columns="columns" :options="options">
            <div slot="child_row" slot-scope="props">
              <aside>
                <legend>Expenses details</legend>
                <v-client-table v-model="props.row.expense_details" :columns="['vehicle_part', 'service_type', 'amount']">
                  <div slot="amount" slot-scope="{row}">
                    {{ params.currency + parseFloat(row.amount).toLocaleString().toFixed(2) }}
                  </div>
                </v-client-table>
              </aside>

            </div>
            <div slot="amount" slot-scope="props">
              {{ params.currency + parseFloat(props.row.amount).toFixed(2).toLocaleString() }}
            </div>
            <div slot="service_charge" slot-scope="props">
              {{ params.currency + parseFloat(props.row.service_charge).toFixed(2).toLocaleString() }}
            </div>
            <div slot="grand_total" slot-scope="props">
              {{ params.currency + parseFloat(props.row.grand_total).toFixed(2).toLocaleString() }}
            </div>
            <div slot="expense_type" slot-scope="props">
              {{ props.row.expense_type }}<br>
              <small style="color: brown" v-html="props.row.details" />
            </div>
            <div slot="created_at" slot-scope="props">
              {{ moment(props.row.created_at).format('MMMM Do, YYYY') }}
            </div>
            <div slot="confirmer.name" slot-scope="{row}">
              <div :id="row.id">
                <div v-if="row.confirmed_by == null">
                  <a
                    v-if="checkPermission(['audit confirm actions'])"
                    title="Click to confirm"
                    class="btn btn-warning"
                    @click="confirmVehicleExpenses(row.id);"
                  >
                    <i class="fa fa-check" />
                  </a>
                </div>
                <div v-else>{{ row.confirmer.name }}</div>
              </div>
            </div>
            <div slot="status" slot-scope="props">
              <div v-if="checkPermission(['approve vehicle expenses']) && props.row.confirmed_by != null">
                <el-select v-model="props.row.status" @input="confirmApproval(props, props.row)">
                  <el-option v-for="(status, index) in statuses" :key="index" :value="status" :label="status" />
                </el-select>
              </div>
              <div v-else>
                {{ props.row.status }}
              </div>
            </div>
          </v-client-table>

        </div>

      </div>

    </div>
  </div>
</template>
<script>
import moment from 'moment';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';
import { parseTime } from '@/utils';

import AddNew from './partials/AddNewVehicleExpense';
import Resource from '@/api/resource';
// import Vue from 'vue';
const necessaryParams = new Resource('fetch-necessary-params');
const listVehicleExpenses = new Resource('logistics/vehicle-expenses');
const approveVehicleExpense = new Resource('logistics/vehicle-expenses/approve');
const confirmVehicleExpensesResource = new Resource('audit/confirm/vehicle-expenses');
export default {
  components: { AddNew },
  props: {
    canAddNew: {
      type: Boolean,
      default: () => (true),
    },
  },
  data() {
    return {
      vehicle_expenses: [],
      columns: ['vehicle.plate_no', 'expense_type', 'amount', 'service_charge', 'grand_total', 'created_at', 'confirmer.name', 'status'],

      options: {
        headings: {
          'confirmer.name': 'Confirmed By',
          'vehicle.plate_no': 'Vehicle',
          expense_type: 'Expense Type',
          amount: 'Amount',
          service_charge: 'Service Charge',
          grand_total: 'Total',
          created_at: 'Date',
          status: 'Status',
          // id: 'S/N',
        },
        // editableColumns: ['type', 'load_capacity', 'is_enabled'],
        sortable: ['vehicle.plate_no', 'status', 'created_at'],
        filterable: ['vehicle.plate_no', 'expense_type', 'status', 'created_at'],
      },
      params: {},
      page: {
        option: 'list',
      },
      form: {
        status: 'Pending',
        warehouse_id: '',
        warehouse_index: '',
        from: '',
        to: '',
        panel: '',
      },
      statuses: ['Pending', 'Declined', 'Approved'],
      submitTitle: 'Fetch',
      panel: 'month',
      future: false,
      panels: ['range', 'week', 'month', 'quarter', 'year'],
      show_calendar: false,
      downloadLoading: false,
      filename: 'Vehicle Expenses',
      tableTitle: 'Expenses on Vehicles',
    };
  },

  mounted() {
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
    confirmVehicleExpenses(vehicle_expense_id) {
      // const app = this;
      const form = { id: vehicle_expense_id };
      const message = 'Click okay to confirm action';
      if (confirm(message)) {
        confirmVehicleExpensesResource
          .update(vehicle_expense_id, form)
          .then((response) => {
            if (response.confirmed === 'success') {
              document.getElementById(vehicle_expense_id).innerHTML =
                response.confirmed_by;
            }
          });
      }
    },
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list()
        .then(response => {
          app.params = response.params;
          if (app.params.warehouses.length > 0) {
            app.form.warehouse_id = app.params.warehouses[0];
            app.form.warehouse_index = 0;
            app.fetchVehicleExpenses();
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
      app.fetchVehicleExpenses();
    },
    fetchVehicleExpenses() {
      const app = this;
      const load = listVehicleExpenses.loaderShow();
      const param = app.form;
      param.warehouse_id = app.params.warehouses[param.warehouse_index].id;
      var table_title = '';
      if (app.form.from !== '' && app.form.to !== '') {
        table_title = ' from ' + app.form.from + ' to ' + app.form.to;
      }
      app.tableTitle = param.status.toUpperCase() + ' Vehicle Expenses for ' + app.params.warehouses[param.warehouse_index].name + table_title;
      listVehicleExpenses.list(param)
        .then(response => {
        // app.vehicle_expenses = response.vehicle_expenses

          app.vehicle_expenses = response.vehicle_expenses;
          load.hide();
        })
        .catch(error => {
          load.hide();
          console.log(error);
        });
    },

    confirmApproval(index, row) {
      const app = this;
      alert(index);
      approveVehicleExpense.update(row.id, row)
        .then(response => {
          app.vehicle_expenses[index] = response.vehicle_expense;
          this.$message({
            message: 'Vehicle Expense is ' + row.status,
            type: 'success',
          });
        })
        .catch(error => {
          console.log(error);
        });
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const multiHeader = [[this.tableTitle, '', '', '', '', '']];
        const tHeader = ['VEHICLE PLATE NUMBER', 'EXPENSES TYPE', 'TOTAL AMOUNT', 'DATE', 'APPROVAL STATUS', 'FURTHER DETAILS'];
        const filterVal = ['vehicle.plate_no', 'expense_type', 'amount', 'created_at', 'status', 'details'];
        const list = this.vehicle_expenses;
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
        if (j === 'vehicle.plate_no') {
          return v['vehicle']['plate_no'];
        }
        if (j === 'details') {
          var expense_details = v['expense_details'];
          var details = '';
          expense_details.forEach(element => {
            var part = element.vehicle_part;
            var service_type = element.service_type;
            var amount = element.amount;
            details += ' (' + part + ' | ' + service_type + ' | ' + amount + '), ';
          });
          return details;
        }
        return v[j];
      }));
    },
  },
};
</script>
