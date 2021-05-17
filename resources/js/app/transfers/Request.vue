<template>
  <div class="app-container">
    <div v-if="page.option === 'list'">
      <router-link
        v-if="
          checkPermission(['manage transfer request']) &&
            canCreateNewTransferRequest
        "
        :to="{ name: 'CreateTransferRequest' }"
        class="btn btn-default"
      >Make Transfer Request</router-link>
      <el-row :gutter="10">
        <el-col :xs="24" :sm="8" :md="8">
          <label for>Select Warehouse</label>
          <el-select
            v-model="form.warehouse_index"
            placeholder="Select Warehouse"
            class="span"
            filterable
            @input="getTransferRequests"
          >
            <el-option
              v-for="(warehouse, index) in warehouses"
              :key="index"
              :value="index"
              :label="warehouse.name"
            />
          </el-select>
        </el-col>
        <el-col :xs="24" :sm="6" :md="6">
          <label for>Filter by:</label>
          <el-select
            v-model="form.status"
            placeholder="Select Status"
            class="span"
            @input="getTransferRequests"
          >
            <el-option
              v-for="(status, index) in invoice_statuses"
              :key="index"
              :value="status.code"
              :label="status.name"
            />
          </el-select>
        </el-col>
        <el-col :xs="24" :sm="10" :md="10">
          <br>
          <el-popover placement="right" trigger="click">
            <date-range-picker
              :from="$route.query.from"
              :to="$route.query.to"
              :panel="panel"
              :panels="panels"
              :submit-title="submitTitle"
              :future="future"
              @update="setDateRange"
            />
            <el-button id="pick_date" slot="reference" type="primary">
              <i class="el-icon-date" /> Pick Date Range
            </el-button>
          </el-popover>
        </el-col>
      </el-row>
      <br>
    </div>
    <div v-if="page.option === 'list'" class="box">
      <div class="box-header">
        <h4 class="box-title">{{ table_title }}</h4>
        <span class="pull-right">
          <el-button
            round
            :loading="downloadLoading"
            type="primary"
            icon="document"
            @click="handleDownload"
          >Export Excel</el-button>
        </span>
      </div>
      <div class="box-body">
        <el-tabs v-model="activeActivity" type="border-card">
          <el-tab-pane label="Incoming Request" name="Incoming">
            <v-client-table
              v-model="incoming_transfer_requests"
              :columns="columns"
              :options="options"
            >
              <div slot="supply_warehouse" slot-scope="props">
                {{ props.row.supply_warehouse.name }}
              </div>
              <div slot="request_warehouse" slot-scope="props">
                {{ props.row.request_warehouse.name }}
              </div>
              <div slot="request_by" slot-scope="props">
                {{ props.row.request_by.name }}
              </div>

              <div slot="waybill_generated" slot-scope="props">
                <div v-if="props.row.transfer_waybill_items.length > 0">
                  <div
                    v-if="props.row.full_waybill_generated === '1'"
                    class="label label-success"
                  >
                    Fully Generated
                  </div>
                  <div v-else class="label label-warning">
                    Partially Generated
                  </div>
                </div>
                <div v-else class="alert alert-danger">No</div>
              </div>
              <div slot="created_at" slot-scope="props">
                {{
                  moment(props.row.created_at).format('MMMM Do YYYY, h:mm:ss a')
                }}
              </div>
              <div slot="action" slot-scope="props">
                <a
                  class="btn btn-default"
                  @click="
                    transfer_request = props.row;
                    page.option = 'request_details';
                  "
                >
                  <i class="el-icon-tickets" />
                </a>
                <!-- <a
                  v-if="
                    props.row.status === 'pending' &&
                      checkPermission(['manage transfer request'])
                  "
                  class="btn btn-warning"
                  @click="
                    transfer_request = props.row;
                    page.option = 'edit_transfer_request';
                    selected_row_index = props.index;
                  "
                >
                  <i class="el-icon-edit" />
                </a> -->
              </div>
            </v-client-table>
          </el-tab-pane>
          <el-tab-pane label="Sent Request" name="Sent">
            <v-client-table
              v-model="sent_requests"
              :columns="columns"
              :options="options"
            >
              <div slot="supply_warehouse" slot-scope="props">
                {{ props.row.supply_warehouse.name }}
              </div>
              <div slot="request_warehouse" slot-scope="props">
                {{ props.row.request_warehouse.name }}
              </div>
              <div slot="request_by" slot-scope="props">
                {{ props.row.request_by.name }}
              </div>

              <div slot="waybill_generated" slot-scope="props">
                <div v-if="props.row.transfer_waybill_items.length > 0">
                  <div
                    v-if="props.row.full_waybill_generated === '1'"
                    class="label label-success"
                  >
                    Fully Generated
                  </div>
                  <div v-else class="label label-warning">
                    Partially Generated
                  </div>
                </div>
                <div v-else class="alert alert-danger">No</div>
              </div>
              <div slot="created_at" slot-scope="props">
                {{
                  moment(props.row.created_at).format('MMMM Do YYYY, h:mm:ss a')
                }}
              </div>
              <span slot="action" slot-scope="props">
                <a
                  class="btn btn-default"
                  @click="
                    transfer_request = props.row;
                    page.option = 'request_details';
                  "
                >
                  <i class="el-icon-tickets" />
                </a>
                <a
                  v-if="
                    props.row.status === 'pending' &&
                      checkPermission(['manage transfer request'])
                  "
                  class="btn btn-warning"
                  @click="
                    transfer_request = props.row;
                    page.option = 'edit_transfer_request';
                    selected_row_index = props.index;
                  "
                >
                  <i class="el-icon-edit" />
                </a>
                <a
                  v-if="
                    props.row.transfer_waybill_items.length < 1 &&
                      checkPermission(['manage transfer request'])
                  "
                  class="btn btn-danger"
                  @click="deleteTransferRequest(props.index, props.row)"
                >
                  <i class="fa fa-trash" />
                </a>
              </span>
            </v-client-table>
          </el-tab-pane>
        </el-tabs>
      </div>
    </div>
    <div v-if="page.option === 'request_details'">
      <a
        class="btn btn-danger no-print"
        @click="page.option = 'list'"
      >Go Back</a>
      <transfer-request-details
        :invoice="transfer_request"
        :page="page"
        :company-name="params.company_name"
        :company-contact="params.company_contact"
        :currency="currency"
      />
    </div>
    <div v-if="page.option === 'edit_transfer_request'">
      <a
        class="btn btn-danger no-print"
        @click="page.option = 'list'"
      >Go Back</a>
      <edit-transfer-request
        :invoice="transfer_request"
        :page="page"
        :params="params"
        @update="onEditUpdate"
      />
    </div>
  </div>
