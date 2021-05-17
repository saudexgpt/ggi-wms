<template>
  <div class="app-container">
    <span v-if="page.option === 'list'">
      <a class="btn btn-info" @click="page.option = 'add_new'"> Add New</a>
    </span>
    <div class="">
      <add-new v-if="page.option === 'add_new'" :params="params" :vehicle-conditions="vehicle_conditions" :page="page" />
      <div v-if="page.option === 'list'" class="box">
        <div class="box-header">
          <h4 class="box-title">Conditions of Vehicles</h4>
        </div>
        <div class="box-body">
          <el-col :xs="24" :sm="12" :md="12">
            <label for="">Select Warehouse</label>
            <el-select v-model="form.warehouse_id" class="span" filterable @input="fetchVehicleConditions">
              <el-option v-for="(warehouse, index) in warehouses" :key="index" :value="warehouse.id" :label="warehouse.name" />

            </el-select>

          </el-col>
          <br><br><br><br>
          <v-client-table v-model="vehicle_conditions" :columns="columns" :options="options">
            <div slot="created_at" slot-scope="props">
              {{ moment(props.row.created_at).format('MMMM Do, YYYY') }}
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

import AddNew from './partials/AddNewVehicleCondition';
import Resource from '@/api/resource';
// import Vue from 'vue';
const necessaryParams = new Resource('fetch-necessary-params');
const listVehicleConditions = new Resource('logistics/vehicle-conditions');
export default {
  components: { AddNew },
  data() {
    return {
      warehouses: [],
      vehicle_conditions: [],
      columns: ['vehicle.plate_no', 'condition', 'description', 'status', 'created_at'],

      options: {
        headings: {
          'vehicle.plate_no': 'Vehicle',
          condition: 'Condition',
          description: 'Description',
          created_at: 'Date',
          status: 'Status',
          // id: 'S/N',
        },
        // editableColumns: ['type', 'load_capacity', 'is_enabled'],
        sortable: ['vehicle.plate_no', 'status', 'created_at'],
        filterable: ['vehicle.plate_no', 'condition_type', 'status', 'created_at'],
      },
      params: {},
      page: {
        option: 'list',
      },
      form: {
        warehouse_id: '',
      },
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
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list()
        .then(response => {
          app.params = response.params;
          app.warehouses = response.params.warehouses;
          if (app.warehouses.length > 0) {
            app.form.warehouse_id = app.warehouses[0].id;
            app.form.warehouse_index = 0;
            app.fetchVehicleConditions();
          }
        });
    },
    fetchVehicleConditions() {
      const app = this;
      const load = listVehicleConditions.loaderShow();
      const param = app.form;
      listVehicleConditions.list(param)
        .then(response => {
        // app.vehicle_conditions = response.vehicle_conditions

          app.vehicle_conditions = response.vehicle_conditions;
          load.hide();
        })
        .catch(error => {
          load.hide();
          console.log(error);
        });
    },
  },
};
</script>
