<template>
  <div class="box">
    <div class="box-header">
      <h4 class="box-title">Edit Product</h4>
      <span class="pull-right">
        <a class="btn btn-danger" @click="page.option = 'list'"> Back</a>
      </span>
    </div>
    <div class="box-body">
      <aside>
        <el-form ref="form" :model="form" label-width="120px">
          <el-row :gutter="5" class="padded">
            <el-col :xs="24" :sm="12" :md="12">
              <label for="">Product Name</label>
              <el-input v-model="form.name" placeholder="Enter item name" class="span" />
              <label for="">Select Product Category</label>
              <el-select v-model="form.category_id" placeholder="Select item category" filterable class="span">
                <el-option v-for="(category, index) in categories" :key="index" :value="category.id" :label="category.name" />

              </el-select>
              <label for="">Unit of Measurement</label>
              <el-select v-model="form.unit_of_measurement" placeholder="Select item category" filterable class="span">
                <el-option v-for="(label, value) in params.unit_of_measurement" :key="value" :value="value" :label="label" />

              </el-select>
              <!-- <label for="">Stock Keeping Unit (SKU)</label>
              <el-input v-model="form.sku" placeholder="Stock Keeping Unit (SKU)" class="span" /> -->
              <label for="">Packaging Type</label>
              <el-select v-model="form.package_type" placeholder="Select Product" filterable class="span">
                <el-option v-for="(type, type_index) in params.package_types" :key="type_index" :value="type" :label="type" />

              </el-select>
              <label for="">Volume per {{ form.package_type }} in {{ params.unit_of_measurement[form.unit_of_measurement] }}</label> <br>
              <el-input-number v-model="form.volume_per_package" type="number" :precision="2" :step="0.1" :min="1" placeholder="Volume per unit" class="span" />
            </el-col>
            <el-col :xs="24" :sm="12" :md="12">
              <!-- <label for="">Cost Price</label>
                <el-input v-model="form.purchase_price" placeholder="Cost Price" class="span" /> -->

              <label for="">Rate per {{ form.package_type }}</label>
              <el-input v-model="item_price.sale_price" placeholder="Selling Price" class="span" />
              <label for="">Select Currency</label>
              <el-select v-model="item_price.currency_id" placeholder="Select Currency" class="span">
                <el-option v-for="(currency, index) in params.currencies" :key="index" :value="currency.id" :label="currency.name+' ('+currency.code+')'" />

              </el-select>
              <label for="">Product Description</label>
              <textarea v-model="form.description" placeholder="Product Description" rows="1" class="form-control" />
              <p />
              <image-cropper
                v-show="imagecropperShow"
                :key="imagecropperKey"
                :width="250"
                :height="250"
                url="upload-file"
                lang-type="en"
                @close="close"
                @crop-upload-success="cropUploadSuccess"
                @crop-upload-fail="cropUploadFail"
              />
              <br><br>

              <a @click="imagecropperShow=true">
                <img :src="form.picture" width="150">
                Click to upload item image
              </a>

            </el-col>
          </el-row>
          <el-row :gutter="2" class="padded">
            <el-col :xs="24" :sm="6" :md="6">
              <el-button type="success" @click="editProduct"><svg-icon icon-class="edit" />
                Update Product
              </el-button>
            </el-col>
          </el-row>
        </el-form>
      </aside>
    </div>
  </div>
</template>

<script>
import ImageCropper from '@/components/ImageCropper';

import Resource from '@/api/resource';
const updateProduct = new Resource('stock/general-items/update');
const deleteProductTax = new Resource('stock/general-items/delete-item-tax');
export default {
  name: 'AddNewProduct',
  components: { ImageCropper },
  props: {
    params: {
      type: Object,
      default: () => ({}),
    },
    categories: {
      type: Array,
      default: () => ([]),
    },
    item: {
      type: Object,
      default: () => ([]),
    },
    page: {
      type: Object,
      default: () => ({
        option: 'edit_item',
      }),
    },

  },
  data() {
    return {
      form: {
        name: '',
        package_type: '',
        quantity_per_carton: '',
        category_id: '',
        description: '',
        unit_of_measurement: 'L',
        volume_per_package: 1,
        picture: '/images/no-image.jpeg',
        // tax_ids: [],

      },
      empty_form: {
        name: '',
        // sku: '',
        category_id: '',
        description: '',
        picture: '/images/no-image.jpeg',
        // tax_ids: [],

      },
      item_price: {
        sale_price: 0,
        currency_id: 1,
      },
      imagecropperShow: false,
      imagecropperKey: 0,
      image: 'images/no-image.jpeg',

    };
  },
  mounted() {
    this.setEditForm();
  },
  methods: {
    setEditForm() {
      const app = this;
      app.form = app.item;
      app.item_price = (app.item.price) ? app.item.price : app.item_price;
    },
    cropUploadSuccess(jsonData, field){
      console.log('-------- upload success --------');
      // console.log(jsonData);
      // console.log('field: ' + field);
      const app = this;
      app.imagecropperShow = false;
      app.imagecropperKey = app.imagecropperKey + 1;
      app.form.picture = jsonData.avatar;
    },
    cropUploadFail(status, field){
      console.log('-------- upload fail --------');
      console.log(status);
      console.log('field: ' + field);
    },
    close() {
      this.imagecropperShow = false;
    },
    editProduct() {
      const app = this;
      const load = updateProduct.loaderShow();
      var form = app.form;
      form.currency_id = app.item_price.currency_id;
      // form.purchase_price = app.item_price.purchase_price;
      form.sale_price = app.item_price.sale_price;
      updateProduct.update(form.id, form)
        .then(response => {
          app.$message({ message: 'Product Updated Successfully!!!', type: 'success' });
          // app.item = response.item;
          app.$emit('update', response.item);

          load.hide();
        })
        .catch(error => {
          load.hide();
          console.log(error.message);
        });
    },
    destroyProductTax(index, tax_id) {
      const app = this;
      const param = {
        item_id: app.item.id,
        tax_id: tax_id,
      };
      deleteProductTax.list(param)
        .then(response => {
          // app.$message({ message: 'Product Updated Successfully!!!', type: 'success' });
          app.item.taxes.splice(index, 1);

          // app.$emit('update', response);
        })
        .catch(error => {
          alert(error.message);
        });
    },

  },
};
</script>
<style rel="stylesheet/scss" lang="scss" scoped>
.list-complete-item {
  cursor: pointer;
  position: relative;
  font-size: 14px;
  padding: 5px 12px;
  margin-top: 4px;
  border: 1px solid #e9a0a0;
  background: #e9a0a0;
  color: #fff;
  transition: all 1s;
}

.list-complete-item-handle {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin-right: 50px;
}
</style>
