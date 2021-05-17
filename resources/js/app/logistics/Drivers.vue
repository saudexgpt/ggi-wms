<template>
  <div class="app-container">
    <span v-if="page.option==='list'">
      <a v-if="checkPermission(['manage drivers'])" class="btn btn-default" @click="edit_driver= false; dialogFormVisible = true; resetNewDriver(); form_title = 'Add New Driver'"><i class="el-icon-s-custom" /> Add New Driver</a>
      <el-dialog :title="form_title" :visible.sync="dialogFormVisible">
        <div v-loading="userCreating" class="form-container">
          <el-form ref="form" :rules="rules" :model="form" label-position="left">
            <el-row :gutter="5" class="padded">
              <el-col :xs="24" :sm="12" :md="12">
                <el-form-item prop="name">
                  <label>{{ $t('user.name') }}</label>
                  <el-input v-model="form.name" prop="name" />
                </el-form-item>
                <el-form-item prop="email">
                  <label>{{ $t('user.email') }}</label>
                  <el-input v-model="form.email" required />
                </el-form-item>
                <el-form-item prop="phone">
                  <label>Phone</label>
                  <el-input v-model="form.phone" required />
                </el-form-item>
                <el-form-item prop="address">
                  <label>Address</label>
                  <textarea v-model="form.address" class="form-control" />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="12" :md="12">
                <el-form-item prop="employee_no">
                  <label>Employee No.</label>
                  <el-input v-model="form.employee_no" />
                </el-form-item>
                <el-form-item prop="license_no">
                  <label>Driver's License No.</label>
                  <el-input v-model="form.license_no" required />
                </el-form-item>
                <el-form-item prop="license_issue_date">
                  <label>License Issue Date</label>
                  <el-date-picker v-model="form.license_issue_date" required style="width: 100%;" />
                </el-form-item>
                <el-form-item prop="license_expiry_date">
                  <label>License Expiry Date</label>
                  <el-date-picker v-model="form.license_expiry_date" required style="width: 100%;" />
                </el-form-item>
                <el-form-item prop="emergency_contact_details">
                  <label>Emergency Contact Details</label>
                  <textarea v-model="form.emergency_contact_details" class="form-control" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-form>
          <div slot="footer" class="dialog-footer">
            <el-button @click="dialogFormVisible = false">
              {{ $t('table.cancel') }}
            </el-button>
            <el-button v-if="edit_driver === false" type="primary" @click="createDriver()">
              Save
            </el-button>
            <el-button v-else type="primary" @click="updateDriver()">
              Update
            </el-button>
          </div>
        </div>
      </el-dialog>
    </span>
    <div>
      <!-- <driver-details v-if="page.option== 'view_details'" :driver-in-stock="driver" :page="page" /> -->
      <!-- <add-new v-if="page.option== 'add_new'" :drivers="drivers" :params="params" :page="page" />
      <edit-driver v-if="page.option=='edit_driver'" :drivers="drivers" :driver="driver" :params="params" :page="page" @update="onEditUpdate" /> -->
      <div v-if="page.option==='list'" class="box">
        <div class="box-header">
          <h4 class="box-title">List of Drivers</h4>

        </div>
        <div class="box-body">
          <v-client-table v-model="drivers" :columns="columns" :options="options">
            <div slot="action" slot-scope="props">
              <a v-if="checkPermission(['manage drivers'])" class="btn btn-primary" @click="setEdit(props.index, props.row)"><i class="fa fa-edit" /> </a>
              <a v-if="checkPermission(['manage drivers'])" class="btn btn-danger" @click="confirmDelete(props)"><i class="fa fa-trash" /> </a>

              <!-- <a class="btn btn-default" @click="driver=props.row; page.option = 'view_details'"><i class="fa fa-eye" /> </a> -->
              <!-- <a class="btn btn-warning" @click="driver=props.row; selected_row_index=props.index; page.option = 'edit_driver'"><i class="fa fa-edit" /> </a>
              <a class="btn btn-danger" @click="confirmDelete(props)"><i class="fa fa-trash" /> </a> -->
            </div>

          </v-client-table>

        </div>

      </div>

    </div>
  </div>
</template>
<script>
import moment from 'moment';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';

