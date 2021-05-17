<template>
  <el-card v-if="user" class="box-card user-bio">
    <div slot="header" class="clearfix">
      <span>Contact</span>
    </div>
    <div class="user-education user-bio-section">
      <div class="user-bio-section-header">
        <i class="el-icon-mobile-phone" />
        <span>Phone: {{ user.phone }}</span>
      </div>
    </div>
    <div class="user-education user-bio-section">
      <div class="user-bio-section-header">
        <i class="el-icon-message" />
        <span>Email: {{ user.email }}</span>
      </div>
    </div>
    <div class="user-education user-bio-section">
      <div class="user-bio-section-header">
        <i class="el-icon-location" />
        <span>Address</span>
      </div>
      <div class="user-bio-section-body">
        <div class="text-muted">
          {{ user.address }}
        </div>
      </div>
    </div>

  </el-card>
</template>

<script>
export default {
  props: {
    user: {
      type: Object,
      default: () => ({
      }),
    },
  },
};
</script>

<style lang="scss" scoped>
.user-bio {
  margin-top: 20px;
  color: #606266;
  span {
    padding-left: 4px;
  }
  .user-bio-section {
    font-size: 14px;
    padding: 15px 0;
    .user-bio-section-header {
      border-bottom: 1px solid #dfe6ec;
      padding-bottom: 10px;
      margin-bottom: 10px;
      font-weight: bold;
    }
  }
}
</style>
