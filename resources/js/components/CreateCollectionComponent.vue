<template>
    <form @submit.prevent="create" class="my-8 mx-20">

        <div class="flex">

            <div class="w-4/5">
                <div class="mb-20">
                    <label class="block uppercase text-gray-600 font-medium">Name</label>
                    <input v-model="name" placeholder="Name" 
                        class="w-full text-5xl text-gray-800 font-bold bg-gray-300 outline-none py-1"/>
                </div>
            </div>
            <div class="w-1/5">
                <div class="text-center mt-6">
                    <button class="text-blue-700 font-semibold text-xl
                        hover:text-white py-2 px-8 border-2 border-blue-500 hover:bg-blue-500">Save</button>
                </div>
            </div>

        </div>
        <!-- 
        <div class="mb-20">
            <label class="block uppercase text-gray-600 font-medium">Add existing Posts</label>
            <Post v-for="post in posts" 
                :key="post.id" 
                :post="post" />
        </div> -->

    </form>
</template>

<script>
import axios from 'axios'
import { mapState } from 'vuex'
import Post from './PostComponent.vue'
export default {
    name: 'CreateCollection',
    components: {
        Post
    },
    data () {
        return {
            name: ''
        }
    },
    methods: {
        create () {
            axios.post('/api/collections', {
                name: this.name
            })
                .then(response => {
                    this.$store.dispatch('collection/addCollection', response.data.data)
                    this.$router.push({ path: '/' })
                })
                .catch(error => {
                    console.log(error.response.data)
                })
        }
    },
    computed: {
        ...mapState('post', [
            'posts'
        ])
    },
    created () {
        this.$store.dispatch('post/fetchPosts', null)
    }
}
</script>