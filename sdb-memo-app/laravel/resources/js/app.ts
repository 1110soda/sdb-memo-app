import "../css/app.css";
import { createApp } from "vue";
import App from "./App.vue";
import pinia from "./plugins/pinia";
import router from "./router";

const isLoggedIn = document.body.dataset.isLoggedIn === "true";
const userName = document.body.dataset.userName || '';

const app = createApp(App, { isLoggedIn, userName });
app.use(pinia);
app.use(router);
app.mount("#app");
