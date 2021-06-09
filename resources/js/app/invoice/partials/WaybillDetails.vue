<template>
  <section class="invoice">
    <div v-if="!print_waybill">
      <div class="row">
        <div class="col-xs-12 page-header" align="center">
          <img src="/svg/logo.png" alt="Company Logo" width="50">
          <label>{{ companyName }}</label>
          <!-- <span v-if="waybill.trips.length > 0"> -->
          <span v-if="waybill.confirmed_by !== null">
            <div v-if="waybill.status === 'pending'" class="pull-right no-print">
              <a v-if="checkPermission(['manage waybill'])" @click="form.status = 'in transit'; changeWaybillStatus(); ">
                <i class="el-icon-printer" /> Print Waybill
              </a>
            </div>
            <div v-else class="pull-right no-print">
              <a v-if="checkPermission(['manage waybill'])" @click="print_waybill = true;">
                <i class="el-icon-printer" /> Print Waybill
              </a>
            </div>
          </span>
          <span v-else><h4 class="alert alert-danger">Items on this Waybill need confirmation by an Auditor</h4></span>
          <!-- </span> -->
        </div>
        <!-- /.col -->
      </div>
      <!-- info row -->
      <div class="row invoice-info" align="center">
        <span v-html="companyContact" />
        <legend>WAYBILL/DELIVERY NOTE</legend>
      </div>
      <!-- /.row -->

      <!-- Table row -->

      <div v-if="edit_ivoice">
        <a
          class="btn btn-danger"
          @click="edit_ivoice = false"
        >Cancel</a>
        <edit-invoice :waybill="waybill" />
      </div>
      <div v-else>

        <div v-if="waybill.status === 'delivered'" align="center">
          <span color="red">Invoice have been automatically generated. You can edit it should you have any discount for the customer. </span><br>
          <a
            class="btn btn-primary"
            @click="edit_ivoice = true"
          > <i class="el-icon-edit" />Edit Invoice</a>
        </div>
        <div class="row">
          <div class="col-xs-8 table-responsive">
            <label>Customer Details</label>
            <address>
              <label>{{ waybill.invoices[0].customer.user.name.toUpperCase() }}</label><br>
              {{ (waybill.invoices[0].customer.type) ? waybill.invoices[0].customer.type.name.toUpperCase() : '' }}<br>
              Phone: {{ waybill.invoices[0].customer.user.phone }}<br>
              Email: {{ waybill.invoices[0].customer.user.email }}<br>
              {{ waybill.invoices[0].customer.user.address }}
            </address>
            <legend>Products</legend>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>
                    <div
                      v-if="waybill.confirmed_by === null && checkPermission(['audit confirm actions'])"
                    >Confirm Items</div>
                    <div v-else>S/N</div>
                  </th>
                  <th>Order No.</th>
                  <!-- <th>Customer</th> -->
                  <th>Product</th>
                  <th>Quantity</th>
                  <th>Batch No.</th>
                  <th>Expires</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(waybill_item, index) in waybill.waybill_items" :key="index">
                  <td>
                    <div :id="waybill_item.id">
                      <div
                        v-if="waybill_item.is_confirmed === '0' && checkPermission(['audit confirm actions'])"
                      >
                        <input
                          v-model="confirmed_items"
                          :value="waybill_item.id"
                          type="checkbox"
                          @change="activateConfirmButton()"
                        >
                      </div>
                      <div v-else>{{ index + 1 }}</div>
                    </div>
                  </td>
                  <td>{{ waybill_item.invoice.invoice_number }}</td>
                  <!-- <td>{{ waybill_item.invoice.customer.user.name.toUpperCase() }}</td> -->
                  <td>{{ waybill_item.item.name }}</td>
                  <!-- <td>{{ waybill_item.item.description }}</td> -->
                  <td>{{ waybill_item.quantity+' '+formatPackageType(waybill_item.type) }}
                  </td>
                  <td>
                    <div v-for="(batch, batch_index) in waybill_item.invoice_item.batches" :key="batch_index">
                      <span v-if="batch.to_supply === waybill_item.quantity">
                        {{ (batch.item_stock_batch) ? batch.item_stock_batch.batch_no : '' }}
                      </span>
                    </div>
                  </td>
                  <td>
                    <div v-for="(batch, batch_index) in waybill_item.invoice_item.batches" :key="batch_index">
                      <span v-if="batch.to_supply === waybill_item.quantity">
                        {{ (batch.item_stock_batch) ? moment(batch.item_stock_batch.expiry_date).format('MMMM Do YYYY') : '' }}
                      </span>
                    </div>
                  </td>
                  <!-- <td align="right">{{ currency + Number(waybill_item.rate).toLocaleString() }}</td>
                  <td>{{ waybill_item.type }}</td>
                  <td align="right">{{ currency + Number(waybill_item.amount).toLocaleString() }}</td>-->
                </tr>
                <!-- <tr>
                  <td colspan="4" align="right"><label>Subtotal</label></td>
                  <td align="right">{{ currency + Number(waybill.invoice.subtotal).toLocaleString() }}</td>
                </tr>
                <tr>
                  <td colspan="4" align="right"><label>Discount</label></td>
                  <td align="right">{{ currency + Number(waybill.invoice.discount).toLocaleString() }}</td>
                </tr>
                <tr>
                  <td colspan="4" align="right"><label>Grand Total</label></td>
                  <td align="right"><label style="color: green">{{ currency + Number(waybill.invoice.amount).toLocaleString() }}</label></td>
                </tr>-->
              </tbody>
            </table>
            <a
              v-if="checkPermission(['audit confirm actions']) && activate_confirm_button"
              class="btn btn-success"
              title="Click to confirm"
              @click="confirmWaybillDetails()"
            >
              <i class="fa fa-check" /> Click to save confirmation
            </a>
          </div>
          <div class="col-xs-4 table-responsive">
            <label>Waybill No.: {{ waybill.waybill_no }}</label><br>
            <label>Dispatched By.: {{ waybill.dispatch_company }}</label>
            <br>
            <label>Date:</label>
            {{ moment(waybill.created_at).format('MMMM Do YYYY') }}
            <table v-if="waybill.dispatcher" class="table table-bordered">
              <tbody>
                <tr>
                  <td>
                    <label>Vehicle No.:</label>
                    {{ waybill.dispatcher.vehicle.plate_no }}
                    <br>
                  </td>
                </tr>
                <tr>
                  <td>Dispatched By:</td>
                </tr>
                <tr
                  v-for="(vehicle_driver, index) in waybill.dispatcher.vehicle.vehicle_drivers"
                  :key="index"
                >
                  <td v-if="vehicle_driver.driver">
                    <label>{{ vehicle_driver.type }} Dispatcher</label>
                    <br>
                    <label>Name:</label>
                    {{ vehicle_driver.driver.user.name }}
                    <br>
                    <label>Phone:</label>
                    {{ vehicle_driver.driver.user.phone }}
                    <br>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- /.col -->
        </div>
        <div v-if="waybill.trips.length > 0">
          <div v-if="waybill.dispatcher && waybill.trips[0].dispatch_company === 'GGI LOGISTICS'" class="row">
            <div class="col-md-6 col-xs-12">
              <label align="center">CURRENT GOODS DELIVERY STATUS</label>
              <div v-if="waybill.status === 'pending'" align="center">
                <img src="/images/pending.png" alt="Pending" width="150">
                <br>
                <label>Goods delivery is pending</label>
              </div>
              <div v-else-if="waybill.status === 'in transit'" align="center">
                <img src="/images/transit.png" alt="Transition" width="150">
                <br>
                <label>Goods are currently in transit for delivery</label>
              </div>
              <div v-else-if="waybill.status === 'delivered'" align="center">
                <img src="/images/delivered.png" alt="Delivered" width="150">
                <br>
                <label>Goods are delivered</label>
              </div>
            </div>
            <div class="col-md-6 col-xs-12">
              <div v-if="waybill.status === 'pending'">
                <a
                  class="btn btn-primary"
                  @click="form.status = 'in transit'; changeWaybillStatus()"
                > <i class="el-icon-printer" /> Click to Mark Goods ON TRANSIT</a>
                <span
                  class="label label-danger"
                >This should be done only when goods have left the warehouse to meet the customer</span>
              </div>
              <div v-else-if="waybill.status === 'in transit'">
                <a
                  class="btn btn-success"
                  @click="form.status = 'delivered'; changeWaybillStatus()"
                >Click to Mark Goods as Delivered</a>
                <span
                  class="label label-danger"
                >This should be done only when goods have been delivered successfully to the customer</span>
              </div>
            </div>
          </div>
          <div v-if="waybill.trips[0].dispatch_company !== 'GGI LOGISTICS'" class="row">
            <div class="col-md-6 col-xs-12">
              <label align="center">CURRENT GOODS DELIVERY STATUS</label>
              <div v-if="waybill.status === 'pending'" align="center">
                <img src="/images/pending.png" alt="Pending" width="150">
                <br>
                <label>Goods delivery is pending</label>
              </div>
              <div v-else-if="waybill.status === 'in transit'" align="center">
                <img src="/images/transit.png" alt="Transition" width="150">
                <br>
                <label>Goods are currently in transit for delivery</label>
              </div>
              <div v-else-if="waybill.status === 'delivered'" align="center">
                <img src="/images/delivered.png" alt="Delivered" width="150">
                <br>
                <label>Goods are delivered</label>
              </div>
            </div>
            <div class="col-md-6 col-xs-12">
              <div v-if="waybill.status === 'pending'">
                <a
                  class="btn btn-primary"
                  @click="form.status = 'in transit'; changeWaybillStatus()"
                > <i class="el-icon-printer" /> Click to Mark Goods ON TRANSIT</a>
                <span
                  class="label label-danger"
                >This should be done only when goods have left the warehouse to meet the customer</span>
              </div>
              <div v-else-if="waybill.status === 'in transit'">
                <a
                  class="btn btn-success"
                  @click="form.status = 'delivered'; changeWaybillStatus()"
                >Click to Mark Goods as Delivered</a>
                <span
                  class="label label-danger"
                >This should be done only when goods have been delivered successfully to the customer</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <print-waybill
      v-if="print_waybill"
      :waybill="waybill"
      :company-name="companyName"
      :currency="currency"
      :company-contact="companyContact"
    />
  </section>
