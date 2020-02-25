<template>
    <transition name="slide" mode="out-in">
        <div v-if="isOpen" class="sidebar pb-6 box-shadow mr-4 md:w-48 xl:w-1/6">
            <div class="flex px-8 py-4">
                <div class="relative flex-1">
                    <div class="absolute mt--4 min-w-48 bg-white" :class="{ 'rounded shadow': menuIsOpen }">
                        <div @click="menuIsOpen = !menuIsOpen" class="p-4 cursor-pointer">
                            <svg class="w-5 mr-2 align-text-bottom fill-current text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 20a10 10 0 1 1 0-20 10 10 0 0 1 0 20zM7 6v2a3 3 0 1 0 6 0V6a3 3 0 1 0-6 0zm-3.65 8.44a8 8 0 0 0 13.3 0 15.94 15.94 0 0 0-13.3 0z"/></svg>
                            <span class="text-lg text-gray-700">{{ authUser.name }}</span>
                            <svg-vue class="w-2.5 ml-2 text-gray-700 cursor-pointer fill-current inline-block" icon="arrow_down"/>
                        </div>
                        <transition name="fade">
                            <div v-if="menuIsOpen" @click="menuIsOpen = false" class="pb-1">
                                <hr class="m-0 mb-1 border-t border-gray-200">
                                <!-- <router-link class="block py-1 px-4 font-medium text-gray-700">
                                    Settings
                                </router-link> -->
                                <router-link to="/users/me" class="menuItem">
                                    My Account
                                </router-link>
                                <!-- <hr class="m-0 mt-1 border-t border-gray-200"> -->
                                <a href="/" @click="logout()" class="menuItem py-2">
                                    Logout
                                </a>
                            </div>
                        </transition>
                    </div>
                </div>
                <svg @click="toggle()" class="menu-icon text-gray-600 cursor-pointer fill-current inline-block"
                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="none" d="M0 0h24v24H0V0z"></path><path d="M3 18h13v-2H3v2zm0-5h10v-2H3v2zm0-7v2h13V6H3zm18 9.59L17.42 12 21 8.41 19.59 7l-5 5 5 5L21 15.59z"></path>
                </svg>
            </div>
            <hr class="block w-full m-0 border border-bottom border-orange-600">
            <div>
                <br><br><br>
                <div class="list">
                    <router-link to="/" tag="li" class="collection mb-4">
                        <svg-vue class="w-4 fill-current mr-2" icon="zondicons/folder-outline-unknown"/>
                        <span class="align-middle text-gray-700">Uncategorized</span>
                    </router-link>
                    <span class="mb-2 px-8 block text-xs text-gray-700 font-medium uppercase">Collections</span>
                    <ol>
                        <router-link v-for="(collection) in collections" :key="collection.id"
                            :to="'/c/' + collection.id" tag="li" class="collection">
                                <svg-vue class="w-4 fill-current mr-2" icon="zondicons/folder"/>
                                <span class="align-middle text-gray-700">{{ collection.name }}</span>
                        </router-link>
                    </ol>
                </div>
                <router-link to="/c/create" class="block ml-8 mt-4 text-orange-600 font-medium">
                    <svg-vue class="w-4 mr-2 fill-current" icon="zondicons/add-solid"/>
                    <span class="align-middle">Create a new collection</span>
                </router-link>
            </div>
            <br><br><br><br><br>
            <div class="px-8 absolute bottom-0">
                <span class="flex-1 text-orange-600 text-xl font-medium">Benote</span>
            </div>
        </div>
        <div v-else class="sidebar pb-6 box-shadow mr-4 w-12 text-center py-4">
            <svg @click="toggle()" class="menu-icon text-gray-500 cursor-pointer fill-current inline-block"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="none" d="M0 0h24v24H0V0z"></path><path d="M3 18h13v-2H3v2zm0-5h10v-2H3v2zm0-7v2h13V6H3zm18 9.59L17.42 12 21 8.41 19.59 7l-5 5 5 5L21 15.59z"></path>
            </svg>
        </div>
    </transition>
</template>

<script>
import { mapState } from 'vuex'
import axios from 'axios'
export default {
    name: 'Sidebar',
    data () {
        return {
            isOpen: (localStorage.getItem('sidebar') === 'false') ? false : true,
            menuIsOpen: false
        }
    },
    methods: {
        init () {
            this.$store.dispatch('collection/fetchCollections')
        },
        toggle () {
            localStorage.setItem('sidebar', !this.isOpen)
            this.isOpen = !this.isOpen
        },
        logout () {
            axios.post('/api/auth/logout')
                .catch(error => {
                    console.log(error.response)
                })
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
    .mt--4 {
        margin-top: -1rem;
    }
    .w-2\.5 {
        width: 0.625rem;
    }
    .min-w-48 {
        min-width: 12rem;
    }
    .py-1\.5 {
        padding-top: 0.375rem;
        padding-bottom: 0.375rem;
    }
    .sidebar {
        box-shadow: 2px 3px 3px 0 rgba(0, 0, 0, 0.1);
        transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s;
        -webkit-transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s;
        -moz-transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s;
        .slide-enter-active, .slide-leave-active {
            transition: width 1s;
        }
        .slide-enter, .slide-leave-to {
            width: 0;
        }
        .fade-enter-active, .fade-leave-active {
            transition: opacity .2s;
        }
        .fade-enter, .fade-leave-to {
            opacity: 0;
        }
        svg {
            display: inline-block;
        }
        .menu-icon {
            width: 1.75rem;
        }
        .menuItem {
            @apply block py-1.5 px-4 font-medium text-gray-700;
            transition: 0.2s background-color;
        }
        .menuItem:hover {
            @apply bg-gray-300;
        }
        .list {
            .collection {
                @apply inline-block w-full px-8 py-1;
                @apply font-semibold text-gray-500 text-lg cursor-pointer;
            }
            .router-link-exact-active {
                @apply bg-orange-200 text-gray-700;
            }
        }
    }
</style>