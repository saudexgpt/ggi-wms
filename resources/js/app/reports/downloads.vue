<template>
  <div class="dashboard-container">
    <component :is="currentComponent" />
  </div>
</template>
<script>
import DownloadableReport from './downloadable';
export default {
  name: 'Reports',
  components: { DownloadableReport },
  data() {
    return {
      currentComponent: 'DownloadableReport',
    };
  },
};
</script>
