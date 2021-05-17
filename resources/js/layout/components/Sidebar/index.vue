<template>
  <el-scrollbar wrap-class="scrollbar-wrapper">
    <el-menu
      :show-timeout="200"
      :default-active="$route.path"
      :collapse="isCollapse"
      mode="vertical"
      background-color="#304156"
      text-color="#bfcbd9"
      active-text-color="#409EFF"
    >
      <div align="center">
        <img src="/svg/logo.png" style="width: 50%;">
      </div>
      <sidebar-item
        v-for="route in routes"
        :key="route.path"
        :item="route"
        :base-path="route.path"
      />
    </el-menu>
  </el-scrollbar>
</template>

<script>
import { mapGetters } from 'vuex';
import SidebarItem from './SidebarItem';

export default {
  components: { SidebarItem },
  computed: {
    ...mapGetters(['sidebar', 'permission_routers']),
    routes() {
      return this.$store.state.permission.routes;
    },
    isCollapse() {
      return !this.sidebar.opened;
    },
  },
};
</script>
<style>
.el-scrollbar__bar.is-vertical {
  width: 10px !important;
  top: 2px;
}
.el-scrollbar__thumb {
  position: relative;
  display: block;
  width: 0;
  height: 0;
  cursor: pointer;
  border-radius: inherit;
  background-color: #06700b !important;
  transition: 0.3s background-color;
}
</style>
