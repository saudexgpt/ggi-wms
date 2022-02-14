<template>
  <div class="app-container">
    <h4 v-if="customer">Transactions for {{ customer.user.name }}</h4>
    <v-client-table v-model="transactions" :columns="columns" :options="options">

      <div slot="quantity_supplied" slot-scope="props">
        {{ props.row.quantity_supplied + ' ' + props.row.package_type }}
      </div>
      <div slot="updated_at" slot-scope="props">
        {{ moment(props.row.updated_at).format('MMMM Do YYYY') }}
      </div>

    </v-client-table>

  </div>
</template>

<script>
import moment from 'moment';
import Resource from '@/api/resource';

const customerTransactionResource = new Resource('invoice/transactions/customer');
export default {
  name: 'Transactions',
  components: {},
  data() {
    return {
      customer: '',
      transactions: [],
      columns: ['name', 'invoice_number', 'waybill_no', 'quantity_supplied', 'status', 'updated_at'],

      options: {
        headings: {
          name: 'Product',
          invoice_number: 'Order No.',
          waybill_no: 'Waybill No.',
          updated_at: 'Date',

          // id: 'S/N',
        },
        pagination: {
          dropdown: true,
          chunk: 10,
        },
        perPage: 10,
        // editableColumns:['name', 'category.name', 'sku'],
        sortable: ['invoice_number', 'waybill_no', 'status', 'updated_at'],
        filterable: ['invoice_number', 'waybill_no', 'status', 'updated_at'],
        // filterable: ['invoice.invoice_number', 'invoices', 'waybill_no', 'trip_no', 'created_at', 'updated_at'],
      },
    };
  },
  created() {
    this.getTransaction();
  },
  methods: {
    moment,
    getTransaction() {
      const id = this.$route.params && this.$route.params.id;
      customerTransactionResource.get(id).then(response => {
        this.transactions = response.transactions;
        this.customer = response.customer;
      });
    },
  },
};
</script>
