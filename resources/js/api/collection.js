import store from './../store'

export function getCollectionName (collectionId) {
    if (collectionId === null) {
        return 'Uncategorized'
    }
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

export function collectionIconIsInline (iconId) {
    return [4003, 4008, 4010, 4017, 4103].indexOf(iconId) > -1
}