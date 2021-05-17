<template>
  <div class="app-container">
    <span v-if="page.option==='list'">
      <a v-if="checkPermission(['manage vehicles']) && canAddNew" class="btn btn-info" @click="page.option = 'add_new'"> Add New</a>
      <el-row>
        <el-col :xs="24" :sm="12" :md="12">
          <label for="">Select Warehouse</label>
          <el-select v-model="form.warehouse_index" placeholder="Select Warehouse" class="span" filterable @input="fetchVehicles">
            <el-option v-for="(warehouse, index) in warehouses" :key="index" :value="index" :label="warehouse.name" />

          </el-select>

        </el-col>
      </el-row>
      <br>
    </span>
    <div>
      <!-- <vehicle-details v-if="page.option== 'view_details'" :vehicle-in-stock="vehicle" :page="page" /> -->
      <add-new v-if="page.option== 'add_new'" :vehicles="vehicles" :params="params" :page="page" />
      <edit-vehicle v-if="page.option=='edit_vehicle'" :vehicles="vehicles" :vehicle="vehicle" :params="params" :page="page" @update="onEditUpdate" />
      <vehicle-details v-if="page.option=='vehicle_details'" :vehicle="vehicle" :page="page" />
      <div v-if="page.option==='list'" class="box">
        <div class="box-header">
          <h4 class="box-title">{{ table_title }}</h4>

        </div>
        <div class="box-body">
          <el-button :loading="downloadLoading" style="margin:0 0 20px 20px;" type="primary" icon="document" @click="handleDownload">
            Export Excel
          </el-button>
          <v-client-table v-model="vehicles" :columns="columns" :options="options">
            <div slot="availability" slot-scope="props">
              {{ props.row.availability.toUpperCase() }}
            </div>
            <div slot="purchase_date" slot-scope="props">
              {{ moment(props.row.purchase_date).format('MMMM Do YYYY') }}
            </div>
            <div slot="drivers" slot-scope="props">
              <span v-for="(vehicle_driver, index) in props.row.vehicle_drivers" :key="index">
                <div v-if="vehicle_driver.driver">
                  <div v-if="vehicle_driver.driver.user">
                    {{ vehicle_driver.driver.user.name+' ('+vehicle_driver.type+' Driver)' }}<br>
                  </div>
                </div>

              </span>
            </div>
            <div slot="action" slot-scope="props">
              <el-tooltip :content="'Assign Driver'" effect="dark" placement="bottom">
                <a v-if="checkPermission(['manage vehicles'])" class="btn btn-warning" @click="assignDriver(props.index, props.row)"> <i class="el-icon-user" /></a>
              </el-tooltip>
              <el-tooltip :content="'View Vehicle Details'" effect="dark" placement="bottom">
                <a v-if="checkPermission(['manage vehicles'])" class="btn btn-default" @click="vehicle=props.row; selected_row_index=props.index; page.option = 'vehicle_details'"><i class="el-icon-truck" /> </a>
              </el-tooltip>
              <el-tooltip :content="'Edit Vehicle Details'" effect="dark" placement="bottom">
                <a v-if="checkPermission(['manage vehicles'])" class="btn btn-primary" @click="vehicle=props.row; selected_row_index=props.index; page.option = 'edit_vehicle'"><i class="el-icon-edit" /> </a>
              </el-tooltip>
              <!-- <a v-if="checkPermission(['manage vehicles'])" class="btn btn-danger" @click="confirmDelete(props)"><i class="el-icon-delete" /> </a> -->

            </div>

          </v-client-table>
          <el-dialog :title="'Assign Driver to Vehicle'" :visible.sync="dialogFormVisible">
            <div v-loading="assigningVehicle" class="form-container">
              <el-form ref="assignVehicle" :model="assignVehicle" label-position="left">
                <el-row :gutter="5" class="padded">
                  <el-col :xs="24" :sm="12" :md="12">
                    <el-select v-model="assignVehicle.driver_id" placeholder="Select Driver" filterable class="span">
                      <el-option v-for="(driver, index) in drivers" :key="index" :value="driver.id" :label="driver.user.name" />
                    </el-select>
                  </el-col>
                  <el-col :xs="24" :sm="12" :md="12">
                    <el-select v-model="assignVehicle.type" placeholder="Select Driver Type" class="span">
                      <el-option value="Primary" label="Primary" />
                      <el-option value="Assistant" label="Assistant" />
                    </el-select>
                  </el-col>
                </el-row>
              </el-form>
              <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">
                  {{ $t('table.cancel') }}
                </el-button>
                <el-button type="primary" @click="assignDriverToVehicle()">
                  Assign
                </el-button>
              </div>
            </div>
          </el-dialog>
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

