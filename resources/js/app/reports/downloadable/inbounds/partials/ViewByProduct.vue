<template>
  <div>
    <div>
      <el-button :loading="downloadLoading" style="margin:0 0 20px 20px;" type="primary" icon="document" @click="handleDownload">
        Export Excel
      </el-button>
    </div>
    <v-client-table v-model="itemsInStock" :columns="columns" :options="options">
      <div slot="quantity" slot-scope="{row}" class="alert alert-info">
        {{ row.quantity }} {{ formatPackageType(row.item.package_type) }}

      </div>
      <div slot="in_transit" slot-scope="{row}" class="alert alert-warning">
        {{ row.in_transit }} {{ formatPackageType(row.item.package_type) }}

      </div>
      <div slot="supplied" slot-scope="{row}" class="alert alert-danger">
        {{ row.supplied }} {{ formatPackageType(row.item.package_type) }}

      </div>
      <div slot="reserved_for_supply" slot-scope="{row}" class="alert alert-default">
        {{ row.reserved_for_supply }} {{ formatPackageType(row.item.package_type) }}

      </div>
      <div slot="in_stock" slot-scope="{row}" class="alert alert-primary">
        {{ row.balance }} {{ formatPackageType(row.item.package_type) }}

      </div>
      <div slot="balance" slot-scope="{row}" class="alert alert-success">
        {{ (row.balance - row.reserved_for_supply) }} {{ formatPackageType(row.item.package_type) }}

      </div>
      <!-- <div slot="updated_at" slot-scope="{row}">
        {{ moment(row.created_at).fromNow() }}

      </div> -->
    </v-client-table>
  </div>
</template>
<script>
import moment from 'moment';
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
      columns: ['warehouse.name', 'item.name', 'quantity', 'in_transit', 'supplied', 'in_stock', 'reserved_for_supply', 'balance'],

      options: {
        headings: {
          'warehouse.name': 'Warehouse',
          'item.name': 'Product',
          quantity: 'Quantity Stocked',
          in_transit: 'In Transit',
          supplied: 'Supplied',
          in_stock: 'Physical Stock',
          balance: 'Main Balance',
          reserved_for_supply: 'Reserved for Supply',

          // id: 'S/N',
        },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['warehouse.name', 'item.name', 'quantity', 'in_transit', 'supplied', 'balance', 'in_stock', 'reserved_for_supply'],
        filterable: ['warehouse.name', 'item.name'],
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
        const multiHeader = [[this.tableTitle, '', '', '', '', '', '', '']];
        const tHeader = ['PRODUCT', 'QUANTITY STOCKED', 'IN TRANSIT', 'SUPPLIED', 'RESERVED', 'PHYSICAL STOCK', 'MAIN BALANCE'];
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
        if (j === 'item.name') {
          return v['item']['name'];
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
