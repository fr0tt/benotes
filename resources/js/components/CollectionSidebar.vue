<template>
    <li>
        <div
            class="collection theme__sidebar__collection"
            :class="[
                isActiveLink('/c/' + collection.id)
                    ? 'router-link-exact-active-parent'
                    : '',
            ]"
            :style="paddingLeft">
            <router-link
                class="relative truncate"
                :class="{
                    inline: isNested(collection),
                }"
                :to="'/c/' + collection.id">
                <svg-vue
                    v-if="collectionIconIsInline(collection.icon_id)"
                    :icon="'glyphs/' + collection.icon_id"
                    class="w-6 h-6 glyphs" />
                <svg v-else-if="collection.icon_id" class="w-6 h-6 glyphs">
                    <use :xlink:href="'/glyphs.svg#' + collection.icon_id" />
                </svg>
                <svg-vue
                    v-else
                    icon="remix/folder-fill"
                    class="no-glyph w-4 fill-current align-text-bottom mr-2" />
                <svg-vue
                    v-if="collection.is_being_shared && showShareState"
                    icon="remix/share-fill"
                    class="absolute w-3 left-2 bottom-0 fill-current text-blue-600 bg-white rounded-xl" />
                <span class="theme__sidebar__label align-middle text-gray-700">{{
                    collection.name
                }}</span>
            </router-link>
            <div v-if="debug" class="absolute bottom-0 right-0">
                <span
                    class="px-1 text-xs text-gray-700 float-right"
                    :class="collection.parent_id ? 'bg-orange-200' : 'bg-gray-200'"
                    >[{{ collection.left + ':' + collection.right }}]</span
                >
            </div>
            <button
                v-if="isNested(collection)"
                class="flex-auto text-left pl-1"
                @click="toggleNestedCollections">
                <svg-vue
                    class="icon transform transition-transform duration-300 text-gray-600"
                    :class="{ 'rotate-180': show }"
                    icon="material/arrow_drop_down" />
            </button>
        </div>
        <transition name="fade">
            <Draggable
                v-bind="{ animation: 200 }"
                :list="collection.nested"
                :group="{ name: 'collections' }"
                :disabled="isUpdating"
                :delay="180"
                :delay-on-touch-only="true"
                tag="ol"
                @change="change">
                <CollectionSidebar
                    v-for="nestedCollection in show ? collection.nested : null"
                    :key="nestedCollection.id"
                    :collection="nestedCollection"
                    :level="level + 1"
                    class="nested -mb-1"
                    @addedOrMoved="addedOrMovedEvent" />
            </Draggable>
        </transition>
    </li>
</template>

<script>
import { collectionIconIsInline } from './../api/collection'
import Draggable from 'vuedraggable'
import { mapState } from 'vuex'
export default {
    name: 'CollectionSidebar',
    components: { Draggable },
    props: {
        collection: {
            type: Object,
            default: null,
        },
        level: {
            type: Number,
            default: 0,
        },
        showShareState: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            show: false,
            debug: false, // requires editing Collection.php for it to work
        }
    },
    computed: {
        ...mapState('collection', ['isUpdating']),
        paddingLeft() {
            if (this.level === 0) return ''
            return 'padding-left: ' + (2 + this.level * 2) + 'rem'
        },
    },
    created() {
        this.show = localStorage.getItem(`c${this.collection.id}-state`)
    },
    methods: {
        isNested(collection) {
            return collection.nested.length > 0
        },
        isActiveLink(route) {
            return this.$route.path === route || this.$route.path === route + '/edit'
        },
        collectionIconIsInline,
        change(event) {
            if (this.level > 0) {
                if (!event.parentId) {
                    event.parentId = this.collection.id
                    event.parentIsBeingShared = this.collection.is_being_shared
                }
                this.$emit('addedOrMoved', event)
            } else {
                if (!event.parentId) {
                    event.parentId = this.collection.id
                    event.parentIsBeingShared = this.collection.is_being_shared
                }
                this.$emit('input', event)
                this.showNestedCollections()
            }
        },
        addedOrMovedEvent(event) {
            if (event.removed) return
            if (this.level > 0) {
                this.$emit('addedOrMoved', event)
            } else {
                this.showNestedCollections()
                this.$emit('input', event)
            }
        },
        moveCollection(event) {
            if (event.added) {
                this.$store.dispatch('collection/moveCollection', {
                    id: event.added.element.id,
                    parentId: event.parentId,
                    localOrder: event.added.newIndex + 1,
                })
            } else if (event.moved) {
                this.$store.dispatch('collection/moveCollection', {
                    id: event.moved.element.id,
                    parentId: event.parentId,
                    localOrder: event.moved.newIndex + 1,
                })
            }
        },
        showNestedCollections() {
            if (this.show) return
            this.show = true
            localStorage.setItem(`c${this.collection.id}-state`, 'show')
        },
        hideNestedCollections() {
            this.show = false
            localStorage.removeItem(`c${this.collection.id}-state`)
        },
        toggleNestedCollections() {
            if (this.show) this.hideNestedCollections()
            else this.showNestedCollections()
        },
    },
}
</script>
