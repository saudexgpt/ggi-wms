import Layout from '@/layout';

const permissionRoutes = {
  path: '/reports',
  component: Layout,
  redirect: 'noredirect',
  alwaysShow: true, // will always show the root menu
  meta: {
    title: 'Reports',
    icon: 'el-icon-s-data',
  },
  children: [
    {
      path: 'bin-card',
      component: () => import('@/app/reports/BinCard'),
      name: 'BinCard',
      meta: {
        title: 'Bin Card',
        icon: 'el-icon-s-management',
        permissions: ['view reports'],
      },
    },
    {
      path: 'graphical-reports',
      component: () => import('@/app/reports/index'),
      name: 'GraphicalReports',
      meta: {
        title: 'Graphical',
        icon: 'el-icon-pie-chart',
        permissions: ['view reports'],
      },
    },
    {
      path: 'downloads',
      component: () => import('@/app/reports/downloads'),
      name: 'DownloadReports',
      meta: {
        title: 'Downloadables',
        icon: 'el-icon-download',
        permissions: ['view reports'],
      },
    },

  ],
};

export default permissionRoutes;
