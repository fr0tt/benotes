import axios from 'axios'

let collectionsPromise
let collectionNames = new Map()

function recursiveCollectionNameMapping(collections) {
    collections.forEach((collection) => {
        collectionNames.set(collection.id, collection.name)
        recursiveCollectionNameMapping(collection.nested)
    })
}

export default {
    namespaced: true,
    state: {
        collections: null,
        sharedCollections: null,
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
        setSharedCollections(state, sharedCollections) {
            state.sharedCollections = sharedCollections
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
        fetchCollections(context, { force = false }) {
            if (collectionsPromise) {
                return collectionsPromise
            }
            if (context.state.collections !== null && force === false) {
                return
            }
            collectionsPromise = axios
                .get('/api/collections', {
                    params: {
                        nested: true,
                        withShared: true,
                    },
                })
                .then((response) => {
                    const collections = response.data.data
                    context.commit('setSharedCollections', collections.shared_collections)
                    context.commit('setCollections', collections.collections)
                    context.dispatch('setCollectionNames', collections)
                    collectionsPromise = null
                })
                .catch((error) => {
                    console.log(error)
                })
            return collectionsPromise
        },
        setCollectionNames(context, nestedCollections) {
            const ownAndSharedCollections = nestedCollections.collections.concat(
                nestedCollections.shared_collections
            )
            collectionNames.set(0, 'Uncategorized')
            recursiveCollectionNameMapping(ownAndSharedCollections)
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
                    is_root: parentId == null,
                })
                .then((response) => {
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
