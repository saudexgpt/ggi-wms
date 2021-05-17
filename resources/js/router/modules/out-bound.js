import Layout from '@/layout';

const permissionRoutes = {
  path: '/outbound',
  component: Layout,
  redirect: 'noredirect',
  alwaysShow: true, // will always show the root menu
  meta: {
    title: 'Out Bound',
    icon: 'el-icon-s-promotion',
    permissions: ['view invoice', 'view waybill', 'manage waybill'],
    // roles: ['admin', 'stock officer'],
  },
  children: [

    {
      path: 'invoices',
      component: () => import('@/app/invoice/Invoice'),
      name: 'Invoices',
      meta: {
        title: 'Invoices',
        permissions: ['create invoice', 'view invoice'],
      },
    },
    // {
    //   path: 'details/:id',
    //   component: () => import('@/app/invoice/Details'),
    //   name: 'InvoiceDetails',
    //   meta: { title: 'Invoice Details', noCache: true, permissions: ['view invoice'] },
    //   hidden: true,
    // },
    {
      path: 'create-new',
      component: () => import('@/app/invoice/partials/CreateInvoice'),
      name: 'CreateInvoice',
      meta: {
        title: 'Create New',
        permissions: ['create invoice'],
      },
      hidden: true,
    },
    {
      path: 'waybill',
      component: () => import('@/app/invoice/Waybill'),
      name: 'Waybills',
      meta: {
        title: 'Waybills',
        permissions: ['view waybill', 'manage waybill'],
      },
    },
    {
      path: 'waybill-delivery-cost',
      component: () => import('@/app/invoice/WaybillDeliveryCost'),
      name: 'WaybillDeliveryCost',
      meta: {
        title: 'Delivery Cost',
        permissions: ['manage waybill cost'],
      },
    },
    {
      path: 'extra-delivery-cost',
      component: () => import('@/app/invoice/ExtraDeliveryCost'),
      name: 'ExtraDeliveryCost',
      meta: {
        title: 'Extra Delivery Cost',
        permissions: ['manage waybill cost'],
      },
    },
    {
      path: 'generate-waybill',
      component: () => import('@/app/invoice/partials/GenerateWaybill'),
      name: 'GenerateWaybill',
      meta: {
        title: 'Generate Waybill',
        permissions: ['generate waybill', 'manage waybill'],
      },
      hidden: true,
    },
  ],
};

export default permissionRoutes;
