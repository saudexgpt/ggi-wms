<template>
  <div class="app-container">
    <add-new v-if="page.add_new" :categories="categories" :page="page" />
    <div class="box">
      <div class="box-header">
        <h4 class="box-title">List of Item Categories</h4>
        <span class="pull-right">
          <a class="btn btn-info" @click="page.add_new = true"> Add New</a>
        </span>
      </div>
      <div class="box-body">
        <h4 class="alert alert-info">Click on the category you wish to edit</h4>
        <v-client-table v-model="categories" :columns="columns" :options="options">

          <div slot="name" slot-scope="{row, update, setEditing, isEditing}">
            <span v-if="!isEditing()" style="cursor:pointer" @click="setEditing(true)">
              {{ row.name }}

            </span>
            <span v-else>
              <input v-model="row.name" type="text" class="form-control" @change="update(row.name); confirmEdit(row); setEditing(false)" @blur="setEditing(false)">

            </span>
          </div>
          <div slot="action" slot-scope="props">
            <a type="text" class="btn btn-danger" @click="confirmDelete(props)"><i class="fa fa-trash" /> </a>
          </div>

        </v-client-table>

      </div>

    </div>

  </div>
</template>
<script>
import AddNew from './partials/AddNew';
import Resource from '@/api/resource';
// import Vue from 'vue';
const itemCategory = new Resource('stock/item-category');
const updateCategory = new Resource('stock/item-category/update');
const deleteCategory = new Resource('stock/item-category/delete');
export default {
  components: { AddNew },
  data() {
    return {
      categories: [],
      columns: ['action', 'name'],

      options: {
        headings: {
          name: 'Name',
          // id: 'S/N',
        },
        editableColumns: ['name'],
        sortable: ['name'],
        filterable: ['name'],
      },
      page: {
        add_new: false,
      },

    };
  },

  mounted() {
    this.fetchItemCategories();
  },
  beforeDestroy() {

  },
  methods: {
    fetchItemCategories() {
      const app = this;
      app.show_class_teacher = true;
      const load = itemCategory.loaderShow();

      const param = app.form;
      itemCategory.list(param)
        .then(response => {
        // app.categories = response.categories

          app.categories = response.categories.map(v => {
            app.$set(v, 'edit', false); // https://vuejs.org/v2/guide/reactivity.html
            v.originalName = v.name; //  will be used when user click the cancel botton
            return v;
          });
          load.hide();
        })
        .catch(error => {
          load.hide();
          console.log(error);
        });
    },

    confirmEdit(row) {
      // this.loader();
      row.edit = false;
      // row.originalName = row.name;

      updateCategory.update(row.id, row)
        .then(response => {
          this.$message({
            message: 'Category has been edited',
            type: 'success',
          });
        })
        .catch(error => {
          console.log(error);
        });
    },
    confirmDelete(props) {
      // this.loader();

      const row = props.row;
      const app = this;
      const message = 'This delete action cannot be undone. Click OK to confirm';
      if (confirm(message)) {
        deleteCategory.destroy(row.id, row)
          .then(response => {
            app.categories.splice(row.index - 1, 1);
            this.$message({
              message: 'Category has been deleted',
              type: 'success',
            });
          })
          .catch(error => {
            console.log(error);
          });
      }
    },
  },
};
</script>
