import Vue from 'vue'
import axios from 'axios'
import { refresh } from '../../api/auth'

export default {
    namespaced: true,
    state: {
        isAuthenticated: false,
        authUser: null,
        staticAuth: null,
        permission: 7,
    },
    mutations: {
        isAuthenticated(state, isAuthenticated) {
            state.isAuthenticated = isAuthenticated
        },
        setAuthUser(state, user) {
            state.authUser = user
        },
        setStaticAuth(state, staticAuth) {
            state.staticAuth = staticAuth
        },
        setPermission(state, permission) {
            state.permission = permission
        },
    },
    actions: {
        fetchAuthUser(context) {
            return axios
                .get('/api/auth/me')
                .then((response) => {
                    const user = response.data.data
                    context.commit('isAuthenticated', true)
                    context.commit('setAuthUser', user)
                    context.commit('setStaticAuth', null)
                    context.dispatch('setPermission', 7)
                    context.dispatch('hideSidebarOnMobile', null, { root: true })
                })
                .catch((error) => {
                    console.log(error)
                })
        },
        login({ dispatch }) {
            return new Promise((resolve, reject) => {
                if (!Vue.cookie.get('token')) {
                    resolve()
                }
                const token = Vue.cookie.get('token')
                axios.defaults.headers.common = { Authorization: `Bearer ${token}` }
                dispatch('fetchAuthUser')
                    .then(() => {
                        resolve()
                    })
                    .catch((error) => {
                        console.log(error)
                        if (error.response.status === 401) {
                            refresh()
                                .then((response) => {
                                    resolve(response.data.data)
                                })
                                .catch((error) => {
                                    reject(error)
                                })
                        } else {
                            reject(error)
                        }
                    })
            })
        },
        setAuthUser(context, user) {
            context.commit('setAuthUser', user)
        },
        getStaticAuth(context, token) {
            return new Promise((resolve, reject) => {
                if (context.state.staticAuth) {
                    resolve(context.state.staticAuth)
                }
                if (!token) {
                    resolve(null)
                }
                axios.defaults.headers.common = { Authorization: `Bearer ${token}` }
                axios
                    .get('/api/shares/public/me')
                    .then((response) => {
                        const share = response.data.data
                        context.commit('setStaticAuth', share)
                        resolve(share)
                    })
                    .catch((error) => {
                        reject(error)
                    })
            })
        },
        setPermission(context, permission) {
            context.commit('setPermission', permission)
        },
    },
}
