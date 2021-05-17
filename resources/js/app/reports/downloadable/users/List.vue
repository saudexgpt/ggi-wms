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
        <!-- <template slot="action" slot-scope="scope">
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
        </template> -->
      </v-client-table>
    </div>

    <pagination
      v-show="total > 0"
      :total="total"
      :page.sync="query.page"
      :limit.sync="query.limit"
      @pagination="getList"
    />
  </div>
</template>

<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import UserResource from '@/api/user';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Permission directive

const userResource = new UserResource();
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
    return {
      list: [],
      columns: [
        'name',
        'email',
        'phone',
        'address',
        'role',
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
  },
  methods: {
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
