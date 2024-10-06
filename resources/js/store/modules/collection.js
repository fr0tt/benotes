import axios from 'axios'

let collectionsPromise
let collectionNames = new Map()
let wasUpdated = false

function recursiveCollectionNameMapping(collections) {
    collections.forEach((collection) => {
        collectionNames.set(collection.id, collection.name)
        recursiveCollectionNameMapping(collection.nested)
    })
}

function recursiveCollectionPositionUpdate(collections, id, parentId, isBeingShared) {
    for (let i = 0; i < collections.length; i++) {
        if (collections[i].id === id) {
            collections[i].parent_id = parentId
            collections[i].is_being_shared = isBeingShared
            wasUpdated = true
        }
        if (collections[i].nested)
            collections[i].nested = recursiveCollectionPositionUpdate(
                collections[i].nested,
                id,
                parentId,
                collections[i].is_being_shared
            )
    }
    return collections
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
        isUpdating: false,
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
        isUpdating(state, isUpdating) {
            state.isUpdating = isUpdating
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
        updateCollection(context, { id, name, parentId, iconId }) {
            context.commit('isUpdating', true)
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
                    context.commit('isUpdating', false)
                })
                .catch((error) => {
                    console.log(error.response.data)
                    context.commit('isUpdating', false)
                })
        },
        moveCollection(context, { id, parentId, localOrder }) {
            context.commit('isUpdating', true)
            context.dispatch('updateLocalCollection', { id, parentId })
            axios
                .patch('/api/collections/' + id, {
                    parent_id: parentId,
                    local_order: localOrder,
                    is_root: parentId == null,
                })
                .catch((error) => {
                    console.log(error)
                    context.dispatch('fetchCollections', {
                        nested: true,
                        force: true,
                    })
                })
            // not needed: context.commit('isUpdating', false)
        },
        deleteCollection(context, { id, nested = false }) {
            context.commit('isUpdating', true)
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
                    context.commit('isUpdating', false)
                })
                .catch((error) => {
                    console.log(error)
                    context.commit('isUpdating', false)
                })
        },
        updateLocalCollection(context, { id, parentId }) {
            wasUpdated = false
            const collections = recursiveCollectionPositionUpdate(
                JSON.parse(JSON.stringify(context.state.collections)),
                id,
                parentId,
                false
            )
            if (collections !== null) context.commit('setCollections', collections)
            context.commit('isUpdating', false)
            if (!wasUpdated) {
                context.dispatch('fetchCollections', {
                    nested: true,
                    force: true,
                })
            }
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
