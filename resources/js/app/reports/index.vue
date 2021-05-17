<template>
  <div class="dashboard-container">
    <component :is="currentComponent" />
  </div>
</template>
<script>
import GraphicalReport from './graphical';
export default {
  name: 'Reports',
  components: { GraphicalReport },
  data() {
    return {
      currentComponent: 'GraphicalReport',
    };
  },
};
</script>
