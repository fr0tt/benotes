import Vue from 'vue'
import Vuex from 'vuex'

import auth from './modules/auth'
import post from './modules/post'
import collection from './modules/collection'
import appbar from './modules/appbar'

Vue.use(Vuex)

export default new Vuex.Store({
    modules: {
        auth,
        post,
        collection,
        appbar
    },
    strict: process.env.NODE_ENV !== 'production',
    state: {
        isMobile: false,
        showSidebar: (localStorage.getItem('sidebar') === 'false') ? false : true,
        showBottomSheet: false,
        bottomSheet: []
    },
    mutations: {
        isMobile (state, isMobile) {
            state.isMobile = isMobile
        },
        showSidebar (state, showSidebar) {
            state.showSidebar = showSidebar
        },
        showBottomSheet(state, showBottomSheet) {
            state.showBottomSheet = showBottomSheet
        },
        setBottomSheet(state, bottomSheet) {
            state.bottomSheet = bottomSheet
        }
    },
    actions: {
        checkDevice (context) {
            if (window.outerWidth > 768) {
                context.commit('isMobile', false)
            } else {
                context.commit('isMobile', true)
            }
        },
        toggleSidebar (context) {
            const showSidebar = !this.state.showSidebar
            context.commit('showSidebar', showSidebar)
            localStorage.setItem('sidebar', showSidebar)
        },
        hideSidebarOnMobile (context) {
            if (this.state.isMobile && this.state.showSidebar) {
                context.commit('showSidebar', false)
                localStorage.setItem('sidebar', false)
            }
        }
    }
})
