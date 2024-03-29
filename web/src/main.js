import Vue from 'vue'
import App from './App.vue'
import ViewUI from 'view-design'
import 'view-design/dist/styles/iview.css'

import router from './router'
import store from './store'
import axios from './libs/axios'
import GeminiScrollbar from 'vue-gemini-scrollbar'

import devArticle from './components/dev-article.vue'

import xcon from './libs/xcon'

// 启用iview
Vue.use(ViewUI)

// 启用滚动条
Vue.use(GeminiScrollbar)

// 跨域
Vue.prototype.$ = axios.ajax;
Vue.prototype.$.all = axios.all;
Vue.prototype.$.spread = axios.spread;

// Vue自带的
Vue.config.productionTip = false;

// 自定义模板
Vue.component('dev-article', devArticle);

// 全局路由守卫
router.beforeEach((to, from, next) => {
    // 启用状态提示
    ViewUI.LoadingBar.start();

    // 本地信息
    store.replaceState(Object.assign({}, store.state, xcon.stateRead()));
    // 菜单鉴权
    let menus = store.state.menus;
    let normal = ['/vlogin'];
    if (normal.filter(item => item === to.name).length === 0 && menus.filter(menu => menu.name === to.name).length === 0) {
        // 即不在首页、登录页，也不在菜单列表当中
        if (!from.name) {
            // 清除登录状态、强制登录
            xcon.stateClear();
            next('/vlogin')
        } else {
            if (menus.filter(menu => menu.name === from.name).length > 0) {
                next(from.name)
            } else {
                xcon.stateClear();
                next('/vlogin')
            }
        }
    } else {
        next();
    }
});

// 关闭状态提示
router.afterEach(() => {
    ViewUI.LoadingBar.finish();
  });

new Vue({
    router,
    store,
    render: h => h(App)
}).$mount('#app');
