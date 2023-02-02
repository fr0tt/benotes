<template>
    <ol>
        <li v-for="collection in collections" :key="collection.id" class="collection">
            <router-link
                :class="{
                    'router-link-exact-active': isActiveLink('/c/' + collection.id),
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
                    class="w-4 fill-current align-text-bottom mr-2" />
                <span class="align-middle text-gray-700">{{ collection.name }}</span>
            </router-link>
            <button @click="show = !show">
                <svg-vue
                    v-if="collection.nested.length > 0"
                    class="icon transform transition-transform duration-300 text-gray-600"
                    :class="{ 'rotate-180': show }"
                    icon="material/arrow_drop_down" />
            </button>
            <transition name="fade">
                <CollectionSidebar
                    v-if="show"
                    :collections="collection.nested"
                    class="nested -mb-1" />
            </transition>
        </li>
    </ol>
</template>

<script>
import { collectionIconIsInline } from './../api/collection'
export default {
    name: 'CollectionSidebar',
    props: {
        collections: {
            type: Array,
            default: null,
        },
    },
    data() {
        return {
            show: false,
        }
    },
    methods: {
        isActiveLink(route) {
            return route === this.$route.path
        },
        collectionIconIsInline,
    },
}
</script>
