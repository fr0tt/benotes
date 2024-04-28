import axios from 'axios'
import store from './../store'

/**
 * @param {int} collection_id  Should already be parsed by parseCollectionId()
 * @param {int} tag_id
 * @param {string} filter
 * @param {boolean} isArchived
 * @param {int} limit
 * @param {int} after_id
 */
function getPosts(
    collection_id,
    tag_id = null,
    filter = null,
    isArchived = false,
    limit = 0,
    after_id = null,
    withTags = false
) {
    return axios.get('/api/posts', {
        params: {
            collection_id: collection_id,
            is_uncategorized: isUncategorized(collection_id),
            tag_id: tag_id,
            filter: filter,
            is_archived: isArchived,
            limit: limit,
            after_id: after_id,
            withTags: withTags,
            withDescendants: filter?.length > 0,
        },
    })
}

/**
 * @param {Object} post
 */
function restorePost(post) {
    store.dispatch('post/updatePost', { post: post, transfer: true, restore: true })
}

/**
 * @param {int} collection_id
 */
function isUncategorized(id) {
    return (id === 0) | 0
}

/**
 * @param {int} collection_id
 */
function parseCollectionId(id) {
    return id > 0 ? id : null
}

export { getPosts, restorePost, isUncategorized, parseCollectionId }
