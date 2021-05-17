<template>
  <el-card v-if="order">
    <el-tabs v-model="activeActivity">
      <el-tab-pane label="Order Summary" name="first">
        <section class="invoice">
          <!-- title row -->
          <div class="row">
            <div class="col-xs-12 page-header">
              <img src="svg/logo.png" alt="Company Logo" width="50">
              <span><label>{{ companyName }}</label></span>
            </div>
            <!-- /.col -->
          </div>
          <!-- info row -->
          <div class="row invoice-info">
            <div class="col-sm-4 invoice-col">
              <label>Customer Details</label>
              <address>
                <label>{{ order.customer.user.name.toUpperCase() }}</label><br>
                {{ (order.customer.type) ? order.customer.type.name.toUpperCase() : '' }}<br>
                Phone: {{ order.customer.user.phone }}<br>
                Email: {{ order.customer.user.email }}<br>
                {{ order.customer.user.address }}
              </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
              <label>Concerned Warehouse</label>
              <address>
                <strong>{{ order.warehouse.name.toUpperCase() }}</strong><br>
                {{ order.warehouse.address }}<br>
              </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
              <label>Order No.: {{ order.order_number }}</label><br>
              <label>Date:</label> {{ moment(order.created_at).format('MMMM Do YYYY, h:mm:ss a') }}<br>
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->

          <!-- Table row -->
          <div class="row">
            <div class="col-xs-12 table-responsive">
              <legend>Ordered Product</legend>
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Quantity</th>
                    <th>Product</th>
                    <th>SKU (S/N)</th>
                    <th>Description</th>
                    <th>Tax</th>
                    <th>Amount</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(order_item, index) in order.order_items" :key="index">
                    <td>{{ order_item.quantity }}</td>
                    <td>{{ order_item.item.name }}</td>
                    <td>{{ order_item.item.sku }}</td>
                    <td>{{ order_item.item.description }}</td>
                    <td>{{ (order_item.tax * 100).toFixed(2) }}%</td>
                    <td align="right">{{ order.currency.code + Number(order_item.total).toLocaleString() }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->

          <div class="row">
            <!-- accepted payments column -->
            <div class="col-xs-6">
              <p class="lead">Payment Methods:</p>
              <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
                Configure Payments for this order here
              </p>
            </div>
            <!-- /.col -->
            <div class="col-xs-6">
              <p class="lead">Amount Due</p>

              <div class="table-responsive">
                <table class="table">
                  <tbody>
                    <tr>
                      <td colspan="4" align="right"><label>Subtotal</label></td>
                      <td align="right">{{ order.currency.code + Number(order.subtotal).toLocaleString() }}</td>
                    </tr>
                    <tr>
                      <td colspan="4" align="right"><label>Discount</label></td>
                      <td align="right">{{ order.currency.code + Number(order.discount).toLocaleString() }}</td>
                    </tr>
                    <tr>
                      <td colspan="4" align="right"><label>Tax</label></td>
                      <td align="right">{{ order.currency.code + Number(order.tax).toLocaleString() }}</td>
                    </tr>
                    <tr>
                      <td colspan="4" align="right"><label>Grand Total</label></td>
                      <td align="right"><label style="color: green">{{ order.currency.code + Number(order.amount).toLocaleString() }}</label></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
        </section>
      </el-tab-pane>
      <el-tab-pane label="Order History" name="second">
        <div class="block">
          <el-timeline>
            <el-timeline-item v-for="(history, index) in order.histories" :key="index" :timestamp="moment(history.created_at).format('MMMM Do YYYY, h:mm:ss a')" placement="top">
              <el-card>
                <h4>Order status: {{ history.status_code.toUpperCase() }}</h4>
                <p>{{ history.description }}</p>
              </el-card>
            </el-timeline-item>
          </el-timeline>
        </div>
      </el-tab-pane>
    </el-tabs>
  </el-card>
</template>

<script>
import moment from 'moment';
import Resource from '@/api/resource';
const orderResource = new Resource('orders');

export default {
  props: {
    order: {
      type: Object,
      default: () => ({}),
    },
    page: {
      type: Object,
      default: () => ({
        option: 'order_details',
      }),
    },
    companyName: {
      type: String,
      default: () => ('Warehouse Management System'),
    },
  },
  data() {
    return {
      activeActivity: 'first',
      updating: false,
    };
  },
  methods: {
    moment,
    onSubmit() {
      this.updating = true;
      orderResource
        .update(this.order.id, this.order)
        .then(response => {
          this.updating = false;
          this.$message({
            message: 'User information has been updated successfully',
            type: 'success',
            duration: 5 * 1000,
          });
        })
        .catch(error => {
          console.log(error);
          this.updating = false;
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.order-activity {
  .order-block {
    .ordername, .description {
      display: block;
      margin-left: 50px;
      padding: 2px 0;
    }
    img {
      width: 40px;
      height: 40px;
      float: left;
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
    .order-images {
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
