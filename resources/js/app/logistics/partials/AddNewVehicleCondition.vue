<template>
  <div>
    <span class="">
      <a class="btn btn-danger" @click="page.option = 'list'"> Back</a>
    </span>
    <div class="box">
      <div class="box-header">
        <h4 class="box-title">Add Vehicle Condition</h4>
      </div>
      <div class="box-body">
        <aside>
          <el-form ref="form" :model="form" label-width="120px">
            <el-row :gutter="5" class="padded">
              <el-col :xs="24" :sm="12" :md="12">
                <label for="">Select Warehouse</label>
                <el-select v-model="selected_warehouse" placeholder="Select Warehouse" filterable class="span" @input="fetchVehicles">
                  <el-option v-for="(warehouse, index) in params.warehouses" :key="index" :value="index" :label="warehouse.name" />

                </el-select>
                <label for="">Select Vehicle</label>
                <el-select v-model="form.vehicle_id" placeholder="Select vehicle" filterable class="span">
                  <el-option v-for="(vehicle, index) in vehicles" :key="index" :value="vehicle.id" :label="vehicle.brand+' ['+vehicle.plate_no+']'" />

                </el-select>

                <label for="">Current Vehicle Condition</label>
                <el-input v-model="form.condition" placeholder="Enter vehicle current condition" filterable class="span" />
              </el-col>
              <el-col :xs="24" :sm="12" :md="12">
                <label for="">Describe Condition</label>
                <textarea v-model="form.description" placeholder="Give details of vehicle condition here..." class="form-control" />
                <label for="">Status</label>
                <el-select v-model="form.status" placeholder="Select Status" filterable class="span">
                  <el-option v-for="(status, index) in statuses" :key="index" :value="status" :label="status" />

                </el-select>
              </el-col>
            </el-row>
            <el-row :gutter="2" class="padded">
              <el-col :xs="24" :sm="6" :md="6">
                <el-button type="success" @click="addVehicleCondition"><i class="el-icon-plus" />
                  Add
                </el-button>
              </el-col>
            </el-row>
          </el-form>
        </aside>

      </div>
    </div>
  </div>
</template>

<script>
import moment from 'moment';
import Resource from '@/api/resource';
const createVehicleCondition = new Resource('logistics/vehicle-conditions/store');
export default {
  name: 'AddNew',

  props: {
    params: {
      type: Object,
      default: () => ({}),
    },
    vehicleConditions: {
      type: Array,
      default: () => ([]),
    },

    page: {
      type: Object,
      default: () => ({
        option: 'add_new',
      }),
    },

  },
  data() {
    return {
      vehicles: [],
      form: {
        warehouse_id: '',
        vehicle_id: '',
        condition: '',
        description: '',
        status: 'Bad Condition',

      },
      empty_form: {
        warehouse_id: '',
        vehicle_id: '',
        condition: '',
        description: '',
        status: 'Bad Condition',

      },
      statuses: ['Bad Condition', 'Mechanic Workshop', 'Good Condition'],
      selected_warehouse: {},

    };
  },
  methods: {
    moment,
    fetchVehicles() {
      this.vehicles = this.params.warehouses[this.selected_warehouse].vehicles;
      this.form.warehouse_id = this.params.warehouses[this.selected_warehouse].id;
    },
    addVehicleCondition() {
      const app = this;
      var form = app.form;
      const load = createVehicleCondition.loaderShow();
      // form.service_date = app.moment(form.service_date).format('LLL');
      createVehicleCondition.store(form)
        .then(response => {
          app.$message({ message: 'Added Successfully!!!', type: 'success' });
          app.vehicleConditions.push(response.vehicle_condition);
          app.form = app.empty_form;
          load.hide();
        })
        .catch(error => {
          load.hide();
          alert(error.message);
        });
    },

  },
};
</script>

