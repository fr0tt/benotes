<template>
    <div class="min-h-full relative restore">
        <div class="sm:ml-8 px-2 md:px-8 pb-16">
            <div class="py-4 md:pt-16 mb-6">
                <h1 class="text-3xl font-medium text-gray-800">
                    Recycle Bin
                </h1>
            </div>
            <transition name="collection-fade">
                <ul v-if="isLoading" class="-ml-4">
                    <Post v-for="post in posts" :key="post.id" :post="post" />
                </ul>
                <div v-else-if="posts.length < 1">
                    There is nothing yet in here.
                </div>
                <ul v-else class="-ml-4">
                    <transition-group name="grid-fade">
                        <Post v-for="post in posts" class="item-transition"
                            :key="post.id" :post="post" :restore="true" />
                    </transition-group>
                </ul>
            </transition>
        </div>
        <PostLoader :isArchived="true"/>
    </div>
</template>

<script>

import { mapState } from 'vuex'
import Post from '../PostItem.vue'
import PostLoader from '../PostLoader.vue'

export default {
    components: {
        Post,
        PostLoader
    },
    computed: {
        ...mapState('post', [
            'posts'
        ]),
        ...mapState('post', [
            'isLoading'
        ]),
    },
    methods: {
        goToCreatePost() {
            this.$router.push(`/c/0/p/create`)
        }
    },
    created () {
        this.$store.commit('post/setPosts', [])
        this.$store.dispatch('appbar/setAppbar', {
            title: 'Recycle Bin',
            button: {
                label: null,
                callback: null,
                icon: null
            },
            options: null,
        })
        this.$store.dispatch('post/fetchPosts', {
            isArchived: 1
        })
    }
}
</script>
<style lang="scss">
.restore {
    .collection-fade-enter-active, .collection-fade-leave {
        transition: opacity .6s;
    }
    .collection-fade-enter, .collection-fade-leave-to {
        opacity: 0;
    }
    .item-transition {
        transition: all 0.4s;
    }
    .grid-fade-enter-active {
        transition: all 0.2s;
    }
    .grid-fade-leave-active {
        position: absolute;
    }
    .grid-fade-leave-to {
        opacity: 0;
        transform: translateY(30px);
    }
    .grid-fade-enter {
        opacity: 0;
    }
    .mb-1\/5 {
        margin-bottom: 0.05rem;
    }
}
</style>