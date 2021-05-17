<template>
  <div>
    <el-row :gutter="10" class="padded">
      <el-col :xs="24" :sm="12" :md="8">

        <label for>Select Warehouse</label>
        <el-select
          v-model="form.warehouse_id"
          placeholder="Select Warehouse"
          filterable
          class="span"
        >
          <el-option
            v-for="(warehouse, warehouse_index) in params.warehouses"
            :key="warehouse_index"
            :value="warehouse.id"
            :label="warehouse.name"
          />
        </el-select>
        <upload-excel-component :on-success="handleSuccess" :before-upload="beforeUpload" />
      </el-col>
      <el-col :xs="24" :sm="12" :md="16">
        <strong>Note:</strong> To upload bulk invoices from excel file, kindly make sure your file follows the header names as stated in the sample below<br>
        <label>Sample</label>
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Description of Goods</th>
              <th>Quantity</th>
              <th>Rate</th>
              <th>per</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>P-Alaxin T.S by 3 Tablets*48</td>
              <td>48 BOX</td>
              <td>7370.00</td>
              <td>BOX</td>
              <td>353760.00</td>
            </tr>
            <tr>
              <td>P-Alaxin Tablet by 12 Tabs* 24</td>
              <td>24 BOX</td>
              <td>7170.00	</td>
              <td>BOX</td>
              <td>172080.00</td>
            </tr>
          </tbody>

        </table>
      </el-col>
    </el-row>
    <el-row v-if="errors.length > 0" :gutter="5" class="padded">
      <div class="alert alert-danger">
        <span v-for="(error, error_index) in errors" :key="error_index">
          {{ error }}<br>
        </span>
      </div>
    </el-row>
    <div>
      <el-row :gutter="2" class="padded">
        <el-col>
          <div style="overflow: auto">
            <label for>INVOICES</label>
            <table class="table table-binvoiceed">
              <thead>
                <tr>
                  <th />
                  <th>S/N</th>
                  <th>Customer</th>
                  <th>Invoice No.</th>
                  <th>Invoice Date</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(bulk_invoice, bulk_invoice_index) in bulk_invoices" :key="bulk_invoice_index">
                  <td>
                    <span>
                      <a
                        v-if="bulk_invoices.length > 1"
                        class="btn btn-danger btn-flat fa fa-trash"
                        @click="removeLine(bulk_invoice_index)"
                      />
                      <a

                        class="btn btn-info btn-flat fa fa-plus"
                        @click="addLine(bulk_invoice_index)"
                      />

                    </span>
                  </td>
                  <td><el-radio v-model="selected_index" :label="bulk_invoice_index">{{ bulk_invoice_index + 1 }}</el-radio></td>
                  <td>
                    <el-select
                      v-model="bulk_invoice.customer_id"
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
                  </td>
                  <td>
                    <el-input
                      v-model="bulk_invoice.invoice_number"
                      placeholder="Enter Invoice Number"
                      class="span"
                    />
                  </td>
                  <td>
                    <el-date-picker
                      v-model="bulk_invoice.invoice_date"
                      type="date"
                      placeholder="Invoice Date"
                      style="width: 100%;"
                      format="yyyy/MM/dd"
                      value-format="yyyy-MM-dd"
                      :picker-options="pickerOptions"
                    />
                  </td>
                  <!-- <td>
                            <el-select
                              v-model="invoice_item.batches"
                              placeholder="Specify product batch for this supply"
                              filterable
                              class="span"
                              multiple
                              collapse-tags
                            >
                              <el-option
                                v-for="(batch, batch_index) in invoice_item.batches_of_items_in_stock"
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
                          </td> -->
                  <td>
                    <a class="btn btn-warning" @click="previewData(bulk_invoice_index)">Preview</a>
                  </td>
                </tr>
                <tr v-if="fill_fields_error">
                  <td colspan="6"><label class="label label-danger">Please fill all empty fields before adding another row</label></td>
                </tr>
              </tbody>
            </table>
          </div>
        </el-col>
      </el-row>
      <el-row :gutter="2" class="padded">
        <el-col :xs="24" :sm="6" :md="6">
          <el-button type="success" @click="addNewInvoice">
            <i class="el-icon-plus" />
            Create Invoice
          </el-button>
        </el-col>
      </el-row>
    </div>
    <el-dialog
      :title="preview_title"
      :visible.sync="dialogVisible"
      width="90%"
    >
      <el-table :data="tableData" border highlight-current-row style="width: 100%;margin-top:20px;">
        <el-table-column v-for="item of tableHeader" :key="item" :prop="item" :label="item" />
      </el-table>
    </el-dialog>
  </div>
