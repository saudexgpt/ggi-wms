<template>
  <div class="app-container">
    <aside>
      <el-row :gutter="5">
        <el-col :xs="24" :sm="12" :md="12">
          <el-select v-model="params.warehouse_id">
            <el-option value="" disabled="disabled">Select Warehouse</el-option>
            <el-option v-for="(warehouse, index) in warehouses" :key="index" :value="warehouse.id" :label="warehouse.name" />
          </el-select>
        </el-col>
        <el-col :xs="24" :sm="12" :md="12">
          <el-popover
            placement="right"
            trigger="click"
          >
            <date-range-picker :from="$route.query.from" :to="$route.query.to" :panel="panel" :panels="panels" :submit-title="submitTitle" :future="future" @update="setDateRange" />
            <el-button id="close_date" slot="reference" type="success">
              <i class="el-icon-date" /> Pick Date Range
            </el-button>
          </el-popover>
        </el-col>
      </el-row>
    </aside>

    <el-row :gutter="0" class="panel-group">
      <highcharts :options="expenses_on_vehicles" />
    </el-row>
    <!-- <el-row :gutter="0" class="panel-group">
      <highcharts :options="state_of_vehicles" />
    </el-row> -->
    <el-row>
      <el-col :xs="{span: 24}" :sm="{span: 24}" :md="{span: 24}" :lg="{span: 12}" :xl="{span: 12}" style="padding: 10px;">
        <legend>Current condition of vehicles</legend>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Vehicle Brand</th>
              <th>Condition</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(vehicle, index) in vehicles" :key="index">
              <td>{{ vehicle.brand }}</td>
              <td>{{ vehicle.condition }}</td>
              <td>
                <el-tag :type="vehicle && vehicle.status | statusFilter">
                  {{ vehicle && vehicle.status }}
                </el-tag>
              </td>
            </tr>
          </tbody>
        </table>
      </el-col>
    </el-row>
  </div>

</template>
<script>

import Resource from '@/api/resource';
const chartDataFetch = new Resource('reports/graphical/reports-on-vehicle');
export default {
  filters: {
    statusFilter(status) {
      const statusMap = {
        'Good Condition': 'success',
        'Mechanic Workshop': 'warning',
        'Bad Condition': 'danger',
      };
      return statusMap[status];
    },
    orderNoFilter(str) {
      return str;
    },
  },
  props: {
    warehouses: {
      type: Array,
      default: () => ([]),
    },
  },
  data() {
    return {
      vehicles: [],
      expenses_on_vehicles: {
        chart: {
          type: 'spline',
          options3d: {
            enabled: false,
            alpha: 0,
            beta: 0,
            depth: 100,
            viewDistance: 25,
          },
          scrollablePlotArea: {
            minWidth: 900,
            scrollPositionX: 1,
          },
        },
        title: {
          text: '',
        },
        subtitle: {
          text: '',
        },
        xAxis: {
          categories: [],
          plotBands: [],
          labels: {
            skew3d: true,
            style: {
              fontSize: '14px',
            },
          },
          // accessibility: {
          //   rangeDescription: 'Range: 2010 to 2017',
          // },
        },
        yAxis: {
          min: 0,
          max: undefined,
          tickInterval: 10000,
          title: {
            text: 'Amount (₦)',
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
        series: [

        ],

        // colors: ['#063', '#910000'],
        credits: {
          enabled: false,
        },
      },
      state_of_vehicles: {
        chart: {
          type: 'pie',
          options3d: {
            enabled: true,
            alpha: 0,
            beta: 0,
            depth: 100,
            viewDistance: 25,
          },
          scrollablePlotArea: {
            minWidth: 900,
            scrollPositionX: 1,
          },
        },
        title: {
          text: '',
        },
        subtitle: {
          text: '',
        },
        tooltip: {
          pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>',
        },
        accessibility: {
          point: {
            valueSuffix: '%',
          },
        },
        plotOptions: {
          pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
              enabled: true,
              format: '<b>{point.name}</b>: {point.percentage:.1f} %',
            },
          },
        },
        series: [

        ],

        // colors: ['#063', '#910000'],
        credits: {
          enabled: false,
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
      panels: ['week', 'month', 'quarter', 'year'],
      show_calendar: false,
    };
  },

  created: function() {
    this.loadChart();
  },

  methods: {

    loadChart() {
      const app = this;

      if (app.params.warehouse_id === '') {
        app.params.warehouse_id = app.warehouses[0].id;
      }
      const loader = chartDataFetch.loaderShow();
      chartDataFetch.list(app.params)
        .then(response => {
          // expenses on vehicles
          app.expenses_on_vehicles.xAxis.categories = response.expenses.categories;
          app.expenses_on_vehicles.xAxis.plotBands = response.expenses.plot_band;
          app.expenses_on_vehicles.series = response.expenses.series;
          app.expenses_on_vehicles.subtitle.text = response.expenses.subtitle;
          app.expenses_on_vehicles.title.text = response.expenses.title;

          // state of vehicles
          // app.state_of_vehicles.series = response.vehicle_state.series;

          // vehicles
          app.vehicles = response.vehicles;
          loader.hide();
        })
        .catch(error => {
          loader.hide();
          console.log(error);
        });
    },
    showCalendar(){
      this.show_calendar = !this.show_calendar;
    },
    paramsat(date) {
      var month = date.toLocaleString('en-US', { month: 'short' });
      return month + ' ' + date.getDate() + ', ' + date.getFullYear();
    },
    setDateRange(values){
      const app = this;
      document.getElementById('close_date').click();
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
<style rel="stylesheet/scss" lang="scss" scoped>
table th {
    font-size: 1.9rem !important;
    font-weight: 400;
}
</style>
