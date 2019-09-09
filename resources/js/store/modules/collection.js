import axios from 'axios'

export default {
    namespaced: true,
    state: {
        collections: null,
        currentCollectionName: null
    },
    mutations: {
        setCollections (state, collections) {
            state.collections = collections
        },
        addCollection (state, collection) {
            state.collections.push(collection)
        },
        setCurrentCollectionName (state, collection) {
            state.currentCollectionName = collection
        }
    },
    actions: {
        fetchCollections (context) {
            axios.get('/api/collections')
                .then(response => {
                    const collections = response.data.data
                    context.commit('setCollections', collections)
                    context.dispatch('getCurrentCollectionName', context.rootState.route.currentRoute.params.id)
                })
                .catch(error => {
                    console.log(error)
                })
        },
        addCollection (context, collection) {
            context.commit('addCollection', collection)
        },
        getCurrentCollectionName (context) {
            if (Object.keys(context.rootState.route.currentRoute.params).length === 0) {
                context.commit('setCurrentCollectionName', 'Uncategorized')
                return
            }
            const id = parseInt(context.rootState.route.currentRoute.params.id)
            context.state.collections.map((collection) => {
                if (collection.id === id) {
                    context.commit('setCurrentCollectionName', collection.name)
                }
            })
        }
    }
}
