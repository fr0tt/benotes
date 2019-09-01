import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'

Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        isAuthenticated: false,
        authUser: null
    },
    mutations: {
        isAuthenticated (state, isAuthenticated) {
            state.isAuthenticated = isAuthenticated
        },
        setAuthUser (state, authUser) {
            state.authUser = authUser
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
                if (context.authUser) {
                    resolve(context.authUser)
                }
                if (!Vue.cookie.get('token')) {
                    resolve(null)
                }
                const token = Vue.cookie.get('token')
                axios.defaults.headers.common = { 'Authorization': `Bearer ${token}` }
                axios.get('/api/auth/me')
                    .then(response => {
                        const user = response.data.data
                        context.commit('setAuthUser', user)
                        resolve(user)
                    })
                    .catch(error => {
                        if (error.response.status === 401) {
                            axios.post('/api/auth/refresh')
                                .then(response => {
                                    const token = response.data.data.token.access_token
                                    Vue.cookie.set('token', token)
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
        }
    }
})
