import Layout from '@/layout';

const permissionRoutes = {
  path: '/warehouse',
  component: Layout,
  redirect: 'noredirect',
  alwaysShow: true, // will always show the root menu
  meta: {
    title: 'Warehouse',
    icon: 'el-icon-office-building',
    roles: ['admin'],
    // permissions: ['manage warehouse'],
  },
  children: [
    {
      path: 'view-warehouse',
      component: () => import('@/app/warehouse/index'),
      name: 'ViewWarehouse',
      meta: {
        title: 'Manage Warehouse',
        permissions: ['manage warehouse'],
      },
    },

  ],
};

export default permissionRoutes;
