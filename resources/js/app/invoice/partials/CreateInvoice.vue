<template>
  <div class="app-container">
    <span v-if="params">
      <router-link
        v-if="checkPermission(['view invoice'])"
        :to="{name:'Invoices'}"
        class="btn btn-default"
      >View Invoices</router-link>
    </span>

    <div>
      <div v-if="params" class="box">
        <div class="box-header">
          <h4 class="box-title">Create New Invoice</h4>
          <span class="pull-right">
            <a
              v-if="checkPermission(['create invoice']) && upload_type ==='normal'"
              class="btn btn-success"
              @click="upload_type ='bulk'"
            >Bulk Upload</a>
            <a
              v-if="checkPermission(['create invoice']) && upload_type ==='bulk'"
              class="btn btn-primary"
              @click="upload_type ='normal'"
            >Normal Upload</a>
            <router-link
              v-if="checkPermission(['view invoice'])"
              :to="{name:'Invoices'}"
              class="btn btn-danger"
            >Cancel</router-link>

          </span>
        </div>

        <div v-if="upload_type ==='bulk'" class="box-body">
          <bulk-invoice-upload
            :params="params"
            :customers="customers"
            @created="onCreateUpdate"
          />
        </div>
        <div v-else class="box-body">
          <el-form ref="form" :model="form" label-width="120px">
            <el-row :gutter="5" class="padded">
              <el-col :xs="24" :sm="12" :md="12">
                <label for>Invoice Number</label>
                <el-input
                  v-model="form.invoice_number"
                  placeholder="Will be auto generated when submitted"
                  class="span"
                  disabled
                />
                <label for>Select Warehouse</label>
                <el-select
                  v-model="form.warehouse_id"
                  placeholder="Select Warehouse"
                  filterable
                  class="span"
                  @input="show_product_list = true"
                >
                  <el-option
                    v-for="(warehouse, warehouse_index) in params.warehouses"
                    :key="warehouse_index"
                    :value="warehouse.id"
                    :label="warehouse.name"
                  />
                </el-select>
              </el-col>
              <el-col :xs="24" :sm="12" :md="12">
                <label for>
                  Select Customer (
                  <a
                    style="color: brown"
                    @click="dialogFormVisible = true"
                  >Click to Add New Customer</a>)
                </label>
                <el-select
                  v-model="form.customer_id"
                  placeholder="Select Customer"
                  filterable
                  class="span"
                >
                  <el-option
                    v-for="(customer, customer_index) in customers"
                    :key="customer_index"
                    :value="customer.id"
                    :label="(customer.user) ? customer.user.name : ''"
                  />
                </el-select>
                <label for>Invoice Date</label>
                <el-date-picker
                  v-model="form.invoice_date"
                  type="date"
                  placeholder="Invoice Date"
                  style="width: 100%;"
                  format="yyyy/MM/dd"
                  value-format="yyyy-MM-dd"
                  :picker-options="pickerOptions"
                />
              </el-col>
            </el-row>
            <div v-if="show_product_list">
              <el-row :gutter="2" class="padded">
                <el-col>
                  <div style="overflow: auto">
                    <label for>Products</label>
                    <table class="table table-binvoiceed">
                      <thead>
                        <tr>
                          <th />
                          <th>Choose Product</th>
                          <th>Quantity</th>
                          <th>Rate</th>
                          <th>Per</th>
                          <!-- <th>Specify Batch (Optional)</th> -->
                          <th>Amount</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(invoice_item, index) in invoice_items" :key="index">
                          <td>
                            <span>
                              <a
                                class="btn btn-danger btn-flat fa fa-trash"
                                @click="removeLine(index)"
                              />
                              <a
                                v-if="index + 1 === invoice_items.length"
                                class="btn btn-info btn-flat fa fa-plus"
                                @click="addLine(index)"
                              />
                            </span>
                          </td>
                          <td>
                            <el-select
                              v-model="invoice_item.item_index"
                              placeholder="Select Product"
                              filterable
                              class="span"
                              @input="fetchItemDetails(index)"
                            >
                              <el-option
                                v-for="(item, item_index) in params.items"
                                :key="item_index"
                                :value="item_index"
                                :label="item.name"
                              />
                            </el-select>
                          </td>
                          <td>
                            <el-input
                              v-model="invoice_item.quantity"
                              type="number"
                              outline
                              placeholder="Quantity"
                              min="1"
                              @input="calculateTotal(index); calculateNoOfCartons(index)"
                            />
                            ({{ invoice_item.no_of_cartons }} CTN)
                          </td>
                          <td>
                            <el-input
                              v-model="invoice_item.rate"
                              type="number"
                              outline
                              @input="calculateTotal(index)"
                            />
                          </td>
                          <td>{{ invoice_item.type }}</td>
                          <!-- <td>
                            <el-select
                              v-model="invoice_item.batches"
                              placeholder="Specify product batch for this supply"
                              filterable
                              class="span"
                              multiple
                              collapse-tags
                            >
                              <el-option
                                v-for="(batch, batch_index) in invoice_item.batches_of_items_in_stock"
                                :key="batch_index"
                                :value="batch.id"
                                :label="batch.batch_no + ' | ' + batch.expiry_date"
                              >
                                <span
                                  style="float: left"
                                >{{ batch.batch_no + ' | ' + batch.expiry_date }}</span>
                                <span
                                  style="float: right; color: #8492a6; font-size: 13px"
                                >({{ batch.balance - batch.reserved_for_supply }})</span>
                              </el-option>
                            </el-select>
                          </td> -->
                          <td align="right">
                            <el-input v-model="invoice_item.amount" type="hidden" outline />
                            {{ Number(invoice_item.amount).toLocaleString() }}
                          </td>
                        </tr>
                        <tr v-if="fill_fields_error">
                          <td colspan="5">
                            <label
                              class="label label-danger"
                            >Please fill all empty fields before adding another row</label>
                          </td>
                        </tr>
                        <tr>
                          <td colspan="5" align="right">
                            <label>Subtotal</label>
                          </td>
                          <td align="right">{{ Number(form.subtotal).toLocaleString() }}</td>
                        </tr>
                        <tr>
                          <td colspan="5" align="right">
                            <el-dropdown
                              class="avatar-container right-menu-item hover-effect"
                              trigger="click"
                            >
                              <div class="avatar-wrapper" style="color: brown">
                                <label style="cursor:pointer">Add Discount</label>
                              </div>
                              <el-dropdown-menu slot="dropdown" style="padding: 10px;">
                                <el-input
                                  v-model="discount_rate"
                                  type="number"
                                  min="0"
                                  style="width: 50%;"
                                  @input="calculateTotal(null)"
                                />% of Subtotal
                                <el-dropdown-item divided>Enter Discount percentage</el-dropdown-item>
                              </el-dropdown-menu>
                            </el-dropdown>
                          </td>
                          <td align="right">{{ Number(form.discount).toLocaleString() }}</td>
                        </tr>
                        <tr>
                          <td colspan="5" align="right">
                            <label>Grand Total</label>
                          </td>
                          <td align="right">
                            <label style="color: green">{{ Number(form.amount).toLocaleString() }}</label>
                          </td>
                        </tr>
                        <tr>
                          <td align="right">Notes</td>
                          <td colspan="5">
                            <textarea
                              v-model="form.notes"
                              class="form-control"
                              rows="5"
                              placeholder="Type extra note on this invoice here..."
                            />
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </el-col>
              </el-row>
              <el-row :gutter="2" class="padded">
                <el-col :xs="24" :sm="6" :md="6">
                  <el-button type="success" @click="addNewInvoice">
                    <i class="el-icon-plus" />
                    Create Invoice
                  </el-button>
                </el-col>
              </el-row>
            </div>
          </el-form>
          <add-new-customer
            :dialog-form-visible="dialogFormVisible"
            :params="params"
            @created="onCreateUpdate"
            @close="dialogFormVisible=false"
          />

        </div>
      </div>
    </div>

  </div>
