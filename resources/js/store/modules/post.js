import axios from 'axios'

export default {
    namespaced: true,
    state: {
        posts: null,
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
            state.posts.push(post)
        },
        setPost (state, post, index) {
            state.posts[index] = post
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
        setContextMenu (state, contextMenu) {
            state.contextMenu = contextMenu
        },
        isLoading (state, isLoading) {
            state.isLoading = isLoading
        }
    },
    actions: {
        fetchPosts (context, collectionId) {
            context.commit('isLoading', true)
            const params = {}
            if (typeof collectionId === 'undefined' || collectionId === null) {
                params.is_uncategorized = true
            } else {
                params.collection_id = collectionId
            }
            axios.get('/api/posts', { params })
                .then(response => {
                    const posts = response.data.data
                    context.commit('setPosts', posts)
                    context.commit('isLoading', false)
                })
                .catch(error => {
                    console.log(error)
                    context.commit('isLoading', false)
                })
        },
        addPost (context, post) {
            context.commit('addPost', post)
        },
        updatePost (context, { post, newCollectionId }) {
            const params = {}
            params.content = post.content
            if (typeof newCollectionId !== 'undefined') {
                if (newCollectionId === null) {
                    params.is_uncategorized = true
                } else {
                    params.collection_id = newCollectionId
                }
            }
            axios.patch('/api/posts/' + post.id, params)
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
