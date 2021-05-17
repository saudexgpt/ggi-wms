<template>
  <div class="app-container">
    <!-- <item-details v-if="page.option== 'view_details'" :item-in-stock="order" :page="page" /> -->
    <!-- <add-new v-if="page.option== 'add_new'" :orders="orders" :params="params" :page="page" />
    <edit-item v-if="page.option=='edit_item'" :orders="orders" :order="order" :params="params" :page="page" @update="onEditUpdate" /> -->
    <div v-if="page.option==='list'" class="box">
      <div class="box-header">
        <h4 class="box-title">List of Orders {{ in_warehouse }}</h4>

        <span class="pull-right">
          <router-link v-if="checkPermission(['create order'])" :to="{name:'Create'}" class="btn btn-info"> Create New Order</router-link>
        </span>

      </div>
      <div class="box-body">
        <aside>
          <el-row>
            <el-col :xs="24" :sm="12" :md="12">
              <p for="">Select Warehouse</p>
              <el-select v-model="form.warehouse_index" placeholder="Select Warehouse" class="span" filterable @input="getOrders">
                <el-option v-for="(warehouse, index) in warehouses" :key="index" :value="index" :label="warehouse.name" />

              </el-select>

            </el-col>
            <el-col :xs="24" :sm="12" :md="12">
              <p for="">Select status</p>
              <el-select v-model="form.status" placeholder="Select Status" class="span" @input="getOrders">
                <el-option v-for="(status, index) in order_statuses" :key="index" :value="status.code" :label="status.name" />

              </el-select>

            </el-col>
          </el-row>
        </aside>
        <v-client-table v-model="orders" :columns="columns" :options="options">
          <div slot="amount" slot-scope="props">
            {{ props.row.currency.code + Number(props.row.amount).toLocaleString() }}
          </div>
          <div slot="created_at" slot-scope="props">
            {{ moment(props.row.created_at).format('MMMM Do YYYY, h:mm:ss a') }}
          </div>
          <div slot="action" slot-scope="props">
            <a class="btn btn-primary" @click="order=props.row; page.option='order_details'"><i class="el-icon-tickets" /> Details</a>
            <!-- <el-dropdown class="avatar-container right-menu-item hover-effect" trigger="click">
              <div class="avatar-wrapper" style="color: brown">
                <label style="cursor:pointer"><i class="el-icon-more-outline" /></label>
              </div>
              <el-dropdown-menu slot="dropdown" style="padding: 10px;">
                <el-dropdown-item v-if="props.row.order_status === 'pending' && checkPermission(['approve order'])">
                  <a @click="approveOrder(props.index, props.row);">Approve</a>
                </el-dropdown-item>
                <el-dropdown-item v-if="props.row.order_status === 'approved' && checkPermission(['approve order', 'deliver order'])" divided>
                  <a @click="deliverOrder(props.index, props.row);">Delivered</a>
                </el-dropdown-item>
                <el-dropdown-item v-if="props.row.order_status === 'pending' && checkPermission(['cancel order'])" divided>
                  <a @click="cancelOrder(props.index, props.row);">Cancel</a>
                </el-dropdown-item>
              </el-dropdown-menu>
            </el-dropdown> -->
          </div>

        </v-client-table>

      </div>

    </div>
    <div v-if="page.option==='order_details'">
      <a class="btn btn-danger no-print" @click="page.option='list'">Go Back</a>
      <order-details :order="order" :page="page" :company-name="params.company_name" />
    </div>
  </div>
</template>
<script>
import moment from 'moment';
import checkPermission from '@/utils/permission';
import checkRole from '@/utils/role';
import OrderDetails from './Details';
import Resource from '@/api/resource';
// import Vue from 'vue';
const necessaryParams = new Resource('fetch-necessary-params');
const fetchOrders = new Resource('order/general');
const approveOrderResource = new Resource('order/general/approve');
const deliverOrderResource = new Resource('order/general/deliver');
const cancelOrderResource = new Resource('order/general/cancel');
// const deleteItemInStock = new Resource('stock/items-in-stock/delete');
export default {
  components: { OrderDetails },
  data() {
    return {
      warehouses: [],
      orders: [],
      order_statuses: [],
      columns: ['action', 'order_number', 'customer.user.name', 'amount', 'created_at', 'order_status'],

      options: {
        headings: {
          'customer.user.name': 'Customer',
          order_number: 'Order Number',
          amount: 'Amount',
          created_at: 'Date',
          order_status: 'Status',

          // id: 'S/N',
        },
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['order_number', 'customer.user.name', 'created_at', 'order_status'],
        filterable: ['order_number', 'customer.user.name', 'created_at', 'order_status'],
      },
      page: {
        option: 'list',
      },
      params: [],
      form: {
        warehouse_index: '',
        warehouse_id: '',
        status: 'pending',
      },
      in_warehouse: '',
      order: {},
      selected_row_index: '',

    };
  },

  mounted() {
    this.fetchNecessaryParams();
  },
  beforeDestroy() {

  },
  methods: {
    moment,
    checkPermission,
    checkRole,
    fetchNecessaryParams() {
      const app = this;
      necessaryParams.list()
        .then(response => {
          app.params = response.params;
          app.warehouses = response.params.warehouses;
          app.order_statuses = response.params.order_statuses;
          if (app.warehouses.length > 0) {
            app.form.warehouse_id = app.warehouses[0];
            app.form.warehouse_index = 0;
            app.getOrders();
          }
        });
    },
    getOrders() {
      const app = this;
      const loader = fetchOrders.loaderShow();

      const param = app.form;
      param.warehouse_id = app.warehouses[param.warehouse_index].id;
      fetchOrders.list(param)
        .then(response => {
          app.orders = response.orders;
          app.in_warehouse = 'in ' + app.warehouses[param.warehouse_index].name;
          loader.hide();
        })
        .catch(error => {
          loader.hide();
          console.log(error.message);
        });
    },
    deliverOrder(index, order){
      const app = this;
      const param = { status: 'delivered' };
      deliverOrderResource.update(order.id, param)
        .then(response => {
          app.orders.splice(index - 1, 1);
        });
    },
    approveOrder(index, order){
      const app = this;
      const param = { status: 'approved' };
      approveOrderResource.update(order.id, param)
        .then(response => {
          app.orders.splice(index - 1, 1);
        });
    },
    cancelOrder(index, order){
      const app = this;
      const param = { status: 'cancelled' };
      cancelOrderResource.update(order.id, param)
        .then(response => {
          app.orders.splice(index - 1, 1);
        });
    },

  },
};
</script>
