import axios from 'axios'
import store from './../store'
import Vue from 'vue'

/**
 * @param {int} collection_id  Should already be parsed by parseCollectionId()
 * @param {string} filter
 * @param {boolean} isArchived
 * @param {int} limit
 * @param {int} after_id
 */
function refresh() {
    return new Promise((resolve, reject) => {
        axios.post('/api/auth/refresh')
        .then(response => {
            const token = response.data.data.token.access_token
            Vue.cookie.set('token', token, { expires: 14, samesite: 'Strict' })
            axios.defaults.headers.common = { 'Authorization': `Bearer ${token}` }
            axios.get('/api/auth/me')
                .then(response => {
                    const user = response.data.data
                    store.commit('auth/setAuthUser', user)
                    store.commit('auth/setStaticAuth', null)
                    resolve(response)
                })
                .catch(error => {
                    reject(error)
                })
        })
        .catch(error => {
            reject(error)
        })
    })
}

export { refresh }
