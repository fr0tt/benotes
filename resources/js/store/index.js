import Vue from 'vue'
import Vuex from 'vuex'

import auth from './modules/auth'
import posts from './modules/posts'

Vue.use(Vuex)

export default new Vuex.Store({
    modules: {
        auth,
        posts
    },
    strict: process.env.NODE_ENV !== 'production'
})
