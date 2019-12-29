import Vue from 'vue'
import VueRouter from 'vue-router'
import VueCookie from 'vue-cookie'
import SvgVue from 'svg-vue'

import routes from './routes.js'
import store from './store'

Vue.use(VueCookie)
Vue.use(VueRouter)
Vue.use(SvgVue)

const router = new VueRouter({
    mode: 'history',
    routes
})

router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.requiresAuth)) {
        store.dispatch('auth/getAuthUser')
            .then(authUser => {
                if (!authUser) {
                    next({
                        path: '/login'
                    })
                } else {
                    next()
                }
            })
            .catch(() => {
                next({
                    path: '/login'
                })
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
