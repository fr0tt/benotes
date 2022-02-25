import store from './../store'

export function getCollectionName (collectionId) {
    if (store.state.collection.collections == null) {
        return ''
    }
    const collection = store.state.collection.collections.find(collection => {
        return collection.id === collectionId
    })
    if (collection == null){
        return ''
    }
    return collection.name
}