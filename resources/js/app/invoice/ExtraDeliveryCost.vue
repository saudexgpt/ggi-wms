<template>
  <div class="app-container">
    <div v-if="page.option==='list'">
      <aside class="col-md-3">
        <el-row :gutter="10">
          <legend>Add Extra Delivery Cost</legend>
          <el-col :xs="24" :sm="24" :md="24">
            <label for="">Select Trip No.</label>
            <el-select v-model="extra_waybill_expense.delivery_trip_id" placeholder="Select Trip No." class="span" filterable>
              <el-option v-for="(regular_delivery_trip, trip_no_index) in regular_delivery_trips" :key="trip_no_index" :value="regular_delivery_trip.delivery_trip.id" :label="regular_delivery_trip.delivery_trip.trip_no" />

            </el-select>

          </el-col>
          <el-col :xs="24" :sm="24" :md="24">
            <label for="">Enter Amount</label>
            <el-input v-model="extra_waybill_expense.amount" min="0" type="number" />
          </el-col>
          <el-col :xs="24" :sm="24" :md="24">
            <label for="">Expenses Details</label>
            <textarea v-model="extra_waybill_expense.details" placeholder="Give extra description about destinations of trip" class="span" />

            <br>
            <el-button type="success" @click="addExtraDeliveryCost"><i class="el-icon-plus" />
              Submit
            </el-button>
          </el-col>

        </el-row>
      </aside>
      <div class="col-md-9">
        <div class="box">
          <div class="box-header">
            <h4 class="box-title">{{ tableTitle }}</h4>

          </div>
          <div class="box-body">
            <v-client-table v-model="extra_delivery_costs" :columns="columns" :options="options">
              <div slot="child_row" slot-scope="props">
                <aside>
                  <legend>Waybills For Trip No.:  {{ props.row.delivery_trip.trip_no }}</legend>
                  <v-client-table v-model="props.row.delivery_trip.waybills" :columns="['action', 'waybill_no', 'created_at']">

                    <div slot="created_at" slot-scope="{row}">
                      {{ moment(row.created_at).fromNow() }}

                    </div>
                    <div slot="action" slot-scope="{row}">
                      <a v-if="row.status === 'pending'" class="btn btn-danger" @click="removeWaybill(row.id, props.row.id, props.index)"><i class="el-icon-delete" /></a>

                    </div>
                  </v-client-table>
                </aside>

              </div>
              <div slot="dispatchers" slot-scope="props">
                <div v-for="(vehicle_driver, index) in props.row.delivery_trip.vehicle.vehicle_drivers" :key="index">
                  {{ vehicle_driver.driver.user.name }} <br>
                </div>

              </div>
              <div slot="trip_no" slot-scope="props">
                {{ props.row.delivery_trip.trip_no }}
              </div>
              <div slot="amount" slot-scope="props">
                {{ currency + Number(props.row.amount).toLocaleString() }}
              </div>
              <div slot="created_at" slot-scope="props">
                {{ moment(props.row.created_at).format('MMMM Do YYYY, h:mm:ss a') }}
              </div>
              <div slot="updated_at" slot-scope="props">
                {{ moment(props.row.updated_at).format('MMMM Do YYYY, h:mm:ss a') }}
              </div>
              <div slot="confirmer.name" slot-scope="{row}">
                <div :id="row.id">
                  <div v-if="row.confirmed_by == null">
                    <a v-if="checkPermission(['audit confirm actions'])" title="Click to confirm" class="btn btn-warning" @click="confirmDeliveryCost(row.id);"><i class="fa fa-check" /> </a>
                  </div>
                  <div v-else>
                    {{ row.confirmer.name }}
                  </div>
                </div>
              </div>

            </v-client-table>

          </div>
        </div>
      </div>
    </div>
    <!-- <div v-if="page.option==='waybill_details'">
      <a class="btn btn-danger no-print" @click="page.option='list'">Go Back</a>
      <waybill-details :waybill="waybill" :page="page" :company-name="params.company_name" :company-contact="params.company_contact" :currency="currency" />
    </div> -->
  </div>
