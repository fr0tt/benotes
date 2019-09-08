<template>
    <div>
        <h1 class="text-2xl font-bold text-blue-800">{{ currentCollectionName }}</h1>
        <ol>
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
            this.$store.dispatch('post/fetchPosts', this.id)
        }
    },
    watch: {
        id () {
            this.init()
        }
    },
    computed: {
        ...mapState('post', [
            'posts'
        ]),
        ...mapState('collection', [
            'currentCollectionName'
        ])
    },
    created () {
        this.init()
    }
}
</script>
<style lang="scss">

</style>