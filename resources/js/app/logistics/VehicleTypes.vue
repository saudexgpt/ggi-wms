<template>
  <div class="app-container">
    <span v-if="page.option==='list'">
      <a class="btn btn-info" @click="page.option = 'add_new'"> Add New</a>
    </span>
    <div>
      <add-new v-if="page.option === 'add_new'" :vehicle-types="vehicle_types" :page="page" />
      <div v-if="page.option === 'list'" class="box">
        <div class="box-header">
          <h4 class="box-title">List of Vehicle Types</h4>
        </div>
        <div class="box-body">
          <h4 class="alert alert-info">Click on the vehicle type you wish to edit</h4>
          <v-client-table v-model="vehicle_types" :columns="columns" :options="options">

            <div slot="type" slot-scope="{row, update, setEditing, isEditing}">
              <span v-if="!isEditing()" style="cursor:pointer" @click="setEditing(true)">
                {{ row.type }}
              </span>
              <span v-else>
                <input v-model="row.type" type="text" class="form-control" autofocus @change="update(row.type); confirmEdit(row); setEditing(false)" @blur="setEditing(false)">

              </span>
            </div>
            <div slot="load_capacity" slot-scope="{row, update, setEditing, isEditing}">
              <span v-if="!isEditing()" style="cursor:pointer" @click="setEditing(true)">
                {{ row.load_capacity }}
              </span>
              <span v-else>
                <input v-model="row.load_capacity" class="form-control" @change="update(row.load_capacity); confirmEdit(row); setEditing(false)" @blur="setEditing(false)">
              </span>
            </div>
            <div slot="is_enabled" slot-scope="{row, update, setEditing, isEditing}">
              <span v-if="!isEditing()" style="cursor:pointer" @click="setEditing(true)">
                <span v-if="row.is_enabled == '1'" class="label label-success">Enabled</span>
                <span v-else class="label label-danger">Disabled</span>

              </span>
              <span v-else>
                <el-switch v-model="row.is_enabled" @change="update(row.is_enabled); confirmEdit(row); setEditing(false)" />

              </span>
            </div>
            <!-- <div slot="action" slot-scope="props">
              <a class="btn btn-primary" @click="warehouse=props.row; page.option = 'edit_warehouse'"><i class="fa fa-edit"></i> </a>
              <a class="btn btn-danger" @click="confirmDelete(props)"><i class="fa fa-trash"></i> </a>
            </div> -->

          </v-client-table>

        </div>

      </div>

    </div>
  </div>
</template>
<script>
import AddNew from './partials/AddNewVehicleType';
import Resource from '@/api/resource';
// import Vue from 'vue';
const listVehicleTypes = new Resource('logistics/vehicle-types');
const updateVehicleType = new Resource('logistics/vehicle-types/update');
const deleteVehicleType = new Resource('logistics/vehicle-types/delete');
export default {
  components: { AddNew },
  data() {
    return {
      vehicle_types: [],
      columns: ['type', 'load_capacity', 'is_enabled'],

      options: {
        headings: {
          type: 'Type',
          load_capacity: 'Load Capacity',
          is_enabled: 'Status',
          // id: 'S/N',
        },
        editableColumns: ['type', 'load_capacity', 'is_enabled'],
        sortable: ['type', 'load_capacity'],
        filterable: ['type', 'load_capacity'],
      },
      page: {
        option: 'list',
      },

    };
  },

  mounted() {
    this.fetchVehicleTypes();
  },
  beforeDestroy() {

  },
  methods: {
    fetchVehicleTypes() {
      const app = this;
      app.show_class_teacher = true;
      const load = listVehicleTypes.loaderShow();

      const param = app.form;
      listVehicleTypes.list(param)
        .then(response => {
        // app.vehicle_types = response.vehicle_types

          app.vehicle_types = response.vehicle_types.map(v => {
            app.$set(v, 'edit', false); // https://vuejs.org/v2/guide/reactivity.html
            v.originalName = v.type; //  will be used when user click the cancel botton
            return v;
          });
          load.hide();
        })
        .catch(error => {
          load.hide();
          console.log(error);
        });
    },

    confirmEdit(row) {
      // this.loader();
      row.edit = false;
      // row.originalName = row.type;

      updateVehicleType.update(row.id, row)
        .then(response => {
          this.$message({
            message: 'Vehicle Type has been edited',
            type: 'success',
          });
        })
        .catch(error => {
          console.log(error);
        });
    },
    confirmDelete(props) {
      // this.loader();

      const row = props.row;
      const app = this;
      const message = 'This delete action cannot be undone. Click OK to confirm';
      if (confirm(message)) {
        deleteVehicleType.destroy(row.id, row)
          .then(response => {
            app.vehicle_types.splice(row.index - 1, 1);
            this.$message({
              message: 'Vehicle Type has been deleted',
              type: 'success',
            });
          })
          .catch(error => {
            console.log(error);
          });
      }
    },
  },
};
</script>
