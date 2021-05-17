<template>
  <el-dropdown class="avatar-container right-menu-item hover-effect" trigger="click">
    <a class="notification" @click="loadNotifications">
      <i class="el-icon-bell" />
      <span v-if="show_notification && notifications.length < 99" class="badge">{{ notifications.length }}</span>
      <span v-if="show_notification && notifications.length > 99" class="badge">99+</span>
    </a>
    <el-dropdown-menu slot="dropdown" />
    <!-- <el-dropdown-menu slot="dropdown" style="height: 350px; overflow: auto">
      <el-dropdown-item v-for="(notification, index) in notifications" :key="index" divided>
        {{ notificatio }}
        <strong style="display:block;">{{ notification.title }}</strong>
        <small>{{ notification.description }}</small>
      </el-dropdown-item>
    </el-dropdown-menu> -->
  </el-dropdown>
</template>
<script>
export default {
  data() {
    return {
      show_notification: true,
    };
  },
  computed: {
    notifications() {
      return this.$store.getters.notifications;
    },
  },
  methods: {
    loadNotifications() {
      this.show_notification = false;
      // then update notifications as read
      this.$router.push('/notifications');
    },
  },
};
</script>
<style lang="scss" scoped>
.notification {
  display: inline-block;
}

.notification .el-icon-bell {
  font-size: 27px;
}

.notification .badge {
  position: absolute;
  top: 3px;
  right: -5px;
  padding: 3px 5px;
  border-radius: 50%;
  background: rgb(12, 133, 1);
  color: white;
}
</style>
