import Layout from '@/layout';

const permissionRoutes = {
  path: '/transfers',
  component: Layout,
  redirect: 'noredirect',
  alwaysShow: true, // will always show the root menu
  meta: {
    title: 'Transfers',
    icon: 'el-icon-s-promotion',
    permissions: ['manage transfer request'],
    // roles: ['admin', 'stock officer'],
  },
  children: [

    {
      path: 'transfer-request',
      component: () =>
        import ('@/app/transfers/Request'),
      name: 'TransferRequest',
      meta: {
        title: 'Transfer Request',
        permissions: ['manage transfer request'],
      },
    },
    {
      path: 'create-new',
      component: () =>
        import ('@/app/transfers/partials/CreateTransferRequest'),
      name: 'CreateTransferRequest',
      meta: {
        title: 'Create New',
        permissions: ['manage transfer request'],
      },
      hidden: true,
    },
    {
      path: 'waybill',
      component: () =>
        import ('@/app/transfers/Waybill'),
      name: 'TransferWaybills',
      meta: {
        title: 'Transfer Waybills',
        permissions: ['manage transfer request'],
      },
    },

    {
      path: 'generate-transfer-waybill',
      component: () =>
        import ('@/app/transfers/partials/GenerateWaybill'),
      name: 'GenerateTransferWaybill',
      meta: {
        title: 'Generate Waybill',
        permissions: ['manage transfer request'],
      },
      hidden: true,
    },
  ],
};

export default permissionRoutes;
