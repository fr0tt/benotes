<template>
    <div class="m-4 mx-6">
        <router-link to="/users/me">
            <svg class="w-5 mr-2 align-text-bottom fill-current text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 20a10 10 0 1 1 0-20 10 10 0 0 1 0 20zM7 6v2a3 3 0 1 0 6 0V6a3 3 0 1 0-6 0zm-3.65 8.44a8 8 0 0 0 13.3 0 15.94 15.94 0 0 0-13.3 0z"/></svg>
            <span class="text-lg">{{ authUser.name }}</span>
        </router-link>
        <br><br><br><br>
        <div class="mb-4">
            <span class="text-sm text-gray-800 tracking-wider uppercase">Collections</span>
            <router-link to="/c/create">
                <svg class="ml-4 w-4 add-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>New Collection</title>
                    <path d="M11 9h4v2h-4v4H9v-4H5V9h4V5h2v4zm-1 11a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16z"/>
                </svg>
            </router-link>
        </div>
        <div class="max-w-14 list">
            <div class="text-xl text-gray-800 mb-4">
                <router-link to="/" class="inline-block w-full font-semibold">Uncategorized</router-link>
            </div>
            <div v-for="(collection) in collections" :key="collection.id" 
                class="text-xl text-gray-800 mb-2">
                <router-link :to="'/c/' + collection.id" class="inline-block w-full font-semibold">{{ collection.name }}</router-link>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
    name: 'Sidebar',
    methods: {
        init () {
            this.$store.dispatch('collection/fetchCollections')       
        }
    },
    computed: {
        ...mapState('auth', [
            'authUser'
        ]),
        ...mapState('collection', [
            'collections'
        ])
    },
    mounted () {
        this.init()
    }
}
</script>

<style lang="scss">
    svg {
        display: inline-block;
    }
    .add-icon {
        margin-top: -0.1rem;
    }
    .max-w-14 {
        max-width: 14rem;
    }
    .list .router-link-exact-active {
        @apply text-blue-500 font-bold;
    }
</style>