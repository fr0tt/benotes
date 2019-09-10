<template>
    <div class="m-4 mx-6">
        <p class="text-lg">{{ authUser.name }}</p>
        <br><br><br><br>
        <div class="mb-4">
            <span class="text-sm text-gray-800 tracking-wider uppercase">Collections</span>
            <router-link to="/c/create">
                <svg class="ml-4 w-4 add-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>New Collection</title>
                    <path d="M11 9V5H9v4H5v2h4v4h2v-4h4V9h-4zm-1 11a10 10 0 1 1 0-20 10 10 0 0 1 0 20z"/>
                </svg>
            </router-link>
        </div>
        <!--<div class="font-normal text-base text-gray-800 my-2">
            New Collection
        </div>-->
        <div class="max-w-14 list">
            <div class="font-bold text-xl text-gray-800 mb-4">
                <router-link to="/" class="inline-block w-full pl-2">Uncategorized</router-link>
            </div>
            <div v-for="(collection) in collections" :key="collection.id" 
                class="font-bold text-xl text-gray-800 mb-2">
                <router-link :to="'/c/' + collection.id" class="inline-block w-full pl-2">{{ collection.name }}</router-link>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: 'Sidebar',
    methods: {
        init () {
            this.$store.dispatch('collection/fetchCollections')       
        }
    },
    computed: {
        authUser () {
            return this.$store.state.auth.authUser
        },
        collections () {
            return this.$store.state.collection.collections
        }
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
        @apply bg-gray-800 text-white;
    }
</style>