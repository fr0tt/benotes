<template>
    <transition name="sidebar-slide" mode="out-in">
        <div v-if="showSidebar" class="sidebar w-full relative md:w-48 xl:w-1/6 pb-6 bg-white">
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
                    <a @click="logout()" class="collection md:px-8 px-4">
                        <svg-vue class="w-4 fill-current mr-2" icon="remix/logout-circle-line"/>
                        <span class="align-middle text-gray-700">Logout</span>
                    </a>
                    <br><br><br>
                    <router-link to="/" class="collection md:px-8 px-4 mb-4"
                        :class="{ 'router-link-exact-active': isActiveLink('/') }">
                        <svg-vue class="w-4 fill-current align-text-bottom mr-2" icon="remix/folder-unknow-fill"/>
                        <span class="align-middle text-gray-700">Uncategorized</span>
                    </router-link>
                    <span class="mb-2 md:px-8 px-4 block text-xs text-gray-700 font-medium uppercase">Collections</span>
                    <ol>
                        <router-link v-for="(collection) in collections" :key="collection.id"
                            :class="{ 'router-link-exact-active': isActiveLink('/c/' + collection.id) }"
                            class="collection md:px-8 px-4"
                            :to="'/c/' + collection.id">
                            <svg-vue class="w-4 fill-current align-text-bottom mr-2" icon="remix/folder-fill"/>
                            <span class="align-middle text-gray-700">{{ collection.name }}</span>
                        </router-link>
                    </ol>
                </div>
                <router-link to="/c/create" class="block md:ml-8 ml-4 mt-4 text-orange-600 font-medium">
                    <svg-vue class="w-4 mr-2 fill-current align-text-bottom" icon="remix/folder-add-fill"/>
                    <span class="align-middle">Create a new collection</span>
                </router-link>
            </div>
            <br><br><br><br><br>
            <div class="w-full px-4 md:px-6 absolute bottom-4">
                <svg-vue class="w-6 align-text-bottom" icon="logo_64x64"/>
                <span class="flex-1 ml-1 text-orange-600 text-xl font-medium">Benotes</span>
            </div>
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
            menuIsOpen: false
        }
    },
    methods: {
        init () {
            this.$store.dispatch('collection/fetchCollections').then(() => {
                if (this.$route.meta.isHome) {
                    this.$store.dispatch('collection/getCurrentCollection', 0)
                } else if (this.$route.params.collectionId !== null) {
                    this.$store.dispatch('collection/getCurrentCollection', 
                        this.$route.params.collectionId)
                }
            })
        },
        logout () {
            axios.post('/api/auth/logout')
                .catch(error => {
                    console.log(error.response)
                })
            this.$router.push({ path: '/login' })
        },
        isActiveLink (route) {
            return route === this.$route.path
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
        ]),
        ...mapState([
            'isMobile'
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
                border-left: 3px solid;
                @apply bg-orange-200 text-gray-700 border-orange-600 font-semibold;
                svg {
                    margin-left: -3px;
                }
            }
        }
    }
</style>