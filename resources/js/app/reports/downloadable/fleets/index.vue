<template>
  <div class="">
    <el-tabs v-model="activeTab" style="margin-top:15px;" type="border-card">
      <el-tab-pane label="Vehicles" name="Vehicles">
        <vehicles v-if="activeTab=='Vehicles'" :can-add-new="false" />
      </el-tab-pane>
      <el-tab-pane label="Vehicle Expenses" name="Expenses" style="padding: 0!important;">
        <vehicle-expenses v-if="activeTab=='Expenses'" :can-add-new="false" />
      </el-tab-pane>
    </el-tabs>
  </div>
</template>
<script>
import Vehicles from '@/app/logistics/Vehicles';
// import Waybill from './partials/Waybill';
import VehicleExpenses from '@/app/logistics/VehicleExpenses';
// const deleteItemInStock = new Resource('stock/items-in-stock/delete');
export default {
  components: { Vehicles, VehicleExpenses },
  props: {
    params: {
      type: Object,
      default: () => ([]),
    },
  },
  data() {
    return {
      activeTab: 'Vehicles',
    };
  },
};
</script>
