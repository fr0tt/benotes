import axios from 'axios'

export default {
    state: {
        posts: null
    },
    mutations: {
        setPosts (state, posts) {
            state.posts = posts
        }
    },
    actions: {
        fetchPosts (context) {
            axios.get('/api/posts')
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