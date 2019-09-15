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
        setPost (state, post, index) {
            state.posts[index] = post
        },
        setCurrentPost (state, post) {
            state.currentPost = post
        },
        setCurrentPostContent (state, content) {
            state.currentPost.content = content
        }
    },
    actions: {
        fetchPosts (context, collectionId) {
            let params = null
            if (typeof collectionId === 'undefined' || collectionId === null) {
                params = {
                    is_uncategorized: true
                }
            } else {
                params = {
                    collection_id: collectionId
                }
            }
            axios.get('/api/posts', params)
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
        },
        setCurrentPostContent (context, content) {
            context.commit('setCurrentPostContent', content)
        },
        updatePost (context, currentPost) {
            axios.patch('/api/posts/' + currentPost.id, {
                content: currentPost.content
            })
                .then(response => {
                    const newPost = response.data.data
                    context.state.posts.find((post, i) => {
                        if (post.id === newPost.id) {
                            context.commit('setPost', post, i)
                            return
                        }
                    })
                })
                .catch(error => {
                    console.log(error)
                })
        }
    }
}
