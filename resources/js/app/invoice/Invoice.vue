<template>
  <div class="app-container">
    <div v-if="page.option==='list'">
      <router-link
        v-if="checkPermission(['create invoice']) && canCreateNewInvoice"
        :to="{name:'CreateInvoice'}"
        class="btn btn-default"
      >Create New Invoice</router-link>
      <el-row :gutter="10">
        <el-col :xs="24" :sm="8" :md="8">
          <label for>Select Warehouse</label>
          <el-select
            v-model="form.warehouse_index"
            placeholder="Select Warehouse"
            class="span"
            filterable
            @input="getInvoices"
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
            @input="getInvoices"
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
    <div v-if="page.option==='list'" class="box">
      <div class="box-header">
        <h4 class="box-title">{{ table_title }}</h4>
      </div>
      <div class="box-body">
        <div>
          <label class="radio-label" style="padding-left:0;">Filename:</label>
          <el-input
            v-model="filename"
            :placeholder="$t('excel.placeholder')"
            style="width:340px;"
            prefix-icon="el-icon-document"
          />
          <el-button
            :loading="downloadLoading"
            style="margin:0 0 20px 20px;"
            type="primary"
            icon="document"
            @click="handleDownload"
          >Export Excel</el-button>
        </div>
        <el-row :gutter="20">
          <el-col :xs="24" :sm="12" :md="12">
            <el-input
              v-model="form.keyword"
              placeholder="Search"
              style="width: 200px"
              class="filter-item"
              @input="handleFilter"
            />
          </el-col>
        </el-row>
        <v-client-table v-model="invoices" :columns="columns" :options="options">

          <div
            slot="amount"
            slot-scope="props"
          >{{ currency + Number(props.row.amount).toLocaleString() }}</div>
          <div
            slot="confirmed_by"
            slot-scope="props"
          >{{ (props.row.confirmer) ? props.row.confirmer.name : '' }}</div>
          <div
            slot="waybill_generated"
            slot-scope="props"
          >
            <div v-if="props.row.waybill_items.length > 0">
              <div v-if="props.row.full_waybill_generated ==='1'" class="label label-success">
                Fully Generated
              </div>
              <div v-else class="label label-warning">
                Partially Generated
              </div>
            </div>
            <div v-else class="alert alert-danger">
              No
            </div>
          </div>
          <div
            slot="invoice_date"
            slot-scope="props"
          >{{ moment(props.row.invoice_date).format('MMMM Do YYYY') }}</div>
          <div
            slot="created_at"
            slot-scope="props"
          >{{ moment(props.row.created_at).format('MMMM Do YYYY, h:mm:ss a') }}</div>
          <div slot="action" slot-scope="props">
            <a class="btn btn-default" @click="invoice=props.row; page.option='invoice_details'">
              <i class="el-icon-tickets" />
            </a>
            <!-- <a
              v-if="props.row.status === 'pending' && props.row.full_waybill_generated ==='0' && checkPermission(['update invoice'])"
              class="btn btn-warning"
              @click="invoice=props.row; page.option='edit_invoice'; selected_row_index=props.index"
            >
              <i class="el-icon-edit" />
            </a> -->
            <a
              v-if="props.row.status === 'pending' && checkPermission(['update invoice'])"
              class="btn btn-warning"
              @click="invoice=props.row; page.option='edit_invoice'; selected_row_index=props.index"
            >
              <i class="el-icon-edit" />
            </a>
            <a
              v-if="props.row.waybill_items.length < 1 && checkPermission(['delete invoice'])"
              class="btn btn-danger"
              @click="deleteInvoice(props.index, props.row)"
            >
              <i class="fa fa-trash" />
            </a>
            <!-- <el-dropdown class="avatar-container right-menu-item hover-effect" trigger="click">
              <div class="avatar-wrapper" style="color: brown">
                <label style="cursor:pointer"><i class="el-icon-more-outline" /></label>
              </div>
              <el-dropdown-menu slot="dropdown" style="padding: 10px;">
                <el-dropdown-item v-if="props.row.invoice_status === 'pending' && checkPermission(['approve invoice'])">
                  <a @click="approveInvoice(props.index, props.row);">Approve</a>
                </el-dropdown-item>
                <el-dropdown-item v-if="props.row.invoice_status === 'approved' && checkPermission(['approve invoice', 'deliver invoice'])" divided>
                  <a @click="deliverInvoice(props.index, props.row);">Delivered</a>
                </el-dropdown-item>
                <el-dropdown-item v-if="props.row.invoice_status === 'pending' && checkPermission(['cancel invoice'])" divided>
                  <a @click="cancelInvoice(props.index, props.row);">Cancel</a>
                </el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown>-->
          </div>
        </v-client-table>
      </div>
      <el-row :gutter="20">
        <pagination
          v-show="total > 0"
          :total="total"
          :page.sync="form.page"
          :limit.sync="form.limit"
          @pagination="getInvoices"
        />
      </el-row>
    </div>
    <div v-if="page.option==='invoice_details'">
      <a class="btn btn-danger no-print" @click="page.option='list'">Go Back</a>
      <invoice-details
        :invoice="invoice"
        :page="page"
        :company-name="params.company_name"
        :company-contact="params.company_contact"
        :currency="currency"
      />
    </div>
    <div v-if="page.option==='edit_invoice'">
      <a class="btn btn-danger no-print" @click="page.option='list'">Go Back</a>
      <edit-invoice :invoice="invoice" :page="page" :params="params" @update="onEditUpdate" />
    </div>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import moment from 'moment';
