<template>
  <div class="dashboard-editor-container">
    <el-card>
      <el-carousel v-if="useCarousel" :interval="10000" indicator-position="outside" :height="'500px'">
        <el-carousel-item>
          <products-in-stock v-if="warehouses.length > 0" :warehouses="warehouses" />
        </el-carousel-item>
        <el-carousel-item>
          <fleet-reports v-if="warehouses.length > 0" :warehouses="warehouses" />
        </el-carousel-item>
        <el-carousel-item>
          <delivery-cost v-if="warehouses.length > 0" :warehouses="warehouses" />
        </el-carousel-item>
      </el-carousel>
      <el-tabs v-if="!useCarousel" v-model="activeActivity">
        <el-tab-pane label="Stock Report" name="first">
          <products-in-stock v-if="warehouses.length > 0" :warehouses="warehouses" />
        </el-tab-pane>
        <el-tab-pane label="Fleet Reports" name="second">
          <fleet-reports v-if="warehouses.length > 0" :warehouses="warehouses" />
        </el-tab-pane>
        <el-tab-pane label="Delivery Cost" name="third">
          <delivery-cost v-if="warehouses.length > 0" :warehouses="warehouses" />
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
</template>

<script>
import ProductsInStock from './components/ProductsInStock';
import FleetReports from './components/FleetReports';
import DeliveryCost from './components/DeliveryCost';
import Resource from '@/api/resource';
// import Vue from 'vue';
const necessaryParams = new Resource('fetch-necessary-params');
export default {
  name: 'AdminReport',
  components: {
    ProductsInStock,
    FleetReports,
    DeliveryCost,
    // LineChart,
    // RaddarChart,
    // PieChart,
    // BarChart,
    // TransactionTable,
    // TodoList,
    // BoxCard,
  },
  props: {
    useCarousel: {
      type: Boolean,
      default: () => (false),
    },
  },
  data() {
    return {
      activeActivity: 'first',
      data_summary: '',
      warehouses: [],
    };
  },
  mounted() {
    this.fetchNecessaryParams();
    // window.Echo.private('App.Laravue.Models.User.' + 1)
    //         .notification((notification) => {
    //           console.log(notification);
    //         });
  },
  methods: {
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list()
        .then(response => {
          app.warehouses = response.params.warehouses;
        });
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.dashboard-editor-container {
  padding: 10px;
  background-color: rgb(240, 242, 245);
  .chart-wrapper {
    background: #fff;
    padding: 16px 16px 0;
    margin-bottom: 10px;
  }
}
</style>
