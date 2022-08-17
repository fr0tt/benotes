<template>
    <div class="min-h-full search">
        <div class="sm:ml-8 px-2 md:px-8 pb-12">
            <div class="py-4 md:pt-16 mb-6">
                <h1 class="text-3xl font-medium text-gray-800">Search</h1>
            </div>
            <Searchbar class="mt-4 mb-8" />
            <transition name="collection-fade">
                <ul v-if="isLoading" class="-ml-4">
                    <Post v-for="post in posts" :key="post.id" :post="post" />
                </ul>
                <div v-else-if="posts.length < 1">No search results.</div>
                <ul v-else class="-ml-4">
                    <transition-group name="grid-fade">
                        <Post v-for="post in posts" :key="post.id" :post="post" :permission="7" />
                    </transition-group>
                </ul>
            </transition>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex'
import Post from '../PostItem.vue'
import Searchbar from '../Searchbar.vue'

export default {
    components: {
        Post,
        Searchbar,
    },
    computed: {
        ...mapState('post', ['posts']),
        ...mapState('post', ['isLoading']),
    },
    created() {
        this.$store.commit('post/setPosts', [])
        this.$store.dispatch('appbar/setAppbar', {
            title: 'Search',
            hint: 'Ctrl + Alt + N',
            button: {
                label: 'Create',
                callback: this.goToCreatePost,
                icon: 'add',
            },
            options: null,
        })
    },
    methods: {
        goToCreatePost() {
            this.$router.push(`/c/0/p/create`)
        },
    },
}
</script>
<style lang="scss">
.search {
    .collection-fade-enter-active,
    .collection-fade-leave {
        transition: opacity 0.6s;
    }
    .collection-fade-enter,
    .collection-fade-leave-to {
        opacity: 0;
    }
    .item-transition {
        transition: all 0.4s;
    }
    .grid-fade-enter-active {
        transition: all 0.2s;
    }
    /*.grid-fade-leave-active {
        position: absolute;
    }*/
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
