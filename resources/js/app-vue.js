import { createApp, ref } from 'vue';
import UserCrud from './components/UserCrud.vue';
import DashboardStats from './components/DashboardStats.vue';

const app = createApp({
    setup() {
        const showUserCrud = ref(false);
        window.setShowUserCrud = (val) => { showUserCrud.value = val; };
        return { showUserCrud };
    },
    components: {
        UserCrud,
        DashboardStats
    },
    template: `
      <div style="padding:2rem; background:#fff; min-height:300px;">
        <user-crud v-if="showUserCrud" />
        <dashboard-stats v-else />
      </div>
    `
});
app.component('user-crud', UserCrud);
app.component('dashboard-stats', DashboardStats);
app.mount('#app-vue');
