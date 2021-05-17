<template>
  <div class="box">
    <div class="box-header">
      <h4 class="box-title">Warehouse Details</h4>
      <span class="pull-right">
        <a class="btn btn-danger" @click="page.option = 'list'"> Back</a>
      </span>
    </div>
    <div class="box-body">
      <aside>
        <el-form ref="form" :model="form" label-width="120px">
          <el-row :gutter="5">
            <el-col :xs="24" :sm="12" :md="12">
              <label for="">Select Users to assign to Warehouse</label>
              <el-select v-model="form.user_ids" multiple="true">
                <el-option v-for="(user, index) in users" :key="index" :value="user.id" :label="user.name" />
              </el-select>
            </el-col>
            <el-col :xs="24" :sm="12" :md="12">
              <el-form-item label="">
                <el-button type="success" @click="assignUsers"><svg-icon icon-class="edit" />
                  Assign
                </el-button>
              </el-form-item>

            </el-col>
          </el-row>
        </el-form>
      </aside>
      <legend>Assigned Users</legend>
      <v-client-table v-model="warehouse.users" :columns="columns" />
    </div>
  </div>
</template>

<script>
import Resource from '@/api/resource';
// import Vue from 'vue';
const assignUsersToWarehouse = new Resource('warehouse/add-user-to-warehouse');
export default {
  // name: 'WarehouseDetails',

  props: {
    warehouses: {
      type: Array,
      default: () => ([]),
    },
    warehouse: {
      type: Object,
      default: () => ({}),
    },
    page: {
      type: Object,
      default: () => ({
        option: 'warehouse_details',
      }),
    },
    users: {
      type: Array,
      default: () => ([]),
    },

  },
  data() {
    return {
      columns: ['name', 'email', 'phone', 'address'],

      form: {
        warehouse_id: this.warehouse.id,
        user_ids: [],

      },

    };
  },
  mounted() {
    this.form.warehouse_id = this.warehouse.id;
    if (this.form.enabled === 1) {
      this.form.enabled = true;
    } else {
      this.form.enabled = false;
    }
  },
  methods: {

    assignUsers() {
      const app = this;
      assignUsersToWarehouse.store(app.form)
        .then(response => {
          app.$message({ message: 'Warehouse Updated Successfully!!!', type: 'success' });
          app.warehouse.users = response.warehouse_users;
        })
        .catch(error => {
          alert(error.message);
        });
    },

  },
};
</script>

