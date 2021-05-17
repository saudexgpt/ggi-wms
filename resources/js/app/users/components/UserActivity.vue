<template>
  <el-card v-if="user.name">
    <el-tabs v-model="activeActivity">
      <!-- <el-tab-pane label="Activities" name="first">
        <div class="block">
          <legend>{{ user.name }}'s Activity Trail</legend>
          <el-timeline style="height: 400px; overflow:auto;">
            <el-timeline-item v-for="(activity_log, index) in user.activity_logs" :key="index" :timestamp="moment(activity_log.created_at).fromNow()" placement="top">
              <el-card>
                <label>{{ activity_log.data.title }}</label>
                <p>{{ activity_log.data.description }}</p>
              </el-card>
            </el-timeline-item>
          </el-timeline>
        </div>
      </el-tab-pane> -->
      <el-tab-pane v-if="user.can_edit" v-loading="updating" label="Update Profile" name="first">
        <el-form-item label="Name">
          <el-input v-model="user.name" :disabled="user.role === 'admin'" />
        </el-form-item>
        <el-form-item label="Email">
          <el-input v-model="user.email" :disabled="user.role === 'admin'" />
        </el-form-item>
        <el-form-item label="Phone">
          <el-input v-model="user.phone" :disabled="user.role === 'admin'" />
        </el-form-item>
        <el-form-item label="Address">
          <el-input v-model="user.address" :disabled="user.role === 'admin'" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :disabled="user.role === 'admin'" @click="onSubmit">
            Update
          </el-button>
        </el-form-item>
      </el-tab-pane>
      <el-tab-pane v-if="user.can_edit" v-loading="updating" label="Update Password" name="second">
        <el-form-item label="Email">
          <el-input v-model="user.email" :disabled="true" />
        </el-form-item>
        <el-form-item label="Password">
          <el-input v-model="user.password" type="password" :disabled="user.role === 'admin'" />
        </el-form-item>
        <el-form-item label="Confirm Password">
          <el-input v-model="user.confirmPassword" type="password" :disabled="user.role === 'admin'" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" :disabled="user.role === 'admin'" @click="updatePassword">
            Update
          </el-button>
        </el-form-item>
      </el-tab-pane>
    </el-tabs>
  </el-card>
</template>

<script>
import moment from 'moment';
import Resource from '@/api/resource';
const userResource = new Resource('users');
const userPasswordResource = new Resource('users/update-password');
export default {
  props: {
    user: {
      type: Object,
      default: () => {
        return {
          name: '',
          email: '',
          avatar: '',
          roles: [],
        };
      },
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
      userResource
        .update(this.user.id, this.user)
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
    updatePassword() {
      this.updating = true;
      userPasswordResource
        .update(this.user.id, this.user)
        .then(response => {
          this.updating = false;
          this.user = response.data;
          this.$message({
            message: 'Password has been updated successfully',
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
.user-activity {
  .user-block {
    .username, .description {
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
    .user-images {
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
