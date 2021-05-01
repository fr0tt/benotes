<template>
    <transition name="fade">
        <div ref="cmModal" class="absolute top-0 left-0 w-full h-full bg-half-transparent z-50">
            <div class="w-5/6 max-w-xl m-auto mt-10 border-gray-400 rounded-lg bg-gray-100">
                <div class="py-8 mx-6">
                    <h2 class="mb-4 mx-1 text-3xl text-orange-600 font-bold">Collections</h2>
                    <p class="mb-8 mx-1 gray-600 italic leading-tight">
                        Select a collection you wish to transfer your post to.
                    </p>
                    <ol v-for="collection in collections" :key="collection.id">
                        <li class="py-3 px-4 cursor-pointer hover:text-orange-500 transition-color-0.2"
                            @click="transfer(collection.id)">
                            <span class="block font-medium gray-700 open-sans">{{ collection.name }}</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </transition>
</template>
<script>
import { mapState } from 'vuex'
export default {
    computed: {
        collections () {
            const uncategorized = [{
                id: null,
                name: 'Uncategorized'
            }]
            return uncategorized.concat(this.$store.state.collection.collections)
        },
        ...mapState('collection', [
            'collectionMenu'
        ])
    },
    methods: {
        transfer (collectionId) {
            // neccessary in order to copy the post instead of referencing it
            const post = JSON.parse(JSON.stringify(this.collectionMenu.post)) 
            post.collection_id = collectionId
            this.$store.dispatch('post/updatePost', { post: post, transfer: true })
            this.$store.dispatch('collection/setCollectionMenu', {
                isVisible: false,
                post: null
            })
        },
        hide (event) {
            if (this.$refs.cmModal == event.target) {
                this.$store.dispatch('collection/setCollectionMenu', {
                    isVisible: false,
                    post: null
                })
                document.querySelector('#app').removeEventListener('click', this.hide, true)
            }
        }
    },
    created () {
        document.querySelector('#app').addEventListener('click', this.hide, true)
    }
}
</script>
<style lang="scss">
    .bg-half-transparent {
        background-color: rgba(0, 0, 0, 0.5);
    }
    .transition-color-0\.2 {
        transition: color 0.2s;
        -webkit-transition: color 0.2s;
        -moz-transition: color 0.2s;
    }
</style>