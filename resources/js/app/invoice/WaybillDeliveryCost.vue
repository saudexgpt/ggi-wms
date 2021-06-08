<template>
  <div class="app-container">
    <div v-if="page.option==='list'">
      <aside class="col-md-3">
        <el-row :gutter="10">
          <legend>Add Delivery Cost</legend>
          <el-col :xs="24" :sm="24" :md="24">
            <label for>Dispatch Company</label>
            <el-select
              v-model="new_waybill_expense.dispatch_company"
              placeholder="Select"
              filterable
              class="span"
            >
              <el-option
                v-for="(dispatch_company, com_index) in params.dispatch_companies"
                :key="com_index"
                :value="dispatch_company"
                :label="dispatch_company"
              />
            </el-select>
          </el-col>
          <el-col :xs="24" :sm="24" :md="24">
            <label for>Trip No.</label>
            <el-input v-model="new_waybill_expense.trip_no" class="span" disabled />
          </el-col>
          <el-col :xs="24" :sm="24" :md="24">
            <label for>Select Pending Waybill No.</label>
            <el-select
              v-model="new_waybill_expense.waybill_ids"
              placeholder="Select Waybill"
              class="span"
              multiple
              filterable
            >
              <el-option
                v-for="(waybill, waybill_index) in waybills_with_pending_wayfare"
                :key="waybill_index"
                :value="waybill.id"
                :label="waybill.waybill_no"
              />
            </el-select>
          </el-col>
          <div v-if="new_waybill_expense.dispatch_company === 'GGI LOGISTICS'">
            <el-col :xs="24" :sm="24" :md="24">
              <label for>Select Vehicle</label>
              <el-select
                v-model="selected_vehicle"
                placeholder="Select Vehicle"
                class="span"
                filterable
                @input="setVehicleDetails"
              >
                <el-option
                  v-for="(vehicle, vehicle_index) in vehicles"
                  :key="vehicle_index"
                  :value="vehicle_index"
                  :label="vehicle.plate_no"
                />
              </el-select>
            </el-col>
          </div>
          <div v-else>
            <el-col :xs="24" :sm="24" :md="24">
              <label for>Vehicle No.</label>
              <el-input v-model="new_waybill_expense.vehicle_no" placeholder="Enter vehicle no." />
            </el-col>
            <el-col :xs="24" :sm="24" :md="24">
              <label for>Dispatcher Name(s)</label>
              <el-input
                v-model="new_waybill_expense.dispatchers"
                type="textarea"
                placeholder="Enter dispatchers name"
              />
            </el-col>
          </div>

          <el-col
            v-if="new_waybill_expense.dispatch_company === 'FOB (Free On Board)'"
            :xs="24"
            :sm="24"
            :md="24"
          >
            <label for>Enter Amount</label>
            <el-input v-model="new_waybill_expense.amount" min="0" type="number" disabled />
          </el-col>
          <el-col v-else :xs="24" :sm="24" :md="24">
            <label for>Enter Amount</label>
            <el-input v-model="new_waybill_expense.amount" min="0" type="number" />
          </el-col>
          <el-col :xs="24" :sm="24" :md="24">
            <label for>Extra Description</label>
            <el-input
              v-model="new_waybill_expense.description"
              type="textarea"
              :row="5"
              placeholder="Give extra description about destinations of trip, vehicle details if not to be delivered by GGI LOGISTICS"
              class="span"
            />
            <p>&nbsp;</p>
            <el-button
              type="success"
              :disabled="new_waybill_expense.description === ''"
              @click="addDeliveryCost"
            >
              <i class="el-icon-plus" />
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
            <el-row :gutter="10">
              <el-col :xs="24" :sm="12" :md="12">
                <label for>Select Warehouse</label>
                <el-select
                  v-model="form.warehouse_index"
                  placeholder="Select Warehouse"
                  class="span"
                  filterable
                  @input="getWaybills"
                >
                  <el-option
                    v-for="(warehouse, index) in warehouses"
                    :key="index"
                    :value="index"
                    :label="warehouse.name"
                  />
                </el-select>
              </el-col>
              <el-col :xs="24" :sm="12" :md="12">
                <!-- <label class="radio-label" style="padding-left:0;">Filename: </label>
                <el-input v-model="filename" :placeholder="$t('excel.placeholder')" style="width:340px;" prefix-icon="el-icon-document" />-->
                <el-button
                  :loading="downloadLoading"
                  style="margin:0 0 20px 20px;"
                  type="primary"
                  icon="document"
                  @click="handleDownload"
                >Export Excel</el-button>
              </el-col>
            </el-row>
            <p>&nbsp;</p>
            <v-client-table v-model="delivery_trips" :columns="columns" :options="options">
              <div slot="child_row" slot-scope="props">
                <aside>
                  <div class="pull-right">
                    Add Waybill to Trip
                    <el-select
                      v-model="extra_waybill_id"
                      placeholder="Select Waybill"
                      class="span"
                      filterable
                    >
                      <el-option
                        v-for="(waybill, waybill_index) in waybills_with_pending_wayfare"
                        :key="waybill_index"
                        :value="waybill.id"
                        :label="waybill.waybill_no"
                      />
                    </el-select>
                    <el-button
                      type="success"
                      icon="el-icon-plus"
                      @click="addWaybillToTrip(props.row.id, props.index)"
                    />
                  </div>
                  <div>Waybills For Trip No.: {{ props.row.trip_no }}</div>

                  <v-client-table
                    v-model="props.row.waybills"
                    :columns="['action', 'waybill_no', 'created_at']"
                  >
                    <div slot="created_at" slot-scope="{row}">{{ moment(row.created_at).fromNow() }}</div>
                    <div slot="action" slot-scope="{row}">
                      <a
                        v-if="row.status === 'pending'"
                        class="btn btn-danger"
                        @click="removeWaybill(row.id, props.row.id, props.index)"
                      >
                        <i class="el-icon-delete" />
                      </a>
                    </div>
                  </v-client-table>
                </aside>
              </div>
              <div slot="waybills" slot-scope="props">
                <div v-if="props.row.waybills.length > 0">
                  <div v-for="(waybill, index) in props.row.waybills" :key="index">
                    {{ waybill.waybill_no }},
                  </div>
                </div>

              </div>
              <div slot="dispatchers" slot-scope="props">
                <div v-if="props.row.dispatchers == null">
                  <div v-for="(vehicle_driver, index) in props.row.vehicle.vehicle_drivers" :key="index">
                    {{ (vehicle_driver.driver) ? vehicle_driver.driver.user.name : '' }} <br>
                  </div>
                </div>
                <div v-else>
                  {{ props.row.dispatchers }}
                </div>

              </div>
              <div
                slot="amount"
                slot-scope="props"
              >{{ currency + Number(props.row.cost.amount).toLocaleString() }}</div>
              <div
                slot="created_at"
                slot-scope="props"
              >{{ moment(props.row.created_at).format('MMMM Do YYYY, h:mm:ss a') }}</div>
              <div
                slot="updated_at"
                slot-scope="props"
              >{{ moment(props.row.updated_at).format('MMMM Do YYYY, h:mm:ss a') }}</div>
              <div slot="confirmer.name" slot-scope="{row}">
                <div :id="row.cost.id">
                  <div v-if="row.cost.confirmed_by == null">
                    <a
                      v-if="checkPermission(['audit confirm actions'])"
                      title="Click to confirm"
                      class="btn btn-warning"
                      @click="confirmDeliveryCost(row.cost.id);"
                    >
                      <i class="fa fa-check" />
                    </a>
                  </div>
                  <div v-else>{{ row.cost.confirmer.name }}</div>
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
    </div>-->
  </div>
