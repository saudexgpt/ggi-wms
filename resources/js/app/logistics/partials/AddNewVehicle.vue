<template>
  <div>
    <span class="">
      <a class="btn btn-danger" @click="page.option = 'list'"> Back</a>
    </span>
    <div class="box">
      <div class="box-header">
        <h4 class="box-title">Add Vehicle</h4>
      </div>
      <div class="box-body">
        <aside>
          <el-form ref="form" :model="form" label-width="120px">
            <el-row :gutter="5" class="padded">
              <el-col :xs="24" :sm="12" :md="12">
                <label for="">Select Warehouse</label>
                <el-select v-model="form.warehouse_id" placeholder="Select Warehouse" filterable class="span">
                  <el-option v-for="(warehouse, index) in params.warehouses" :key="index" :value="warehouse.id" :label="warehouse.name" />

                </el-select>

                <label for="">Select Vehicle Type</label>
                <el-select v-model="form.vehicle_type_id" placeholder="Select vehicle type" filterable class="span">
                  <el-option v-for="(vehicle_type, index) in params.vehicle_types" :key="index" :value="vehicle_type.id" :label="vehicle_type.type+' ['+vehicle_type.load_capacity+']'" />

                </el-select>
                <label for="">Plate No.</label>
                <el-input v-model="form.plate_no" placeholder="Plate No." class="span" />
                <label for="">VIN</label>
                <el-input v-model="form.vin" placeholder="VIN" class="span" />
                <label for="">Date of Purchase</label>
                <el-date-picker v-model="form.purchase_date" type="date" placeholder="Purchase Date" style="width: 100%;" format="yyyy/MM/dd" value-format="yyyy-MM-dd" />
              </el-col>
              <el-col :xs="24" :sm="12" :md="12">
                <label for="">Brand</label>
                <el-input v-model="form.brand" placeholder="Brand" class="span" />
                <label for="">Model</label>
                <el-input v-model="form.model" placeholder="Model" class="span" />
                <label for="">Initial Mileage</label>
                <el-input v-model="form.initial_mileage" placeholder="Initial Mileage" class="span" />
                <label for="">Engine Type</label>
                <el-select v-model="form.engine_type" placeholder="Select Engine Type" filterable class="span">
                  <el-option v-for="(engine_type, index) in params.engine_types" :key="index" :value="engine_type" :label="engine_type" />

                </el-select>
                <label for="">Extra Vehicle Information</label>
                <textarea v-model="form.notes" placeholder="Extra Notes" class="form-control" />
              </el-col>
            </el-row>
            <el-row :gutter="2" class="padded">
              <el-col :xs="24" :sm="6" :md="6">
                <el-button type="success" @click="addVehicle"><i class="el-icon-plus" />
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
const createVehicle = new Resource('logistics/vehicles/store');

export default {
  name: 'AddNewVehicle',

  props: {
    params: {
      type: Object,
      default: () => ({}),
    },
    vehicles: {
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
      form: {
        warehouse_id: '',
        vehicle_type_id: '',
        plate_no: '',
        vin: '',
        brand: '',
        model: '',
        initial_mileage: '',
        purchase_date: '',
        engine_type: '',
        notes: '',

      },
      empty_form: {
        warehouse_id: '',
        vehicle_type_id: '',
        plate_no: '',
        vin: '',
        brand: '',
        model: '',
        initial_mileage: '',
        purchase_date: '',
        engine_type: '',
        notes: '',

      },

    };
  },

  methods: {
    moment,
    addVehicle() {
      const app = this;
      var form = app.form;
      const load = createVehicle.loaderShow();
      createVehicle.store(form)
        .then(response => {
          app.$message({ message: 'New Vehicle Added Successfully!!!', type: 'success' });
          app.vehicles.push(response.vehicle);
          app.form = app.empty_form;
          app.$emit('update', response);
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

