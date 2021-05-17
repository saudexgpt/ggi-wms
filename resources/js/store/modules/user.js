import { login, logout, getInfo } from '@/api/auth';
import { getToken, setToken, removeToken } from '@/utils/auth';
import router, { resetRouter } from '@/router';
import store from '@/store';

const state = {
  id: null,
  token: getToken(),
  name: '',
  avatar: '',
  introduction: '',
  roles: [],
  permissions: [],
  p_status: '',
  notifications: [],
};

const mutations = {
  SET_ID: (state, id) => {
    state.id = id;
  },
  SET_TOKEN: (state, token) => {
    state.token = token;
  },
  SET_INTRODUCTION: (state, introduction) => {
    state.introduction = introduction;
  },
  SET_NAME: (state, name) => {
    state.name = name;
  },
  SET_AVATAR: (state, avatar) => {
    state.avatar = avatar;
  },
  SET_ROLES: (state, roles) => {
    state.roles = roles;
  },
  SET_PERMISSIONS: (state, permissions) => {
    state.permissions = permissions;
  },
  SET_PASSWORD_STATUS: (state, p_status) => {
    state.p_status = p_status;
  },
  SET_NOTIFICATIONS: (state, notifications) => {
    state.notifications = notifications;
  },
};

const actions = {
  // user login
  login({ commit }, userInfo) {
    const { email, password } = userInfo;
    return new Promise((resolve, reject) => {
      login({ email: email.trim(), password: password })
        .then(response => {
          commit('SET_TOKEN', response.token);
          setToken(response.token);
          resolve();
        })
        .catch(error => {
          reject(error);
        });
    });
  },

  // get user info
  getInfo({ commit, state }) {
    return new Promise((resolve, reject) => {
      getInfo(state.token)
        .then(response => {
          const { data } = response;

          if (!data) {
            reject('Verification failed, please Login again.');
          }

          const { roles, name, avatar, introduction, permissions, id, p_status, notifications } = data;
          // roles must be a non-empty array
          if (!roles || roles.length <= 0) {
            reject('getInfo: roles must be a non-null array!');
          }

          commit('SET_ROLES', roles);
          commit('SET_PASSWORD_STATUS', p_status);
          commit('SET_PERMISSIONS', permissions);
          commit('SET_NAME', name);
          commit('SET_AVATAR', avatar);
          commit('SET_INTRODUCTION', introduction);
          commit('SET_ID', id);
          commit('SET_NOTIFICATIONS', notifications);
          resolve(data);
        })
        .catch(error => {
          reject('Error: ' + error);
        });
    });
  },

  // user logout
  logout({ commit, state }) {
    return new Promise((resolve, reject) => {
      logout(state.token)
        .then(() => {
          commit('SET_TOKEN', '');
          commit('SET_ROLES', []);
          removeToken();
          resetRouter();
          resolve();
        })
        .catch(error => {
          reject(error);
        });
    });
  },

  resetPasswordStatus({ commit }, status) {
    const { p_status } = status;
    commit('SET_PASSWORD_STATUS', p_status);
  },

  setNotifications({ commit }, notifications) {
    commit('SET_NOTIFICATIONS', notifications);
  },
  // remove token
  resetToken({ commit }) {
    return new Promise(resolve => {
      commit('SET_TOKEN', '');
      commit('SET_ROLES', []);
      removeToken();
      resolve();
    });
  },

  // Dynamically modify permissions
  changeRoles({ commit, dispatch }, role) {
    return new Promise(async resolve => {
      // const token = role + '-token';

      // commit('SET_TOKEN', token);
      // setToken(token);

      // const { roles } = await dispatch('getInfo');

      const roles = [role.name];
      const permissions = role.permissions.map(permission => permission.name);
      commit('SET_ROLES', roles);
      commit('SET_PERMISSIONS', permissions);
      resetRouter();

      // generate accessible routes map based on roles
      const accessRoutes = await store.dispatch('permission/generateRoutes', { roles, permissions });

      // dynamically add accessible routes
      router.addRoutes(accessRoutes);

      resolve();
    });
  },
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
};
