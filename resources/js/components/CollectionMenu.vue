<template>
    <transition name="fade">
        <div
            ref="cmModal"
            class="fixed top-0 left-0 w-full h-full bg-half-transparent z-50">
            <div
                class="w-5/6 max-w-xl m-auto mt-10 border-gray-400 rounded-lg bg-gray-100 theme__modal">
                <div class="py-8 mx-6">
                    <h2 class="mb-4 mx-1 text-3xl text-orange-600 font-bold">
                        Collections
                    </h2>
                    <p class="mb-8 mx-1 gray-600 italic leading-tight">
                        Select a collection you wish to transfer your post to.
                    </p>
                    <Treeselect
                        v-model="selectedCollectionId"
                        :options="optionsCollections"
                        :normalizer="
                            (node) => {
                                return {
                                    id: node.id,
                                    label: node.name,
                                    children:
                                        node.nested?.length > 0
                                            ? node.nested
                                            : node.children,
                                }
                            }
                        "
                        placeholder=""
                        class="block w-80" />
                    <button class="button block mt-4 ml-auto mr-0" @click="transfer">
                        Transfer
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>
<script>
import Treeselect from '@riophae/vue-treeselect'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'
import { mapState } from 'vuex'
export default {
    components: {
        Treeselect,
    },
    data() {
        return {
            optionsCollections: [],
            selectedCollectionId: null,
        }
    },
    computed: {
        ...mapState('collection', ['collections']),
        ...mapState('collection', ['collectionMenu']),
    },
    created() {
        const uncategorized = { name: 'Home', id: 0, nested: null }
        this.optionsCollections.push(uncategorized)
        this.$store.dispatch('collection/fetchCollections', { nested: true }).then(() => {
            this.optionsCollections = this.optionsCollections.concat(this.collections)
            this.selectedCollectionId = this.collectionMenu.post.collection_id
                ? this.collectionMenu.post.collection_id
                : 0
        })
        document.querySelector('#app').addEventListener('click', this.hide, true)
    },
    methods: {
        transfer() {
            // necessary in order to copy the post instead of referencing it
            const post = JSON.parse(JSON.stringify(this.collectionMenu.post))
            post.collection_id = this.selectedCollectionId
            this.$store.dispatch('post/updatePost', { post: post, transfer: true })
            this.$store.dispatch('collection/setCollectionMenu', {
                isVisible: false,
                post: null,
            })
        },
        hide(event) {
            if (this.$refs.cmModal == event.target) {
                this.$store.dispatch('collection/setCollectionMenu', {
                    isVisible: false,
                    post: null,
                })
                document
                    .querySelector('#app')
                    .removeEventListener('click', this.hide, true)
            }
        },
    },
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
