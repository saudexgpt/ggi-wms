<template>
  <div>
    <div>
      <label class="radio-label" style="padding-left:0;">Filename: </label>
      <el-input v-model="filename" :placeholder="$t('excel.placeholder')" style="width:340px;" prefix-icon="el-icon-document" />
      <el-button :loading="downloadLoading" style="margin:0 0 20px 20px;" type="primary" icon="document" @click="handleDownload">
        Export Excel
      </el-button>
    </div>
    <v-client-table v-model="itemsInStock" :columns="columns" :options="options">
      <div slot="quantity" slot-scope="{row}" class="alert alert-info">
        {{ row.quantity }} {{ formatPackageType(row.item_stock.item.package_type) }}

      </div>
      <div slot="in_transit" slot-scope="{row}" class="alert alert-warning">
        {{ row.in_transit }} {{ formatPackageType(row.item_stock.item.package_type) }}

      </div>
      <div slot="supplied" slot-scope="{row}" class="alert alert-danger">
        {{ row.supplied }} {{ formatPackageType(row.item_stock.item.package_type) }}

      </div>
      <div slot="balance" slot-scope="{row}" class="alert alert-success">
        {{ row.balance }} {{ formatPackageType(row.item_stock.item.package_type) }}

      </div>
      <div slot="created_at" slot-scope="{row}">
        {{ moment(row.created_at).fromNow() }}

      </div>
      <div slot="updated_at" slot-scope="{row}">
        {{ moment(row.updated_at).fromNow() }}

      </div>
    </v-client-table>
  </div>
</template>
<script>
import moment from 'moment';
import { parseTime } from '@/utils';
export default {
  props: {
    itemsInStock: {
      type: Array,
      default: () => ([]),
    },
    tableTitle: {
      type: String,
      default: () => ([]),
    },
  },
  data() {
    return {
      columns: ['item_stock.item.name', 'item_stock.batch_no', 'batch_no', 'expiry_date', 'quantity', 'in_transit', 'supplied', 'balance', 'created_at', 'updated_at'],

      options: {
        headings: {
          'item_stock.item.name': 'Product',
          'item_stock.batch_no': 'Batch No.',
          batch_no: 'Sub-Batch No.',
          expiry_date: 'Expires',
          quantity: 'Quantity Stocked',
          in_transit: 'In Transit',
          supplied: 'Supplied',
          balance: 'Balance',
          created_at: 'Stock Date',
          updated_at: 'Last Modified',

          // id: 'S/N',
        },
        pagination: {
          dropdown: true,
          chunk: 20,
        },
        filterByColumn: true,
        texts: {
          filter: 'Search:',
        },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['item_stock.item.name', 'item_stock.batch_no', 'batch_no', 'expiry_date', 'created_at', 'updated_at'],
        filterable: ['item_stock.item.name', 'item_stock.batch_no', 'batch_no', 'expiry_date', 'created_at', 'updated_at'],
      },
      page: {
        option: 'list',
      },
      downloadLoading: false,
      filename: 'Products',
    };
  },

  mounted() {
  },
  beforeDestroy() {

  },
  methods: {
    moment,
    formatPackageType(type){
      // var formated_type = type + 's';
      // if (type === 'Box') {
      //   formated_type = type + 'es';
      // }
      return type;
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const multiHeader = [[this.tableTitle, '', '', '', '', '', '', '', '', '']];
        const tHeader = ['PRODUCT', 'BATCH NO.', 'SUB-BATCH NO.', 'EXPIRES', 'QUANTITY STOCKED', 'IN TRANSIT', 'SUPPLIED', 'BALANCE', 'STOCK DATE', 'LAST MODIFIED'];
        const filterVal = this.columns;
        const list = this.itemsInStock;
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
      return jsonData.map(v => filterVal.map(j => {
        if (j === 'created_at') {
          return parseTime(v[j]);
        }
        if (j === 'updated_at') {
          return parseTime(v[j]);
        }
        if (j === 'item_stock.item.name') {
          return v['item_stock']['item']['name'];
        }
        if (j === 'item_stock.batch_no') {
          return v['item_stock']['batch_no'];
        }
        return v[j];
      }));
    },
  },
};
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.alert {
  padding: 5px;
  margin: -5px;
  text-align: right;
}
td {
  padding: 0px !important;
}

</style>
