<template>
    <div class="min-h-full relative restore">
        <div class="sm:ml-8 px-2 md:px-8 pb-16">
            <div class="py-4 md:pt-16 mb-6">
                <h1 class="text-3xl font-medium text-gray-800">Recycle Bin</h1>
            </div>
            <transition name="collection-fade">
                <ul v-if="isLoading" class="-ml-4">
                    <Post v-for="post in posts" :key="post.id" :post="post" />
                </ul>
                <div v-else-if="posts.length < 1">There is nothing yet in here.</div>
                <ul v-else class="-ml-4">
                    <transition-group name="grid-fade">
                        <Post
                            v-for="post in posts"
                            :key="post.id"
                            class="item-transition"
                            :post="post"
                            :restore="true" />
                    </transition-group>
                </ul>
            </transition>
        </div>
        <Confirmation
            v-if="showPopup"
            :action="clearRecycleBin"
            :hide="() => (showPopup = false)"
            confirmation-text="Delete"
            description="Type 'delete' if you wish to delete your trashed posts for all eternity. " />
        <PostLoader :is-archived="true" />
    </div>
</template>

<script>
import { mapState } from 'vuex'
import Post from '../PostItem.vue'
import PostLoader from '../PostLoader.vue'
import axios from 'axios'
import Confirmation from '../Confirmation.vue'

export default {
    components: {
        Confirmation,
        Post,
        PostLoader,
    },
    data() {
        return {
            showPopup: false,
        }
    },
    computed: {
        ...mapState('post', ['posts']),
        ...mapState('post', ['isLoading']),
    },
    created() {
        this.$store.commit('post/setPosts', [])
        this.$store.dispatch('appbar/setAppbar', {
            title: 'Recycle Bin',
            button: {
                label: 'Clear',
                callback: () => (this.showPopup = true),
                icon: 'delete',
            },
            options: null,
        })
        this.$store.dispatch('post/fetchPosts', {
            isArchived: 1,
        })
    },
    methods: {
        goToCreatePost() {
            this.$router.push(`/c/0/p/create`)
        },
        clearRecycleBin() {
            axios
                .delete('/api/posts?is_trashed=1')
                .catch(() => {
                    this.$store.dispatch('notification/setNotification', {
                        type: 'error',
                        title: 'Error',
                        description: 'Recycle Bin could not be cleared.',
                    })
                })
                .then(() => {
                    this.$store.commit('post/setPosts', [])
                })
        },
    },
}
</script>
<style lang="scss">
.restore {
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
