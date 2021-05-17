<template>
  <el-card v-if="order">
    <div class="block">
      <el-timeline>
        <el-timeline-item timestamp="2019/4/17" placement="top">
          <el-card>
            <h4>Update Github template</h4>
            <p>tuandm committed 2019/4/17 20:46</p>
          </el-card>
        </el-timeline-item>
        <el-timeline-item timestamp="2019/4/18" placement="top">
          <el-card>
            <h4>Update Github template</h4>
            <p>tonynguyen committed 2019/4/18 20:46</p>
          </el-card>
          <el-card>
            <h4>Update Github template</h4>
            <p>tuandm committed 2019/4/19 21:16</p>
          </el-card>
        </el-timeline-item>
        <el-timeline-item timestamp="2019/4/19" placement="top">
          <el-card>
            <h4>
              Deploy
              <a href="https://laravue.dev" target="_blank">laravue.dev</a>
            </h4>
            <p>tuandm deployed 2019/4/19 10:23</p>
          </el-card>
        </el-timeline-item>
      </el-timeline>
    </div>
  </el-card>
</template>

<script>
import Resource from '@/api/resource';
const orderResource = new Resource('orders');

export default {
  props: {
    order: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      activeActivity: 'first',
      updating: false,
    };
  },
  methods: {
    onSubmit() {
      this.updating = true;
      orderResource
        .update(this.order.id, this.order)
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
  },
};
</script>

<style lang="scss" scoped>
.order-activity {
  .order-block {
    .ordername, .description {
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
    .order-images {
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
