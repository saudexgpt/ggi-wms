<template>
  <el-dialog :visible.sync="dialogVisible">
    <div class="form-container" align="center">
      <h3>You have been inactive for a while and we will have to sign you out.</h3>
      <h4>To keep working, hover your mouse or press a key on your keyboard</h4>
      <h4>{{ time/1000 }} seconds left to automatic sign out</h4>
    </div>
  </el-dialog>
</template>
<script>
export default {
  data() {
    return {
      time: 15000,
      dialogVisible: false,
    };
  },
  created() {
    const timerId = setInterval(() => {
      this.time -= 1000;
      if (!this.$store.state.idleVue.isIdle) {
        clearInterval(timerId);
      } else {
        this.dialogVisible = true;
      }
      if (this.time < 1) {
        clearInterval(timerId);
        // Your logout function should be over here
        this.logout();
      }
    }, 1000);
  },
  methods: {
    async logout() {
      await this.$store.dispatch('user/logout');
      // this.$router.push(`/login?redirect=${this.$route.fullPath}`);
      this.$router.push(`/login`);
    },
  },
};
</script>
