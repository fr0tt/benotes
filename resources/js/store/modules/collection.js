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
        getCurrentCollection (context) {
            if (context.rootState.route.currentRoute === null) {
                return
            }
            if (Object.keys(context.rootState.route.currentRoute.params).length === 0) {
                const collection = {
                    'id': null,
                    'name': 'Uncategorized'
                }
                context.commit('setCurrentCollection', collection)
                return
            }
            const id = parseInt(context.rootState.route.currentRoute.params.id)
            context.state.collections.map((collection) => {
                if (collection.id === id) {
                    context.commit('setCurrentCollection', collection)
                }
            })
        },
        setCollectionMenu (context, collectionMenu) {
            context.commit('setCollectionMenu', collectionMenu)
        }
    }
}
