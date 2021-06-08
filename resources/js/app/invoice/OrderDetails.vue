<template>
  <div v-if="invoice" class="print-padded">
    <el-tabs v-model="activeActivity">
      <el-tab-pane label="Order Summary" name="first">
        <section class="invoice">
          <!-- title row -->
          <div class="row">
            <div class="col-xs-12 page-header">
              <img src="/svg/logo.png" alt="Company Logo" width="50">
              <span>
                <label>{{ companyName }}</label>
                <div class="pull-right no-print">
                  <a @click="doPrint()">
                    <i class="el-icon-printer" /> Print Order
                  </a>
                </div>
              </span>
            </div>
            <!-- /.col -->
          </div>
          <!-- info row -->
          <div class="row invoice-info">
            <div class="col-sm-4 invoice-col">
              <label>Customer Details</label>
              <address>
                <label>{{ invoice.customer.user.name.toUpperCase() }}</label>
                <br>
                {{
                  invoice.customer.type
                    ? invoice.customer.type.name.toUpperCase()
                    : ''
                }}
                <br>
                Phone: {{ invoice.customer.user.phone }}
                <!-- <br>
                Email: {{ invoice.customer.user.email }}-->
                <br>
                {{ invoice.customer.user.address }}
              </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
              <label>Concerned Warehouse</label>
              <address>
                <strong>{{ invoice.warehouse.name.toUpperCase() }}</strong>
                <br>
                {{ invoice.warehouse.address }}
                <br>
              </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
              <label>Order No.: {{ invoice.invoice_number }}</label>
              <br>
              <label>Date:</label>
              {{
                moment(invoice.invoice_date).format('MMMM Do YYYY')
              }}
              <br>
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->

          <!-- Table row -->
          <div class="row">
            <div class="col-xs-12">
              <small class="pull-right no-print"> Confirmed By: {{ (invoice.confirmer) ? invoice.confirmer.name : 'Not Confirmed' }}</small>
              <legend>Order Products</legend>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>
                      <div v-if="invoice.confirmed_by === null && checkPermission(['audit confirm actions'])">
                        Confirm Items
                      </div>
                      <div v-else>S/N</div>
                    </th>
                    <th>Product</th>
                    <!-- <th>Description</th> -->
                    <th>Quantity</th>
                    <th>Supplied</th>
                    <th>Balance</th>
                    <!-- <th>Rate</th>
                    <th>Per</th>
                    <th>Amount</th> -->
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(invoice_item, index) in invoice.invoice_items"
                    :key="index"
                  >
                    <td>
                      <div :id="invoice_item.id">
                        <div
                          v-if="
                            invoice_item.is_confirmed === 0 &&
                              checkPermission(['audit confirm actions'])
                          "
                        >
                          <input
                            v-model="confirmed_items"
                            :value="invoice_item.id"
                            type="checkbox"
                            @change="activateConfirmButton()"
                          >
                        </div>
                        <div v-else>{{ index + 1 }}</div>
                      </div>
                    </td>
                    <td>
                      {{ invoice_item.item ? invoice_item.item.name : '' }}
                    </td>
                    <!-- <td>{{ invoice_item.item.description }}</td> -->
                    <td>
                      {{ invoice_item.quantity }} {{ invoice_item.type }}
                    </td>
                    <td>
                      {{ invoice_item.quantity_supplied }} {{ invoice_item.type }}
                    </td>
                    <td>
                      {{ invoice_item.quantity - invoice_item.quantity_supplied }}
                      {{ invoice_item.type }}
                    </td>
                    <!-- <td align="right">
                      {{
                        currency + Number(invoice_item.rate).toLocaleString()
                      }}
                    </td>
                    <td>{{ invoice_item.type }}</td>
                    <td align="right">
                      {{
                        currency + Number(invoice_item.amount).toLocaleString()
                      }}
                    </td> -->
                  </tr>
                  <tr v-if="checkPermission(['audit confirm actions']) && activate_confirm_button">
                    <td colspan="7">
                      <a
                        v-if="checkPermission(['audit confirm actions']) && activate_confirm_button"
                        v-loading="confirm_loader"
                        class="btn btn-success"
                        title="Click to confirm"
                        @click="confirmOrderDetails()"
                      >
                        <i class="fa fa-check" /> Click to save confirmation
                      </a>
                    </td>
                  </tr>
                  <!-- <tr>
                    <td colspan="7" align="right">
                      <label>Subtotal</label>
                    </td>
                    <td align="right">
                      {{ currency + Number(invoice.subtotal).toLocaleString() }}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="7" align="right">
                      <label>Discount</label>
                    </td>
                    <td align="right">
                      {{ currency + Number(invoice.discount).toLocaleString() }}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="7" align="right">
                      <label>Grand Total</label>
                    </td>
                    <td align="right">
                      <label style="color: black">{{
                        currency + Number(invoice.amount).toLocaleString()
                      }}</label>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="8" align="right"><label>In Words: {{ inWords(invoice.amount).toUpperCase() + ' NAIRA ONLY' }}</label></td>
                  </tr> -->
                </tbody>
              </table>
              <p>
                <small>{{ invoice.notes }}</small>
              </p>
              <!-- <table>
                <tr>
                  <td>Prepared By: ______________________________</td>
                  <td>Authorized By: ______________________________</td>
                  <td>Approved By: ______________________________</td>
                </tr>
              </table> -->
            </div>

            <!-- /.col -->
          </div>
        </section>
      </el-tab-pane>
      <el-tab-pane label="Order History" name="second">
        <div class="row">
          <div class="col-xs-12">
            <h4>
              Order History for Order No.: {{ invoice.invoice_number }}
            </h4>
            <div class="pull-right no-print">
              <a class="btn btn-default" @click="doPrint()">
                <i class="el-icon-printer" /> Print History
              </a>
              <a
                :loading="downloadLoading"
                class="btn btn-info"
                @click="handleDownload"
              >
                <i class="el-icon-download" /> Export Excel
              </a>
            </div>
          </div>
        </div>
        <div class="block">
          <el-timeline>
            <el-timeline-item
              v-for="(history, index) in invoice.histories"
              :key="index"
              :timestamp="
                moment(history.created_at).format('MMMM Do YYYY')
              "
              placement="top"
            >
              <el-card>
                <label>{{ history.title }}</label>
                <p>{{ history.description }}</p>
              </el-card>
            </el-timeline-item>
          </el-timeline>
        </div>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
