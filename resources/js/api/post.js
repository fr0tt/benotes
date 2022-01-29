import axios from 'axios'

/**
 * @param {int} collection_id  Should already be parsed by parseCollectionId()
 * @param {string} filter
 */
function getPosts (collection_id, filter = null) {
    return axios.get('/api/posts', {
        params: {
            collection_id: collection_id,
            is_uncategorized: isUncategorized(collection_id),
            filter: filter
        }
    })
}

/**
 * @param {int} collection_id
 */
function isUncategorized (id) {
    return (id === 0) | 0
}

/**
 * @param {int} collection_id
 */
function parseCollectionId (id) {
    return (id > 0) ? id : null
}

export {getPosts, isUncategorized, parseCollectionId}
