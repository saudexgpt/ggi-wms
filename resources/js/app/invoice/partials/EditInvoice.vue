<template>
  <div class>
    <div>
      <div v-if="params" class="box">
        <div class="box-header">
          <h4 class="box-title">Edit Invoice for waybill {{ invoice.waybill.waybill_no }}</h4>
        </div>
        <div class="box-body">
          <el-form ref="form" :model="form" label-width="120px">
            <!-- <el-row :gutter="5" class="padded">
              <el-col :xs="24" :sm="12" :md="12">
                <label for>Invoice Number</label>
                <el-input v-model="form.invoice_number" type="text" />
                <label for>Warehouse</label>
                <p v-if="form.warehouse">{{ form.warehouse.name }}</p>
              </el-col>
              <el-col :xs="24" :sm="12" :md="12">
                <label for>Customer</label>
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
                <p>{{ form.invoice_date }}</p>
              </el-col>
            </el-row> -->
            <div>
              <el-row :gutter="2" class="padded">
                <el-col>
                  <div style="overflow: auto">
                    <label for>PRODUCT DETAILS</label>
                    <table class="table table-binvoiceed">
                      <thead>
                        <tr>
                          <th />
                          <th>Product</th>
                          <th>Quantity</th>
                          <th>Rate</th>
                          <th>Per</th>
                          <th>Amount</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(invoice_item, index) in invoice_items" :key="index">
                          <td>{{ index + 1 }}</td>
                          <td>{{ invoice_item.item.name }}</td>
                          <td>{{ invoice_item.quantity }}</td>
                          <td>
                            <el-input
                              v-model="invoice_item.rate"
                              type="number"
                              outline
                              @input="calculateTotal(index)"
                            />
                          </td>
                          <td>{{ invoice_item.type }}</td>
                          <td align="right">
                            <el-input v-model="invoice_item.amount" type="hidden" outline />
                            {{ Number(invoice_item.amount).toLocaleString() }}
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
const editInvoice = new Resource('invoice/general/update-customer-invoice');
const fetchWaybillInvoice = new Resource('invoice/general/waybill-invoice');
export default {
  // name: 'AddNewInvoice',
  props: {
    waybill: {
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
      pickerOptions: {
        disabledDate(date) {
          var d = new Date(); // today
          d.setDate(d.getDate()); // one year from now
          return date > d;
        },
      },
      dialogFormVisible: false,
      userCreating: false,
      fill_fields_error: false,
      show_product_list: false,
      invoice: {
        waybill: {
          waybill_items: [],
        },
      },
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
      discount_rate: 0,
    };
  },
  mounted() {
    this.fetchCustomerInvoice();
  },
  methods: {
    moment,
    checkPermission,
    checkRole,
    fetchCustomerInvoice() {
      const app = this;
      const param = { waybill_id: app.waybill.id };
      fetchWaybillInvoice.list(param).then((response) => {
        app.invoice = response.customer_invoice;
        app.form = response.customer_invoice;
        app.invoice_items = response.customer_invoice.waybill.waybill_items;
      });
    },
    updateInvoice() {
      const app = this;
      var form = app.form;
      const load = editInvoice.loaderShow();
      form.invoice_items = app.invoice_items;
      editInvoice
        .update(form.id, form)
        .then((response) => {
          app.$message({
            message: 'Invoice Updated Successfully!!!',
            type: 'success',
          });
          // app.form = app.empty_form;
          app.$emit('update', response.invoice);
          load.hide();
          app.page.option = 'list';
        })
        .catch((error) => {
          load.hide();
          console.log(error.message);
        });
    },
    fetchItemDetails(index) {
      const app = this;
      const item_index = app.invoice_items[index].item_index;
      const item = app.params.items[item_index];
      app.invoice_items[index].rate = item.price.sale_price;
      app.invoice_items[index].item_id = item.id;
      app.invoice_items[index].item = item;
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
      app.calculateNoOfCartons(index);
    },
    calculateTotal(index) {
      const app = this;
      // Get total amount for this item without tax
      if (index !== null) {
        const quantity = app.invoice_items[index].quantity;
        const unit_rate = app.invoice_items[index].rate;
        app.invoice_items[index].amount = parseFloat(
          quantity * unit_rate,
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
        (app.discount_rate / 100) * subtotal,
      ).toFixed(2);
      // subtract discount
      app.form.amount = parseFloat(subtotal - app.form.discount).toFixed(2);
    },
  },
};
</script>

