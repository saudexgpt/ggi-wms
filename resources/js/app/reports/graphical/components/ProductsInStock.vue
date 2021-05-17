<template>
  <div class="app-container">
    <aside>
      <el-row :gutter="5">
        <el-col :xs="24" :sm="12" :md="12">
          <el-select v-model="params.warehouse_id">
            <el-option value disabled="disabled">Select Warehouse</el-option>
            <el-option
              v-for="(warehouse, index) in warehouses"
              :key="index"
              :value="warehouse.id"
              :label="warehouse.name"
            />
          </el-select>
        </el-col>
        <el-col :xs="24" :sm="12" :md="12">
          <el-popover placement="right" trigger="click">
            <date-range-picker
              :from="$route.query.from"
              :to="$route.query.to"
              :panel="panel"
              :panels="panels"
              :submit-title="submitTitle"
              :future="future"
              @update="setDateRange"
            />
            <el-button id="pick_date" slot="reference" type="success">
              <i class="el-icon-date" /> Pick Date Range
            </el-button>
          </el-popover>
        </el-col>
      </el-row>
    </aside>

    <el-row :gutter="0" class="panel-group" style="width: 100%">
      <highcharts :options="products_in_stock" />
    </el-row>
  </div>
</template>
<script>
import Resource from '@/api/resource';
const chartDataFetch = new Resource('reports/graphical/products-in-stock');
export default {
  props: {
    warehouses: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      products_in_stock: {
        chart: {
          type: 'column',
          options3d: {
            enabled: true,
            alpha: 0,
            beta: 0,
            depth: 100,
            viewDistance: 25,
          },
          // scrollablePlotArea: {
          //   minWidth: 900,
          //   scrollPositionX: 1,
          // },
        },
        title: {
          text: '',
        },
        subtitle: {
          text: '',
        },
        xAxis: {
          categories: [], // type: 'category', // categories: [],
          min: 0,
          max: 3,
          labels: {
            skew3d: true,
            style: {
              fontSize: '14px',
            },
          },
          title: {
            text: 'Products',
          },
        },
        yAxis: {
          min: 0,
          max: undefined,
          tickInterval: 50000,
          title: {
            text: 'No. of Products',
          },
          stackLabels: {
            enabled: true,
            style: {
              fontWeight: 'bold',
              color: 'gray',
            },
          },
        },
        plotOptions: {
          column: {
            stacking: 'normal',
            dataLabels: {
              enabled: true,
            },
          },
        },
        series: [],

        // colors: ['#063', '#910000'],
        credits: {
          enabled: false,
        },
        scrollbar: {
          enabled: true,
          barBackgroundColor: 'gray',
          barBorderRadius: 7,
          barBorderWidth: 0,
          buttonBackgroundColor: 'gray',
          buttonBorderWidth: 0,
          buttonArrowColor: 'yellow',
          buttonBorderRadius: 7,
          rifleColor: 'yellow',
          trackBackgroundColor: '#fcfcfc',
          trackBorderWidth: 1,
          trackBorderColor: 'silver',
          trackBorderRadius: 7,
        },
      },
      params: {
        warehouse_id: '', // initially set the id of first warehouse
        from: '',
        to: '',
        panel: '',
      },
      submitTitle: 'Fetch Report',
      panel: 'month',
      future: false,
      panels: ['range', 'week', 'month', 'quarter', 'year'],
      show_calendar: false,
    };
  },

  created() {
    this.loadChart();
  },

  methods: {
    loadChart() {
      const app = this;

      if (app.params.warehouse_id === '') {
        app.params.warehouse_id = app.warehouses[0].id;
      }
      const loader = chartDataFetch.loaderShow();
      chartDataFetch
        .list(app.params)
        .then((response) => {
          app.products_in_stock.series = response.series;
          app.products_in_stock.xAxis.categories = response.categories;
          app.products_in_stock.subtitle.text = response.subtitle;
          app.products_in_stock.title.text = response.title;
          loader.hide();
        })
        .catch((error) => {
          loader.hide();
          console.log(error);
        });
    },
    showCalendar() {
      this.show_calendar = !this.show_calendar;
    },
    paramsat(date) {
      var month = date.toLocaleString('en-US', { month: 'short' });
      return month + ' ' + date.getDate() + ', ' + date.getFullYear();
    },
    setDateRange(values) {
      const app = this;
      document.getElementById('pick_date').click();
      let panel = app.panel;
      let from = app.week_start;
      let to = app.week_end;
      if (values !== '') {
        to = this.paramsat(new Date(values.to));
        from = this.paramsat(new Date(values.from));
        panel = values.panel;
      }
      app.params.from = from;
      app.params.to = to;
      app.params.panel = panel;
      app.loadChart();
    },
  },
};
</script>
<style>
.params-control {
  border-radius: 4px;
  box-shadow: none;
  border-color: #4db1eb;
  width: 100%;
  min-height: 35px;
}
</style>