import { parseTime } from '@/utils';
import Resource from '@/api/resource';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';
import InvoiceDetails from './Details';
import EditInvoice from './partials/EditInvoice';
const necessaryParams = new Resource('fetch-necessary-params');
const fetchInvoices = new Resource('invoice/general');
// const approveInvoiceResource = new Resource('invoice/general/approve');
// const deliverInvoiceResource = new Resource('invoice/general/deliver');
const cancelInvoiceResource = new Resource('invoice/general/cancel');
const deleteInvoiceResource = new Resource('invoice/general/delete');
export default {
  components: { InvoiceDetails, EditInvoice, Pagination },
  props: {
    canCreateNewInvoice: {
      type: Boolean,
      default: () => true,
    },
  },
  data() {
    return {
      params: {},
      warehouses: [],
      invoices: [],
      invoice_statuses: [],
      currency: '',
      columns: [
        'action',
        'invoice_number',
        'customer.user.name',
        'amount',
        'invoice_date',
        'created_at',
        'status',
        'waybill_generated',
        'confirmed_by',
      ],

      options: {
        headings: {
          'customer.user.name': 'Customer',
          invoice_number: 'Invoice Number',
          amount: 'Amount',
          invoice_date: 'Invoice Date',
          created_at: 'Date Saved',
          status: 'Status',
          waybill_generated: 'Waybill Generated',

          // id: 'S/N',
        },
        pagination: {
          dropdown: true,
          chunk: 10,
        },
        perPage: 10,
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: [
          'invoice_number',
          'customer.user.name',
          'invoice_date',
          'status',
        ],
        filterable: false,
        // filterable: [
        //   'invoice_number',
        //   'customer.user.name',
        //   'invoice_date',
        //   'status',
        // ],
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
        page: 1,
        limit: 10,
        keyword: '',
      },
      total: 0,
      loading: false,
      load_table: false,
      downloading: false,
      userCreating: false,
      submitTitle: 'Fetch Report',
      panel: 'month',
      future: false,
      panels: ['range', 'week', 'month', 'quarter', 'year'],
      show_calendar: false,
      table_title: '',
      in_warehouse: '',
      invoice: {},
      selected_row_index: '',
      downloadLoading: false,
      filename: 'Invoices',
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
      app.invoices[app.selected_row_index - 1] = updated_row;
    },
    showCalendar() {
      this.show_calendar = !this.show_calendar;
    },
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list().then(response => {
        app.params = response.params;
        app.warehouses = response.params.warehouses;
        app.invoice_statuses = response.params.invoice_statuses;
        app.currency = response.params.currency;
        if (app.warehouses.length > 0) {
          app.form.warehouse_id = app.warehouses[0];
          app.form.warehouse_index = 0;
          app.getInvoices();
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
      app.getInvoices();
    },
    handleFilter() {
      this.form.page = 1;
      this.getInvoices();
    },
    getInvoices() {
      const app = this;
      const loader = fetchInvoices.loaderShow();
      const { limit, page } = app.form;
      app.options.perPage = limit;
      const param = app.form;
      param.warehouse_id = app.warehouses[param.warehouse_index].id;
      var extra_tableTitle = '';
      if (app.form.from !== '' && app.form.to !== '') {
        extra_tableTitle = ' from ' + app.form.from + ' to ' + app.form.to;
      }
      app.table_title =
        app.form.status.toUpperCase() +
        ' Invoices  in ' +
        app.warehouses[param.warehouse_index].name +
        extra_tableTitle;
      fetchInvoices
        .list(param)
        .then(response => {
          // app.invoices = response.invoices;
          app.invoices = response.invoices.data;
          app.invoices.forEach((element, index) => {
            element['index'] = (page - 1) * limit + index + 1;
          });
          app.total = response.invoices.total;
          loader.hide();
        })
        .catch(error => {
          loader.hide();
          console.log(error.message);
        });
    },
    cancelInvoice(index, invoice) {
      const app = this;
      const param = { status: 'cancelled' };
      cancelInvoiceResource.update(invoice.id, param).then(response => {
        app.invoices.splice(index - 1, 1);
      });
    },
    deleteInvoice(index, invoice) {
      const app = this;
      const message = 'Are you sure? This cannot be undone!';
      if (confirm(message)) {
        deleteInvoiceResource.destroy(invoice.id, invoice).then(response => {
          app.invoices.splice(index - 1, 1);
        });
      }
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const multiHeader = [[this.table_title, '', '', '', '', '']];
        const tHeader = [
          'INVOICE NUMBER',
          'CUSTOMER',
          'AMOUNT',
          'INVOICE DATE',
          'DATE SAVED',
          'STATUS',
        ];
        const filterVal = [
          'invoice_number',
          'customer.user.name',
          'amount',
          'invoice_date',
          'status',
        ];
        const list = this.invoices;
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
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === 'invoice_date') {
            return parseTime(v[j]);
          }
          if (j === 'customer.user.name') {
            return v['customer']['user']['name'];
          }
          if (j === 'created_at') {
            return parseTime(v[j]);
          }
          return v[j];
        })
      );
    },
  },
};
</script>
