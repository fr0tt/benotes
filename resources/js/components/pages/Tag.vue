<template>
    <div class="min-h-full tag-page">
        <div class="sm:ml-8 px-2 md:px-8 pb-12">
            <div class="py-4 md:pt-16">
                <h1 class="text-3xl font-medium text-gray-800">#{{ name }}</h1>
            </div>
            <transition name="collection-fade">
                <ul v-if="isLoading" class="-ml-4">
                    <Post v-for="post in posts" :key="post.id" :post="post" />
                </ul>
                <div v-else-if="posts.length < 1">No posts to be found.</div>
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
import axios from 'axios'
import { mapState } from 'vuex'
import Post from '../PostItem.vue'

export default {
    components: {
        Post,
    },
    props: {
        id: {
            type: Number,
            default: 0,
        },
    },
    data() {
        return {
            name: '',
        }
    },
    computed: {
        ...mapState('post', ['posts']),
        ...mapState('post', ['isLoading']),
    },
    watch: {
        id() {
            this.init()
        },
    },
    created() {
        this.init()
    },
    methods: {
        init() {
            if (!this.id) {
                this.$router.back()
            }
            axios.get('/api/tags/' + this.id).then((response) => {
                const tag = response.data.data
                this.name = tag.name
                this.$store.commit('appbar/setTitle', '#' + tag.name)
            })
            this.$store.commit('post/setPosts', [])
            this.$store.dispatch('appbar/setAppbar', {
                title: '',
                hint: '',
                button: {
                    label: null,
                    callback: null,
                    icon: null,
                },
                options: null,
            })
            this.$store.dispatch('post/fetchPosts', {
                tagId: this.id,
            })
        },
    },
}
</script>
<style lang="scss">
.card {
    @apply bg-gray-100;
}
.tag-page {
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
