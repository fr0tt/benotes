import axios from 'axios'

export default {
    namespaced: true,
    state: {
        posts: null
    },
    mutations: {
        setPosts (state, posts) {
            state.posts = posts
        }
    },
    actions: {
        fetchPosts (context, collectionId) {
            axios.get('/api/posts', {
                params: {
                    collection_id: collectionId
                }
            })
                .then(response => {
                    const posts = response.data.data
                    context.commit('setPosts', posts)
                })
                .catch(error => {
                    console.log(error)
                })
        }
    }
}
