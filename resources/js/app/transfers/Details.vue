<template>
  <el-card v-if="invoice">
    <el-tabs v-model="activeActivity">
      <el-tab-pane label="Invoice Summary" name="first">
        <section class="invoice">
          <!-- title row -->
          <div class="row">
            <div class="col-xs-12 page-header">
              <img src="/svg/logo.png" alt="Company Logo" width="50">
              <span>
                <label>{{ companyName }}</label>
                <div class="pull-right no-print">
                  <a @click="doPrint();">
                    <i class="el-icon-printer" /> Print Request
                  </a>
                </div>
              </span>
            </div>
            <!-- /.col -->
          </div>
          <!-- info row -->
          <div class="row invoice-info">
            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
              <label>Concerned Warehouse</label>
              <address>
                <strong>{{ invoice.request_warehouse.name.toUpperCase() }}</strong>
                <br>
                {{ invoice.request_warehouse.address }}
                <br>
              </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
              <label>Request No.: {{ invoice.request_number }}</label>
              <br>
              <label>Date:</label>
              {{ moment(invoice.created_at).format('MMMM Do YYYY, h:mm:ss a') }}
              <br>
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->

          <!-- Table row -->
          <div class="row">
            <div class="col-xs-12 table-responsive">
              <legend>Request Products</legend>
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Product</th>
                    <!-- <th>Description</th> -->
                    <th>Quantity</th>
                    <th>Supplied</th>
                    <th>Packaging</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(invoice_item, index) in invoice.transfer_request_items" :key="index">
                    <td>{{ (invoice_item.item) ? invoice_item.item.name : '' }}</td>
                    <!-- <td>{{ invoice_item.item.description }}</td> -->
                    <td>
                      {{ invoice_item.quantity }} {{ invoice_item.type }}
                      <small>({{ invoice_item.no_of_cartons }} CTN)</small>
                    </td>
                    <td>
                      {{ invoice_item.quantity_supplied }} {{ invoice_item.type }}
                    </td>
                    <td>{{ invoice_item.type }}</td>
                  </tr>
                </tbody>
              </table>
              <p>{{ invoice.notes }}</p>
            </div>
            <!-- /.col -->
          </div>
        </section>
      </el-tab-pane>
      <el-tab-pane label="Invoice History" name="second">
        <div class="row">
          <div class="col-xs-12">
            <h4>Invoice History for Invoice No.: {{ invoice.invoice_number }}</h4>
            <div class="pull-right no-print">
              <a class="btn btn-default" @click="doPrint();">
                <i class="el-icon-printer" /> Print History
              </a>
              <a :loading="downloadLoading" class="btn btn-info" @click="handleDownload">
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
              :timestamp="moment(history.created_at).format('MMMM Do YYYY, h:mm:ss a')"
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
  </el-card>
</template>

<script>
import moment from 'moment';
import { parseTime } from '@/utils';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';
// import NewWaybill from './partials/NewWaybill';

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
    };
  },
  methods: {
    checkPermission,
    checkRole,
    moment,
    doPrint() {
      window.print();
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then((excel) => {
        const multiHeader = [
          [
            'Invoice History for Invoice No.: ' + this.invoice.invoice_number,
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
          filename: 'Invoice History ' + this.invoice.invoice_number,
          autoWidth: true,
          bookType: 'csv',
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) =>
        filterVal.map((j) => {
          if (j === 'created_at') {
            return parseTime(v[j]);
          }
          return v[j];
        }),
      );
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
      binvoice-radius: 50%;
      binvoice: 2px solid #d2d6de;
      padding: 2px;
    }
    span {
      font-weight: 500;
      font-size: 12px;
    }
  }
  .post {
    font-size: 14px;
    binvoice-bottom: 1px solid #d2d6de;
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
