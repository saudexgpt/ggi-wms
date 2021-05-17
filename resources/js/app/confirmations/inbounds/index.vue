<template>
  <div class="">
    <aside>
      <el-row :gutter="10">
        <el-col v-if="params.warehouses.length > 0" :xs="24" :sm="8" :md="8">
          <label for="">Select Warehouse</label>
          <el-select v-model="warehouse_index" placeholder="Select Warehouse" class="span" filterable>
            <el-option v-for="(warehouse, index) in warehouses" :key="index" :value="index" :label="warehouse.name" />

          </el-select>

        </el-col>
        <el-col :xs="24" :sm="6" :md="6">
          <label for="">Filter by: </label>
          <el-select v-model="view_by" placeholder="Select Option" class="span" @input="fetchItemStocks">
            <el-option v-for="(view_option, index) in view_options" :key="index" :value="view_option.key" :label="view_option.name" />

          </el-select>

        </el-col>
        <el-col :xs="24" :sm="10" :md="10">
          <br>
          <el-popover
            placement="right"
            trigger="click"
          >
            <date-range-picker :from="$route.query.from" :to="$route.query.to" :panel="panel" :panels="panels" :submit-title="submitTitle" :future="future" @update="setDateRange" />
            <el-button id="pick_date" slot="reference" type="success">
              <i class="el-icon-date" /> Pick Date Range
            </el-button>
          </el-popover>
        </el-col>

      </el-row>
    </aside>
    <div v-if="view_by != null" class="box">
      <div class="box-header">
        <h4 class="box-title">{{ table_title }}</h4>
      </div>
      <div class="box-body">
        <view-by-sub-batch v-if="view_by === 'sub_batch'" :items-in-stock="items_in_stock" :table-title="table_title" />
        <view-by-batch v-if="view_by === 'batch'" :items-in-stock="items_in_stock" :table-title="table_title" />
        <view-by-product v-if="view_by === 'product'" :items-in-stock="items_in_stock" :table-title="table_title" />
      </div>

    </div>

  </div>
</template>
<script>
import ViewBySubBatch from './partials/ViewBySubBatch';
import ViewByBatch from './partials/ViewByBatch';
import ViewByProduct from './partials/ViewByProduct';
import Resource from '@/api/resource';
// const fetchWarehouse = new Resource('warehouse/fetch-warehouse');
const itemsInStock = new Resource('reports/tabular/products-in-stock');
export default {
  components: { ViewBySubBatch, ViewByBatch, ViewByProduct },
  props: {
    params: {
      type: Object,
      default: () => ([]),
    },
  },
  data() {
    return {
      warehouses: [],
      items_in_stock: [],
      view_by: null,
      form: {
        warehouse_id: '',
        from: '',
        to: '',
        panel: '',
      },
      submitTitle: 'Fetch Report',
      panel: 'month',
      future: false,
      panels: ['range', 'week', 'month', 'quarter', 'year'],
      show_calendar: false,
      table_title: '',
      selected_row_index: '',
      warehouse_index: '',
      view_options: [
        { key: 'product', name: 'Products' },
        { key: 'batch', name: 'Batches' },
        { key: 'sub_batch', name: 'Sub Batches' },
      ],
    };
  },

  mounted() {
    this.warehouses = this.params.warehouses;
    this.form.warehouse_id = this.warehouses[0].id;
  },
  beforeDestroy() {

  },
  methods: {
    showCalendar(){
      this.show_calendar = !this.show_calendar;
    },
    format(date) {
      var month = date.toLocaleString('en-US', { month: 'short' });
      return month + ' ' + date.getDate() + ', ' + date.getFullYear();
    },
    setDateRange(values){
      const app = this;
      document.getElementById('pick_date').click();
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
      app.fetchItemStocks();
    },
    fetchItemStocks() {
      const app = this;
      app.show_progress = true;
      app.form.warehouse_id = app.warehouses[app.warehouse_index].id;
      const loader = itemsInStock.loaderShow();
      const param = app.form;
      param.view_by = app.view_by;
      itemsInStock.list(param)
        .then(response => {
          app.items_in_stock = response.items_in_stock;
          app.table_title = 'List of Products in ' + app.warehouses[app.warehouse_index].name + ' from: ' + app.form.from + ' to: ' + app.form.to;
          loader.hide();
        })
        .catch(error => {
          loader.hide();
          console.log(error.message);
        });
    },
  },
};
</script>
