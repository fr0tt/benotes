import Vue from 'vue'
import axios from 'axios'

export default {
    namespaced: true,
    state: {
        isAuthenticated: false,
        authUser: null,
        staticAuth: null,
        permission: 7
    },
    mutations: {
        isAuthenticated (state, isAuthenticated) {
            state.isAuthenticated = isAuthenticated
        },
        setAuthUser (state, user) {
            state.authUser = user
        },
        setStaticAuth (state, staticAuth) {
            state.staticAuth = staticAuth
        },
        setPermission (state, permission) {
            state.permission = permission
        }
    },
    actions: {
        fetchAuthUser (context) {
            axios.get('/api/auth/me')
                .then(response => {
                    const user = response.data.data
                    context.commit('isAuthenticated', true)
                    context.commit('setAuthUser', user)
                })
                .catch(error => {
                    console.log(error)
                })
        },
        getAuthUser (context) {
            return new Promise((resolve, reject) => {
                if (context.state.authUser) {
                    context.commit('setStaticAuth', null)
                    resolve(context.state.authUser)
                }
                if (!Vue.cookie.get('token')) {
                    resolve(null)
                }
                const token = Vue.cookie.get('token')
                axios.defaults.headers.common = { 'Authorization': `Bearer ${token}` }
                axios.get('/api/auth/me')
                    .then(response => {
                        const user = response.data.data
                        context.commit('setStaticAuth', null)
                        context.commit('setAuthUser', user)
                        resolve(user)
                    })
                    .catch(error => {
                        if (error.response.status === 401) {
                            axios.post('/api/auth/refresh')
                                .then(response => {
                                    const token = response.data.data.token.access_token
                                    Vue.cookie.set('token', token, { expires: 14, samesite: 'Strict' })
                                    axios.defaults.headers.common = { 'Authorization': `Bearer ${token}` }
                                    axios.get('api/auth/me')
                                        .then(response => {
                                            const user = response.data.data
                                            context.commit('setAuthUser', user)
                                            resolve(user)
                                        })
                                })
                                .catch(error => {
                                    reject(error)
                                })
                        } else {
                            reject(error)
                        }
                    })
            })
        },
        setAuthUser (context, user) {
            context.commit('setAuthUser', user)
        },
        getStaticAuth (context, token) {
            return new Promise((resolve, reject) => {
                if (context.state.staticAuth) {
                    resolve(context.state.staticAuth)
                }
                if (!token) {
                    resolve(null)
                }
                axios.defaults.headers.common = { 'Authorization': `Bearer ${token}` }
                axios.get('/api/shares/me')
                    .then(response => {
                        const share = response.data.data
                        context.commit('setStaticAuth', share)
                        resolve(share)
                    })
                    .catch(error => {
                        reject(error)
                    })
            })
        },
        setPermission (context, permission) {
            context.commit('setPermission', permission)
        }
    }
}
