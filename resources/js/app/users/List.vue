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
            <el-select
              v-model="query.role"
              :placeholder="$t('table.role')"
              clearable
              style="width: 90px"
              class="filter-item"
              @change="handleFilter"
            >
              <el-option
                v-for="role in roles"
                :key="role.name"
                :label="role.name | uppercaseFirst"
                :value="role.name"
              />
            </el-select>
            <!-- <el-button round v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>-->
            <el-button
              v-if="canAddNew"
              round
              class="filter-item"
              style="margin-left: 10px"
              type="primary"
              icon="el-icon-plus"
              @click="handleCreate"
            >{{ $t('table.add') }}
            </el-button>
            <el-button
              v-waves
              round
              :loading="downloading"
              class="filter-item"
              type="primary"
              icon="el-icon-download"
              @click="handleDownload"
            >{{ $t('table.export') }}</el-button>
          </span>
        </el-col>
      </el-row>
    </div>
    <div v-loading="load_table">
      <v-client-table
        v-if="list.length > 0"
        v-model="list"
        :columns="columns"
        :options="options"
      >
        <template slot="role" slot-scope="scope">
          <span :id="scope.row.id">{{ scope.row.roles.join(', ') }}</span>
        </template>
        <template slot="assign_role" slot-scope="{ row }">
          <el-select
            v-if="!row.roles.includes('admin')"
            v-model="row.new_role"
            class="filter-item"
            placeholder="Please select role"
            @change="assignUserRole(row, $event)"
          >
            <el-option
              v-for="role in defaultRoles"
              :key="role.name"
              :label="role.name | uppercaseFirst"
              :value="role.name"
            />
          </el-select>
        </template>
        <template slot="action" slot-scope="scope">
          <el-tooltip
            class="item"
            effect="dark"
            content="Edit User"
            placement="top-start"
          >
            <router-link
              v-if="!scope.row.roles.includes('admin')"
              :to="'/administrator/users/edit/' + scope.row.id"
            >
              <el-button
                v-permission="['manage user']"
                round
                type="primary"
                size="small"
                icon="el-icon-edit"
              />
            </router-link>
          </el-tooltip>
          <el-tooltip
            class="item"
            effect="dark"
            content="Manage Permission"
            placement="top-start"
          >
            <el-button
              v-if="!scope.row.roles.includes('admin')"
              v-permission="['manage permission']"
              round
              type="success"
              size="small"
              icon="el-icon-user"
              @click="handleEditPermissions(scope.row.id)"
            />
          </el-tooltip>
          <el-tooltip
            class="item"
            effect="dark"
            content="Reset Password"
            placement="top-start"
          >
            <el-button
              v-if="!scope.row.roles.includes('admin')"
              v-permission="['manage user']"
              round
              type="warning"
              size="small"
              icon="el-icon-key"
              @click="resetUserPassword(scope.row.id, scope.row.name)"
            />
          </el-tooltip>
          <el-tooltip
            class="item"
            effect="dark"
            content="Delete User"
            placement="top-start"
          >
            <el-button
              v-if="!scope.row.roles.includes('admin')"
              v-permission="['manage user']"
              round
              type="danger"
              size="small"
              icon="el-icon-delete"
              @click="handleDelete(scope.index, scope.row.id, scope.row.name)"
            />
          </el-tooltip>
        </template>
      </v-client-table>
    </div>

    <!-- <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="ID" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Name">
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Email">
        <template slot-scope="scope">
          <span>{{ scope.row.email }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Phone">
        <template slot-scope="scope">
          <span>{{ scope.row.phone }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Address">
        <template slot-scope="scope">
          <span>{{ scope.row.address }}</span>
        </template>
      </el-table-column>
      <el-table-column align="center" label="Role" width="120">
        <template slot-scope="scope">
          <span>{{ scope.row.roles.join(', ') | uppercaseFirst }}</span>
        </template>
      </el-table-column>

      <el-table-column v-if="canAddNew" align="center" label="Actions" width="250">
        <template slot-scope="scope">
          <router-link v-if="!scope.row.roles.includes('admin')" :to="'/administrator/users/edit/'+scope.row.id">
            <el-button round v-permission="['manage user']" type="primary" size="small" icon="el-icon-edit">
              Edit
            </el-button>
          </router-link>
          <el-button round v-if="!scope.row.roles.includes('admin')" v-permission="['manage permission']" type="warning" size="small" icon="el-icon-edit" @click="handleEditPermissions(scope.row.id);">
            Permissions
          </el-button>
          <el-button round v-if="scope.row.roles.includes('visitor')" v-permission="['manage user']" type="danger" size="small" icon="el-icon-delete" @click="handleDelete(scope.row.id, scope.row.name);">
            Delete
          </el-button>
        </template>
      </el-table-column>
    </el-table>-->
    <el-row :gutter="20">
      <pagination
        v-show="total > 0"
        :total="total"
        :page.sync="query.page"
        :limit.sync="query.limit"
        @pagination="getList"
      />
    </el-row>

    <el-dialog
      :visible.sync="dialogPermissionVisible"
      :title="'Edit Permissions - ' + currentUser.name"
    >
      <div
        v-if="currentUser.name"
        v-loading="dialogPermissionLoading"
        class="form-container"
      >
        <div class="permissions-container">
          <div class="block">
            <el-form
              :model="currentUser"
              label-width="80px"
              label-position="top"
            >
              <el-form-item label="Menus">
                <el-tree
                  ref="menuPermissions"
                  :data="normalizedMenuPermissions"
                  :default-checked-keys="permissionKeys(userMenuPermissions)"
                  :props="permissionProps"
                  show-checkbox
                  node-key="id"
                  class="permission-tree"
                />
              </el-form-item>
            </el-form>
          </div>
          <div class="block">
            <el-form
              :model="currentUser"
              label-width="80px"
              label-position="top"
            >
              <el-form-item label="Permissions">
                <el-tree
                  ref="otherPermissions"
                  :data="normalizedOtherPermissions"
                  :default-checked-keys="permissionKeys(userOtherPermissions)"
                  :props="permissionProps"
                  show-checkbox
                  node-key="id"
                  class="permission-tree"
                />
              </el-form-item>
            </el-form>
          </div>
          <div class="clear-left" />
        </div>
        <div style="text-align: right">
          <el-button round type="danger" @click="dialogPermissionVisible = false">{{
            $t('permission.cancel')
          }}</el-button>
          <el-button round type="primary" @click="confirmPermission">{{
            $t('permission.confirm')
          }}</el-button>
        </div>
      </div>
    </el-dialog>

    <el-dialog :title="'Create new user'" :visible.sync="dialogFormVisible">
      <div v-loading="userCreating" class="form-container">
        <el-form
          ref="userForm"
          :rules="rules"
          :model="newUser"
          label-position="left"
          label-width="150px"
          style="max-width: 500px"
        >
          <el-form-item :label="$t('user.role')" prop="role">
            <el-select
              v-model="newUser.role"
              class="filter-item"
              placeholder="Please select role"
            >
              <el-option
                v-for="role in defaultRoles"
                :key="role.name"
                :label="role.name | uppercaseFirst"
                :value="role.name"
                multiple
              />
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('user.name')" prop="name">
            <el-input v-model="newUser.name" />
          </el-form-item>
          <el-form-item :label="$t('user.email')" prop="email">
            <el-input v-model="newUser.email" />
          </el-form-item>
          <el-form-item label="Phone" prop="phone">
            <el-input v-model="newUser.phone" />
          </el-form-item>
          <el-form-item label="Address" prop="address">
            <el-input v-model="newUser.address" />
          </el-form-item>
          <el-form-item :label="$t('user.password')" prop="password">
            <el-input v-model="newUser.password" show-password />
          </el-form-item>
          <el-form-item
            :label="$t('user.confirmPassword')"
            prop="confirmPassword"
          >
            <el-input v-model="newUser.confirmPassword" show-password />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button round @click="dialogFormVisible = false">{{
            $t('table.cancel')
          }}</el-button>
          <el-button round type="primary" @click="createUser()">{{
            $t('table.confirm')
          }}</el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import UserResource from '@/api/user';
import Resource from '@/api/resource';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Permission directive
import checkPermission from '@/utils/permission'; // Permission checking

const userResource = new UserResource();
const permissionResource = new Resource('permissions');
const resetUserPasswordResource = new Resource('users/reset-password');
const deleteUserResource = new Resource('users');
const assignRoleResource = new Resource('users/assign-role');
const necessaryParams = new Resource('fetch-necessary-params');
export default {
  name: 'UserList',
  components: { Pagination },
  directives: { waves, permission },
  props: {
    canAddNew: {
      type: Boolean,
      default: () => true,
    },
  },
  data() {
    var validateConfirmPassword = (rule, value, callback) => {
      if (value !== this.newUser.password) {
        callback(new Error('Password is mismatched!'));
      } else {
        callback();
      }
    };
    return {
      list: [],
      columns: [
        'name',
        'email',
        'phone',
        'address',
        'role',
        'assign_role',
        'action',
      ],

      options: {
        headings: {},
        pagination: {
          dropdown: true,
          chunk: 10,
        },
        perPage: 10,
        // filterByColumn: true,
        // texts: {
        //   filter: 'Search:',
        // },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['name', 'email', 'phone'],
        filterable: false, // ['name', 'email', 'phone', 'address'],
      },
      total: 0,
      loading: false,
      load_table: false,
      downloading: false,
      userCreating: false,
      query: {
        page: 1,
        limit: 10,
        keyword: '',
        role: '',
      },
      roles: [],
      defaultRoles: [],
      newUser: {},
      dialogFormVisible: false,
      dialogPermissionVisible: false,
      dialogPermissionLoading: false,
      currentUserId: 0,
      currentUser: {
        name: '',
        permissions: [],
        rolePermissions: [],
      },
      rules: {
        role: [
          { required: true, message: 'Role is required', trigger: 'change' },
        ],
        name: [
          { required: true, message: 'Name is required', trigger: 'blur' },
        ],
        phone: [
          { required: true, message: 'Phone is required', trigger: 'blur' },
        ],
        address: [
          { required: true, message: 'Address is required', trigger: 'blur' },
        ],
        email: [
          { required: true, message: 'Email is required', trigger: 'blur' },
          {
            type: 'email',
            message: 'Please input correct email address',
            trigger: ['blur', 'change'],
          },
        ],
        password: [
          { required: true, message: 'Password is required', trigger: 'blur' },
        ],
        confirmPassword: [
          { validator: validateConfirmPassword, trigger: 'blur' },
        ],
      },
      permissionProps: {
        children: 'children',
        label: 'name',
        disabled: 'disabled',
      },
      permissions: [],
      menuPermissions: [],
      otherPermissions: [],
      new_role: '',
    };
  },
  computed: {
    normalizedMenuPermissions() {
      let tmp = [];
      this.currentUser.permissions.role.forEach((permission) => {
        tmp.push({
          id: permission.id,
          name: permission.name,
          disabled: true,
        });
      });
      const rolePermissions = {
        id: -1, // Just a faked ID
        name: 'Inherited from role',
        disabled: true,
        children: this.classifyPermissions(tmp).menu,
      };

      tmp = this.menuPermissions.filter(
        (permission) =>
          !this.currentUser.permissions.role.find((p) => p.id === permission.id)
      );
      const userPermissions = {
        id: 0, // Faked ID
        name: 'Extra menus',
        children: tmp,
        disabled: tmp.length === 0,
      };

      return [rolePermissions, userPermissions];
    },
    normalizedOtherPermissions() {
      let tmp = [];
      this.currentUser.permissions.role.forEach((permission) => {
        tmp.push({
          id: permission.id,
          name: permission.name,
          disabled: true,
        });
      });
      const rolePermissions = {
        id: -1,
        name: 'Inherited from role',
        disabled: true,
        children: this.classifyPermissions(tmp).other,
      };

      tmp = this.otherPermissions.filter(
        (permission) =>
          !this.currentUser.permissions.role.find((p) => p.id === permission.id)
      );
      const userPermissions = {
        id: 0,
        name: 'Extra permissions',
        children: tmp,
        disabled: tmp.length === 0,
      };

      return [rolePermissions, userPermissions];
    },
    userMenuPermissions() {
      return this.classifyPermissions(this.userPermissions).menu;
    },
    userOtherPermissions() {
      return this.classifyPermissions(this.userPermissions).other;
    },
    userPermissions() {
      return this.currentUser.permissions.role.concat(
        this.currentUser.permissions.user
      );
    },
  },
  created() {
    this.getList();
    this.fetchNecessaryParams();
    this.resetNewUser();
    if (checkPermission(['manage permission'])) {
      this.getPermissions();
    }
  },
  methods: {
    checkPermission,
    async getPermissions() {
      const { data } = await permissionResource.list({});
      const { all, menu, other } = this.classifyPermissions(data);
      this.permissions = all;
      this.menuPermissions = menu;
      this.otherPermissions = other;
    },
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list().then((response) => {
        // app.roles = response.params.all_roles;
        app.roles = response.params.default_roles;
        app.defaultRoles = response.params.default_roles;
        // if (app.warehouses.length > 0) {
        //   app.form.warehouse_id = app.warehouses[0];
        //   app.form.warehouse_index = 0;
        //   app.getWaybills();
        // }
      });
    },
    getList() {
      const { limit, page } = this.query;
      this.options.perPage = limit;
      this.load_table = true;
      userResource
        .list(this.query)
        .then((response) => {
          this.list = response.data;
          this.list.forEach((element, index) => {
            element['index'] = (page - 1) * limit + index + 1;
          });
          this.total = response.meta.total;
          this.load_table = false;
        })
        .catch((error) => {
          console.log(error);
          this.load_table = false;
        });
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleCreate() {
      this.resetNewUser();
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['userForm'].clearValidate();
      });
    },
    resetUserPassword(id, name) {
      this.$confirm(
        'This will reset the password of ' + name + '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          this.loading = true;
          resetUserPasswordResource
            .update(id)
            .then((response) => {
              // this.$store.dispatch('user/resetPasswordStatus', {
              //   p_status: 'default',
              // });
              this.$message({
                type: 'success',
                message: 'Password Changed',
              });
              alert(
                'New Password for ' + name + ' is: ' + response.new_password
              );
              this.handleFilter();
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
          deleteUserResource
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
    async handleEditPermissions(id) {
      this.currentUserId = id;
      this.dialogPermissionLoading = true;
      this.dialogPermissionVisible = true;
      const found = this.list.find((user) => user.id === id);
      const { data } = await userResource.permissions(id);
      this.currentUser = {
        id: found.id,
        name: found.name,
        permissions: data,
      };
      this.dialogPermissionLoading = false;
      this.$nextTick(() => {
        this.$refs.menuPermissions.setCheckedKeys(
          this.permissionKeys(this.userMenuPermissions)
        );
        this.$refs.otherPermissions.setCheckedKeys(
          this.permissionKeys(this.userOtherPermissions)
        );
      });
    },
    createUser() {
      this.$refs['userForm'].validate((valid) => {
        if (valid) {
          this.newUser.roles = [this.newUser.role];
          this.userCreating = true;
          userResource
            .store(this.newUser)
            .then((response) => {
              this.$message({
                message:
                  'New user ' +
                  this.newUser.name +
                  '(' +
                  this.newUser.email +
                  ') has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewUser();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch((error) => {
              console.log(error);
            })
            .finally(() => {
              this.userCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    assignUserRole(user, role) {
      this.$confirm(
        user.name + ' will be assigned the role of ' + role + '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          this.loading = true;
          assignRoleResource
            .update(user.id, { role: role })
            .then((response) => {
              this.$message({
                type: 'success',
                message: 'Role assigned',
              });
              document.getElementById(
                user.id
              ).innerHTML = response.data.roles.join(', ');
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
            message: 'Action Canceled',
          });
        });
    },
    resetNewUser() {
      this.newUser = {
        name: '',
        email: '',
        password: '',
        confirmPassword: '',
        role: '',
      };
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
      import('@/vendor/Export2Excel').then((excel) => {
        const tHeader = [
          'name',
          'email',
          'phone',
          'address',
          'role',
        ];
        const filterVal = [
          'name',
          'email',
          'phone',
          'address',
          'role',
        ];
        const data = this.formatJson(filterVal, export_data);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'user-list',
        });
        this.downloading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) =>
        filterVal.map((j) => {
          if (j === 'role') {
            return v['roles'].join(', ');
          }
          return v[j];
        })
      );
    },
    permissionKeys(permissions) {
      return permissions.map((permssion) => permssion.id);
    },
    classifyPermissions(permissions) {
      const all = [];
      const menu = [];
      const other = [];
      permissions.forEach((permission) => {
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
      return {
        id: permission.id,
        name: this.$options.filters.uppercaseFirst(
          permission.name.substring(10)
        ),
        disabled: permission.disabled || false,
      };
    },

    normalizePermission(permission) {
      const disabled =
        permission.disabled || permission.name === 'manage permission';
      return {
        id: permission.id,
        name: this.$options.filters.uppercaseFirst(permission.name),
        disabled: disabled,
      };
    },

    confirmPermission() {
      const checkedMenu = this.$refs.menuPermissions.getCheckedKeys();
      const checkedOther = this.$refs.otherPermissions.getCheckedKeys();
      const checkedPermissions = checkedMenu.concat(checkedOther);
      this.dialogPermissionLoading = true;

      userResource
        .updatePermission(this.currentUserId, {
          permissions: checkedPermissions,
        })
        .then((response) => {
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
