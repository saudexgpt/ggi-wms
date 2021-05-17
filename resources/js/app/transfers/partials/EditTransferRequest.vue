<template>
  <div class>
    <div>
      <div v-if="params" class="box">
        <div class="box-header">
          <h4 class="box-title">Update Transfer Request</h4>
        </div>
        <div class="box-body">
          <el-form ref="form" :model="form" label-width="120px">
            <el-row :gutter="5" class="padded">
              <el-col :xs="24" :sm="12" :md="12">
                <label for>Request Number: </label>
                {{ form.request_number }}<br>
                <label for>Warehouse</label>
                <p v-if="form.request_warehouse">{{ form.request_warehouse.name }}</p>
                <label for>Date</label>
                <p>{{ moment(form.created_at).format('MMMM Do YYYY, h:mm:ss a') }}</p>

              </el-col>
              <el-col :xs="24" :sm="12" :md="12">

                <label for>Supplying Warehouse</label>
                <el-select v-model="form.supply_warehouse_id">
                  <el-option
                    v-for="(warehouse, warehouse_index) in params.warehouses"
                    :key="warehouse_index"
                    :value="warehouse.id"
                    :label="warehouse.name"
                    :disabled="warehouse.id === form.request_warehouse.id"
                  />
                </el-select>

              </el-col>
            </el-row>
            <div v-if="invoice.transfer_waybill_items.length < 1">
              <el-row :gutter="2" class="padded">
                <el-col>
                  <div style="overflow: auto">
                    <label for>Products</label>
                    <table class="table table-binvoiceed">
                      <thead>
                        <tr>
                          <th />
                          <th>Product</th>
                          <th>Quantity</th>
                          <th>Per</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(invoice_item, index) in transfer_request_items" :key="index">
                          <td>{{ index + 1 }}</td>
                          <td>
                            <span v-if="invoice_item.item">{{ invoice_item.item.name }}</span>
                            <el-select v-model="invoice_item.item_index" filterable placeholder="Change" @input="fetchItemDetails(index)">
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
                              @input="calculateNoOfCartons(index)"
                            />
                            ({{ invoice_item.no_of_cartons }} CTN)
                          </td>
                          <td>{{ invoice_item.type }}</td>
                        </tr>
                        <tr v-if="fill_fields_error">
                          <td colspan="5">
                            <label
                              class="label label-danger"
                            >Please fill all empty fields before adding another row</label>
                          </td>
                        </tr>
                        <tr>
                          <td align="right">Notes</td>
                          <td colspan="5">{{ form.notes }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </el-col>
              </el-row>
            </div>
            <div v-else>
              <el-row :gutter="2" class="padded">
                <el-col>
                  <div style="overflow: auto">
                    <label for>Products</label>
                    <table class="table table-binvoiceed">
                      <thead>
                        <tr>
                          <th />
                          <th>Product</th>
                          <th>Quantity</th>
                          <th>Per</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(invoice_item, index) in form.transfer_request_items" :key="index">
                          <td>{{ index + 1 }}</td>
                          <td>
                            <span v-if="invoice_item.item">{{ invoice_item.item.name }}</span>
                          </td>
                          <td>{{ invoice_item.quantity }}({{ invoice_item.no_of_cartons }} CTN)</td>
                          <td>{{ invoice_item.rate }}</td>
                          <td>{{ invoice_item.type }}</td>
                          <td align="right">{{ Number(invoice_item.amount).toLocaleString() }}</td>
                        </tr>
                        <tr v-if="fill_fields_error">
                          <td colspan="5">
                            <label
                              class="label label-danger"
                            >Please fill all empty fields before adding another row</label>
                          </td>
                        </tr>
                        <tr>
                          <td align="right">Notes</td>
                          <td colspan="5">{{ form.notes }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </el-col>
              </el-row>
            </div>
            <el-row :gutter="2" class="padded">
              <el-col :xs="24" :sm="6" :md="6">
                <el-button type="success" @click="updateInvoice">
                  <i class="el-icon-edit" />
                  Update Invoice
                </el-button>
              </el-col>
            </el-row>
          </el-form>
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
const editInvoice = new Resource('transfers/general/update');
const necessaryParams = new Resource('fetch-necessary-params');
const getCustomers = new Resource('fetch-customers');
// const fetchProductBatches = new Resource(
//   'stock/items-in-stock/product-batches'
// );
export default {
  // name: 'AddNewInvoice',
  props: {
    invoice: {
      type: Object,
      default: () => ({}),
    },
    page: {
      type: Object,
      default: () => ({
        option: 'edit_invoice',
      }),
    },
    params: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
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
        request_number: '',
        status: 'pending',
        invoice_date: '',
        subtotal: 0,
        discount: 0,
        amount: 0,
        notes: '',
        request_warehouse: '',
        transfer_request_items: [
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
        customer_id: '',
        request_number: '',
        status: 'pending',
        invoice_date: '',
        subtotal: 0,
        discount: 0,
        amount: 0,
        notes: '',
        transfer_request_items: [
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
      transfer_request_items: [],
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
        customer_type_id: [
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
    transfer_request_items() {
      this.blockRemoval = this.transfer_request_items.length <= 1;
    },
  },
  mounted() {
    this.form = this.invoice;
    this.transfer_request_items = this.invoice.transfer_request_items;
    // this.fetchCustomers();
    // this.addLine();
  },
  methods: {
    moment,
    checkPermission,
    checkRole,
    addLine(index) {
      this.fill_fields_error = false;

      const checkEmptyLines = this.transfer_request_items.filter(
        (detail) =>
          detail.item_id === '' ||
          detail.quantity === '' ||
          detail.rate === null ||
          detail.tax === null ||
          detail.total === 0
      );

      if (checkEmptyLines.length >= 1 && this.transfer_request_items.length > 0) {
        this.fill_fields_error = true;
        // this.transfer_request_items[index].seleted_category = true;
        return;
      } else {
        // if (this.transfer_request_items.length > 0)
        //     this.transfer_request_items[index].grade = '';

        this.transfer_request_items.push({
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
        this.transfer_request_items.splice(detailId, 1);
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
    updateInvoice() {
      const app = this;
      var form = app.form;
      const checkEmptyFields =
        form.warehouse_id === '' ||
        form.customer_id === '' ||
        form.invoice_date === '' ||
        form.currency_id === '' ||
        form.invoice_number === '';
      if (!checkEmptyFields) {
        const load = editInvoice.loaderShow();
        form.transfer_request_items = app.transfer_request_items;
        editInvoice
          .update(form.id, form)
          .then((response) => {
            app.$message({
              message: 'Request Updated Successfully!!!',
              type: 'success',
            });
            // app.form = app.empty_form;
            app.$emit('update', response.transfer_request);
            load.hide();
            app.page.option = 'list';
          })
          .catch((error) => {
            load.hide();
            console.log(error.message);
          });
      } else {
        alert('Please fill the form fields completely');
      }
    },
    fetchItemDetails(index) {
      const app = this;
      const item_index = app.transfer_request_items[index].item_index;
      const item = app.params.items[item_index];
      app.transfer_request_items[index].rate = item.price.sale_price;
      app.transfer_request_items[index].item_id = item.id;
      app.transfer_request_items[index].item = item;
      app.transfer_request_items[index].type = item.package_type;
      app.calculateNoOfCartons(index);
    },
    calculateNoOfCartons(index) {
      const app = this;
      if (index !== null) {
        const item_index = app.transfer_request_items[index].item_index;
        const item = app.params.items[item_index];
        const quantity = app.transfer_request_items[index].quantity;
        const quantity_per_carton =
          item.quantity_per_carton;
        if (quantity_per_carton > 0) {
          const no_of_cartons = quantity / quantity_per_carton;
          app.transfer_request_items[index].no_of_cartons = no_of_cartons; // + parseFloat(tax);
        }
      }
    },
  },
};
</script>

