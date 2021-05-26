import Vue from 'vue'
import VueRouter from 'vue-router'
import VueCookie from 'vue-cookie'
import SvgVue from 'svg-vue'
import VueLazyload from 'vue-lazyload'

import routes from './routes.js'
import store from './store'

Vue.use(VueCookie)
Vue.use(VueRouter)
Vue.use(SvgVue)
Vue.use(VueLazyload)

const router = new VueRouter({
    mode: 'history',
    routes
})

router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.staticAuth) && to.query.token) {
        store.dispatch('auth/getStaticAuth', to.query.token)
            .then(share => {
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
    } else if (to.matched.some(record => record.meta.authUser)) {
        store.dispatch('auth/getAuthUser')
            .then(authUser => {
                if (!authUser) {
                    next({ path: '/login' })
                } else {
                    store.dispatch('auth/setPermission', 7)
                    store.dispatch('hideSidebarOnMobile')
                    next()
                }
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
    store
})


let refreshTimer = setInterval(() => {
    if (Vue.cookie.get('token')) {
        store.dispatch('auth/getAuthUser')
            .then(authUser => {
                if (!authUser)
                    window.location = '/login'
            })
            .catch(error => {
                console.log(error)
                clearInterval(refreshTimer)
            })
    }
}, 11 * 60 * 1000)

if (!Vue.config.devtools) { // if not dev env
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/service-worker.js')
        })
    }
}