import AddNew from './partials/AddNewVehicle';
import EditVehicle from './partials/EditVehicle';
import VehicleDetails from './partials/VehicleDetails';
import Resource from '@/api/resource';
const necessaryParams = new Resource('fetch-necessary-params');
const vehicleList = new Resource('logistics/vehicles');
const driverList = new Resource('logistics/drivers');
const assignDriverToVehicle = new Resource('logistics/vehicles/assign-driver');
const deleteVehicle = new Resource('logistics/vehicles/delete');
export default {
  components: { AddNew, EditVehicle, VehicleDetails },
  props: {
    canAddNew: {
      type: Boolean,
      default: () => (true),
    },
  },
  data() {
    return {
      warehouses: [],
      vehicles: [],
      drivers: [],
      dialogFormVisible: false,
      columns: ['action', 'plate_no', 'vehicle_type.type', 'brand', 'model', 'initial_mileage', 'engine_type', 'purchase_date', 'drivers', 'availability'],

      options: {
        headings: {
          'vehicle_type.type': 'Type',
          plate_no: 'Plate No.',
          brand: 'Brand',
          model: 'Model',
          engine_type: 'Engine Type',
          purchase_date: 'Purchase Date',
          drivers: 'Drivers',

          // id: 'S/N',
        },
        rowAttributesCallback: row => {
          if (row.availability === 'available') {
            return { 'style': 'background: #a8ee99;' };
          } else if (row.availability === 'in transit'){
            return { 'style': 'background: #97f0f0;' };
          } else if (row.availability === 'repairs') {
            return { 'style': 'background: #f08c8c;' };
          }
        },
        pagination: {
          dropdown: true,
          chunk: 20,
        },
        filterByColumn: true,
        texts: {
          filter: 'Search:',
        },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['vehicle_type.type', 'plate_no', 'brand'],
        filterable: ['vehicle_type.type', 'plate_no', 'brand', 'model', 'purchase_date'],
      },
      page: {
        option: 'list',
      },
      params: {},
      form: {
        warehouse_index: '',
        warehouse_id: '',
      },
      table_title: '',
      assignVehicle: {
        vehicle_id: '',
        type: '',
        driver_id: '',
      },
      selected_row_index: '',
      assigningVehicle: false,
      downloadLoading: false,

    };
  },

  mounted() {
    // this.getWarehouse();
    this.fetchNecessaryParams();
    this.fetchDrivers();
  },
  beforeDestroy() {

  },
  methods: {
    moment,
    checkPermission,
    checkRole,
    assignDriver(selected_row_index, vehicle) {
      const app = this;
      app.selected_row_index = selected_row_index;
      app.assignVehicle.vehicle_id = vehicle.id;
      app.dialogFormVisible = true;
    },
    fetchDrivers() {
      const app = this;
      driverList.list()
        .then(response => {
          app.drivers = response.drivers;
        });
    },
    assignDriverToVehicle() {
      const app = this;
      app.assigningVehicle = true;
      assignDriverToVehicle.store(app.assignVehicle)
        .then(response => {
          app.assigningVehicle = false;
          app.vehicles = response.vehicles;
          this.$message({
            message: 'Vehicle Assigned',
            type: 'success',
          });
          app.dialogFormVisible = false;
        })
        .catch(error => {
          app.assigningVehicle = false;
          console.log(error.message);
        });
    },
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list()
        .then(response => {
          app.params = response.params;
          app.warehouses = response.params.warehouses;
          if (app.warehouses.length > 0) {
            app.form.warehouse_id = app.warehouses[0].id;
            app.form.warehouse_index = 0;
            app.fetchVehicles();
          }
        });
    },
    fetchVehicles() {
      const app = this;
      const loader = vehicleList.loaderShow();

      const param = app.form;
      param.warehouse_id = app.warehouses[param.warehouse_index].id;
      vehicleList.list(param)
        .then(response => {
          app.vehicles = response.vehicles;
          app.table_title = 'LIST OF VEHICLES IN ' + app.warehouses[param.warehouse_index].name.toUpperCase();
          loader.hide();
        })
        .catch(error => {
          loader.hide();
          console.log(error.message);
        });
    },

    onEditUpdate(updated_row) {
      const app = this;
      // app.vehicles.splice(app.vehicle.index-1, 1);
      app.vehicles[app.selected_row_index - 1] = updated_row;
    },
    confirmDelete(props) {
      // this.loader();

      const row = props.row;
      const app = this;
      const message = 'This delete action cannot be undone. Click OK to confirm';
      if (confirm(message)) {
        deleteVehicle.destroy(row.id, row)
          .then(response => {
            app.vehicles.splice(row.index - 1, 1);
            this.$message({
              message: 'Vehicle has been deleted',
              type: 'success',
            });
          })
          .catch(error => {
            console.log(error);
          });
      }
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const multiHeader = [[this.table_title, '', '', '', '', '', '', '']];
        const tHeader = ['PLATE NO.', 'VEHICLE TYPE', 'BRAND', 'MODEL', 'INITIAL MILEAGE', 'ENGINE TYPE', 'PURCHASE DATE', 'IN DRIVERS'];
        const filterVal = ['plate_no', 'vehicle_type.type', 'brand', 'model', 'initial_mileage', 'engine_type', 'purchase_date', 'drivers'];
        const list = this.vehicles;
        const data = this.formatJson(filterVal, list);
        excel.export_json_to_excel({
          multiHeader,
          header: tHeader,
          data,
          filename: this.table_title,
          autoWidth: true,
          bookType: 'csv',
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => {
        if (j === 'purchase_date') {
          return parseTime(v[j]);
        }
        if (j === 'vehicle_type.type') {
          return v['vehicle_type']['type'];
        }
        if (j === 'drivers') {
          var vehicle_drivers = v['vehicle_drivers'];
          var drivers = '';
          if (vehicle_drivers.length > 0) {
            vehicle_drivers.forEach(element => {
              if (element.driver) {
                if (element.driver.user) {
                  var name = element.driver.user.name;
                  drivers += name + ', ';
                }
              }
            });
          }
          return drivers;
        }
        return v[j];
      }));
    },
  },
};
</script>
