import Layout from '@/layout';

const permissionRoutes = {
  path: '/inbound',
  component: Layout,
  redirect: 'noredirect',
  alwaysShow: true, // will always show the root menu
  meta: {
    title: 'In Bound',
    icon: 'el-icon-s-management',
    // permissions: ['view-menu-warehouse'],
    permissions: ['manage item stocks', 'view item stocks', 'manage item category', 'manage general items'],
  },
  children: [

    {
      path: 'item-category',
      component: () => import('@/app/stock/item-category/ItemCategory'),
      name: 'ItemCategory',
      meta: {
        title: 'Categories',
        permissions: ['manage item category'],
      },
    },
    {
      path: 'manage-items',
      component: () => import('@/app/stock/item/ManageItem'),
      name: 'ManageItem',
      meta: {
        title: 'Products',
        permissions: ['manage general items'],
      },
    },
    {
      path: 'item-stocks',
      component: () => import('@/app/stock/items-stock/ItemStocks'),
      name: 'ItemStocks',
      meta: {
        title: 'Products In Stock',
        permissions: ['manage item stocks', 'view item stocks'],
      },
    },
    {
      path: 'returns',
      component: () => import('@/app/stock/returns'),
      name: 'Returns',
      meta: {
        title: 'Returned Products',
        permissions: ['view returned products', 'manage returned products'],
      },
    },

  ],
};

export default permissionRoutes;
