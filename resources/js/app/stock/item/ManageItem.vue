<template>
  <div class="app-container">
    <add-new v-if="page.option== 'add_new'" :categories="categories" :page="page" :items="items" :params="params" />
    <edit-item v-if="page.option=='edit_item'" :categories="categories" :page="page" :item="item" :params="params" @update="onEditUpdate" />
    <div v-if="page.option=='list'" class="box">
      <div class="box-header">
        <h4 class="box-title">List of Products</h4>
        <span class="pull-right">
          <a v-if="canCreateNewProduct" class="btn btn-info" @click="page.option = 'add_new'"> Add New</a>
        </span>
      </div>
      <div class="box-body">
        <div>
          <el-button :loading="downloadLoading" style="margin:0 0 20px 20px;" type="primary" icon="document" @click="handleDownload">
            Export Excel
          </el-button>
        </div>
        <v-client-table v-model="items" :columns="columns" :options="options">

          <div slot="category.name" slot-scope="{row}">
            {{ row.category.name }}
          </div>
          <div slot="price.sale_price" slot-scope="{row}">
            <span align="right">{{ '₦' + Number(row.price.sale_price).toLocaleString() }}</span>
          </div>
          <div slot="action" slot-scope="props">
            <a class="btn btn-primary" @click="item=props.row; selected_row_index=props.index; page.option = 'edit_item'"><i class="fa fa-edit" /> </a>
            <a class="btn btn-danger" @click="confirmDelete(props)"><i class="fa fa-trash" /> </a>
          </div>

        </v-client-table>

      </div>

    </div>

  </div>
</template>
<script>
import AddNew from './partials/AddNew';
import EditItem from './partials/EditItem';
import Resource from '@/api/resource';
const necessaryParams = new Resource('fetch-necessary-params');
const itemCategory = new Resource('stock/item-category');
const generalProducts = new Resource('stock/general-items');
const deleteGeneralProducts = new Resource('stock/general-items/delete');
export default {
  components: { AddNew, EditItem },
  props: {
    canCreateNewProduct: {
      type: Boolean,
      default: () => (true),
    },
  },
  data() {
    return {
      categories: [],
      items: [],
      columns: ['action', 'name', 'category.name', 'package_type', 'quantity_per_carton', 'price.sale_price'],

      options: {
        headings: {
          name: 'Name',
          'category.name': 'Category',
          package_type: 'Package Type',
          quantity_per_carton: 'Quantity Per Carton',
          'price.sale_price': 'Rate',
          // id: 'S/N',
        },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['name', 'category.name'],
        filterable: ['name', 'category.name'],
      },
      page: {
        option: 'list',
      },
      item: {

      },
      params: [],
      selected_row_index: '',
      downloadLoading: false,

    };
  },

  mounted() {
    this.fetchGeneralProducts();
    this.fetchNecessaryParams();
    this.fetchCategories();
  },
  beforeDestroy() {

  },
  methods: {
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list()
        .then(response => {
          app.params = response.params;
        });
    },
    fetchCategories() {
      const app = this;
      // let loader = Vue.$loading.show({});
      // const load = itemCategory.loaderShow();
      const param = app.form;
      itemCategory.list(param)
        .then(response => {
          app.categories = response.categories;
          // load.hide();
        });
    },
    fetchGeneralProducts() {
      const app = this;
      const load = generalProducts.loaderShow();
      generalProducts.list()
        .then(response => {
        // app.categories = response.categories

          app.items = response.items;
          load.hide();
        });
    },
    onEditUpdate(updated_row) {
      const app = this;
      // app.items_in_stock.splice(app.itemInStock.index-1, 1);
      app.items[app.selected_row_index - 1] = updated_row;
    },

    confirmDelete(props) {
      // this.loader();

      const row = props.row;
      const app = this;
      const message = 'This delete action cannot be undone. Click OK to confirm';
      if (confirm(message)) {
        const load = deleteGeneralProducts.loaderShow();
        deleteGeneralProducts.destroy(row.id, row)
          .then(response => {
            app.items.splice(row.index - 1, 1);
            this.$message({
              message: 'Item has been deleted',
              type: 'success',
            });
            load.hide();
          })
          .catch(error => {
            load.hide();
            console.log(error);
          });
      }
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        // const multiHeader = [['List of Products', '', '', '', '']];
        const tHeader = ['PRODUCT', 'CATEGORY', 'PACKAGE_TYPE', 'QUANTITY_PER_CARTON', 'RATE'];
        const filterVal = ['name', 'category.name', 'package_type', 'quantity_per_carton', 'price.sale_price'];
        const list = this.items;
        const data = this.formatJson(filterVal, list);
        excel.export_json_to_excel({
          // multiHeader,
          header: tHeader,
          data,
          filename: 'Product-List',
          autoWidth: true,
          bookType: 'csv',
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => {
        if (j === 'category.name') {
          return v['category']['name'];
        } else {
          if (j === 'price.sale_price') {
            if (v['price']){
              return v['price']['sale_price'];
            } else {
              return '0';
            }
          }
        }
        return v[j];
      }));
    },
  },
};
</script>
