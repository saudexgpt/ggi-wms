<template>
  <div class="app-container">
    <div class="filter-container">
      <el-row :gutter="20">
        <el-col :xs="24" :sm="12" :md="12">
          <el-input
            v-model="query.keyword"
            placeholder="Search"
            style="width: 200px"
            class="filter-item"
            @input="handleFilter"
          />
        </el-col>
        <el-col :xs="24" :sm="12" :md="12">
          <span class="pull-right">
            <el-button v-if="canAddNew" round class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="dialogFormVisible = true">
              {{ $t('table.add') }}
            </el-button>
            <el-button v-if="canAddNew" round class="filter-item" style="margin-left: 10px;" type="warning" icon="el-icon-plus" @click="bulk_upload = true">
              Upload Multiple Customers
            </el-button>
            <el-button v-waves round :loading="downloading" class="filter-item" type="danger" icon="el-icon-download" @click="handleDownload">
              {{ $t('table.export') }}
            </el-button>
          </span>
        </el-col>
      </el-row>

    </div>
    <div v-if="bulk_upload">
      <a class="btn btn-danger" @click="bulk_upload = false"> Cancel</a>
      <upload-excel-component :on-success="handleSuccess" :before-upload="beforeUpload" />
      <legend v-if="tableData.length > 0">Preview what you just uploaded and then click on the submit button. <a class="btn btn-success" @click="addBulkCustomer">Submit</a> </legend>
      <div style="height: 600px; overflow:auto;">
        <el-table :data="tableData" border highlight-current-row style="width: 100%;margin-top:20px;">
          <el-table-column v-for="item of tableHeader" :key="item" :prop="item" :label="item" />
        </el-table>
      </div>
    </div>
    <div v-else>
      <v-client-table v-model="list" v-loading="loading" :columns="columns" :options="options">
        <template slot="role" slot-scope="scope">
          <span>{{ scope.row.roles.join(', ') | uppercaseFirst }}</span>
        </template>
        <template slot="action" slot-scope="scope">
          <router-link v-if="!scope.row.roles.includes('admin')" :to="'/administrator/users/edit/'+scope.row.id">
            <el-button v-permission="['manage user']" round type="primary" size="small" icon="el-icon-edit">
              Edit
            </el-button>
          </router-link>
          <el-button v-if="! scope.row.roles.includes('admin')" v-permission="['manage user']" round type="warning" size="small" icon="el-icon-key" @click="resetUserPassword(scope.row.id, scope.row.name);">
            Reset Password
          </el-button>
          <el-button
            v-if="!scope.row.roles.includes('admin')"
            v-permission="['manage user']"
            round
            type="danger"
            size="small"
            icon="el-icon-delete"
            @click="handleDelete(scope.index,scope.row.id, scope.row.name);"
          >Delete</el-button>
        </template>

      </v-client-table>

    </div>
    <pagination
      v-show="total > 0"
      :total="total"
      :page.sync="query.page"
      :limit.sync="query.limit"
      @pagination="getList"
    />
    <add-new-customer :dialog-form-visible="dialogFormVisible" :params="params" @created="onCreateUpdate" @close="dialogFormVisible=false" />
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import UploadExcelComponent from '@/components/UploadExcel/index.vue';
import UserResource from '@/api/user';
import Resource from '@/api/resource';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Permission directive
import checkPermission from '@/utils/permission'; // Permission checking
import AddNewCustomer from './AddNewCustomer';

