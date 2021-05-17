<template>
  <div class="box">
    <div class="box-header">
      <h4 class="box-title">Add Warehouse</h4>
      <span class="pull-right">
        <a class="btn btn-danger" @click="page.option = 'list'"> Back</a>
      </span>
    </div>
    <div class="box-body">
      <aside>
        <el-form ref="form" :model="form" label-width="120px">
          <el-row :gutter="5" class="padded">
            <el-col :xs="24" :sm="12" :md="12">
              <el-input v-model="form.name" placeholder="Name of warehouse" class="span" />

              <p />

              <textarea v-model="form.address" placeholder="Warehouse Address" rows="3" class="form-control" />
            </el-col>
            <el-col :xs="24" :sm="12" :md="12">
              <el-form-item label="Status">
                <el-switch v-model="form.enabled" />
              </el-form-item>
              <p />
              <el-form-item label="">
                <el-button type="success" @click="addWarehouse"><i class="el-icon-plus" />
                  Add
                </el-button>
              </el-form-item>

            </el-col>
          </el-row>

        </el-form>
      </aside>

    </div>
  </div>
</template>

<script>

import Resource from '@/api/resource';
const createWarehouse = new Resource('warehouse/store');

export default {
  name: 'AddNewWarehouse',

  props: {
    warehouses: {
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
        name: '',
        address: '',
        enabled: true,

      },
      empty_form: {
        name: '',
        address: '',
        enabled: true,

      },

    };
  },

  methods: {

    addWarehouse() {
      const app = this;
      const load = createWarehouse.loaderShow();
      createWarehouse.store(app.form)
        .then(response => {
          app.$message({ message: 'New Warehouse Added Successfully!!!', type: 'success' });
          app.warehouses.push(response.warehouse);
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

