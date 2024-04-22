import Vue from 'vue'
import VueRouter from 'vue-router'
import VueCookie from 'vue-cookie'
import SvgVue from 'svg-vue'
import VueLazyload from 'vue-lazyload'
import axios from 'axios'
import createAuthRefreshInterceptor from 'axios-auth-refresh'

import routes from './routes.js'
import store from './store'

import { refresh } from './api/auth'

createAuthRefreshInterceptor(axios, (failedRequest) =>
    refresh().then(() => {
        failedRequest.response.config.headers = axios.defaults.headers.common
        return Promise.resolve()
    })
)

Vue.use(VueCookie)
Vue.use(VueRouter)
Vue.use(SvgVue)
Vue.use(VueLazyload)

const router = new VueRouter({
    mode: 'history',
    routes,
})

router.beforeEach((to, from, next) => {
    if (to.matched.some((record) => record.meta.staticAuth) && to.query.token) {
        store
            .dispatch('auth/getStaticAuth', to.query.token)
            .then((share) => {
                if (!share) {
                    next({ path: '/login' })
                } else {
                    store.dispatch('auth/setPermission', share.permission)
                    next()
                }
            })
            .catch(() => {
                next({ path: '/login' })
            })
    } else if (to.matched.some((record) => record.meta.authUser)) {
        if (store.state.auth.authUser) {
            next()
            return
        }
        store
            .dispatch('auth/login')
            .then(() => {
                if (store.state.auth.authUser) {
                    next()
                    return
                }
                next({ path: '/login' })
            })
            .catch(() => {
                next({ path: '/login' })
            })
    } else {
        next()
    }
})

/* const app = */ new Vue({
    el: '#app',
    router,
    store,
})

if (!Vue.config.devtools) {
    // if not dev env
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/service-worker.js')
        })
    }
}
