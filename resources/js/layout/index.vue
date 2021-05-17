<template>
  <div :class="classObj" class="app-wrapper">
    <audio id="myAudio">
      <source src="alert.mp3" type="audio/mpeg">
    </audio>
    <div style="display: none">
      <button id="play_audio" @click="playAudio()">Play Audio</button>
    </div>

    <div v-if="device==='mobile'&&sidebar.opened" class="drawer-bg" @click="handleClickOutside" />
    <sidebar class="sidebar-container" />
    <div :class="{hasTagsView:needTagsView}" class="main-container">
      <div :class="{'fixed-header':fixedHeader}">
        <navbar />
        <tags-view v-if="needTagsView" />
      </div>
      <app-main />
      <right-panel v-if="showSettings">
        <settings />
      </right-panel>
    </div>
    <idle-modal v-if="isIdle" />
  </div>
</template>

<script>
import IdleModal from './IdleModal';
import RightPanel from '@/components/RightPanel';
import { Navbar, Sidebar, AppMain, TagsView, Settings } from './components';
import ResizeMixin from './mixin/resize-handler.js';
import { mapState } from 'vuex';
import Pusher from 'pusher-js';
import Echo from 'laravel-echo';
import Resource from '@/api/resource';
const userNotifications = new Resource('user-notifications');
export default {
  name: 'Layout',
  components: {
    IdleModal,
    AppMain,
    Navbar,
    RightPanel,
    Settings,
    Sidebar,
    TagsView,
  },
  mixins: [ResizeMixin],
  computed: {
    isIdle() {
      return this.$store.state.idleVue.isIdle;
    },
    ...mapState({
      sidebar: state => state.app.sidebar,
      device: state => state.app.device,
      showSettings: state => state.settings.showSettings,
      needTagsView: state => state.settings.tagsView,
      fixedHeader: state => state.settings.fixedHeader,
    }),
    classObj() {
      return {
        hideSidebar: !this.sidebar.opened,
        openSidebar: this.sidebar.opened,
        withoutAnimation: this.sidebar.withoutAnimation,
        mobile: this.device === 'mobile',
      };
    },
    listenForChanges() {
      window.Pusher = Pusher;
      window.Echo = new Echo({
        broadcaster: 'pusher',
        key: process.env.MIX_PUSHER_APP_KEY,
        cluster: process.env.MIX_PUSHER_APP_CLUSTER,
        encrypted: true,
        auth: {
          headers: {
            Authorization: 'Bearer ' + this.$store.getters.token,
          },
        },
      });
      const currentUserId = this.$store.getters.userId;
      // console.log(currentUserId);
      return window.Echo.private('App.Laravue.Models.User.' + currentUserId)
        .notification((notification) => {
          // this.playAudio();
          document.getElementById('play_audio').click();
          this.pushNotification(notification);
          this.$notify({
            title: notification.title,
            message: notification.description,
            type: 'success',
            duration: 10000,
          });
        });
    },

  },
  created(){
    this.fetchUserNotifications();
    this.listenForChanges;
  },
  methods: {
    fetchUserNotifications() {
      const app = this;
      userNotifications.list().then((response) => {
        app.$store.dispatch('user/setNotifications', response.notifications);
      });
    },
    pushNotification(notification) {
      const data = {
        title: notification.title,
        description: notification.description,
      };
      notification.data = data;
      this.$store.getters.notifications.unshift(notification);
    },
    handleClickOutside() {
      this.$store.dispatch('app/closeSideBar', { withoutAnimation: false });
    },
    playAudio() {
      var audio = document.getElementById('myAudio');
      audio.play();
      // const src = 'alert.mp3';
      // const audio = new Audio(src);
      // try {
      //   audio.play();
      // } catch (err) {
      //   // playAudio();
      // }
    },
  },
};
</script>

<style lang="scss" scoped>
  @import "~@/styles/mixin.scss";
  @import "~@/styles/variables.scss";

  .app-wrapper {
    @include clearfix;
    position: relative;
    height: 100%;
    width: 100%;

    &.mobile.openSidebar {
      position: fixed;
      top: 0;
    }
  }

  .drawer-bg {
    background: #000;
    opacity: 0.3;
    width: 100%;
    top: 0;
    height: 100%;
    position: absolute;
    z-index: 999;
  }

  .fixed-header {
    position: fixed;
    top: 0;
    right: 0;
    z-index: 9;
    width: calc(100% - #{$sideBarWidth});
    transition: width 0.28s;
  }

  .hideSidebar .fixed-header {
    width: calc(100% - 54px)
  }

  .mobile .fixed-header {
    width: 100%;
  }
</style>
