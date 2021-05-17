<template>
  <div class="app-container">
    <span v-if="params">
      <router-link
        v-if="checkPermission(['view invoice'])"
        :to="{name:'TransferRequest'}"
        class="btn btn-default"
      >View Transfer Requests</router-link>
    </span>
    <div>
      <div v-if="params" class="box">
        <div class="box-header">
          <h4 class="box-title">Create New Transfer Request</h4>
          <span class="pull-right">
            <router-link
              v-if="checkPermission(['view invoice'])"
              :to="{name:'TransferRequest'}"
              class="btn btn-danger"
            >Cancel</router-link>
          </span>
        </div>
        <div class="box-body">
          <el-form ref="form" :model="form" label-width="120px">
            <el-row :gutter="5" class="padded">
              <el-col :xs="24" :sm="12" :md="12">
                <label for>Requesting Warehouse</label>
                <el-select
                  v-model="form.request_warehouse_id"
                  placeholder="Select Warehouse"
                  filterable
                  class="span"
                  @input="show_product_list = true; form.supply_warehouse_id = ''"
                >
                  <el-option
                    v-for="(warehouse, warehouse_index) in params.warehouses"
                    :key="warehouse_index"
                    :value="warehouse.id"
                    :label="warehouse.name"
                  />
                </el-select>
                <!-- <label for>Transfer Invoice Number</label>
                <el-input
                  v-model="form.request_number"
                  placeholder="Enter Transfer Invoice Number"
                  class="span"
                /> -->
              </el-col>
              <el-col :xs="24" :sm="12" :md="12">
                <label for>Supplying Warehouse</label>
                <el-select
                  v-model="form.supply_warehouse_id"
                  placeholder="Select Warehouse"
                  filterable
                  class="span"
                  @input="show_product_list = true"
                >
                  <el-option
                    v-for="(warehouse, warehouse_index) in params.all_warehouses"
                    :key="warehouse_index"
                    :value="warehouse.id"
                    :label="warehouse.name"
                    :disabled="form.request_warehouse_id===warehouse.id"
                  />
                </el-select>
                <!-- <label for>TransferRequest Date</label>
                <el-date-picker
                  v-model="form.invoice_date"
                  type="date"
                  placeholder="TransferRequest Date"
                  style="width: 100%;"
                  format="yyyy/MM/dd"
                  value-format="yyyy-MM-dd"
                /> -->
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
                          <th>Packaging</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(request_item, index) in request_items" :key="index">
                          <td>
                            <span>
                              <a
                                class="btn btn-danger btn-flat fa fa-trash"
                                @click="removeLine(index)"
                              />
                              <a
                                v-if="index + 1 === request_items.length"
                                class="btn btn-info btn-flat fa fa-plus"
                                @click="addLine(index)"
                              />
                            </span>
                          </td>
                          <td>
                            <el-select
                              v-model="request_item.item_index"
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
                              v-model="request_item.quantity"
                              type="number"
                              outline
                              placeholder="Quantity"
                              min="1"
                              @input="calculateNoOfCartons(index)"
                            />
                            ({{ request_item.no_of_cartons }} CTN)
                          </td>
                          <td>{{ request_item.type }}</td>
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
                  <el-button round type="success" @click="addNewTransferRequest">
                    <i class="el-icon-plus" />
                    Create Transfer Request
                  </el-button>
                </el-col>
              </el-row>
            </div>
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
const createTransferRequest = new Resource('transfers/general/store');
const necessaryParams = new Resource('fetch-necessary-params');
export default {
  name: 'CreateTransferRequest',

  data() {
    return {
      params: {},
      customer_types: [],
      items_in_stock_dialog: false,
      dialogFormVisible: false,
      userCreating: false,
      fill_fields_error: false,
      show_product_list: false,
      batches_of_items_in_stock: [],
      form: {
        request_number: '',
        request_warehouse_id: '',
        supply_warehouse_id: '',
        status: 'pending',
        notes: '',
        request_items: [
          {
            item_id: '',
            quantity: 1,
            type: '',
          },
        ],
      },
      empty_form: {
        request_number: '',
        request_warehouse_id: '',
        supply_warehouse_id: '',
        status: 'pending',
        notes: '',
        request_items: [
          {
            item_id: '',
            quantity: 1,
            type: '',
          },
        ],
      },
      request_items: [],
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
    request_items() {
      this.blockRemoval = this.request_items.length <= 1;
    },
  },
  mounted() {
    this.fetchNecessaryParams();
    this.addLine();
  },
  methods: {
    moment,
    checkPermission,
    checkRole,
    addLine(index) {
      this.fill_fields_error = false;

      const checkEmptyLines = this.request_items.filter(
        (detail) =>
          detail.item_id === '' ||
          detail.quantity === ''
      );

      if (checkEmptyLines.length >= 1 && this.request_items.length > 0) {
        this.fill_fields_error = true;
        // this.request_items[index].seleted_category = true;
        return;
      } else {
        // if (this.request_items.length > 0)
        //     this.request_items[index].grade = '';

        this.request_items.push({
          item_index: null,
          item_id: '',
          quantity: 1,
          type: '',
        });
      }
    },
    removeLine(detailId) {
      this.fill_fields_error = false;
      if (!this.blockRemoval) {
        this.request_items.splice(detailId, 1);
      }
    },
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list().then((response) => {
        app.params = response.params;
      });
    },
    addNewTransferRequest() {
      const app = this;
      var form = app.form;
      const checkEmptyFielads =
        // form.request_number === '' ||
        form.request_warehouse_id === '' ||
        form.supply_warehouse_id === '';
      if (!checkEmptyFielads) {
        const load = createTransferRequest.loaderShow();
        form.request_items = app.request_items;
        createTransferRequest
          .store(form)
          .then((response) => {
            app.$message({
              message: 'TransferRequest Created Successfully!!!',
              type: 'success',
            });
            app.form = app.empty_form;
            app.request_items = app.form.request_items;
            app.$router.push({ name: 'TransferRequest' });
            load.hide();
          })
          .catch((error) => {
            load.hide();
            console.log(error.message);
          });
      } else {
        app.$alert('Please fill the form fields completely');
      }
    },
    fetchItemDetails(index) {
      const app = this;
      const item_index = app.request_items[index].item_index;
      const item = app.params.items[item_index];
      app.request_items[index].item_id = item.id;
      app.request_items[index].type = item.package_type;
      app.request_items[index].quantity_per_carton = item.quantity_per_carton;
      app.request_items[index].no_of_cartons = 0;
    },
    calculateNoOfCartons(index) {
      const app = this;
      if (index !== null) {
        const quantity = app.request_items[index].quantity;
        const quantity_per_carton =
          app.request_items[index].quantity_per_carton;
        if (quantity_per_carton > 0) {
          const no_of_cartons = quantity / quantity_per_carton;
          app.request_items[index].no_of_cartons = no_of_cartons; // + parseFloat(tax);
        }
      }
    },
  },
};
</script>

