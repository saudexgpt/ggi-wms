<template>
  <div class="">
    <span class="">
      <a class="btn btn-danger" @click="page.option = 'list'"> Back</a>
    </span>
    <div>
      <div v-if="params" class="box">
        <div class="box-header">
          <h4 class="box-title">Make Vehicle Expenses Request</h4>
        </div>
        <div class="box-body">
          <el-form ref="form" :model="form" label-width="120px">
            <el-row :gutter="5" class="padded">
              <el-col :xs="24" :sm="12" :md="12">
                <label for="">Select Warehouse</label>
                <el-select v-model="selected_warehouse_index" placeholder="Select Warehouse" filterable class="span" @input="fetchVehicles()">
                  <el-option v-for="(warehouse, index) in params.warehouses" :key="index" :value="index" :label="warehouse.name" />

                </el-select>
                <label for="">Select Vehicle</label>
                <el-select v-model="form.vehicle_id" placeholder="Select vehicle" filterable class="span">
                  <el-option v-for="(vehicle, index) in vehicles" :key="index" :value="vehicle.id" :label="vehicle.brand+' ['+vehicle.plate_no+']'" />

                </el-select>

              </el-col>
              <el-col :xs="24" :sm="12" :md="12">
                <label for="">Select Expense Type</label>
                <el-select v-model="form.expense_type" placeholder="Select Type" filterable class="span" @input="resetServiceDetails()">
                  <el-option v-for="(expense_type, index) in params.expense_types" :key="index" :value="expense_type" :label="expense_type" />

                </el-select>
                <div v-if="form.expense_type === 'Maintenance / Repairs'">
                  <label for="">Select Automobile Mechanic (<a style="color: brown" @click="dialogFormVisible = true">Click to Add New Mechanic</a>)</label>
                  <el-select v-model="form.automobile_engineer_id" placeholder="Select Mechanic" filterable class="span">
                    <el-option v-for="(automobile_engineer, mechanic_index) in automobile_engineers" :key="mechanic_index" :value="automobile_engineer.id" :label="automobile_engineer.company_name+' ('+automobile_engineer.name+')'" />
                  </el-select>
                </div>
                <div v-if="form.expense_type !== 'Maintenance / Repairs'">
                  <label for="">Amount</label>
                  <el-input v-model="form.amount" placeholder="Amount" type="number" min="0" class="span" />
                </div>
              </el-col>
            </el-row>
            <el-row v-if="form.expense_type === 'Maintenance / Repairs'" :gutter="2" class="padded">
              <el-col>
                <div style="overflow: auto">
                  <label for="">Service Details</label>
                  <table class="table table-binvoiceed">
                    <thead>
                      <tr>
                        <th />
                        <th>Vehicle Part</th>
                        <th>Service Type</th>
                        <th>Cost ({{ params.currency }})</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(servicing_detail, index) in servicing_details" :key="index">
                        <td>
                          <span>
                            <a class="btn btn-danger btn-flat fa fa-trash" @click="removeLine(index)" />
                            <a v-if="index + 1 === servicing_details.length" class="btn btn-info btn-flat fa fa-plus" @click="addLine(index)" />
                          </span>
                        </td>
                        <td>
                          <el-input v-model="servicing_detail.vehicle_part" type="text" outline placeholder="Example: Brake Pad, Tyre, etc" min="1" @input="calculateTotal(index)" />
                        </td>
                        <td>
                          <el-select v-model="servicing_detail.service_type" placeholder="Select Service Type" filterable class="span">
                            <el-option value="Repairs">Repairs</el-option>
                            <el-option value="Replacement">Replacement</el-option>
                            <el-option value="Servicing">Servicing</el-option>
                          </el-select>
                        </td>
                        <td>
                          <el-input v-model="servicing_detail.amount" type="number" outline @input="calculateTotal(index)" />
                        </td>
                      </tr>
                      <tr v-if="fill_fields_error">
                        <td colspan="4"><label class="label label-danger">Please fill all empty fields before adding another row</label></td>
                      </tr>
                      <tr>
                        <td colspan="3" align="right"><label>Subtotal</label></td>
                        <td><label style="color: green">{{ Number(form.amount).toLocaleString() }}</label></td>
                      </tr>
                      <tr>
                        <td colspan="3" align="right"><label>Service Charge</label></td>
                        <td>
                          <el-input v-model="form.service_charge" type="number" outline min="0" @input="calculateGrandTotal()" />
                        </td>
                      </tr>
                      <tr>
                        <td colspan="3" align="right"><label>Grand Total</label></td>
                        <td><label style="color: green">{{ Number(form.grand_total).toLocaleString() }}</label></td>
                      </tr>
                      <tr>
                        <td align="right">Extra Notes</td>
                        <td colspan="3"><textarea v-model="form.details" class="form-control" rows="5" placeholder="Type extra note on this request here..." /></td>
                      </tr>
                    </tbody>

                  </table>
                </div>
              </el-col>
            </el-row>
            <el-row :gutter="2" class="padded">
              <el-col :xs="24" :sm="6" :md="6">
                <el-button type="success" @click="addVehicleExpense"><i class="el-icon-plus" />
                  Add
                </el-button>
              </el-col>
            </el-row>
          </el-form>
          <el-dialog :title="'Add New Auto Mechanic'" :visible.sync="dialogFormVisible">
            <div v-loading="userCreating" class="form-container">
              <el-form ref="newMechanic" :rules="rules" :model="newMechanic" label-position="left" label-width="150px" style="max-width: 500px;">
                <el-form-item :label="$t('user.name')" prop="name">
                  <el-input v-model="newMechanic.name" />
                </el-form-item>
                <el-form-item :label="$t('user.email')" prop="email">
                  <el-input v-model="newMechanic.email" required />
                </el-form-item>
                <el-form-item label="Phone" prop="phone_no">
                  <el-input v-model="newMechanic.phone_no" required />
                </el-form-item>
                <el-form-item label="Company Name" prop="company_name">
                  <el-input v-model="newMechanic.company_name" required />
                </el-form-item>
                <el-form-item label="Workshop Address" prop="workshop_address">
                  <textarea v-model="newMechanic.workshop_address" class="form-control" />
                </el-form-item>
              </el-form>
              <div slot="footer" class="dialog-footer">
                <el-button @click="dialogFormVisible = false">
                  {{ $t('table.cancel') }}
                </el-button>
                <el-button type="primary" @click="createMechanic()">
                  {{ $t('table.confirm') }}
                </el-button>
              </div>
            </div>
          </el-dialog>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from 'moment';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';

