<template>
    <transition name="sidebar-slide" mode="out-in">
        <div v-if="showSidebar" class="sidebar w-full md:w-48 xl:w-1/6 pb-6 bg-white">
            <!--
            <div class="flex md:px-8 px-4 py-4">
                <div class="relative flex-1">
                    <div class="absolute menu min-w-48 bg-white" :class="{ 'rounded shadow': menuIsOpen }">
                        <div @click="menuIsOpen = !menuIsOpen" class="p-4 cursor-pointer">
                            <svg class="w-5 mr-2 align-text-bottom fill-current text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 20a10 10 0 1 1 0-20 10 10 0 0 1 0 20zM7 6v2a3 3 0 1 0 6 0V6a3 3 0 1 0-6 0zm-3.65 8.44a8 8 0 0 0 13.3 0 15.94 15.94 0 0 0-13.3 0z"/></svg>
                            <span class="text-gray-700">{{ authUser.name }}</span>
                            <svg-vue class="w-2 ml-2 text-gray-700 cursor-pointer fill-current inline-block" icon="arrow_down"/>
                        </div>
                        <transition name="fade">
                            <div v-if="menuIsOpen" @click="menuIsOpen = false" class="pb-1">
                                <hr class="m-0 mb-1 border-t border-gray-200">
                                <router-link to="/users" class="menuItem">
                                    Users
                                </router-link>
                                <router-link :to="'/users/' + authUser.id" class="menuItem">
                                    My Account
                                </router-link>
                                <a href="/" @click="logout()" class="menuItem py-2">
                                    Logout
                                </a>
                            </div>
                        </transition>
                    </div>
                </div>
                <svg @click="toggle()" class="menu-icon text-gray-600 cursor-pointer fill-current inline-block z-50"
                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="none" d="M0 0h24v24H0V0z"></path><path d="M3 18h13v-2H3v2zm0-5h10v-2H3v2zm0-7v2h13V6H3zm18 9.59L17.42 12 21 8.41 19.59 7l-5 5 5 5L21 15.59z"></path>
                </svg>
            </div>
            <hr class="block w-full m-0 border border-bottom border-orange-600" style="margin-left: -2px">
            -->
            <div>
                <br>
                <div class="list">
                    <router-link to="/users" tag="li" class="collection md:px-8 px-4">
                        <svg-vue class="w-4 fill-current mr-2" icon="remix/group-fill"/>
                        <span class="align-middle text-gray-700">Users</span>
                    </router-link>
                    <router-link :to="'/users/' + authUser.id" tag="li" class="collection md:px-8 px-4">
                        <svg-vue class="w-4 fill-current mr-2" icon="remix/user-settings-fill"/>
                        <span class="align-middle text-gray-700">My Account</span>
                    </router-link>
                    <a href="/" @click="logout()" class="collection md:px-8 px-4">
                        <svg-vue class="w-4 fill-current mr-2" icon="remix/logout-circle-line"/>
                        <span class="align-middle text-gray-700">Logout</span>
                    </a>
                    <br><br><br>
                    <router-link to="/" tag="li" class="collection md:px-8 px-4 mb-4">
                        <svg-vue class="w-4 fill-current mr-2" icon="remix/folder-unknow-fill"/>
                        <span class="align-middle text-gray-700">Uncategorized</span>
                    </router-link>
                    <span class="mb-2 md:px-8 px-4 block text-xs text-gray-700 font-medium uppercase">Collections</span>
                    <ol>
                        <router-link v-for="(collection) in collections" :key="collection.id"
                            :to="'/c/' + collection.id" tag="li" class="collection md:px-8 px-4">
                                <svg-vue class="w-4 fill-current mr-2" icon="remix/folder-fill"/>
                                <span class="align-top text-gray-700">{{ collection.name }}</span>
                        </router-link>
                    </ol>
                </div>
                <router-link to="/c/create" class="block md:ml-8 ml-4 mt-4 text-orange-600 font-medium">
                    <svg-vue class="w-4 mr-2 fill-current" icon="remix/folder-add-fill"/>
                    <span class="align-top">Create a new collection</span>
                </router-link>
            </div>
            <br><br><br><br><br>
            <div class="w-full px-4 md:px-6 absolute bottom-4">
                <svg-vue class="w-6 align-text-bottom" icon="logo_64x64"/>
                <span class="flex-1 ml-1 text-orange-600 text-xl font-medium">Benotes</span>
            </div>
        </div>
        <!--
        <div v-else class="sidebar relative pb-6 w-12 text-center py-4">
            <svg @click="toggle()" class="menu-icon text-gray-500 cursor-pointer fill-current inline-block"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="none" d="M0 0h24v24H0V0z"></path><path d="M3 18h13v-2H3v2zm0-5h10v-2H3v2zm0-7v2h13V6H3zm18 9.59L17.42 12 21 8.41 19.59 7l-5 5 5 5L21 15.59z"></path>
            </svg>
            <button @click="logout()" class="absolute left-0 bottom-0 ml-3 mb-4 cursor-pointer">
                <svg-vue class="w-6 text-gray-500 fill-current" icon="zondicons/stand-by"/>
            </button>
        </div>-->
    </transition>
</template>

<script>
import { mapState } from 'vuex'
import axios from 'axios'
export default {
    name: 'Sidebar',
    data () {
        return {
            //isOpen: (localStorage.getItem('sidebar') === 'false') ? false : true,
            menuIsOpen: false
        }
    },
    methods: {
        init () {
            this.$store.dispatch('collection/fetchCollections')
        },
        /*toggle () {
            localStorage.setItem('sidebar', !this.isOpen)
            this.isOpen = !this.isOpen
        },*/
        logout () {
            axios.post('/api/auth/logout')
                .catch(error => {
                    console.log(error.response)
                })
            this.$router.push({ path: '/login' })
        }
    },
    computed: {
        ...mapState([
            'showSidebar'
        ]),
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
    .min-w-48 {
        min-width: 12rem;
    }
    .py-1\.5 {
        padding-top: 0.375rem;
        padding-bottom: 0.375rem;
    }
    .bottom-4 {
        bottom: 1rem;
    }
    .sidebar-slide-enter-active, .sidebar-slide-leave-active {
        transition: width, opacity 1s;
        transition-timing-function: cubic-bezier(1, 0.01, 1, 0.9);
    }
    .sidebar-slide-enter, .sidebar-slide-leave-to {
        width: 0;
        opacity: 0;
    }
    .sidebar {
        font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        box-shadow: 2px 3px 3px 0 rgba(0, 0, 0, 0.1);
        transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s;
        -webkit-transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s;
        -moz-transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s;
        .fade-enter-active, .fade-leave-active {
            transition: opacity .2s;
        }
        .fade-enter, .fade-leave-to {
            opacity: 0;
        }
        svg {
            display: inline-block;
        }
        .menu {
            margin-top: -0.9rem;
            .menuItem {
                @apply block py-1.5 px-4 font-medium text-gray-700;
                transition: 0.2s background-color;
            }
            .menuItem:hover {
                @apply bg-gray-300;
            }
        }
        .menu-icon {
            width: 1.75rem;
        }
        .list {
            .collection {
                @apply inline-block w-full py-1;
                @apply font-medium text-gray-500 cursor-pointer;
            }
            .router-link-exact-active {
                @apply bg-orange-200 text-gray-700 font-semibold;
            }
        }
    }
</style>