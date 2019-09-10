<template>
    <div>
        <!-- <h1 class="text-3xl my-4 font-bold text-blue-500">{{ currentCollection.name }}</h1> -->
        <ol class="mt-4 mb-40">
            <Post v-for="post in posts" 
                :key="post.id" 
                :post="post" />
        </ol>
    </div>
</template>
<script>
import { mapState } from 'vuex'
import Post from './PostComponent.vue'
export default {
    props: ['id'],
    components: {
        Post
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
        // this.$store.dispatch('collection/fetchCollections')
    }
}
</script>
<style lang="scss">

</style>