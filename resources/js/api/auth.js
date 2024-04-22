import axios from 'axios'
import store from './../store'
import Vue from 'vue'

function refresh() {
    return axios.post('/api/auth/refresh').then((response) => {
        const token = response.data.data.token.access_token
        Vue.cookie.set('token', token, { expires: 14, samesite: 'Strict' })
        axios.defaults.headers.common = { Authorization: `Bearer ${token}` }
        store.dispatch('auth/fetchAuthUser')
        return Promise.resolve(true)
    })
}

export { refresh }
