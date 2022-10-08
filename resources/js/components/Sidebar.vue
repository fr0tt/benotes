<template>
    <transition name="sidebar-slide">
        <div v-if="showSidebar" class="sidebar md:w-48 lg:w-64 xl:w-1/6">
            <div class="list pt-2">
                <div class="mb-12">
                    <router-link to="/search" tag="li" class="collection">
                        <svg-vue class="w-4 fill-current mr-2" icon="remix/search-line" />
                        <span class="align-middle text-gray-700">Search</span>
                    </router-link>
                    <router-link to="/tags" tag="li" class="collection">
                        <svg-vue class="w-5 fill-current mr-1" icon="material/label" />
                        <span class="align-middle text-gray-700">Tags</span>
                    </router-link>
                    <router-link
                        to="/restore"
                        class="collection"
                        :class="{ 'router-link-exact-active': isActiveLink('/restore') }">
                        <svg-vue
                            icon="zondicons/trash"
                            class="w-4 fill-current align-text-bottom mr-2" />
                        <span class="align-middle text-gray-700">Recycle Bin</span>
                    </router-link>
                    <router-link to="/users" tag="li" class="collection">
                        <svg-vue class="w-4 fill-current mr-2" icon="remix/group-fill" />
                        <span class="align-middle text-gray-700">Users</span>
                    </router-link>
                    <router-link :to="'/users/' + authUser.id" tag="li" class="collection">
                        <svg-vue class="w-4 fill-current mr-2" icon="remix/user-settings-fill" />
                        <span class="align-middle text-gray-700">My Account</span>
                    </router-link>
                    <a class="collection" @click="logout()">
                        <svg-vue class="w-4 fill-current mr-2" icon="remix/logout-circle-line" />
                        <span class="align-middle text-gray-700">Logout</span>
                    </a>
                </div>
                <router-link
                    to="/"
                    class="collection mb-4"
                    :class="{ 'router-link-exact-active': isActiveLink('/') }">
                    <svg-vue
                        icon="remix/folder-unknow-fill"
                        class="w-4 fill-current align-text-bottom mr-2" />
                    <span class="align-middle text-gray-700">Uncategorized</span>
                </router-link>
                <span class="mb-2 md:px-8 px-4 block text-xs text-gray-700 font-medium uppercase">
                    Collections
                </span>
                <ol>
                    <router-link
                        v-for="collection in collections"
                        :key="collection.id"
                        :class="{
                            'router-link-exact-active': isActiveLink('/c/' + collection.id),
                        }"
                        class="collection"
                        :to="'/c/' + collection.id">
                        <svg-vue
                            v-if="collectionIconIsInline(collection.icon_id)"
                            :icon="'glyphs/' + collection.icon_id"
                            class="w-6 h-6 glyphs" />
                        <svg v-else-if="collection.icon_id" class="w-6 h-6 glyphs">
                            <use :xlink:href="'/glyphs.svg#' + collection.icon_id" />
                        </svg>
                        <svg-vue
                            v-else
                            icon="remix/folder-fill"
                            class="w-4 fill-current align-text-bottom mr-2" />
                        <span class="align-middle text-gray-700">{{ collection.name }}</span>
                    </router-link>
                </ol>
            </div>
            <router-link to="/c/create" class="block md:mx-8 mx-4 mt-4 text-orange-600 font-medium">
                <svg-vue
                    class="w-4 mr-2 fill-current align-text-bottom"
                    icon="remix/folder-add-fill" />
                <span class="align-middle">Create a new collection</span>
            </router-link>
            <div class="w-full px-4 md:px-6 mb-0 mt-auto pt-12">
                <svg-vue class="w-6 align-text-bottom" icon="logo_64x64" />
                <span class="flex-1 ml-1 text-orange-600 text-xl font-medium">Benotes</span>
            </div>
        </div>
    </transition>
</template>

<script>
import { mapState } from 'vuex'
import axios from 'axios'
import { collectionIconIsInline } from './../api/collection'
export default {
    name: 'Sidebar',
    data() {
        return {
            menuIsOpen: false,
        }
    },
    computed: {
        ...mapState(['showSidebar']),
        ...mapState('auth', ['authUser']),
        ...mapState('collection', ['collections']),
        ...mapState(['isMobile']),
    },
    mounted() {
        this.init()
    },
    methods: {
        init() {
            this.$store.dispatch('collection/fetchCollections').then(() => {
                if (this.$route.meta.isHome) {
                    this.$store.dispatch('collection/getCurrentCollection', 0)
                } else if (this.$route.params.collectionId !== null) {
                    this.$store.dispatch(
                        'collection/getCurrentCollection',
                        this.$route.params.collectionId
                    )
                }
            })
        },
        logout() {
            axios.post('/api/auth/logout').catch((error) => {
                console.log(error.response)
            })
            this.$cookie.delete('token')
            this.$router.push({ path: '/login' })
        },
        isActiveLink(route) {
            return route === this.$route.path
        },
        collectionIconIsInline,
    },
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
.sidebar-slide-enter-active {
    transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s, opacity 0.5s;
    -webkit-transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s, opacity 0.5s;
    -moz-transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s, opacity 0.5s;
}

.sidebar-slide-leave-active {
    // transition: width, opacity 0.8s;
    transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s;
    -webkit-transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s;
    -moz-transition: width cubic-bezier(0.4, 0, 0.2, 1) 0.35s;
}
.sidebar-slide-enter,
.sidebar-slide-leave-to {
    width: 0;
    opacity: 0;
}
.sidebar {
    @apply h-full fixed flex flex-col  pb-6 pt-20 overflow-y-auto z-40 bg-white;
    font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu,
        Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    box-shadow: 2px 3px 3px 0 rgba(0, 0, 0, 0.1);
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
            @apply px-4;
        }
        @screen sm {
            .collection {
                @apply px-8;
            }
        }
        .router-link-exact-active {
            border-left: 3px solid;
            @apply bg-orange-200 text-gray-700 border-orange-600 font-semibold;
            svg {
                margin-left: -3px;
            }
            .glyphs {
                margin-left: calc(-0.25rem + -3px);
            }
        }
        .glyphs {
            margin-left: -0.25rem;
            margin-right: 0.25rem;
        }
    }
}
.sidebar::-webkit-scrollbar {
    width: 3px;
    background-color: #f5f5f5;
}
.sidebar::-webkit-scrollbar-track {
    -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
    background-color: rgba(255, 255, 255, 0.8);
}
.sidebar::-webkit-scrollbar-thumb {
    background-color: #86827d;
    border-radius: 8px;
}
</style>
