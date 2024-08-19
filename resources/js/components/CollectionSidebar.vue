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

            <button
                v-if="isNested(collection)"
                class="flex-auto text-left pl-1"
                @click="show = !show">
                <svg-vue
                    class="icon transform transition-transform duration-300 text-gray-600"
                    :class="{ 'rotate-180': show }"
                    icon="material/arrow_drop_down" />
            </button>
        </div>
        <transition name="fade">
            <ol v-if="show">
                <CollectionSidebar
                    v-for="nestedCollection in collection.nested"
                    :key="nestedCollection.id"
                    :collection="nestedCollection"
                    :level="level + 1"
                    class="nested -mb-1" />
            </ol>
        </transition>
    </li>
</template>

<script>
import { collectionIconIsInline } from './../api/collection'
export default {
    name: 'CollectionSidebar',
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
        }
    },
    computed: {
        paddingLeft() {
            if (this.level === 0) return ''
            return 'padding-left: ' + (2 + this.level * 2) + 'rem'
        },
    },
    methods: {
        isNested(collection) {
            return collection.nested.length > 0
        },
        isActiveLink(route) {
            return this.$route.path === route || this.$route.path === route + '/edit'
        },
        collectionIconIsInline,
    },
}
</script>