import moment from 'moment';
import { parseTime } from '@/utils';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';
// import NewWaybill from './partials/NewWaybill';
import Resource from '@/api/resource';
const confirmOrderDetailsResource = new Resource('audit/confirm/invoice');
export default {
  // components: { NewWaybill },
  props: {
    invoice: {
      type: Object,
      default: () => ({}),
    },
    page: {
      type: Object,
      default: () => ({
        option: 'invoice_details',
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
      updating: false,
      partialPage: {
        option: '',
      },
      downloadLoading: false,
      confirmed_items: [],
      activate_confirm_button: false,
      confirm_loader: false,
    };
  },
  methods: {
    checkPermission,
    checkRole,
    moment,
    doPrint() {
      window.print();
    },
    activateConfirmButton() {
      this.activate_confirm_button =
      this.invoice.invoice_items.length === this.confirmed_items.length;
    },
    confirmOrderDetails() {
      const app = this;
      var param = { invoice_item_ids: app.confirmed_items };
      const message = 'Are you sure everything is intact? Click OK to confirm.';
      if (confirm(message)) {
        app.confirm_loader = true;
        confirmOrderDetailsResource
          .update(app.invoice.id, param)
          .then(response => {
            if (response.confirmed === 'success') {
              app.activate_confirm_button = false;
              app.$message('Order Items Confirmed Successfully');
            }
            app.confirm_loader = false;
          });
      }
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const multiHeader = [
          [
            'Order History for Order No.: ' + this.invoice.invoice_number,
            '',
            '',
          ],
        ];
        const tHeader = ['Title', 'Description', 'Date'];
        const filterVal = ['title', 'description', 'created_at'];
        const list = this.invoice.histories;
        const data = this.formatJson(filterVal, list);
        excel.export_json_to_excel({
          multiHeader,
          header: tHeader,
          data,
          filename: 'Order History ' + this.invoice.invoice_number,
          autoWidth: true,
          bookType: 'csv',
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === 'created_at') {
            return parseTime(v[j]);
          }
          return v[j];
        }),
      );
    },

    inWords(n) {
      var string = n.toString(), units, tens, scales, start, end, chunks, chunksLen, chunk, ints, i, word, words, and = 'and';

      /* Remove spaces and commas */
      string = string.replace(/[, ]/g, '');

      /* Is number zero? */
      if (parseInt(string) === 0) {
        return 'zero';
      }

      /* Array of units as words */
      units = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];

      /* Array of tens as words */
      tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

      /* Array of scales as words */
      scales = ['', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion', 'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quatttuor-decillion', 'quindecillion', 'sexdecillion', 'septen-decillion', 'octodecillion', 'novemdecillion', 'vigintillion', 'centillion'];

      /* Split user argument into 3 digit chunks from right to left */
      start = string.length;
      chunks = [];
      while (start > 0) {
        end = start;
        chunks.push(string.slice((start = Math.max(0, start - 3)), end));
      }

      /* Check if function has enough scale words to be able to stringify the user argument */
      chunksLen = chunks.length;
      if (chunksLen > scales.length) {
        return '';
      }

      /* Stringify each integer in each chunk */
      words = [];
      for (i = 0; i < chunksLen; i++) {
        chunk = parseInt(chunks[i]);

        if (chunk) {
          /* Split chunk into array of individual integers */
          ints = chunks[i].split('').reverse().map(parseFloat);

          /* If tens integer is 1, i.e. 10, then add 10 to units integer */
          if (ints[1] === 1) {
            ints[0] += 10;
          }

          /* Add scale word if chunk is not zero and array item exists */
          if ((word = scales[i])) {
            words.push(word);
          }

          /* Add unit word if array item exists */
          if ((word = units[ ints[0] ])) {
            words.push(word);
          }

          /* Add tens word if array item exists */
          if ((word = tens[ ints[1] ])) {
            words.push(word);
          }

          /* Add 'and' string after units or tens integer if: */
          if (ints[0] || ints[1]) {
            /* Chunk has a hundreds integer or chunk is the first of multiple chunks */
            if (ints[2] || !i && chunksLen) {
              words.push(and);
            }
          }

          /* Add hundreds word if array item exists */
          if ((word = units[ ints[2] ])) {
            words.push(word + ' hundred');
          }
        }
      }

      return words.reverse().join(' ');
    },
  },
};
</script>

