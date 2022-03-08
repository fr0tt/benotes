<template>
    <div class="absolute z-20 px-4 py-4 bg-white shadow-lg">
        <h4 class="text-lg font-medium mb-2">Icons</h4>
        <div class="-ml-1 w-25">
            <button title="No Icon" @click="selectIcon({ id: null, label: 'No Icon' })"
                class="inline-block p-1 my-1 mx-1 rounded hover:bg-gray-200">
                <svg-vue icon="remix/folder-fill"
                    class="w-6 folder-icon fill-current text-gray-500"/>
            </button>
            <button v-for="icon in icons" :key="icon.id" :title="icon.label"
                @click="selectIcon(icon)"
                class="inline-block p-1 my-1 mx-1 rounded hover:bg-gray-200">
                <svg-vue v-if="isInline(icon.id)" :icon="'glyphs/' + icon.id"
                    class="w-7 h-7"/>
                <svg v-else class="w-7 h-7">
                    <use :xlink:href="'/glyphs.svg#' + icon.id"></use>
                </svg>
            </button>
        </div>
    </div>
</template>

<script>
import data from './../../json/glyphs.json'
import { collectionIconIsInline } from './../api/collection'
export default {
    data () {
        return {
            icons: data
        }
    },
    methods: {
        selectIcon (icon) {
            this.$emit('iconSelected', icon)
        },
        isInline (id) {
            return collectionIconIsInline(Number(id))
        }
    }
}
</script>

<style>
    .w-25 {
        width: 25rem;
    }
    .folder-icon {
        margin-top: -1.6rem;
    }
</style>