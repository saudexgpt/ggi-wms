import Layout from '@/layout';

const permissionRoutes = {
  path: '/logistics',
  component: Layout,
  redirect: 'noredirect',
  alwaysShow: true, // will always show the root menu
  meta: {
    title: 'Logistics',
    icon: 'el-icon-truck',
    permissions: ['manage vehicles', 'manage drivers', 'manage vehicle expenses'],
    // roles: ['admin', 'stock officer'],
  },
  children: [
    {
      path: 'drivers',
      component: () => import('@/app/logistics/Drivers'),
      name: 'Drivers',
      meta: {
        title: 'Drivers',
        permissions: ['manage drivers'],
      },
    },
    {
      path: 'view-vehicles',
      component: () => import('@/app/logistics/Vehicles'),
      name: 'Vehicles',
      meta: {
        title: 'Vehicles',
        permissions: ['manage vehicles'],
      },
    },
    // {
    //   path: 'details/:id',
    //   component: () => import('@/app/order/Details'),
    //   name: 'OrderDetails',
    //   meta: { title: 'Order Details', noCache: true, permissions: ['view order'] },
    //   hidden: true,
    // },
    {
      path: 'vehicle-conditions',
      component: () => import('@/app/logistics/VehicleConditions'),
      name: 'VehicleConditions',
      meta: {
        title: 'Vehicle Condition',
        permissions: ['manage vehicle conditions'],
      },
    },
    {
      path: 'vehicle-expenses',
      component: () => import('@/app/logistics/VehicleExpenses'),
      name: 'VehicleExpenses',
      meta: {
        title: 'Vehicle Expenses',
        permissions: ['manage vehicle expenses'],
      },
    },
    {
      path: 'vehicle-types',
      component: () => import('@/app/logistics/VehicleTypes'),
      name: 'VehicleTypes',
      meta: {
        title: 'Vehicle Types',
        permissions: ['manage vehicles'],
      },
    },
  ],
};

export default permissionRoutes;