<style lang="scss" scoped>
.invoice-activity {
  .invoice-block {
    .invoicename,
    .description {
      display: block;
      margin-left: 50px;
      padding: 2px 0;
    }
    img {
      width: 40px;
      height: 40px;
      float: left;
    }
    :after {
      clear: both;
    }
    .img-circle {
      padding: 2px;
    }
    span {
      font-weight: 500;
      font-size: 12px;
    }
  }
  .post {
    font-size: 14px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    color: #666;
    .image {
      width: 100%;
    }
    .invoice-images {
      padding-top: 20px;
    }
  }
  .list-inline {
    padding-left: 0;
    margin-left: -5px;
    list-style: none;
    li {
      display: inline-block;
      padding-right: 5px;
      padding-left: 5px;
      font-size: 13px;
    }
    .link-black {
      &:hover,
      &:focus {
        color: #999;
      }
    }
  }
  .el-carousel__item h3 {
    color: #475669;
    font-size: 14px;
    opacity: 0.75;
    line-height: 200px;
    margin: 0;
  }

  .el-carousel__item:nth-child(2n) {
    background-color: #99a9bf;
  }

  .el-carousel__item:nth-child(2n + 1) {
    background-color: #d3dce6;
  }

}
</style>
<style>

@media print {
  .el-tabs__header {
    display: none !important;
  }

}
</style>
