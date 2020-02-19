<template>
    <div>
        <div class="flex pl-5 pr-8 py-4">
            <div class="flex-1">
                <span class="text-orange-600 font-semibold text-2xl">{{ currentCollection.name }}</span>
            </div>
            <div class="">
                <button class="button">
                    Create
                </button>
            </div>
        </div>
        <div>
            <transition name="collection-fade">
                <ol class="mt-4 mb-40" v-if="!isLoading">
                    <transition-group name="grid-fade">
                        <Post v-for="post in posts" class="item"
                            :key="post.id"
                            :post="post" />
                    </transition-group>
                </ol>
            </transition>
        </div>
        <CreatePost/>
        <CollectionMenu v-if="collectionMenu.isVisible"/>
    </div>
</template>
<script>
import { mapState } from 'vuex'
import Post from './PostItem.vue'
import CreatePost from './CreatePostComponent.vue'
import CollectionMenu from './CollectionMenuComponent.vue'

export default {
    props: ['id'],
    components: {
        Post,
        CreatePost,
        CollectionMenu
    },
    methods: {
        init () {
            this.$store.dispatch('route/setCurrentRoute', this.$route)
            this.$store.dispatch('post/fetchPosts', this.id)
        }
    },
    watch: {
        id () {
            this.init()
            this.$store.dispatch('collection/getCurrentCollection')
        }
    },
    computed: {
        ...mapState('post', [
            'posts'
        ]),
        ...mapState('post', [
            'isLoading'
        ]),
        ...mapState('collection', [
            'collectionMenu'
        ]),
        ...mapState('collection', [
            'currentCollection'
        ])
    },
    created () {
        this.init()
    }
}
</script>
<style lang="scss">
    .collection-fade-enter-active, .collection-fade-leave-active {
        transition: opacity .6s;
    }
    .collection-fade-enter, .collection-fade-leave-to{
        opacity: 0;
    }
    .item {
        transition: all 0.6s;
    }
    .grid-fade-enter-active {
        transition: all 0.2s;
    }
    .grid-fade-leave-active {
        position: absolute;
    }
    .grid-fade-leave-to {
        opacity: 0;
        transform: translateX(30px);
    }
    .grid-fade-enter {
        opacity: 0;
    }
</style>