const userResource = new UserResource();
const resetUserPasswordResource = new Resource('users/reset-password');
const necessaryParams = new Resource('fetch-necessary-params');
const uploadBulkCustomer = new Resource('users/add-bulk-customers');
const deleteCustomerResource = new Resource('customers');
export default {
  // name: 'CustomerList',
  components: { AddNewCustomer, UploadExcelComponent, Pagination },
  directives: { waves, permission },
  props: {
    canAddNew: {
      type: Boolean,
      default: () => (true),
    },
  },
  data() {
    return {
      tableData: [],
      tableHeader: [],
      list: [],
      columns: ['name', 'email', 'phone', 'address', 'role', 'action'],

      options: {
        headings: {
        },
        pagination: {
          dropdown: true,
          chunk: 10,
        },
        perPage: 10,
        filterByColumn: false,
        texts: {
          filter: 'Search:',
        },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['name', 'email', 'phone'],
        filterable: false, // ['name', 'email', 'phone', 'address'],
      },
      total: 0,
      loading: true,
      downloading: false,
      userCreating: false,
      query: {
        page: 1,
        limit: 10,
        keyword: '',
        role: 'customer',
      },
      bulk_upload: false,
      params: {},
      roles: [],
      defaultRoles: [],
      newCustomer: {},
      dialogFormVisible: false,
      dialogPermissionVisible: false,
      dialogPermissionLoading: false,
      currentUserId: 0,
      currentUser: {
        name: '',
        permissions: [],
        rolePermissions: [],
      },
    };
  },
  computed: {
    // normalizedMenuPermissions() {
    //   let tmp = [];
    //   this.currentUser.permissions.role.forEach(permission => {
    //     tmp.push({
    //       id: permission.id,
    //       name: permission.name,
    //       disabled: true,
    //     });
    //   });
    //   const rolePermissions = {
    //     id: -1, // Just a faked ID
    //     name: 'Inherited from role',
    //     disabled: true,
    //     children: this.classifyPermissions(tmp).menu,
    //   };

    //   tmp = this.menuPermissions.filter(permission => !this.currentUser.permissions.role.find(p => p.id === permission.id));
    //   const userPermissions = {
    //     id: 0, // Faked ID
    //     name: 'Extra menus',
    //     children: tmp,
    //     disabled: tmp.length === 0,
    //   };

    //   return [rolePermissions, userPermissions];
    // },
    // normalizedOtherPermissions() {
    //   let tmp = [];
    //   this.currentUser.permissions.role.forEach(permission => {
    //     tmp.push({
    //       id: permission.id,
    //       name: permission.name,
    //       disabled: true,
    //     });
    //   });
    //   const rolePermissions = {
    //     id: -1,
    //     name: 'Inherited from role',
    //     disabled: true,
    //     children: this.classifyPermissions(tmp).other,
    //   };

    //   tmp = this.otherPermissions.filter(permission => !this.currentUser.permissions.role.find(p => p.id === permission.id));
    //   const userPermissions = {
    //     id: 0,
    //     name: 'Extra permissions',
    //     children: tmp,
    //     disabled: tmp.length === 0,
    //   };

    //   return [rolePermissions, userPermissions];
    // },
    // userMenuPermissions() {
    //   return this.classifyPermissions(this.userPermissions).menu;
    // },
    // userOtherPermissions() {
    //   return this.classifyPermissions(this.userPermissions).other;
    // },
    // userPermissions() {
    //   return this.currentUser.permissions.role.concat(this.currentUser.permissions.user);
    // },
  },
  created() {
    this.getList();
    this.fetchNecessaryParams();
  },
  methods: {
    checkPermission,
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list()
        .then(response => {
          app.roles = response.params.all_roles;
          app.defaultRoles = response.params.default_roles;
          app.params = response.params;
          // if (app.warehouses.length > 0) {
          //   app.form.warehouse_id = app.warehouses[0];
          //   app.form.warehouse_index = 0;
          //   app.getWaybills();
          // }
        });
    },
    beforeUpload(file) {
      const isLt1M = file.size / 1024 / 1024 < 1;

      if (isLt1M) {
        return true;
      }

      this.$message({
        message: 'Please do not upload files larger than 1m in size.',
        type: 'warning',
      });
      return false;
    },
    handleSuccess({ results, header }) {
      this.tableData = results;
      this.tableHeader = header;
      // console.log(results);
    },
    addBulkCustomer() {
      const app = this;
      var form = { bulk_data: app.tableData };

      const load = uploadBulkCustomer.loaderShow();
      uploadBulkCustomer.store(form)
        .then(response => {
          app.getList;
          app.$message({ message: 'Customers uploaded Successfully!!!', type: 'success' });
          // app.itemsInStock.push(response.item_in_stock);
          // app.$emit('update', response);
          load.hide();
          app.bulk_upload = false;
        });
      // .catch(error => {
      //   load.hide();
      //   console.log(error);
      //   // alert('An error occured while trying to upload bulk product. Kindly try again.');
      // });
    },
    async getList() {
      const { limit, page } = this.query;
      this.options.perPage = limit;
      this.options.pagination.chunk = limit;
      this.loading = true;
      const { data, meta } = await userResource.list(this.query);
      this.list = data;
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleCreate() {
      // this.resetNewUser();
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['userForm'].clearValidate();
      });
    },
    resetUserPassword(id, name) {
      this.$confirm('This will reset the password of ' + name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        this.loading = true;
        resetUserPasswordResource.update(id).then(response => {
          this.$store.dispatch('user/resetPasswordStatus', { p_status: 'default' });
          this.$message({
            type: 'success',
            message: 'Delete completed',
          });
          alert('New Password for ' + name + ' is: ' + response.new_password);
          this.handleFilter();
          this.loading = false;
        }).catch(error => {
          console.log(error);
          this.loading = false;
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Password Reset canceled',
        });
      });
    },
    handleDelete(index, id, name) {
      this.$confirm(
        'This will delete the account of ' + name + '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          this.loading = true;
          deleteCustomerResource
            .destroy(id)
            .then((response) => {
              this.$message({
                type: 'success',
                message: 'Delete completed',
              });
              this.list.splice(index - 1, 1);
              this.loading = false;
            })
            .catch((error) => {
              console.log(error);
              this.loading = false;
            });
        })
        .catch(() => {
          this.$message({
            type: 'info',
            message: 'Delete Action Canceled',
          });
        });
    },
    onCreateUpdate(created_row) {
      const app = this;
      app.list.push(created_row);
    },
    handleDownload(){
      // fetch all data for export
      this.query.limit = this.total;
      this.downloading = true;
      userResource.list(this.query)
        .then(response => {
          this.export(response.data);

          this.downloading = false;
        });
    },
    export(export_data) {
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['name', 'email', 'phone', 'address'];
        const filterVal = ['name', 'email', 'phone', 'address'];
        const data = this.formatJson(filterVal, export_data);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'customer-list',
        });
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => {
        if (j === 'role') {
          return v['roles'].join(', ');
        }
        return v[j];
      }));
    },
    permissionKeys(permissions) {
      return permissions.map(permssion => permssion.id);
    },
    classifyPermissions(permissions) {
      const all = []; const menu = []; const other = [];
      permissions.forEach(permission => {
        const permissionName = permission.name;
        all.push(permission);
        if (permissionName.startsWith('view menu')) {
          menu.push(this.normalizeMenuPermission(permission));
        } else {
          other.push(this.normalizePermission(permission));
        }
      });
      return { all, menu, other };
    },

    normalizeMenuPermission(permission) {
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name.substring(10)), disabled: permission.disabled || false };
    },

    normalizePermission(permission) {
      const disabled = permission.disabled || permission.name === 'manage permission';
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name), disabled: disabled };
    },

    confirmPermission() {
      const checkedMenu = this.$refs.menuPermissions.getCheckedKeys();
      const checkedOther = this.$refs.otherPermissions.getCheckedKeys();
      const checkedPermissions = checkedMenu.concat(checkedOther);
      this.dialogPermissionLoading = true;

      userResource.updatePermission(this.currentUserId, { permissions: checkedPermissions }).then(response => {
        this.$message({
          message: 'Permissions has been updated successfully',
          type: 'success',
          duration: 5 * 1000,
        });
        this.dialogPermissionLoading = false;
        this.dialogPermissionVisible = false;
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.edit-input {
  padding-right: 100px;
}
.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}
.dialog-footer {
  text-align: left;
  padding-top: 0;
  margin-left: 150px;
}
.app-container {
  flex: 1;
  justify-content: space-between;
  font-size: 14px;
  padding-right: 8px;
  .block {
    float: left;
    min-width: 250px;
  }
  .clear-left {
    clear: left;
  }
}
</style>