</template>

<script>
import moment from 'moment';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';
import EditInvoice from './EditInvoice';
import PrintWaybill from './PrintWaybill';
import Resource from '@/api/resource';
const confirmWaybillDetailsResource = new Resource('audit/confirm/waybill');
const changeWaybillStatusResource = new Resource('invoice/waybill/change-status');
export default {
  components: { PrintWaybill, EditInvoice },
  props: {
    waybill: {
      type: Object,
      default: () => ({}),
    },
    page: {
      type: Object,
      default: () => ({
        option: 'print_waybill',
      }),
    },
    companyName: {
      type: String,
      default: () => 'Warehouse Management System',
    },
    companyContact: {
      type: String,
      default: () => '',
    },
    currency: {
      type: String,
      default: () => '₦',
    },
  },
  data() {
    return {
      activeActivity: 'first',
      form: {
        status: 'pending',
      },
      print_waybill: false,
      confirmed_items: [],
      activate_confirm_button: false,
      edit_ivoice: false,
    };
  },
  mounted() {
    // this.doPrint();
  },
  methods: {
    checkPermission,
    checkRole,
    moment,
    changeWaybillStatus(){
      const app = this;
      var param = app.waybill;
      const message = 'Do you really want to update goods delivery status to: ' + app.form.status.toUpperCase() + '?';
      if (confirm(message)) {
        param.status = app.form.status;
        app.waybill.status = app.form.status;
        if (app.form.status === 'in transit') {
          app.print_waybill = true;
        }
        changeWaybillStatusResource.update(param.id, param)
          .then();
      }
    },
    activateConfirmButton() {
      this.activate_confirm_button =
      this.waybill.waybill_items.length === this.confirmed_items.length;
    },
    confirmWaybillDetails() {
      const app = this;
      var param = { waybill_item_ids: app.confirmed_items };
      const message = 'Are you sure everything is intact? Click OK to submit.';
      if (confirm(message)) {
        param.status = app.form.status;
        confirmWaybillDetailsResource
          .update(app.waybill.id, param)
          .then(response => {
            app.waybill.status = app.form.status;

            if (response.confirmed === 'success') {
              app.activate_confirm_button = false;
              app.waybill.confirmed_by = 23;
              app.$message('Waybill Items Confirmed Successfully');
            }
          });
      }
    },
    formatPackageType(type) {
      // var formated_type = type + 's';
      // if (type === 'Box') {
      //   formated_type = type + 'es';
      // }
      return type;
    },
  },
};
</script>
