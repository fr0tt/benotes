import axios from 'axios'

let collectionsPromise

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
        fetchCollections (context, force = false) {
            if (collectionsPromise) {
                return collectionsPromise
            }
            if (context.state.collections !== null && force == false) {
                return
            }
            collectionsPromise = axios.get('/api/collections')
                .then(response => {
                    const collections = response.data.data
                    context.commit('setCollections', collections)
                    collectionsPromise = null
                })
                .catch(error => {
                    console.log(error)
                })
            return collectionsPromise // test
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
        getCurrentCollection (context, id) {
            return new Promise((resolve, reject) => {
                if (id === null) {
                    resolve()
                }
                if (context.state.collections === null) {
                    resolve()
                }
                id = parseInt(id)
                if (id === 0) {
                    const collection = {
                        'id': 0,
                        'name': 'Uncategorized'
                    }
                    context.commit('setCurrentCollection', collection)
                    resolve()
                }
                context.state.collections.map((collection) => {
                    if (collection.id === id) {
                        context.commit('setCurrentCollection', collection)
                        resolve()
                    }
                })
            })
        },
        setCollectionMenu (context, collectionMenu) {
            context.commit('setCollectionMenu', collectionMenu)
        }
    }
}
