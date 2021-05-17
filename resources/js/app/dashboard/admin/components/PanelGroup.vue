<template>
  <el-row :gutter="10" class="panel-group">
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <router-link :to="{name: 'ViewWarehouse'}">
        <div class="small-box bg-aqua-gradient">
          <div class="inner">
            <h3>{{ dataSummary.warehouses_count }}</h3>
            <p>Warehouses</p>
          </div>
          <div class="icon">
            <i class="el-icon-office-building" />
          </div>
        </div>
      </router-link>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <router-link :to="{name: 'Vehicles'}">
        <div class="small-box bg-green-gradient">
          <div class="inner">
            <h3>{{ dataSummary.vehicles_count }}</h3>
            <p>Vehicles</p>
          </div>
          <div class="icon">
            <i class="el-icon-truck" />
          </div>
        </div>
      </router-link>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <router-link :to="{name: 'ManageItem'}">
        <div class="small-box bg-maroon-gradient">
          <div class="inner">
            <h3>{{ dataSummary.products_count }}</h3>
            <p>Product Varieties</p>
          </div>
          <div class="icon">
            <i class="el-icon-present" />
          </div>
        </div>
      </router-link>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <router-link :to="{name: 'Invoices'}">
        <div class="small-box bg-red-gradient">
          <div class="inner">
            <h3>{{ dataSummary.pending_invoices_count }}</h3>
            <p>Pending Invoices</p>
          </div>
          <div class="icon">
            <i class="el-icon-s-order" />
          </div>
        </div>
      </router-link>
    </el-col>
  </el-row>
</template>

<script>

export default {
  props: {
    dataSummary: {
      type: Object,
      default: () => ({
        warehouses_count: 0,
        vehicles_count: 0,
        products_count: 0,
        pending_invoices_count: 0,
      }),
    },
  },
  methods: {
    // handleSetLineChartData(type) {
    //   this.$emit('handleSetLineChartData', type);
    // },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.panel-group {
  margin-top: 5px;
  .card-panel-col{
    margin-bottom: 5px;
  }
  .card-panel {
    height: 108px;
    cursor: pointer;
    font-size: 12px;
    position: relative;
    overflow: hidden;
    color: rgb(76, 182, 85);
    background: #fff;
    box-shadow: 4px 4px 40px rgba(0, 0, 0, .05);
    border-color: rgba(0, 0, 0, .05);
    &:hover {
      .card-panel-icon-wrapper {
        color: #fff;
      }
      .icon-people {
         background: #40c9c6;
      }
      .icon-message {
        background: #36a3f7;
      }
      .icon-money {
        background: #f4516c;
      }
      .icon-shopping {
        background: #34bfa3
      }
    }
    .icon-people {
      color: #40c9c6;
    }
    .icon-message {
      color: #36a3f7;
    }
    .icon-money {
      color: #f4516c;
    }
    .icon-shopping {
      color: #34bfa3
    }
    .card-panel-icon-wrapper {
      float: left;
      margin: 14px 0 0 14px;
      padding: 16px;
      transition: all 0.38s ease-out;
      border-radius: 6px;
    }
    .card-panel-icon {
      float: left;
      font-size: 48px;
    }
    .card-panel-description {
      float: right;
      font-weight: bold;
      margin: 26px;
      margin-left: 0px;
      .card-panel-text {
        line-height: 18px;
        color: rgba(0, 0, 0, 0.45);
        font-size: 16px;
        margin-bottom: 12px;
      }
      .card-panel-num {
        font-size: 20px;
      }
    }
  }
}
</style>
