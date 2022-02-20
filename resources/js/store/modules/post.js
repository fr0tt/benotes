import axios from 'axios'
import { getPosts } from './../../api/post.js'

export default {
    namespaced: true,
    state: {
        posts: [],
        isLoading: false,
        isUpdating: false,
        contextMenu: {
            post: null,
            target: null,
            positionX: 0
        }
    },
    getters: {
        maxOrder: state => {
            return state.posts.length
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
        updatePostOrders (state, { start, end }) {
            for (let i = start; i <= end; i++) {
                state.posts[i].order = this.getters['post/maxOrder'] - i
            }
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
        fetchPosts (context, { collectionId, filter = null }) {
            context.commit('isLoading', true)
            context.commit('setPlaceholderPosts')

            getPosts(collectionId, filter)
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
                        return
                    }

                    context.commit('setPost', {
                        post: newPost,
                        index: index
                    })


                })
                .catch(error => {
                    post.isUpdating = false
                    this.$store.dispatch('notification/setNotification', {
                        type: 'error',
                        title: 'Error',
                        description: 'Post could not be updated.'
                    })
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
        updatePostOrder (context, { oldIndex, newIndex }) {
            context.commit('updatePostOrders', {
                start: (oldIndex < newIndex) ? oldIndex : newIndex,
                end: (newIndex > oldIndex) ? newIndex : oldIndex
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
