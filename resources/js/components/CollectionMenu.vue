<template>
    <transition name="fade">
        <div class="absolute top-0 left-0 w-full h-full bg-half-transparent z-999">
            <div class="w-5/6 max-w-xl m-auto mt-10 border-gray-400 rounded-lg bg-gray-100">
                <div class="py-8 mx-6">
                    <h2 class="mb-4 mx-1 text-3xl text-orange-600 font-bold">Collections</h2>
                    <p class="mb-8 mx-1 gray-600 italic leading-tight">
                        Select a collection you wish to transfer your post to.
                    </p>
                    <div v-for="collection in collections" :key="collection.id">
                        <div class="py-3 my-2 px-4 shadow cursor-pointer hover:text-orange-500 transition-color-0.2"
                            @click="transfer(collection.id)">
                            <span class="block font-medium gray-700 open-sans">{{ collection.name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
<script>
import { mapState } from 'vuex'
export default {
    methods: {
        transfer (collectionId) {
            this.collectionMenu.post.collection_id = collectionId // @todo test
            this.$store.dispatch('post/updatePost', this.collectionMenu.post)
            this.$store.dispatch('collection/setCollectionMenu', {
                isVisible: false,
                post: null
            })
        }
    },
    computed: {
        ...mapState('collection', [
            'collections'
        ]),
        ...mapState('collection', [
            'collectionMenu'
        ])
    }
}
</script>
<style lang="scss">
    .bg-half-transparent {
        background-color: rgba(0, 0, 0, 0.5);
    }
    .z-999 {
        z-index: 999;
    }
    .transition-color-0\.2 {
        transition: color 0.2s;
        -webkit-transition: color 0.2s;
        -moz-transition: color 0.2s;
    }
</style>