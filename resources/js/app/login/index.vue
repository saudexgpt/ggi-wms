<template>
  <div class="login-container">
    <el-form
      ref="loginForm"
      v-loading="loading"
      :model="loginForm"
      :rules="loginRules"
      class="login-form"
      auto-complete="on"
      label-position="left"
    >
      <div align="center">
        <img :src="img" alt="Company Logo" width="100">
        <h4 class="title">GGI Warehouse Management System</h4>
      </div>
      <mdb-input
        v-model="loginForm.email"
        outline
        far
        icon="user"
        name="email"
        type="email"
        auto-complete="off"
        label="Email"
        required="required"
      />
      <!-- <md-input  v-model="loginForm.email" icon="user" name="email" type="text" auto-complete="off"   required="required">Email or User ID</md-input> -->

      <!-- <md-input  v-model="loginForm.password" icon="lock" name="password" :type="pwdType" auto-complete="off"  required="required" @keyup.enter.native="handleLogin">Password</md-input> -->

      <mdb-input
        v-model="loginForm.password"
        outline
        icon="lock"
        :type="pwdType"
        auto-complete="off"
        label="Password"
        name="password"
        @keyup.enter.native="handleLogin"
      />
      <!-- <p class="font-small blue-text d-flex justify-content-end pb-3" align="right"><router-link :to="{name: 'passwordRecovery'}">Forgot Password?</router-link></p> -->

      <!-- <span class="show-pwd pull-right" @click="showPwd">
        <svg-icon icon-class="eye-open" />
      </span> -->
      <mdb-btn
        :loading="loading"
        color="success"
        style="width: 100%"
        @click.native.prevent="handleLogin"
      >Login</mdb-btn>
      <!-- <div class="tips">
        <div align="right">Yet to register?
          <router-link :to="{name: 'Register'}">Click Here</router-link>
        </div>
      </div> -->
    </el-form>
  </div>
</template>

<script>
import 'mdbvue/lib/css/mdb.min.css';
// import LangSelect from '@/components/LangSelect';
// import { validEmail } from '@/utils/validate';
// import MdInput from '@/components/MDinput';
export default {
  // components: {MdInput},
  name: 'Login',
  // components: { LangSelect },
  data() {
    // const validateEmail = (rule, value, callback) => {
    //   if (!validEmail(value)) {
    //     callback(new Error('Please enter the correct email'));
    //   } else {
    //     callback();
    //   }
    // };
    const validateUsername = (rule, value, callback) => {
      if (value.length < 1) {
        callback(new Error('Please enter a valid User ID or Email'));
      } else {
        callback();
      }
    };
    const validatePass = (rule, value, callback) => {
      if (value.length < 4) {
        callback(new Error('Password cannot be less than 4 digits'));
      } else {
        callback();
      }
    };
    return {
      img: 'images/logo.png',
      loginForm: {
        email: '',
        password: '',
      },
      loginRules: {
        email: [
          { required: true, trigger: 'blur', validator: validateUsername },
        ],
        password: [
          { required: true, trigger: 'blur', validator: validatePass },
        ],
      },
      loading: false,
      pwdType: 'password',
      redirect: undefined,
    };
  },
  watch: {
    $route: {
      handler: function(route) {
        this.redirect = route.query && route.query.redirect;
      },
      immediate: true,
    },
  },
  methods: {
    showPwd() {
      if (this.pwdType === 'password') {
        this.pwdType = '';
      } else {
        this.pwdType = 'password';
      }
    },
    handleLogin() {
      this.$refs.loginForm.validate((valid) => {
        if (valid) {
          this.loading = true;
          this.$store
            .dispatch('user/login', this.loginForm)
            .then(() => {
              // this.$router.push({ path: this.redirect || '/' });
              window.location = '/';
              this.loading = false;
            })
            .catch((response) => {
              this.loading = false;
              // console.log(response)
            });
        } else {
          console.log('Please fill the form accordingly');
          return false;
        }
      });
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss">
$primary: #116809;
$secondary: #666;
$dark_gray: #889aa4;
$light_gray: #eee;
.login-container {
  position: fixed;
  height: 100%;
  width: 100%;
  background-image: url('../../assets/bg/warehouse-management-system.jpg');
  .login-form {
    position: absolute;
    left: 50;
    right: 0;
    width: 520px;
    max-width: 100%;
    height: 100%;
    padding: 35px 35px 15px 35px;
    background-color: #fff;
    opacity: 0.9;
  }
  .tips {
    font-size: 14px;
    color: #000000;
    margin-bottom: 10px;
    span {
      &:first-of-type {
        margin-right: 16px;
      }
    }
  }
  .svg-container {
    padding: 6px 5px 6px 15px;
    color: $primary;
    vertical-align: middle;
    width: 30px;
    display: inline-block;
  }
  .title {
    font-size: 20px;
    font-weight: 400;
    color: $primary;
    margin: 0px auto 40px auto;
    text-align: center;
    font-weight: bold;
  }
  .show-pwd {
    position: absolute;
    right: 10px;
    top: 7px;
    font-size: 16px;
    color: $primary;
    cursor: pointer;
    user-select: none;
  }
  .set-language {
    color: #fff;
    position: absolute;
    top: 40px;
    right: 35px;
  }
  .md-form label.active {
    font-size: 1.3rem;
  }
  .md-form .prefix {
    top: 0.25rem;
    font-size: 1.5rem;
  }
  .md-form.md-outline .prefix {
    position: absolute;
    top: 0.9rem;
    font-size: 1.9rem;
    -webkit-transition: color 0.2s;
    transition: color 0.2s;
  }
  .md-form.md-outline .form-control {
    padding: 1rem;
  }
}
</style>
