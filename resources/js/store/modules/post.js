import axios from 'axios'

export default {
    namespaced: true,
    state: {
        posts: null,
        isLoading: false,
        contextMenu: {
            post: null,
            target: null,
            positionX: 0
        }
    },
    getters: {
        maxOrder: state => {
            return state.posts.length > 0 ? state.posts[0].order : 0
        }
    },
    mutations: {
        setPosts (state, posts) {
            state.posts = posts
        },
        addPost (state, post) {
            state.posts.unshift(post)
        },
        setPost (state, { post, index }) {
            state.posts.splice(index, 1, post)
        },
        deletePost (state, index) {
            state.posts.splice(index, 1)
        },
        setContextMenu (state, contextMenu) {
            state.contextMenu = contextMenu
        },
        isLoading (state, isLoading) {
            state.isLoading = isLoading
        },
        setPlaceholderPosts (state) {
            let posts = [];
            const max = 8
            for (let i = 1; i <= max; i++) {
                posts.push({
                    id: 'placeholder-' + i,
                    type: 'placeholder',
                    order: max - i + 1
                })
            }
            state.posts = posts
        }
    },
    actions: {
        fetchPosts (context, collectionId) {
            context.commit('isLoading', true)
            context.commit('setPlaceholderPosts')
            axios.get('/api/posts', {
                params: {
                    collection_id: (collectionId > 0) ? collectionId : null,
                    is_uncategorized: (collectionId === 0) | 0
                }
            })
                .then(response => {
                    const posts = response.data.data
                    context.commit('isLoading', false)
                    context.commit('setPosts', posts)
                })
                .catch(error => {
                    console.log(error)
                    context.commit('isLoading', false)
                })
        },
        getPost (context, id) {
            return new Promise((resolve, reject) => {
                axios.get('/api/posts/' + id)
                    .then(response => {
                        const post = response.data.data
                        resolve(post)
                    })
                    .catch(error => {
                        console.log(error)
                        reject(error)
                    })
            })
        },
        addPost (context, post) {
            context.commit('addPost', post)
        },
        updatePost (context, { post, transfer = false }) { 
            const params = {}
            params.title = post.title
            params.content = post.content
            params.collection_id = post.collection_id
            params.is_uncategorized = (post.collection_id <= 0) | 0
            axios.patch('/api/posts/' + post.id, params)
                .then(response => {
                    const newPost = response.data.data
                    if (context.state.posts === null) {
                        return
                    }
                    const index = context.state.posts.findIndex(item => {
                        return post.id === item.id
                    })

                    if (transfer) {
                        context.commit('deletePost', index)
                    } else {
                        context.commit('setPost', {
                            post: newPost,
                            index: index
                        })
                    }
                })
                .catch(error => {
                    console.log(error)
                    if (typeof error.response !== 'undefined') {
                        console.log(error.response.data)
                    }
                })
        },
        setPostById (context, { id, post }) {
            const index = context.state.posts.findIndex(item => {
                return id === item.id
            })
            context.commit('setPost', {
                post: post,
                index: index
            })
        },
        deletePost (context, id) {
            axios.delete('/api/posts/' + id)
                .then(response => {
                    const index = context.state.posts.findIndex((post) => {
                        return post.id === id
                    })
                    context.commit('deletePost', index)
                })
                .catch(error => {
                    console.log(error)
                })
        },
        setContextMenu (context, contextMenu) {
            context.commit('setContextMenu', contextMenu)
        },
        hideContextMenu (context) {
            context.commit('setContextMenu', {
                post: null,
                target: null,
                positionX: 0
            })
        }
    }
}
