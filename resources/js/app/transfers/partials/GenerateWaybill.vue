<template>
  <div class="app-container">
    <span v-if="params">
      <router-link v-if="checkPermission(['manage waybill'])" :to="{name:'TransferWaybills'}" class="btn btn-default"> View Waybills</router-link>
    </span>
    <div>
      <div v-if="params" class="box">
        <div class="box-header">
          <h4 class="box-title">Generate Waybill</h4>
        </div>
        <div class="box-body">
          <el-form ref="form" :model="form" label-width="120px">
            <el-row :gutter="5" class="padded">
              <el-col :xs="24" :sm="12" :md="12">
                <label for="">Select Warehouse</label>
                <el-select v-model="form.warehouse_id" placeholder="Select Warehouse" filterable class="span" @change="fetchUndeliveredInvoices()">
                  <el-option v-for="(warehouse, warehouse_index) in params.warehouses" :key="warehouse_index" :value="warehouse.id" :label="warehouse.name" />

                </el-select>
              </el-col>
              <el-col :xs="24" :sm="12" :md="12">
                <label for="">Search Request</label>
                <el-select v-model="selected_invoice" placeholder="Select Request" filterable class="span" multiple collapse-tags @input="displayInvoiceitems()">
                  <el-option v-for="(invoice, invoice_index) in transfer_requests" :key="invoice_index" :value="invoice_index" :label="(invoice.request_warehouse) ? invoice.request_warehouse.name + '[ '+ invoice.request_number + '] ' : invoice.request_number " />
                </el-select>
              </el-col>
              <!-- <el-col :xs="24" :sm="2" :md="2">
                <br>
                <el-button type="success" @click="displayInvoiceitems()"><i class="el-icon-plus" />
                  Generate Waybill
                </el-button>
              </el-col> -->
            </el-row>
            <el-row :gutter="2" class="padded">
              <el-col>
                <div style="overflow: auto">
                  <label for="">Products</label>
                  <table v-loading="loading" class="table table-binvoiceed">
                    <thead>
                      <tr>
                        <th />
                        <th>Product</th>
                        <th>Order</th>
                        <!-- <th>Batch(es)</th> -->
                        <th>Specify Batch(es)</th>
                        <th>Supplied</th>
                        <th>Balance</th>
                        <th>To Supply</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td colspan="3" />
                        <td><label>BN / Quantity</label></td>
                        <td colspan="3" />
                      </tr>
                      <tr v-for="(invoice_item, index) in form.invoice_items" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td>{{ invoice_item.item.name }}</td>
                        <td>{{ invoice_item.quantity }} {{ formatPackageType(invoice_item.item.package_type) }}</td>
                        <td>
                          <el-select
                            v-model="invoice_item.batches"
                            placeholder="Specify product batch for this supply"
                            filterable
                            class="span"
                            multiple
                            collapse-tags
                          >
                            <el-option
                              v-for="(batch, batch_index) in invoice_item.item.stocks"
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
                        </td>

                        <td>{{ invoice_item.quantity_supplied+' ('+invoice_item.delivery_status+')' }}</td>
                        <td><div class="alert alert-danger">{{ invoice_item.quantity - invoice_item.quantity_supplied }}</div></td>
                        <td>
                          <div v-if="invoice_item.supply_bal > 0">
                            <input
                              v-model="invoice_item.quantity_for_supply"
                              class="form-control"
                              placeholder="Set Quantity for Supply"
                              type="number"
                              :max="invoice_item.supply_bal"
                              min="0"
                              @change="checkForOverflow(invoice_item.supply_bal, index)"
                            >
                          </div>
                        </td>
                      </tr>
                    </tbody>

                  </table>
                </div>
              </el-col>
            </el-row>
            <el-row>
              <el-form ref="form" :model="form" label-position="left" label-width="130px" style="max-width: 600px;">

                <el-form-item label="Waybill No." prop="waybill_no">
                  <el-input v-model="form.waybill_no" required />
                </el-form-item>
                <!-- <el-form-item v-else label="Waybill No." prop="waybill_no">
                  <el-input v-model="form.waybill_no" required />
                </el-form-item> -->
                <!-- <el-form-item v-if="available_vehicles.length > 0" label="Dispatch Vehicle" prop="vehicle_id">
                  <el-select v-model="form.vehicle_id" placeholder="Select Vehicle" filterable class="span">
                    <el-option v-for="(vehicle, index) in available_vehicles" :key="index" :value="vehicle.id" :label="vehicle.plate_no.toUpperCase()" />
                  </el-select>
                </el-form-item>
                <el-form-item v-else>
                  <span class="label label-danger">No vehicles available</span>
                </el-form-item> -->
              </el-form>
            </el-row>
            <el-row v-if="form.waybill_no" :gutter="2" class="padded">
              <el-col :xs="24" :sm="6" :md="6">
                <el-button round type="success" :disabled="disabled" @click="generateWaybill()"><i class="el-icon-plus" />
                  Generate Waybill
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
// const createInvoice = new Resource('invoice/general/store');
const necessaryParams = new Resource('fetch-necessary-params');
const unDeliveredInvoices = new Resource('transfers/waybill/undelivered-invoices');
// const availableVehicles = new Resource('invoice/waybill/fetch-available-vehicles');
const storeWaybillResource = new Resource('transfers/waybill/store');
const fetchProductBatches = new Resource('stock/items-in-stock/product-batches');
export default {
  // name: 'GenerateWaybill',

  data() {
    return {
      params: {},
      form: {
        warehouse_id: '',
        waybill_no: '',
        dispatch_company: 'GREEN LIFE LOGISTICS',
        status: 'pending',
        invoice_ids: [],
        invoice_items: [],
      },
      transfer_requests: [],
      selected_invoice: [],
      invoice_items: [],
      waybill_items: [],
      available_vehicles: [],
      batches_of_items_in_stock: [],
      // rules: {
      //   // vehicle_id: [{ required: true, message: 'Vehicle is required', trigger: 'change' }],
      //   waybill_no: [{ required: true, message: 'Waybill Number is required', trigger: 'blur' }],
      // },
      loading: false,
      disabled: false,
    };
  },
  mounted() {
    this.fetchNecessaryParams();
  },
  methods: {
    moment,
    checkPermission,
    checkRole,
    setProductBatches(item_id) {
      const app = this;
      const param = {
        warehouse_id: app.form.warehouse_id,
        item_id: item_id,
      };
      fetchProductBatches.list(param).then((response) => {
        return response.batches_of_items_in_stock;
      });
    },
    checkForOverflow(limit, index) {
      const app = this;
      const value = app.invoice_items[index].quantity_for_supply;
      const product = app.invoice_items[index].item.name;
      const package_type = app.invoice_items[index].item.package_type;
      if (value > limit) {
        app.$alert('Make sure you DO NOT exceed ' + limit + ' ' + package_type + ' for ' + product);
        app.invoice_items[index].quantity_for_supply = limit;
      }
    },
    fetchUndeliveredInvoices(index) {
      const app = this;
      var form = app.form;
      const loader = unDeliveredInvoices.loaderShow();
      unDeliveredInvoices.list(form)
        .then(response => {
          app.transfer_requests = response.transfer_requests;
          app.form.waybill_no = response.waybill_no;
          loader.hide();
          // app.fetchAvailableDrivers();
        });
    },
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list()
        .then(response => {
          app.params = response.params;
        });
    },
    // displayInvoiceitems() {
    //   const app = this;
    //   var selected_invoice = app.selected_invoice;
    //   var invoice_items = [];
    //   var invoice_ids = [];
    //   // app.loading = true;
    //   for (let index = 0; index < selected_invoice.length; index++) {
    //     const element = selected_invoice[index];
    //     invoice_items.push(...app.transfer_requests[element].transfer_request_items);
    //     invoice_ids.push(app.transfer_requests[element].id);
    //   }
    //   // console.log(invoice_items);
    //   invoice_items.forEach((invoice_item) => {
    //     var total_batch_balance = 0;
    //     var supply_bal = invoice_item.quantity - invoice_item.quantity_supplied;
    //     var stocks = invoice_item.item.stocks;

    //     stocks.forEach((stock_batch) => {
    //       total_batch_balance +=
    //         parseInt(stock_batch.balance) -
    //         parseInt(stock_batch.reserved_for_supply);
    //     });

    //     invoice_item.supply_bal = supply_bal;
    //     invoice_item.quantity_for_supply = supply_bal;
    //     if (supply_bal > total_batch_balance) {
    //       invoice_item.supply_bal = total_batch_balance;
    //       invoice_item.quantity_for_supply = total_batch_balance;
    //     }
    //     invoice_item.total_batch_balance = total_batch_balance;
    //   });
    //   app.invoice_items = invoice_items;
    //   app.form.invoice_ids = invoice_ids;
    //   // app.loading = false;
    // },
    displayInvoiceitems() {
      const app = this;
      var selected_invoice = app.selected_invoice;
      var invoice_items = [];
      var invoice_ids = [];
      app.loading = true;
      for (let index = 0; index < selected_invoice.length; index++) {
        const element = selected_invoice[index];
        invoice_items.push(...app.transfer_requests[element].transfer_request_items);
        invoice_ids.push(app.transfer_requests[element].id);
      }
      invoice_items.forEach(invoice_item => {
        var total_batch_balance = 0;
        var supply_bal = invoice_item.quantity - invoice_item.quantity_supplied;
        invoice_item.item.stocks.forEach(batch => {
          total_batch_balance += parseInt(batch.balance - batch.reserved_for_supply);
        });

        invoice_item.supply_bal = supply_bal;
        invoice_item.quantity_for_supply = supply_bal;
        if (supply_bal > total_batch_balance) {
          invoice_item.supply_bal = total_batch_balance;
          invoice_item.quantity_for_supply = total_batch_balance;
        }
        invoice_item.total_batch_balance = total_batch_balance;
      });
      app.form.invoice_items = invoice_items;
      app.form.invoice_ids = invoice_ids;
      app.loading = false;
    },
    // fetchAvailableDrivers(){
    //   const app = this;
    //   var form = app.form;
    //   availableVehicles
    //     .list(form)
    //     .then(response => {
    //       app.available_vehicles = response.available_vehicles;
    //     });
    // },
    generateWaybill() {
      // this.form.invoice_items = this.invoice_items;
      if (this.form.invoice_items.length < 1) {
        this.$alert('Please select at least one invoice request to generate a waybill');
        return false;
      }
      this.$refs['form'].validate((valid) => {
        if (valid) {
          this.$confirm('Cross check your selection before submitting. Continue?', 'Warning', {
            confirmButtonText: 'OK',
            cancelButtonText: 'Cancel',
            type: 'warning',
          }).then(() => {
            const loader = storeWaybillResource.loaderShow();

            this.disabled = true;
            storeWaybillResource
              .store(this.form)
              .then(response => {
                if (response.status) {
                  this.error_message = response.status + response.message;
                } else {
                  this.$message({
                    message: 'Waybill created successfully.',
                    type: 'success',
                    duration: 5 * 1000,
                  });
                  loader.hide();
                  this.$router.replace('waybill');
                }
              })
              .catch(error => {
                console.log(error.message);
                this.disabled = false;
              })
              .finally(() => {
                this.creatingWaybill = false;
                this.disabled = false;
              });
          }).catch(() => {
            // this.$message({
            //   type: 'info',
            //   message: 'Delete canceled',
            // });
          });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    formatPackageType(type){
      // var formated_type = type + 's';
      // if (type === 'Box') {
      //   formated_type = type + 'es';
      // }
      return type;
    },

  },
};
</script>