</template>

<script>
import moment from 'moment';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';
import AddNewCustomer from '@/app/users/AddNewCustomer';
import BulkInvoiceUpload from './BulkInvoiceUpload';
import Resource from '@/api/resource';
const createInvoice = new Resource('invoice/general/store');
const necessaryParams = new Resource('fetch-necessary-params');
const getCustomers = new Resource('fetch-customers');
const customerResource = new Resource('user/customer/store');
const fetchProductBatches = new Resource('stock/items-in-stock/product-batches');
export default {
  name: 'AddNewInvoice',
  components: { AddNewCustomer, BulkInvoiceUpload },

  data() {
    return {
      pickerOptions: {
        disabledDate(date) {
          var d = new Date(); // today
          d.setDate(d.getDate()); // one year from now
          return date > d;
        },
      },
      upload_type: 'normal',
      params: {},
      customers: [],
      customer_types: [],
      items_in_stock_dialog: false,
      dialogFormVisible: false,
      userCreating: false,
      fill_fields_error: false,
      show_product_list: false,
      batches_of_items_in_stock: [],
      form: {
        warehouse_id: '',
        customer_id: '',
        invoice_number: '',
        status: 'pending',
        invoice_date: '',
        subtotal: 0,
        discount: 0,
        amount: 0,
        notes: '',
        invoice_items: [
          {
            item_id: '',
            quantity: 1,
            rate: null,
            tax: null,
            type: '',
            total: 0,
            batches: [],
            batches_of_items_in_stock: [],
          },
        ],
      },
      empty_form: {
        warehouse_id: '',
        customer_id: '',
        invoice_number: '',
        status: 'pending',
        invoice_date: '',
        subtotal: 0,
        discount: 0,
        amount: 0,
        notes: '',
        invoice_items: [
          {
            item_index: '',
            item_id: '',
            quantity: 1,
            type: '',
            rate: null,
            amount: 0,
          },
        ],
      },
      invoice_items: [],
      newCustomer: {
        name: '',
        email: null,
        phone: null,
        address: '',
        role: 'customer',
        customer_type_id: '',
        password: '',
        confirmPassword: '',
      },
      rules: {
        customer_type: [
          {
            required: true,
            message: 'Customer Type is required',
            trigger: 'change',
          },
        ],
        name: [
          { required: true, message: 'Name is required', trigger: 'blur' },
        ],
        // email: [
        //   { required: true, message: 'Email is required', trigger: 'blur' },
        //   { type: 'email', message: 'Please input correct email address', trigger: ['blur', 'change'] },
        // ],
        // phone: [{ required: true, message: 'Phone is required', trigger: 'blur' }],
      },
      discount_rate: 0,
    };
  },
  watch: {
    invoice_items() {
      this.blockRemoval = this.invoice_items.length <= 1;
    },
  },
  mounted() {
    this.fetchNecessaryParams();
    this.fetchCustomers();
    this.addLine();
  },
  methods: {
    moment,
    checkPermission,
    checkRole,
    addLine(index) {
      this.fill_fields_error = false;

      const checkEmptyLines = this.invoice_items.filter(
        (detail) =>
          detail.item_id === '' ||
          detail.quantity === '' ||
          detail.rate === null ||
          detail.tax === null ||
          detail.total === 0
      );

      if (checkEmptyLines.length >= 1 && this.invoice_items.length > 0) {
        this.fill_fields_error = true;
        // this.invoice_items[index].seleted_category = true;
        return;
      } else {
        // if (this.invoice_items.length > 0)
        //     this.invoice_items[index].grade = '';

        this.invoice_items.push({
          item_index: null,
          item_id: '',
          quantity: 1,
          type: '',
          rate: null,
          amount: 0,
          batches: [],
          batches_of_items_in_stock: [],
        });
      }
    },
    removeLine(detailId) {
      this.fill_fields_error = false;
      if (!this.blockRemoval) {
        this.invoice_items.splice(detailId, 1);
        this.calculateTotal(null);
      }
    },
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list().then((response) => {
        app.params = response.params;
      });
    },
    fetchCustomers() {
      const app = this;
      getCustomers.list().then((response) => {
        app.customers = response.customers;
        app.customer_types = response.customer_types;
      });
    },
    addNewInvoice() {
      const app = this;
      var form = app.form;
      const checkEmptyFielads =
        form.warehouse_id === '' ||
        form.customer_id === '' ||
        form.invoice_date === '' ||
        form.currency_id === '';
      if (!checkEmptyFielads) {
        const load = createInvoice.loaderShow();
        form.invoice_items = app.invoice_items;
        createInvoice
          .store(form)
          .then((response) => {
            app.$message({
              message: 'Invoice Created Successfully!!!',
              type: 'success',
            });
            app.form = app.empty_form;
            app.invoice_items = app.form.invoice_items;
            app.$router.push({ name: 'Invoices' });
            load.hide();
          })
          .catch((error) => {
            load.hide();
            console.log(error.message);
          });
      } else {
        alert('Please fill the form fields completely');
      }
    },

    onCreateUpdate(created_row) {
      const app = this;
      app.customers.push(created_row);
    },
    createCustomer() {
      this.$refs['newCustomer'].validate((valid) => {
        if (valid) {
          this.newCustomer.roles = [this.newCustomer.role];
          this.newCustomer.password = this.newCustomer.phone; // set password as phone
          this.newCustomer.confirmPassword = this.newCustomer.phone;
          this.userCreating = true;
          customerResource
            .store(this.newCustomer)
            .then((response) => {
              this.$message({
                message:
                  'New user ' +
                  this.newCustomer.name +
                  '(' +
                  this.newCustomer.email +
                  ') has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              this.customers.push(response.customer);
              this.resetNewCustomer();
              this.dialogFormVisible = false;
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
    resetNewCustomer() {
      this.newCustomer = {
        name: '',
        email: null,
        phone: null,
        address: '',
        role: 'customer',
        customer_type_id: '',
        password: '',
        confirmPassword: '',
      };
    },
    fetchItemDetails(index) {
      const app = this;
      const item_index = app.invoice_items[index].item_index;
      const item = app.params.items[item_index];
      app.invoice_items[index].rate = item.price.sale_price;
      app.invoice_items[index].item_id = item.id;
      app.invoice_items[index].type = item.package_type;
      app.invoice_items[index].quantity_per_carton = item.quantity_per_carton;
      app.invoice_items[index].no_of_cartons = 0;
      // let tax = 0;
      // for (let a = 0; a < item.taxes.length; a++) {
      //   tax += parseFloat(item.taxes[a].rate);
      // }
      // app.invoice_items[index].tax = tax;
      app.setProductBatches(index, app.form.warehouse_id, item.id);
      app.calculateTotal(index);
    },
    setProductBatches(index, warehouse_id, item_id) {
      const app = this;
      const param = {
        warehouse_id: warehouse_id,
        item_id: item_id,
      };
      fetchProductBatches.list(param).then((response) => {
        app.invoice_items[index].batches_of_items_in_stock =
          response.batches_of_items_in_stock;
        app.invoice_items[index].batches = [];
      });
    },
    showItemsInStock(index) {
      const app = this;
      app.batches_of_items_in_stock =
        app.invoice_items[index].batches_of_items_in_stock;
      app.items_in_stock_dialog = true;
    },
    calculateNoOfCartons(index) {
      const app = this;
      if (index !== null) {
        const quantity = app.invoice_items[index].quantity;
        const quantity_per_carton =
          app.invoice_items[index].quantity_per_carton;
        if (quantity_per_carton > 0) {
          const no_of_cartons = quantity / quantity_per_carton;
          app.invoice_items[index].no_of_cartons = no_of_cartons; // + parseFloat(tax);
        }
      }
    },
    calculateTotal(index) {
      const app = this;
      // Get total amount for this item without tax
      if (index !== null) {
        const quantity = app.invoice_items[index].quantity;
        const unit_rate = app.invoice_items[index].rate;
        app.invoice_items[index].amount = parseFloat(
          quantity * unit_rate
        ).toFixed(2); // + parseFloat(tax);
      }

      // we now calculate the running total of items invoiceed for with tax //////////
      // let total_tax = 0;
      let subtotal = 0;
      for (let count = 0; count < app.invoice_items.length; count++) {
        // const tax_rate = app.invoice_items[count].tax;
        // const quantity = app.invoice_items[count].quantity;
        // const unit_rate = app.invoice_items[count].rate;
        // total_tax += parseFloat(tax_rate * quantity * unit_rate);
        subtotal += parseFloat(app.invoice_items[count].amount);
      }
      // app.form.tax = total_tax.toFixed(2);
      app.form.subtotal = subtotal.toFixed(2);
      app.form.discount = parseFloat(
        (app.discount_rate / 100) * subtotal
      ).toFixed(2);
      // subtract discount
      app.form.amount = parseFloat(subtotal - app.form.discount).toFixed(2);
    },
  },
};
</script>

