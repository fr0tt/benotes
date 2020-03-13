import axios from 'axios'

export default {
    namespaced: true,
    state: {
        collections: null,
        currentCollection: {
            'id': null,
            'name': ''
        },
        collectionMenu: {
            isVisible: false,
            post: null
        }
    },
    mutations: {
        setCollections (state, collections) {
            state.collections = collections
        },
        addCollection (state, collection) {
            state.collections.push(collection)
        },
        deleteCollection (state, index) {
            state.collections.splice(index, 1)
        },
        setCollection (state, { index, collection }) {
            state.collections.splice(index, 1, collection)
        },
        setCurrentCollection (state, collection) {
            state.currentCollection = collection
        },
        setCollectionMenu (state, collectionMenu) {
            state.collectionMenu = collectionMenu
        }
    },
    actions: {
        fetchCollections (context) {
            axios.get('/api/collections')
                .then(response => {
                    const collections = response.data.data
                    context.commit('setCollections', collections)
                    context.dispatch('getCurrentCollection')
                })
                .catch(error => {
                    console.log(error)
                })
        },
        addCollection (context, collection) {
            context.commit('addCollection', collection)
        },
        updateCollection (context, { id, name }) {
            axios.patch('/api/collections/' + id, {
                name: name
            })
                .then((response) => {
                    const newCollection = response.data.data
                    const index = context.state.collections.findIndex((collection) => {
                        return collection.id === newCollection.id
                    })
                    context.commit('setCollection', { index, collection: newCollection })
                })
                .catch(error => {
                    console.log(error.response.data)
                })
        },
        deleteCollection (context, id) {
            axios.delete('/api/collections/' + id)
                .then(response => {
                    const index = context.state.collections.findIndex((collection) => {
                        return collection.id === id
                    })
                    context.commit('deleteCollection', index)
                })
                .catch(error => {
                    console.log(error)
                })
        },
        getCurrentCollection (context) {
            if (context.rootState.route.currentRoute === null) {
                return
            }
            if (Object.keys(context.rootState.route.currentRoute.params).length === 0) {
                const collection = {
                    'id': 0,
                    'name': 'Uncategorized'
                }
                context.commit('setCurrentCollection', collection)
                return
            }
            const id = parseInt(context.rootState.route.currentRoute.params.id)
            context.state.collections.map((collection) => {
                if (collection.id === id) {
                    context.commit('setCurrentCollection', collection)
                    return
                }
            })
        },
        setCollectionMenu (context, collectionMenu) {
            context.commit('setCollectionMenu', collectionMenu)
        }
    }
}
