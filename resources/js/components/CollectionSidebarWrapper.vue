<template>
    <Draggable
        v-bind="{ animation: 200 }"
        :list="value"
        :group="{ name: 'collections' }"
        :disabled="isUpdating"
        :delay="180"
        :delay-on-touch-only="true"
        tag="ol"
        @change="rootLevelChange">
        <CollectionSidebar
            v-for="collection in value"
            :key="collection.id"
            :collection="collection"
            :show-share-state="true"
            @input="lowerLevelChange" />
    </Draggable>
</template>
<script>
import CollectionSidebar from './CollectionSidebar.vue'
import Draggable from 'vuedraggable'
import { mapState } from 'vuex'

export default {
    components: {
        Draggable,
        CollectionSidebar,
    },
    props: {
        value: {
            required: false,
            type: Array,
            default: null,
        },
    },
    computed: {
        ...mapState('collection', ['isUpdating']),
    },
    methods: {
        lowerLevelChange(event) {
            this.$emit('input', this.value)
            this.moveCollection(event)
        },
        // required for root level movements
        rootLevelChange(event) {
            this.$emit('input', this.value)
            this.moveCollection(event, true)
        },
        moveCollection(event, isRoot = false) {
            let data = null
            if (event.added) {
                data = {
                    id: event.added.element.id,
                    parentId: isRoot ? null : event.parentId,
                    localOrder: event.added.newIndex + 1,
                }
            } else if (event.moved) {
                data = {
                    id: event.moved.element.id,
                    parentId: isRoot ? null : event.parentId,
                    localOrder: event.moved.newIndex + 1,
                }
            }
            if (data) this.$store.dispatch('collection/moveCollection', data)
        },
    },
}
</script>
