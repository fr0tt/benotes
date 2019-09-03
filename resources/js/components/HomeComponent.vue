<template>
    <div class="w-full">
        <div class="card">
            <h1>{{ authUser.name }}</h1>
            <ol>
                <Post v-for="(post, i) in posts" 
                    :key="i" 
                    :post="post" />
            </ol>
        </div>
    </div>
</template>

<script>
import Post from './PostComponent.vue'

    export default {
        components: { 
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
