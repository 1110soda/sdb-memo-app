import "../css/app.css";
import { createApp } from "vue";
import App from "./App.vue";
import pinia from "./plugins/pinia";
import router from "./router";
import { useAuth } from './composables/useAuth';
import { useThemeStore } from "./stores/useThemeStore.ts";

const app = createApp(App);
app.use(pinia);
useThemeStore();
const { checkAuth } = useAuth();

// 認証チェックが完了してからルーターを登録し、アプリをマウントする
checkAuth().then(() => {
    app.use(router);
    app.mount("#app");
});

