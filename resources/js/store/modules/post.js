import axios from 'axios'

export default {
    namespaced: true,
    state: {
        posts: null,
        maxOrder: 0,
        currentPost: null,
        isLoading: false,
        contextMenu: {
            post: null,
            target: null,
            positionX: 0
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
        setCurrentPost (state, post) {
            state.currentPost = post
        },
        setCurrentPostContent (state, content) {
            state.currentPost.content = content
        },
        setCurrentPostTitle (state, title) {
            state.currentPost.title = title
        },
        setContextMenu (state, contextMenu) {
            state.contextMenu = contextMenu
        },
        isLoading (state, isLoading) {
            state.isLoading = isLoading
        },
        setMaxOrder (state, order) {
            state.maxOrder = order
        }
    },
    actions: {
        fetchPosts (context, collectionId) {
            context.commit('isLoading', true)
            console.log('bo', collectionId === 0, collectionId, typeof (collectionId === 0))
            axios.get('/api/posts', {
                params: {
                    collection_id: (collectionId > 0) ? collectionId : null,
                    is_uncategorized: (collectionId === 0) | 0
                }
            })
                .then(response => {
                    const posts = response.data.data
                    context.commit('setPosts', posts)
                    context.commit('setMaxOrder', posts[0].order)
                    context.commit('isLoading', false)
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
                        // context.commit('setCurrentPost', post)
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
        updatePost (context, post) { 
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
                    context.state.posts.find((post, i) => {
                        if (post.id === newPost.id) {
                            context.commit('setPost', {
                                post: newPost,
                                index: i
                            })
                            return
                        }
                    })
                })
                .catch(error => {
                    console.log(error)
                    if (typeof error.response !== 'undefined') {
                        console.log(error.response.data)
                    }
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
        setCurrentPost (context, post) {
            context.commit('setCurrentPost', post)
        },
        setCurrentPostContent (context, content) {
            context.commit('setCurrentPostContent', content)
        },
        setCurrentPostTitle (context, title) {
            context.commit('setCurrentPostTitle', title)
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
