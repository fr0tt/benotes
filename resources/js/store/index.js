import Vue from 'vue'
import Vuex from 'vuex'

import auth from './modules/auth'
import post from './modules/post'
import collection from './modules/collection'

Vue.use(Vuex)

export default new Vuex.Store({
    modules: {
        auth,
        post,
        collection
    },
    strict: process.env.NODE_ENV !== 'production'
})