</template>
<script>
import UploadExcelComponent from '@/components/UploadExcel/index.vue';
import Resource from '@/api/resource';
const saveBulkInvoices = new Resource('invoice/general/bulk-upload');
export default {
  components: { UploadExcelComponent },
  props: {
    params: {
      type: Object,
      default: () => ({}),
    },
    customers: {
      type: Array,
      default: () => ([]),
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
      errors: [],
      dialogVisible: false,
      preview_title: '',
      form: {
        warehouse_id: '',
        bulk_invoices: [],
      },
      bulk_invoices: [
        {
          customer_id: '',
          invoice_number: '',
          invoice_date: '',
          amount: 0,
          bulk_invoices_data: [],
          bulk_invoices_header: [],
        },
      ],
      fill_fields_error: false,
      tableData: [],
      tableHeader: [],
      selected_index: 0,
    };
  },
  watch: {
    bulk_invoices() {
      this.blockRemoval = this.bulk_invoices.length <= 1;
    },
  },
  mounted() {
    this.addLine();
  },
  methods: {
    addLine(index) {
      this.fill_fields_error = false;

      const checkEmptyLines = this.bulk_invoices.filter(
        (detail) =>
          detail.customer_id === '' ||
          detail.invoice_number === '' ||
          detail.invoice_date === '' ||
          detail.bulk_invoices_data.length < 1 ||
          detail.bulk_invoices_header.length < 1
      );

      if (checkEmptyLines.length >= 1 && this.bulk_invoices.length > 0) {
        this.fill_fields_error = true;
        // this.bulk_invoices[index].seleted_category = true;
        return;
      } else {
        // if (this.bulk_invoices.length > 0)
        //     this.bulk_invoices[index].grade = '';

        this.bulk_invoices.push({
          customer_id: '',
          invoice_number: '',
          invoice_date: '',
          amount: 0,
          bulk_invoices_data: [],
          bulk_invoices_header: [],

        });
        this.selected_index = parseInt(this.bulk_invoices.length) - parseInt(1);
      }
    },
    removeLine(detailId) {
      this.fill_fields_error = false;
      if (!this.blockRemoval) {
        this.bulk_invoices.splice(detailId, 1);
      }
    },
    beforeUpload(file) {
      const isLt1M = file.size / 1024 / 1024 < 1;

      if (isLt1M) {
        return true;
      }

      this.$message({
        message: 'Please do not upload files larger than 1m in size.',
        type: 'warning',
      });
      return false;
    },
    handleSuccess({ results, header }) {
      const app = this;
      const index = app.selected_index;
      var amount = 0;
      results.forEach(element => {
        amount += element.Amount;
      });
      app.bulk_invoices[index].amount = amount;
      app.bulk_invoices[index].bulk_invoices_data = results;
      app.bulk_invoices[index].bulk_invoices_header = header;
    },
    previewData(index){
      const app = this;
      app.tableData = app.bulk_invoices[index].bulk_invoices_data;
      app.tableHeader = app.bulk_invoices[index].bulk_invoices_header;
      app.preview_title = 'Invoice Product Details for Invoice No.: ' + app.bulk_invoices[index].invoice_number;
      app.dialogVisible = true;
    },
    addNewInvoice() {
      const app = this;
      var form = app.form;
      // const checkEmptyFields = form.warehouse_id === null || form.warehouse_id === '';
      const checkEmptyLines = this.bulk_invoices.filter(
        (detail) =>
          detail.customer_id === '' ||
          detail.invoice_number === '' ||
          detail.invoice_date === '' ||
          detail.bulk_invoices_data.length < 1 ||
          detail.bulk_invoices_header.length < 1
      );

      if (checkEmptyLines.length > 0 || form.warehouse_id === '') {
        alert('Please fill the form fields completely and upload all Items for each row');
        return;
      } else {
        const load = saveBulkInvoices.loaderShow();
        form.bulk_invoices = app.bulk_invoices;
        saveBulkInvoices
          .store(form)
          .then((response) => {
            if (response.message === 'success') {
              app.$message({
                message: 'Bulk Invoices Uploaded Successfully!!!',
                type: 'success',
              });
              app.$router.push({ name: 'Invoices' });
            } else {
              app.errors = response.error;
            }

            load.hide();
          })
          .catch((error) => {
            load.hide();
            console.log(error.message);
          });
      }
    },
  },
};
</script>
