<template>
    <div class="sidebar max-w-xs my-4" :class="isOpen ? 'md:w-48 xl:w-64 mx-6' : 'w-0 mx-3 md:mx-6'">
        <div class="relative z-50">
            <svg class="menu-icon" @click="toggle()" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="none" d="M0 0h24v24H0V0z"/><path d="M3 18h13v-2H3v2zm0-5h10v-2H3v2zm0-7v2h13V6H3zm18 9.59L17.42 12 21 8.41 19.59 7l-5 5 5 5L21 15.59z"/></svg>
        </div>
        <div :class="{ 'overflow-hidden': !isOpen }">
            <div class="relative">
                <router-link to="/users/me">
                    <svg class="w-5 mr-2 align-text-bottom fill-current text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 20a10 10 0 1 1 0-20 10 10 0 0 1 0 20zM7 6v2a3 3 0 1 0 6 0V6a3 3 0 1 0-6 0zm-3.65 8.44a8 8 0 0 0 13.3 0 15.94 15.94 0 0 0-13.3 0z"/></svg>
                    <span class="text-lg">{{ authUser.name }}</span>
                </router-link>
            </div>
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
    </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
    name: 'Sidebar',
    data () {
        return {
            isOpen: (localStorage.getItem('sidebar') === 'false') ? false : true
        }
    },
    methods: {
        init () {
            this.$store.dispatch('collection/fetchCollections')       
        },
        toggle () {
            localStorage.setItem('sidebar', !this.isOpen)
            this.isOpen = !this.isOpen
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
    .sidebar {
        transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s;
        -webkit-transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s;
        -moz-transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s;
        svg {
            display: inline-block;
        }
        .menu-icon {
            @apply w-8 cursor-pointer absolute inline-block;
            right: -1.5rem;
            fill: #2d3748;
        }
        .add-icon {
            margin-top: -0.1rem;
            fill: #2d3748;
        }
        .max-w-14 {
            max-width: 14rem;
        }
        .list .router-link-exact-active {
            @apply text-blue-500;
        }
    }
</style>