<template>
  <div class="box">
    <div class="box-header">
      <h4 class="box-title">Add Category</h4>
      <span class="pull-right">
        <a class="btn btn-default" @click="page.add_new = false"> Cancel</a>
      </span>
    </div>
    <div class="box-body">

      <aside>
        <el-form ref="form" :model="form" label-width="120px">
          <el-row :gutter="5" class="padded">
            <el-col :xs="22" :sm="20" :md="12" class="el-col-xs-offset-1 el-col-sm-offset-2 el-col-md-offset-6">
              <el-input v-model="form.name" placeholder="Category Name" class="span" />
            </el-col>

          </el-row>

          <el-row :gutter="2" class="padded">
            <el-col :xs="24" :sm="2" :md="2" class="el-col-xs-offset-1 el-col-sm-offset-2 el-col-md-offset-6">
              <el-button type="success" @click="addCategory">
                Add
              </el-button>
            </el-col>
          </el-row>
        </el-form>
      </aside>
    </div>
  </div>
</template>

<script>
import Resource from '@/api/resource';
const createCategory = new Resource('stock/item-category/store');

export default {
  props: {
    categories: {
      type: Array,
      default: () => ([]),
    },
    page: {
      type: Object,
      default: () => ({
        add_new: true,
      }),
    },

  },
  data() {
    return {
      form: {
        name: '',
      },

    };
  },

  methods: {
    addCategory() {
      const app = this;
      const load = createCategory.loaderShow();
      createCategory.store(app.form)
        .then(response => {
          app.$message({ message: 'New Category Added Successfully!!!', type: 'success' });
          app.categories.push(response.category);
          app.form.name = '';
          app.$emit('update', response);
          load.hide();
        })
        .catch(error => {
          load.hide();
          console.log(error);
        });
    },
    onCancel() {
      this.$message({
        message: 'cancel!',
        type: 'warning',
      });
    },
  },
};
</script>