// import AddNew from './partials/AddNewDriver';
// import EditDriver from './partials/EditDriver';
import Resource from '@/api/resource';
const driverList = new Resource('logistics/drivers');
const deleteDriver = new Resource('logistics/drivers/delete');
const driverResource = new Resource('user/driver/store');
const updateDriverResource = new Resource('user/driver/update');
export default {
  // components: { AddNew, EditDriver },
  data() {
    return {
      dialogFormVisible: false,
      drivers: [],
      columns: ['action', 'user.name', 'user.phone', 'user.email', 'employee_no', 'license_no'],

      options: {
        headings: {
          'user.name': 'Name',
          'user.phone': 'Phone No.',
          'user.email': 'Email',
          employee_no: 'Employee No.',
          license_no: 'License No.',
          action: 'Action',
          // id: 'S/N',
        },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['user.name', 'user.phone', 'user.email', 'employee_no', 'license_no'],
        filterable: ['user.name', 'user.phone', 'user.email', 'employee_no', 'license_no'],
      },
      page: {
        option: 'list',
      },
      params: {},
      edit_driver: false,
      selected_row_index: '',
      form_title: 'Add New Driver',
      form: {
        id: '',
        name: '',
        email: '',
        phone: '',
        address: '',
        role: 'driver',
        license_no: '',
        license_issue_date: '',
        license_expiry_date: '',
        employee_no: '',
        emergency_contact_details: '',
        password: '',
        confirmPassword: '',
      },
      userCreating: false,
      rules: {
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        email: [
          { required: true, message: 'Email is required', trigger: 'blur' },
          { type: 'email', message: 'Please input correct email address', trigger: ['blur', 'change'] },
        ],
        phone: [{ required: true, message: 'Phone is required', trigger: 'blur' }],
        employee_no: [{ required: true, message: 'Employee No. is required', trigger: 'blur' }],
        license_no: [{ required: true, message: 'Driver License is required', trigger: 'blur' }],
        license_issue_date: [{ required: true, message: 'License issue date is required', trigger: 'blur' }],
        license_expiry_date: [{ required: true, message: 'License expiry date is required', trigger: 'blur' }],
      },

    };
  },

  mounted() {
    this.fetchDrivers();
  },
  beforeDestroy() {

  },
  methods: {
    moment,
    checkPermission,
    checkRole,
    fetchDrivers() {
      const app = this;
      const loader = driverList.loaderShow();
      driverList.list()
        .then(response => {
          app.drivers = response.drivers;
          loader.hide();
        })
        .catch(error => {
          loader.hide();
          console.log(error.message);
        });
    },

    setEdit(selected_driver_index, driver) {
      const app = this;
      app.selected_row_index = selected_driver_index - 1;
      app.form_title = 'Edit Driver';
      app.form.id = driver.id;
      app.form.name = driver.user.name;
      app.form.email = driver.user.email;
      app.form.phone = driver.user.phone;
      app.form.address = driver.user.address;
      app.form.role = 'driver';
      app.form.license_no = driver.license_no;
      app.form.license_issue_date = driver.license_issue_date;
      app.form.license_expiry_date = driver.license_expiry_date;
      app.form.employee_no = driver.employee_no;
      app.form.emergency_contact_details = driver.emergency_contact_details;
      app.edit_driver = true;
      app.dialogFormVisible = true;
    },
    confirmDelete(props) {
      // this.loader();

      const row = props.row;
      const app = this;
      const message = 'This delete action cannot be undone. Click OK to confirm';
      if (confirm(message)) {
        deleteDriver.destroy(row.id, row)
          .then(response => {
            app.drivers.splice(row.index - 1, 1);
            this.$message({
              message: 'Driver has been deleted',
              type: 'success',
            });
          })
          .catch(error => {
            console.log(error);
          });
      }
    },
    updateDriver() {
      const app = this;
      app.$refs['form'].validate((valid) => {
        if (valid) {
          app.form.roles = [app.form.role];
          app.form.license_issue_date = app.moment(app.form.license_issue_date).format('LLL');
          app.form.license_expiry_date = app.moment(app.form.license_expiry_date).format('LLL');
          app.userCreating = true;
          updateDriverResource
            .update(app.form.id, app.form)
            .then(response => {
              app.drivers[app.selected_row_index] = response.driver;
              app.$message({
                message: 'Driver updated successfully',
                type: 'success',
                // duration: 5 * 1000,
              });
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              app.userCreating = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    createDriver() {
      this.$refs['form'].validate((valid) => {
        if (valid) {
          this.form.roles = [this.form.role];
          this.form.password = this.form.phone; // set password as phone
          this.form.confirmPassword = this.form.phone;
          this.form.license_issue_date = this.moment(this.form.license_issue_date).format('LLL');
          this.form.license_expiry_date = this.moment(this.form.license_expiry_date).format('LLL');
          this.userCreating = true;
          driverResource
            .store(this.form)
            .then(response => {
              this.$message({
                message: 'New user ' + this.form.name + '(' + this.form.email + ') has been created successfully.',
                type: 'success',
                // duration: 5 * 1000,
              });
              this.drivers.push(response.driver);
              this.resetNewDriver();
              this.dialogFormVisible = false;
            })
            .catch(error => {
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
    resetNewDriver() {
      this.form = {
        name: '',
        email: '',
        phone: '',
        address: '',
        role: 'driver',
        license_no: '',
        license_issue_date: '',
        license_expiry_date: '',
        employee_no: '',
        emergency_contact_details: '',
        password: '',
        confirmPassword: '',
      };
    },
  },
};
</script>
