<template>
  <div class="app-container">
    <add-new v-if="page.option== 'add_new'" :warehouses="warehouses" :page="page" />
    <warehouse-details v-if="page.option=='warehouse_details'" :warehouses="warehouses" :page="page" :warehouse="warehouse" :users="users" />
    <div v-if="page.option=='list'" class="box">
      <div class="box-header">
        <h4 class="box-title">List of Warehouses</h4>
        <span class="pull-right">
          <a class="btn btn-info" @click="page.option = 'add_new'"> Add New</a>
        </span>
      </div>
      <div class="box-body">
        <h4 class="alert alert-info">Click on the cell you wish to edit</h4>
        <v-client-table v-model="warehouses" :columns="columns" :options="options">

          <div slot="name" slot-scope="{row, update, setEditing, isEditing}">
            <span v-if="!isEditing()" style="cursor:pointer" @click="setEditing(true)">
              {{ row.name }}
            </span>
            <span v-else>
              <input v-model="row.name" type="text" class="form-control" autofocus @change="update(row.name); confirmEdit(row); setEditing(false)" @blur="setEditing(false)">

            </span>
          </div>
          <div slot="address" slot-scope="{row, update, setEditing, isEditing}">
            <span v-if="!isEditing()" style="cursor:pointer" @click="setEditing(true)">
              {{ row.address }}
            </span>
            <span v-else>
              <textarea v-model="row.address" placeholder="Warehouse Address" rows="2" class="form-control" @change="update(row.address); confirmEdit(row); setEditing(false)" @blur="setEditing(false)" />
            </span>
          </div>
          <div slot="enabled" slot-scope="{row, update, setEditing, isEditing}">
            <span v-if="!isEditing()" style="cursor:pointer" @click="setEditing(true)">
              <span v-if="row.enabled == '1'" class="label label-success">Enabled</span>
              <span v-else class="label label-danger">Disabled</span>

            </span>
            <span v-else>
              <el-switch v-model="row.enabled" @change="update(row.enabled); confirmEdit(row); setEditing(false)" />

            </span>
          </div>
          <div slot="action" slot-scope="props">
            <a class="btn btn-primary" @click="warehouse=props.row; page.option = 'warehouse_details'"><i class="fa fa-file" /> </a>
            <!-- <a class="btn btn-danger" @click="confirmDelete(props)"><i class="fa fa-trash" /> </a> -->
          </div>

        </v-client-table>

      </div>

    </div>

  </div>
</template>
<script>
import AddNew from './partials/AddNew';
import WarehouseDetails from './partials/WarehouseDetails';
import Resource from '@/api/resource';
// import Vue from 'vue';
const readWarehouse = new Resource('warehouse');
const readUsers = new Resource('warehouse/assignable-users');
const updateWarehouse = new Resource('warehouse/update');
// const deleteWarehouse = new Resource('warehouse/delete');
export default {
  components: { AddNew, WarehouseDetails },
  data() {
    return {
      warehouses: [],
      columns: ['action', 'name', 'address', 'enabled'],

      options: {
        headings: {
          name: 'Name',
          address: 'Address',
          enabled: 'Status',
          // id: 'S/N',
        },
        editableColumns: ['name', 'address', 'enabled'],
        sortable: ['name'],
        filterable: ['name'],
      },
      page: {
        option: 'list',
      },
      warehouse: {

      },
      users: [],
    };
  },

  mounted() {
    this.fetchWarehouses();
    this.fetchUsers();
  },
  beforeDestroy() {

  },
  methods: {
    fetchWarehouses() {
      const app = this;
      // let loader = Vue.$loading.show({});
      const load = readWarehouse.loaderShow();
      readWarehouse.list()
        .then(response => {
          app.warehouses = response.warehouses;
          load.hide();
          // loader.hide();
        });
    },
    fetchUsers() {
      const app = this;
      readUsers.list()
        .then(response => {
          app.users = response.assignable_users;
        });
    },
    confirmEdit(row) {
      // this.loader();
      row.edit = false;
      // row.originalName = row.name;
      const load = updateWarehouse.loaderShow();
      updateWarehouse.update(row.id, row)
        .then(response => {
          load.hide();
          this.$message({
            message: 'Update Successful',
            type: 'success',
          });
        })
        .catch(error => {
          console.log(error);
        });
    },
    // confirmDelete(props) {
    //   //this.loader();

    //   let row = props.row
    //   let app = this;
    //   let message = "This delete action cannot be undone. Click OK to confirm";
    //   if (confirm(message)) {
    //     deleteGeneralWarehouses.destroy(row.id, row)
    //     .then(response => {
    //       app.warehouses.splice(row.index-1, 1);
    //       this.$message({
    //         message: 'Warehouse has been deleted',
    //         type: 'success',
    //       });

    //     })
    //     .catch(error => {
    //       console.log(error);
    //     });
    //   }

    // },
  },
};
</script>
