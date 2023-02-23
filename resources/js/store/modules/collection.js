import axios from 'axios'

let collectionsPromise

export default {
    namespaced: true,
    state: {
        collections: null,
        collectionNames: new Map(),
        currentCollection: {
            id: null,
            name: '',
        },
        collectionMenu: {
            isVisible: false,
            post: null,
        },
    },
    mutations: {
        setCollections(state, collections) {
            state.collections = collections
        },
        addCollection(state, collection) {
            state.collections.push(collection)
        },
        deleteCollection(state, index) {
            state.collections.splice(index, 1)
        },
        setCollection(state, { index, collection }) {
            state.collections.splice(index, 1, collection)
        },
        setCollectionNames(state, collections) {
            state.collectionNames = collections
        },
        setCurrentCollection(state, collection) {
            state.currentCollection = collection
        },
        setCollectionMenu(state, collectionMenu) {
            state.collectionMenu = collectionMenu
        },
    },
    actions: {
        fetchCollections(context, { force = false, nested = false }) {
            if (collectionsPromise) {
                return collectionsPromise
            }
            if (context.state.collections !== null && force == false) {
                return
            }
            collectionsPromise = axios
                .get('/api/collections', {
                    params: {
                        nested: nested,
                    },
                })
                .then((response) => {
                    const collections = response.data.data
                    context.commit('setCollections', collections)
                    context.dispatch('setCollectionNames', collections)
                    collectionsPromise = null
                })
                .catch((error) => {
                    console.log(error)
                })
            return collectionsPromise
        },
        setCollectionNames(context, nestedCollections) {
            const collectionNames = new Map()
            collectionNames.set(0, 'Uncategorized')
            nestedCollections.forEach((col) => {
                let collections = col.nested
                while (collections.length > 0) {
                    collections.forEach((childCol) => {
                        collectionNames.set(childCol.id, childCol.name)
                        collections = childCol.nested
                    })
                }
                collectionNames.set(col.id, col.name)
            })
            context.commit('setCollectionNames', collectionNames)
        },
        addCollection(context, collection) {
            context.commit('addCollection', collection)
        },
        updateCollection(context, { id, name, parentId, iconId }) {
            axios
                .patch('/api/collections/' + id, {
                    name: name,
                    parent_id: parentId,
                    icon_id: iconId,
                })
                .then((response) => {
                    /*
                    const newCollection = response.data.data
                    const index = context.state.collections.findIndex((collection) => {
                        return collection.id === newCollection.id
                    })
                    context.commit('setCollection', { index, collection: newCollection })
                    */
                    // @TODO test
                    context.dispatch('fetchCollections', {
                        nested: true,
                        force: true,
                    })
                })
                .catch((error) => {
                    console.log(error.response.data)
                })
        },
        deleteCollection(context, { id, nested = false }) {
            axios
                .delete('/api/collections/' + id, {
                    params: {
                        nested: nested,
                    },
                })
                .then((response) => {
                    const index = context.state.collections.findIndex((collection) => {
                        return collection.id === id
                    })
                    context.commit('deleteCollection', index)
                })
                .catch((error) => {
                    console.log(error)
                })
        },
        getCurrentCollection(context, id) {
            if (id === null) {
                return new Promise((resolve, reject) => {
                    reject()
                })
            }
            id = parseInt(id)

            if (id === 0) {
                const collection = {
                    id: 0,
                    name: 'Uncategorized',
                }
                context.commit('setCurrentCollection', collection)
                return new Promise((resolve) => {
                    resolve(collection.name)
                })
            }
            if (context.state.collectionNames.size === 0) {
                return axios.get('/api/collections/' + id).then((response) => {
                    context.commit('setCurrentCollection', response.data.data)
                })
            }
            context.commit('setCurrentCollection', {
                id: id,
                name: context.state.collectionNames.get(id),
            })
            return new Promise((resolve) => {
                resolve(context.state.collectionNames.get(id))
            })
        },
        setCollectionMenu(context, collectionMenu) {
            context.commit('setCollectionMenu', collectionMenu)
        },
    },
}
