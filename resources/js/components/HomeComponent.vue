<template>
    <div class="flex w-full">
        <div class="max-w-xs w-1/6">
            <Sidebar/>
        </div>
        <div class="w-5/6 pr-6">
            <ol>
                <Post v-for="(post, i) in posts" 
                    :key="i" 
                    :post="post" />
            </ol>
        </div>
    </div>
</template>

<script>
import Sidebar from './SidebarComponent.vue'
import Post from './PostComponent.vue'

    export default {
        components: { 
            Sidebar,
            Post 
        },
        methods: {
            init () {
                this.$store.dispatch('fetchPosts')
            }
        },
        computed: {
            authUser () {
                return this.$store.state.auth.authUser
            },
            posts () {
                return this.$store.state.posts.posts
            }
        },
        mounted () {
            this.init()
        }
    }
</script>
