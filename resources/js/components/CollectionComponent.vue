<template>
    <div>
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
    </div>
</template>
<script>
import { mapState } from 'vuex'
import Post from './PostComponent.vue'
import CreatePost from './CreatePostComponent.vue'

export default {
    props: ['id'],
    components: {
        Post,
        CreatePost
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
        ]),
        ...mapState('post', [
            'isLoading'
        ])
    },
    created () {
        this.init()
    }
}
</script>
<style lang="scss">
    .collection-fade-enter-active, .collection-fade-leave-active {
        transition: opacity .8s;
    }
    .collection-fade-enter, .collection-fade-leave-to{
        opacity: 0;
    }
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