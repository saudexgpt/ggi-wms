<template>
  <div class="">
    <span class="">
      <a class="btn btn-danger" @click="page.option = 'list'"> Back</a>
    </span>
    <div class="box">
      <div class="box-header">
        <h4 class="box-title">Add Vehicle Type</h4>
      </div>
      <div class="box-body">
        <aside>
          <el-form ref="form" :model="form" label-width="120px">
            <el-row :gutter="5" class="padded">
              <el-col :xs="24" :sm="12" :md="12">
                <el-input v-model="form.type" placeholder="Example: Mini Truck, Truck, etc" class="span" />

                <p />

                <el-input v-model="form.load_capacity" placeholder="Example: 20 x 20 feet" rows="3" class="span" />
              </el-col>
              <el-col :xs="24" :sm="12" :md="12">
                <el-form-item label="Status">
                  <el-switch v-model="form.is_enabled" />
                </el-form-item>
                <p />
                <el-form-item label="">
                  <el-button type="success" @click="addVehicleType"><i class="el-icon-plus" />
                    Add
                  </el-button>
                </el-form-item>

              </el-col>
            </el-row>

          </el-form>
        </aside>

      </div>
    </div>
  </div>
</template>

<script>

import Resource from '@/api/resource';
const createVehicleType = new Resource('logistics/vehicle-types/store');

export default {
  name: 'AddNewVehicleType',

  props: {
    vehicleTypes: {
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
        type: '',
        load_capacity: '',
        is_enabled: true,

      },
      empty_form: {
        type: '',
        load_capacity: '',
        is_enabled: true,

      },

    };
  },

  methods: {

    addVehicleType() {
      const app = this;
      const load = createVehicleType.loaderShow();
      createVehicleType.store(app.form)
        .then(response => {
          app.$message({ message: 'New VehicleType Added Successfully!!!', type: 'success' });
          app.vehicleTypes.push(response.vehicle_type);
          app.form = app.empty_form;
          // app.$emit('update', response);

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