</template>
<script>
import moment from 'moment';
import { parseTime } from '@/utils';
import Resource from '@/api/resource';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';
import TransferRequestDetails from './Details';
import EditTransferRequest from './partials/EditTransferRequest';
const necessaryParams = new Resource('fetch-necessary-params');
const fetchTransferRequests = new Resource('transfers/general');
// const approveTransferRequestResource = new Resource('transfer_request/general/approve');
// const deliverTransferRequestResource = new Resource('transfer_request/general/deliver');
// const cancelTransferRequestResource = new Resource('transfers/general/cancel');
const deleteTransferRequestResource = new Resource('transfers/general/delete');
export default {
  nama: 'TransferRequest',
  components: { TransferRequestDetails, EditTransferRequest },
  props: {
    canCreateNewTransferRequest: {
      type: Boolean,
      default: () => true,
    },
  },
  data() {
    return {
      params: {},
      warehouses: [],
      incoming_transfer_requests: [],
      sent_requests: [],
      invoice_statuses: [],
      currency: '',
      activeActivity: 'Incoming',
      columns: [
        'action',
        'supply_warehouse',
        'request_warehouse',
        'request_number',
        'request_by',
        'created_at',
        'status',
        'waybill_generated',
      ],

      options: {
        headings: {
          request_by: 'Request By',
          supply_warehouse: 'Supplying Warehouse',
          request_warehouse: 'Requesting Warehouse',
          request_number: 'TXN Number',
          amount: 'Amount',
          created_at: 'Date',
          status: 'Status',
          waybill_generated: 'Waybill Generated',

          // id: 'S/N',
        },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['request_number', 'request_by', 'request_date', 'status'],
        filterable: ['request_number', 'request_by', 'request_date', 'status'],
      },
      page: {
        option: 'list',
      },
      form: {
        warehouse_index: '',
        warehouse_id: '',
        from: '',
        to: '',
        panel: '',
        status: 'pending',
      },
      submitTitle: 'Fetch Report',
      panel: 'month',
      future: false,
      panels: ['range', 'week', 'month', 'quarter', 'year'],
      show_calendar: false,
      table_title: '',
      in_warehouse: '',
      transfer_request: {},
      selected_row_index: '',
      downloadLoading: false,
      filename: 'TransferRequests',
    };
  },

  mounted() {
    this.fetchNecessaryParams();
  },
  beforeDestroy() {},
  methods: {
    moment,
    checkPermission,
    checkRole,
    onEditUpdate(updated_row) {
      const app = this;
      // app.items_in_stock.splice(app.itemInStock.index-1, 1);
      app.sent_requests[app.selected_row_index - 1] = updated_row;
    },
    showCalendar() {
      this.show_calendar = !this.show_calendar;
    },
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list().then((response) => {
        app.params = response.params;
        app.warehouses = response.params.warehouses;
        app.invoice_statuses = response.params.invoice_statuses;
        app.currency = response.params.currency;
        if (app.warehouses.length > 0) {
          app.form.warehouse_id = app.warehouses[0];
          app.form.warehouse_index = 0;
          app.getTransferRequests();
        }
      });
    },
    format(date) {
      var month = date.toLocaleString('en-US', { month: 'short' });
      return month + ' ' + date.getDate() + ', ' + date.getFullYear();
    },
    setDateRange(values) {
      const app = this;
      document.getElementById('pick_date').click();
      app.show_calendar = false;
      let panel = app.panel;
      let from = app.week_start;
      let to = app.week_end;
      if (values !== '') {
        to = this.format(new Date(values.to));
        from = this.format(new Date(values.from));
        panel = values.panel;
      }
      app.form.from = from;
      app.form.to = to;
      app.form.panel = panel;
      app.getTransferRequests();
    },
    getTransferRequests() {
      const app = this;
      const loader = fetchTransferRequests.loaderShow();

      const param = app.form;
      param.warehouse_id = app.warehouses[param.warehouse_index].id;
      var extra_tableTitle = '';
      if (app.form.from !== '' && app.form.to !== '') {
        extra_tableTitle = ' from ' + app.form.from + ' to ' + app.form.to;
      }
      app.table_title =
        app.form.status.toUpperCase() +
        ' Transfer Requests  in ' +
        app.warehouses[param.warehouse_index].name +
        extra_tableTitle;
      fetchTransferRequests
        .list(param)
        .then((response) => {
          app.incoming_transfer_requests = response.incoming_transfer_requests;
          app.sent_requests = response.sent_requests;
          loader.hide();
        })
        .catch((error) => {
          loader.hide();
          console.log(error.message);
        });
    },
    // cancelTransferRequest(index, transfer_request) {
    //   const app = this;
    //   const param = { status: 'cancelled' };
    //   cancelTransferRequestResource
    //     .update(transfer_request.id, param)
    //     .then((response) => {
    //       app.incoming_transfer_requests.splice(index - 1, 1);
    //     });
    // },
    deleteTransferRequest(index, transfer_request) {
      const app = this;
      const message = 'Are you sure? This cannot be undone!';
      if (confirm(message)) {
        deleteTransferRequestResource
          .destroy(transfer_request.id, transfer_request)
          .then((response) => {
            app.sent_requests.splice(index - 1, 1);
          });
      }
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then((excel) => {
        const multiHeader = [[this.table_title, '', '', '', '']];
        const tHeader = [
          'INVOICE NUMBER',
          'CUSTOMER',
          'AMOUNT',
          'DATE',
          'STATUS',
        ];
        const filterVal = [
          'request_number',
          'request_by',
          'amount',
          'request_date',
          'status',
        ];
        const list = this.incoming_transfer_requests;
        const data = this.formatJson(filterVal, list);
        excel.export_json_to_excel({
          multiHeader,
          header: tHeader,
          data,
          filename: this.filename,
          autoWidth: true,
          bookType: 'csv',
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map((v) =>
        filterVal.map((j) => {
          if (j === 'request_date') {
            return parseTime(v[j]);
          } else {
            if (j === 'request_by') {
              return v['customer']['user']['name'];
            }
            return v[j];
          }
        })
      );
    },
  },
};
</script>
