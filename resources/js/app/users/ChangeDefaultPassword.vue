<template>
  <div class="app-container">
    <legend>Your password needs to be updated. Please change your password below.</legend>
    <el-form>
      <el-form-item label="Password">
        <el-input v-model="user.password" type="password" />
      </el-form-item>
      <el-form-item label="Confirm Password">
        <el-input v-model="user.confirmPassword" type="password" />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="updatePassword">
          Update
        </el-button>
      </el-form-item>
    </el-form>
  </div>
</template>
<script>
import Resource from '@/api/resource';
const userPasswordResource = new Resource('users/update-password');
export default {
  name: 'ChangeDefaultPassword',
  data() {
    return {
      user: {},
    };
  },
  watch: {
    '$route': 'getUser',
  },
  created() {
    this.getUser();
  },
  methods: {
    async getUser() {
      const data = await this.$store.dispatch('user/getInfo');
      this.user = data;
    },
    async logout() {
      await this.$store.dispatch('user/logout');
      this.$router.push('/login');
    },
    updatePassword() {
      if (this.user.password === this.user.confirmPassword) {
        const load = userPasswordResource.loaderShow();
        userPasswordResource
          .update(this.user.id, this.user)
          .then(response => {
            load.hide();
            this.$store.dispatch('user/resetPasswordStatus', { p_status: 'custom' });
            this.$message({
              message: 'Password has been updated successfully',
              type: 'success',
            });
            this.logout();
          })
          .catch(error => {
            console.log(error);
            this.updating = false;
          });
      } else {
        alert('Password do not match');
      }
    },
  },
};
</script>