</template>
<script>
import moment from 'moment';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';
import { parseTime } from '@/utils';
import Resource from '@/api/resource';
// import WaybillDetails from './partials/WaybillDetails';
const necessaryParams = new Resource('fetch-necessary-params');
const fetchWaybillExpenses = new Resource('invoice/waybill/expenses');
const addWaybillExpenses = new Resource('invoice/waybill/add-waybill-expenses');
const detachWaybillFromTrip = new Resource('invoice/waybill/detach-waybill-from-trip');
const addWaybillToTripResource = new Resource(
  'invoice/waybill/add-waybill-to-trip',
);
const confirmDeliveryCostResource = new Resource('audit/confirm/delivery-cost');
// const deleteItemInStock = new Resource('stock/items-in-stock/delete');
export default {
  // name: 'WaybillDeliveryCost',
  // components: { WaybillDetails },
  props: {
    canGenerateNewWaybill: {
      type: Boolean,
      default: () => true,
    },
  },
  data() {
    return {
      warehouses: [],
      vehicles: [],
      delivery_trips: [],
      waybills_with_pending_wayfare: [],
      currency: '',
      columns: [
        'confirmer.name',
        'dispatch_company',
        'trip_no',
        'waybills',
        'amount',
        'description',
        'vehicle_no',
        'dispatchers',
        'created_at',
      ],

      options: {
        headings: {
          trip_no: 'Trip No.',
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
      params: {},
      form: {
        warehouse_index: '',
        warehouse_id: '',
      },
      new_waybill_expense: {
        dispatch_company: 'GGI LOGISTICS',
        waybill_ids: [],
        vehicle_id: null,
        amount: 0,
        description: '',
        warehouse_id: '',
        trip_no: '',
        dispatchers: '',
      },
      tableTitle: '',
      selected_row_index: '',
      selected_vehicle: null,
      // submitTitle: 'Fetch',
      // panel: 'month',
      // future: false,
      // panels: ['range', 'week', 'month', 'quarter', 'year'],
      // show_calendar: false,
      downloadLoading: false,
      filename: 'Delivery Cost',
      extra_waybill_id: null,
    };
  },

  created() {
    this.fetchNecessaryParams();
  },
  beforeDestroy() {},
  methods: {
    moment,
    checkPermission,
    checkRole,
    showCalendar() {
      this.show_calendar = !this.show_calendar;
    },
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list().then((response) => {
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
    setVehicleDetails() {
      const app = this;
      const selected_vehicle = app.selected_vehicle;
      app.new_waybill_expense.vehicle_id = app.vehicles[selected_vehicle].id;
      app.new_waybill_expense.vehicle_no =
        app.vehicles[selected_vehicle].plate_no;
      const vehicle_drivers = app.vehicles[selected_vehicle].vehicle_drivers;
      var drivers = '';
      vehicle_drivers.forEach((vehicle_driver) => {
        drivers += vehicle_driver.driver.user.name + ',';
      });
      app.new_waybill_expense.dispatchers = drivers;
    },
    format(date) {
      var month = date.toLocaleString('en-US', { month: 'short' });
      return month + ' ' + date.getDate() + ', ' + date.getFullYear();
    },
    setDateRange(values) {
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
      app.new_waybill_expense.warehouse_id =
        app.warehouses[app.form.warehouse_index].id;
      const loader = fetchWaybillExpenses.loaderShow();
      const param = app.form;
      param.warehouse_id = app.warehouses[param.warehouse_index].id;
      app.tableTitle =
        'Waybill Delivery Expenses in ' +
        app.warehouses[param.warehouse_index].name;
      fetchWaybillExpenses
        .list(param)
        .then((response) => {
          app.delivery_trips = response.delivery_trips;
          app.waybills_with_pending_wayfare =
            response.waybills_with_pending_wayfare;
          app.new_waybill_expense.trip_no = response.trip_no;
          app.vehicles = response.vehicles;
          loader.hide();
        })
        .catch((error) => {
          loader.hide();
          console.log(error.message);
        });
    },
    confirmDeliveryCost(delivery_cost_id) {
      // const app = this;
      const form = { id: delivery_cost_id };
      const message = 'Click okay to confirm action';
      if (confirm(message)) {
        confirmDeliveryCostResource
          .update(delivery_cost_id, form)
          .then((response) => {
            if (response.confirmed === 'success') {
              document.getElementById(delivery_cost_id).innerHTML =
                response.confirmed_by;
            }
          });
      }
    },
    addDeliveryCost() {
      const app = this;
      const loader = addWaybillExpenses.loaderShow();

      const param = app.new_waybill_expense;
      addWaybillExpenses
        .store(param)
        .then((response) => {
          app.resetForm();
          app.delivery_trips.push(response.delivery_trip);
          app.waybills_with_pending_wayfare =
            response.waybills_with_pending_wayfare;
          app.new_waybill_expense.trip_no = response.trip_no;
          loader.hide();
        })
        .catch((error) => {
          loader.hide();
          console.log(error.message);
        });
    },
    addWaybillToTrip(delivery_trip_id, index) {
      const app = this;
      const param = {
        waybill_id: app.extra_waybill_id,
        delivery_trip_id: delivery_trip_id,
      };
      if (app.extra_waybill_id !== null) {
        addWaybillToTripResource.store(param).then((response) => {
          app.delivery_trips[index - 1].waybills =
            response.delivery_trip.waybills;
          app.waybills_with_pending_wayfare =
            response.waybills_with_pending_wayfare;
          app.new_waybill_expense.trip_no = response.trip_no;

          app.extra_waybill_id = null; // return to initial value
        });
      } else {
        this.$alert('Please select a waybill to add', 'Nothing Selected', {
          confirmButtonText: 'OK',
          callback: () => {},
        });
      }
    },
    removeWaybill(waybill_id, delivery_trip_id, index) {
      const app = this;
      app
        .$confirm(
          'Do you really want to remove this waybill from this trip?',
          'Please Confirm',
          {
            confirmButtonText: 'OK',
            cancelButtonText: 'Cancel',
            type: 'warning',
          },
        )
        .then(() => {
          const param = {
            waybill_id: waybill_id,
            delivery_trip_id: delivery_trip_id,
          };
          detachWaybillFromTrip.store(param).then((response) => {
            app.delivery_trips[index - 1].waybills =
              response.delivery_trip.waybills;
            app.waybills_with_pending_wayfare =
              response.waybills_with_pending_wayfare;
            app.new_waybill_expense.trip_no = response.trip_no;
          });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Action canceled',
          });
        });
    },
    resetForm() {
      this.new_waybill_expense = {
        dispatch_company: 'GGI LOGISTICS',
        waybill_ids: [],
        vehicle_id: null,
        amount: 0,
        description: '',
        trip_no: '',
        dispatchers: '',
      };
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then((excel) => {
        const multiHeader = [[this.tableTitle, '', '', '', '', '', '']];
        const tHeader = [
          'WAYBILL NUMBER',
          'AMOUNT',
          'NOTE',
          'DATE',
          'DISPATCHERS',
        ];
        const filterVal = [
          'waybill.waybill_no',
          'amount',
          'description',
          'created_at',
          'dispatchers',
        ];
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
      return jsonData.map((v) =>
        filterVal.map((j) => {
          if (j === 'created_at') {
            return parseTime(v[j]);
          }
          if (j === 'waybill.waybill_no') {
            return v['waybill']['waybill_no'];
          }
          if (j === 'dispatchers') {
            var vehicle_drivers =
              v['waybill']['dispatcher']['vehicle']['vehicle_drivers'];
            var drivers = '';
            vehicle_drivers.forEach((element) => {
              var name = element.driver.user.name;
              drivers += name + ', ';
            });
            return drivers;
          }
          return v[j];
        }),
      );
    },
  },
};
</script>