</template>
<script>
import moment from 'moment';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';
import { parseTime } from '@/utils';
import Resource from '@/api/resource';
// import WaybillDetails from './partials/WaybillDetails';
const deliveryTripsForExtraCost = new Resource('invoice/waybill/delivery-trips-for-extra-cost');
// const fetchWaybillExpenses = new Resource('invoice/waybill/expenses');
const addExtraDeliveryCost = new Resource('invoice/waybill/add-extra-delivery-cost');
const detachWaybillFromTrip = new Resource('invoice/waybill/detach-waybill-from-trip');
const confirmDeliveryCostResource = new Resource('audit/confirm/delivery-cost');
// const deleteItemInStock = new Resource('stock/items-in-stock/delete');
export default {
  // name: 'WaybillDeliveryCost',
  // components: { WaybillDetails },
  props: {
    canGenerateNewWaybill: {
      type: Boolean,
      default: () => (true),
    },
  },
  data() {
    return {
      warehouses: [],
      extra_delivery_costs: [],
      currency: '₦',
      columns: ['confirmer.name', 'trip_no', 'amount', 'details', 'dispatchers', 'created_at'],

      options: {
        headings: {
          'trip_no': 'Trip No.',
          'confirmer.name': 'Confirmed By',

          // id: 'S/N',
        },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['trip_no', 'amount', 'created_at', 'updated_at'],
        filterable: ['trip_no', 'amount', 'created_at', 'updated_at'],
      },
      page: {
        option: 'list',
      },
      regular_delivery_trips: [],
      extra_waybill_expense: {
        delivery_trip_id: '',
        amount: 0,
        details: '',
      },
      tableTitle: 'Extra Delivery Cost',
      selected_row_index: '',
      // submitTitle: 'Fetch',
      // panel: 'month',
      // future: false,
      // panels: ['range', 'week', 'month', 'quarter', 'year'],
      // show_calendar: false,
      downloadLoading: false,
      filename: 'Delivery Cost',

    };
  },

  created() {
    this.fetchDeliveryTripsForExtraCost();
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
    fetchDeliveryTripsForExtraCost() {
      const app = this;
      deliveryTripsForExtraCost.list()
        .then(response => {
          app.regular_delivery_trips = response.regular_delivery_trips;
          app.extra_delivery_costs = response.extra_delivery_costs;
        });
    },
    confirmDeliveryCost(delivery_cost_id) {
      // const app = this;
      const form = { id: delivery_cost_id, is_extra: true };
      const message = 'Click okay to confirm action';
      if (confirm(message)) {
        confirmDeliveryCostResource.update(delivery_cost_id, form)
          .then(response => {
            if (response.confirmed === 'success'){
              document.getElementById(delivery_cost_id).innerHTML = response.confirmed_by;
            }
          });
      }
    },
    // format(date) {
    //   var month = date.toLocaleString('en-US', { month: 'short' });
    //   return month + ' ' + date.getDate() + ', ' + date.getFullYear();
    // },
    // setDateRange(values){
    //   const app = this;
    //   document.getElementById('pick_date').click();
    //   app.show_calendar = false;
    //   let panel = app.panel;
    //   let from = app.week_start;
    //   let to = app.week_end;
    //   if (values !== '') {
    //     to = this.format(new Date(values.to));
    //     from = this.format(new Date(values.from));
    //     panel = values.panel;
    //   }
    //   app.form.from = from;
    //   app.form.to = to;
    //   app.form.panel = panel;
    //   app.getWaybills();
    // },
    // getWaybills() {
    //   const app = this;
    //   app.extra_waybill_expense.warehouse_id = app.warehouses[app.form.warehouse_index].id;
    //   const loader = fetchWaybillExpenses.loaderShow();
    //   const param = app.form;
    //   param.warehouse_id = app.warehouses[param.warehouse_index].id;
    //   app.tableTitle = 'Waybill Delivery Expenses in ' + app.warehouses[param.warehouse_index].name;
    //   fetchWaybillExpenses.list(param)
    //     .then(response => {
    //       app.delivery_trips = response.delivery_trips;
    //       app.waybills_with_pending_wayfare = response.waybills_with_pending_wayfare;
    //       loader.hide();
    //     })
    //     .catch(error => {
    //       loader.hide();
    //       console.log(error.message);
    //     });
    // },
    addExtraDeliveryCost() {
      const app = this;
      const loader = addExtraDeliveryCost.loaderShow();

      const param = app.extra_waybill_expense;
      addExtraDeliveryCost.store(param)
        .then(response => {
          app.resetForm();
          app.extra_delivery_costs.push(response.delivery_expense);
          app.waybills_with_pending_wayfare = response.waybills_with_pending_wayfare;
          app.extra_waybill_expense.trip_no = response.trip_no;
          loader.hide();
        })
        .catch(error => {
          loader.hide();
          console.log(error.message);
        });
    },
    resetForm() {
      this.extra_waybill_expense = {
        delivery_trip_id: '',
        amount: 0,
        details: '',
      };
    },
    removeWaybill(waybill_id, delivery_trip_id, index){
      const app = this;
      if (confirm('Do you really want to remove this waybill from this trip? This cannot be undone.')) {
        const param = { 'waybill_id': waybill_id, 'delivery_trip_id': delivery_trip_id };
        detachWaybillFromTrip.store(param)
          .then(response => {
            app.delivery_trips[index - 1].waybills = response.delivery_trip.waybills;
            app.waybills_with_pending_wayfare = response.waybills_with_pending_wayfare;
            app.extra_waybill_expense.trip_no = response.trip_no;
          });
      }
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const multiHeader = [[this.tableTitle, '', '', '', '', '', '']];
        const tHeader = ['WAYBILL NUMBER', 'AMOUNT', 'NOTE', 'DATE', 'DISPATCHERS'];
        const filterVal = ['waybill.waybill_no', 'amount', 'description', 'created_at', 'dispatchers'];
        const list = this.delivery_trips;
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
        if (j === 'waybill.waybill_no') {
          return v['waybill']['waybill_no'];
        }
        if (j === 'dispatchers') {
          var vehicle_drivers = v['waybill']['dispatcher']['vehicle']['vehicle_drivers'];
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
