<template>
  <div class="clear-margin">
    <!-- title row -->
    <div class="row">
      <div class="col-xs-12 page-header" align="center">
        <img src="/svg/logo.png" alt="Company Logo" width="50">
        <span>
          <label>{{ companyName }}</label>
          <div class="pull-right no-print">
            <a v-if="checkPermission(['manage waybill'])" @click="doPrint();"><i class="el-icon-printer" /> Print Waybill</a>
          </div>
        </span>
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
    <div class="row">
      <div class="col-xs-8 table-responsive">
        <label>Customer Details</label>
        <address>
          <label>{{ waybill.transfer_requests[0].request_warehouse.name.toUpperCase() }}</label>
        </address>
        <legend>Invoice Products</legend>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>S/N</th>
              <th>Product</th>
              <!-- <th>Description</th> -->
              <th>Quantity</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(waybill_item, index) in waybill.waybill_items" :key="index">
              <td>{{ index + 1 }}</td>
              <td>{{ waybill_item.item.name }}</td>
              <!-- <td>{{ waybill_item.item.description }}</td> -->
              <td>{{ waybill_item.quantity+' '+waybill_item.type }}</td>
              <!-- <td align="right">{{ currency + Number(waybill_item.rate).toLocaleString() }}</td>
              <td>{{ waybill_item.type }}</td>
              <td align="right">{{ currency + Number(waybill_item.amount).toLocaleString() }}</td> -->
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
            </tr> -->
          </tbody>
        </table>
      </div>
      <div class="col-xs-4 table-responsive">
        <label>Waybill No.: {{ waybill.transfer_request_waybill_no }}</label><br>
        <label>Dispatched By.: {{ (waybill.dispatcher) ? waybill.dispatcher.name : '' }}</label>
        <br>
        <label>Date:</label> {{ moment(waybill.created_at).format('MMMM Do YYYY') }}
      </div>
      <!-- /.col -->
    </div>
    <div class="row">
      <div class="col-xs-12">
        <span>Confirm the receipt of the above listed goods in good condition and complete</span><br>
        <span>Which are not returnable</span><br><br><br>
      </div>
      <div class="col-xs-7">
        <label>Name: ______________________________</label><br><br><br>
        <label>Sign: ______________________________</label>
      </div>
      <div class="col-xs-5">
        <label>Date: _______________________</label><br><br><br>
        <label>Time: _______________________</label>
      </div>
    </div>
  </div>
</template>

<script>
import moment from 'moment';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';
// import Watermark from '@/watermark';
export default {
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
      default: () => ('Warehouse Management System'),
    },
    companyContact: {
      type: String,
      default: () => (''),
    },
    currency: {
      type: String,
      default: () => ('₦'),
    },
  },
  data() {
    return {
      activeActivity: 'first',
    };
  },
  mounted() {
    // Watermark.set('Green Life Pharmaceutical Ltd.');
    this.doPrint();
  },
  methods: {
    checkPermission,
    checkRole,
    moment,
    doPrint() {
      window.print();
    },
  },
};
</script>
<style>
@media print {
* {
    -webkit-print-color-adjust: exact !important; /*Chrome, Safari */
    color-adjust: exact !important;  /*Firefox*/

  }
  .clear-margin {
    margin-top: -100px !important;
    background: url("../../../../../public/svg/watermark.png");
  }
}
</style>
