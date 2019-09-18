<template>
    <div>
        <div>
            <!-- <h1 class="text-3xl my-4 font-bold text-blue-500">{{ currentCollection.name }}</h1> -->
            <ol class="mt-4 mb-40">
                <transition-group name="grid-fade">
                <Post v-for="post in posts" class="item"
                    :key="post.id" 
                    :post="post" />
                </transition-group>
            </ol>
            <ContextMenu/>
        </div>
        <CreatePost/>
    </div>
</template>
<script>
import { mapState } from 'vuex'
import Post from './PostComponent.vue'
import CreatePost from './CreatePostComponent.vue'
import ContextMenu from './ContextMenu.vue'
export default {
    props: ['id'],
    components: {
        Post,
        CreatePost,
        ContextMenu
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
    .item {
        transition: all 0.8s;
    }
    .grid-fade-enter-active {
        transition: all 0.3s;
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