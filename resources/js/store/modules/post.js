import axios from 'axios'

export default {
    namespaced: true,
    state: {
        posts: null,
        currentPost: null
    },
    mutations: {
        setPosts (state, posts) {
            state.posts = posts
        },
        addPost (state, post) {
            state.posts.push(post)
        },
        setCurrentPost (state, post) {
            state.currentPost = post
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
        },
        addPost (context, post) {
            context.commit('addPost', post)
        },
        setCurrentPost (context, post) {
            context.commit('setCurrentPost', post)
        }
    }
}
