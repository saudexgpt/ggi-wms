const getters = {
  sidebar: state => state.app.sidebar,
  language: state => state.app.language,
  size: state => state.app.size,
  device: state => state.app.device,
  visitedViews: state => state.tagsView.visitedViews,
  cachedViews: state => state.tagsView.cachedViews,
  userId: state => state.user.id,
  token: state => state.user.token,
  avatar: state => state.user.avatar,
  name: state => state.user.name,
  pStatus: state => state.user.p_status, // password status i.e default or custom
  introduction: state => state.user.introduction,
  roles: state => state.user.roles,
  permissions: state => state.user.permissions,
  notifications: state => state.user.notifications,
  permissionRoutes: state => state.permission.routes,
  addRoutes: state => state.permission.addRoutes,
};
export default getters;
