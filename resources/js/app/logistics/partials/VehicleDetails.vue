<template>
  <div>
    <span class="">
      <a class="btn btn-danger" @click="page.option = 'list'"> Back</a>
    </span>
    <el-card v-if="vehicle">
      <el-tabs v-model="activeActivity">
        <el-tab-pane label="Details" name="first">
          <div class="col-md-4">
            <div class="box">
              <div class="box-header">
                <p class="box-title">Vehicle Details</p>
              </div>
              <div class="box-body">
                <div class="vehicle-icon box-center">
                  <i class="el-icon-truck" />
                </div>
                <div class="user-name">
                  <label>Plate No.:</label> {{ vehicle.plate_no }}<br>
                  <label>VIN:</label> {{ vehicle.vin }}<br>
                  <label>Brand:</label> {{ vehicle.brand }}<br>
                  <label>Model:</label> {{ vehicle.model }}<br>
                  <label>Engine:</label> {{ vehicle.engine_type }}<br>
                  <label>Initial Mileage:</label> {{ vehicle.initial_mileage }}<br>
                  <label>Purchase Date:</label> {{ vehicle.purchase_date }}<br>
                  <label>Extra Note:</label> {{ vehicle.notes }}<br>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-8">
            <div class="box">
              <div class="box-header">
                <p class="box-title">Drivers</p>
              </div>
              <div class="box-body">
                <div v-for="(vehicle_driver, index) in vehicle.vehicle_drivers" :key="index" class="col-md-6">
                  <div class="small-box" align="center">
                    <img :src="vehicle_driver.driver.user.photo" width="100"><br>
                    <label>Name:</label> {{ vehicle_driver.driver.user.name }}<br>
                    <label>Role:</label> {{ vehicle_driver.type }} Driver<br>
                    <label>Phone:</label> {{ vehicle_driver.driver.user.phone }}<br>
                    <label>License No.:</label> {{ vehicle_driver.driver.license_no }}<br>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </el-tab-pane>
        <el-tab-pane label="Expenses" name="second">
          <el-timeline>
            <el-timeline-item v-for="(vehicle_expense, index) in vehicle.expenses" :key="index" :timestamp="moment(vehicle_expense.created_at).format('MMMM Do YYYY, h:mm:ss a')" placement="top">
              <el-card>
                <span class="pull-right">[{{ vehicle_expense.status }}]</span>
                <h4>
                  {{ vehicle_expense.expense_type }}
                </h4>
                <p>{{ vehicle_expense.details }}</p>
                <p>{{ 'Amount: '+ currency + Number(vehicle_expense.amount).toLocaleString() }}</p>
              </el-card>
            </el-timeline-item>
          </el-timeline>
        </el-tab-pane>
        <el-tab-pane label="Condition History" name="third">
          <el-timeline>
            <el-timeline-item v-for="(condition, index) in vehicle.conditions" :key="index" :timestamp="moment(condition.created_at).format('MMMM Do YYYY, h:mm:ss a')" placement="top">
              <el-card>
                <span class="pull-right">[{{ condition.status }}]</span>
                <h4>
                  {{ condition.condition }}
                </h4>
                <p>{{ condition.description }}</p>
              </el-card>
            </el-timeline-item>
          </el-timeline>
        </el-tab-pane>
      </el-tabs>
    </el-card>
  </div>
</template>

<script>
import moment from 'moment';
export default {
  props: {
    vehicle: {
      type: Object,
      default: () => {},
    },
    page: {
      type: Object,
      default: () => ({
        option: 'vehicle_details',
      }),
    },
  },
  data() {
    return {
      activeActivity: 'first',
      currency: '₦',
    };
  },
  methods: {
    moment,
  },
};
</script>

<style lang="scss" scoped>
.vehicle-icon {
  font-size: 80px;
}
.user-activity {
  .user-block {
    .username, .description {
      display: block;
      margin-left: 50px;
      padding: 2px 0;
    }
    :after {
      clear: both;
    }
    .img-circle {
      border-radius: 50%;
      border: 2px solid #d2d6de;
      padding: 2px;
    }
    span {
      font-weight: 500;
      font-size: 12px;
    }
  }
  .post {
    font-size: 14px;
    border-bottom: 1px solid #d2d6de;
    margin-bottom: 15px;
    padding-bottom: 15px;
    color: #666;
    .image {
      width: 100%;
    }
    .user-images {
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
      &:hover, &:focus {
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

  .el-carousel__item:nth-child(2n+1) {
    background-color: #d3dce6;
  }
}
</style>
