import Vue from 'vue'
import VueRouter from 'vue-router'
import Vuex from 'vuex'

import {routes} from './routes.js'

Vue.use(VueRouter)

const router = new VueRouter({
    mode: 'history',
    routes
})

const app = new Vue({
    el: '#app',
    router
})