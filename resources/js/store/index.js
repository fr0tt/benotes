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
    strict: process.env.NODE_ENV !== 'production'
})
