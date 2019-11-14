import Vue from 'vue'
import Vuex from 'vuex'

import auth from './modules/auth'
import post from './modules/post'
import collection from './modules/collection'
import route from './modules/route'

Vue.use(Vuex)

export default new Vuex.Store({
    modules: {
        auth,
        post,
        collection,
        route
    },
    strict: process.env.NODE_ENV !== 'production',
    state: {
        isMobile: false
    },
    mutations: {
        isMobile (state, isMobile) {
            state.isMobile = isMobile
        }
    },
    actions: {
        checkDevice (context) {
            if (screen.width <= 600) {
                context.commit('isMobile', true)
            } else {
                context.commit('isMobile', false)
            }
        }
    }
})