import Resource from '@/api/resource';
const createVehicleExpense = new Resource('logistics/vehicle-expenses/store');
// const necessaryParams = new Resource('fetch-necessary-params');
const mechanicResource = new Resource('logistics/vehicle-expenses/add-automobile-engineer');
export default {
  name: 'AddNewInvoice',
  props: {
    params: {
      type: Object,
      default: () => ({}),
    },
    vehicleExpenses: {
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
      vehicles: [],
      automobile_engineers: [],
      customer_types: [],
      dialogFormVisible: false,
      userCreating: false,
      fill_fields_error: false,
      form: {
        warehouse_id: '',
        vehicle_id: '',
        expense_type: '',
        amount: 0,
        service_charge: 0,
        grand_total: 0,
        status: 'Pending',
        details: '',
        automobile_engineer_id: '',
        servicing_details: [
          {
            item_id: '',
            quantity: 1,
            rate: null,
            tax: null,
            type: '',
            total: 0,
          },
        ],
      },
      empty_form: {
        warehouse_id: '',
        vehicle_id: '',
        expense_type: '',
        amount: '',
        status: 'Pending',
        details: '',
        automobile_engineer_id: '',
        servicing_details: [
          {
            item_id: '',
            quantity: 1,
            rate: null,
            tax: null,
            type: '',
            total: 0,
          },
        ],
      },
      servicing_details: [],
      initial_service_detail: {
        vehicle_part: null,
        service_type: 'Repairs',
        amount: 0,
      },
      newMechanic: {
        name: '',
        email: '',
        phone_no: '',
        workshop_address: '',
        company_name: '',
      },
      rules: {
        company_name: [{ required: true, message: 'Company Name is required', trigger: 'change' }],
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        email: [
          { required: true, message: 'Email is required', trigger: 'blur' },
          { type: 'email', message: 'Please input correct email address', trigger: ['blur', 'change'] },
        ],
        phone: [{ required: true, message: 'Phone is required', trigger: 'blur' }],
        workshop_address: [{ required: true, message: 'Address is required', trigger: 'blur' }],
      },
      discount_rate: 0,
      selected_warehouse_index: '',
    };
  },
  watch: {
    servicing_details() {
      this.blockRemoval = this.servicing_details.length <= 1;
    },

  },
  mounted() {
    this.addLine();
    this.automobile_engineers = this.params.automobile_engineers;
  },
  methods: {
    moment,
    checkPermission,
    checkRole,
    fetchVehicles() {
      this.vehicles = this.params.warehouses[this.selected_warehouse_index].vehicles;
      this.form.warehouse_id = this.params.warehouses[this.selected_warehouse_index].id;
    },
    addLine(index) {
      this.fill_fields_error = false;

      const checkEmptyLines = this.servicing_details.filter(detail => detail.vehicle_part === null || detail.service_type === null || detail.amount === 0);

      if (checkEmptyLines.length >= 1 && this.servicing_details.length > 0) {
        this.fill_fields_error = true;
        // this.servicing_details[index].seleted_category = true;
        return;
      } else {
        // if (this.servicing_details.length > 0)
        //     this.servicing_details[index].grade = '';

        this.servicing_details.push({
          vehicle_part: null,
          service_type: 'Repairs',
          amount: 0,
        });
      }
    },
    removeLine(detailId) {
      this.fill_fields_error = false;
      if (!this.blockRemoval) {
        this.servicing_details.splice(detailId, 1);
        this.calculateTotal(null);
      }
    },
    // fetchNecessaryParams() {
    //   const app = this;
    //   necessaryParams.list()
    //     .then(response => {
    //       app.params = response.params;
    //       app.automobile_engineers = response.params.automobile_engineers;
    //     });
    // },
    addVehicleExpense() {
      const app = this;
      var form = app.form;
      const load = createVehicleExpense.loaderShow();
      form.servicing_details = app.servicing_details;
      // form.service_date = app.moment(form.service_date).format('LLL');
      createVehicleExpense.store(form)
        .then(response => {
          app.$message({ message: 'Request added Successfully!!!', type: 'success' });
          app.vehicleExpenses.push(response.vehicle_expense);
          app.form = app.empty_form;
          load.hide();
        })
        .catch(error => {
          load.hide();
          alert(error.message);
        });
    },
    createMechanic() {
      this.$refs['newMechanic'].validate((valid) => {
        if (valid) {
          this.userCreating = true;
          mechanicResource
            .store(this.newMechanic)
            .then(response => {
              this.$message({
                message: 'New Mechanic ' + this.newMechanic.name + '(' + this.newMechanic.email + ') has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.automobile_engineers.push(response.automobile_engineer);
              this.resetNewMechanic();
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
    resetNewMechanic() {
      this.newMechanic = {
        name: '',
        email: '',
        phone_no: '',
        workshop_address: '',
        company_name: '',
      };
    },
    resetServiceDetails() {
      this.servicing_details = [this.initial_service_detail];
    },
    calculateTotal(index) {
      const app = this;
      let subtotal = 0;
      for (let count = 0; count < app.servicing_details.length; count++) {
        subtotal += parseFloat(app.servicing_details[count].amount);
      }
      // app.form.tax = total_tax.toFixed(2);
      app.form.subtotal = subtotal.toFixed(2);
      app.form.amount = parseFloat(subtotal).toFixed(2);

      app.calculateGrandTotal();
    },
    calculateGrandTotal() {
      const app = this;
      app.form.grand_total = parseFloat(app.form.service_charge) + parseFloat(app.form.amount);
    },

  },
};
</script>

