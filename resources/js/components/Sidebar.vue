<template>
    <transition name="sidebar-slide">
        <div
            v-if="showSidebar"
            class="sidebar w-full md:w-48 lg:w-64 xl:w-1/6 theme__sidebar">
            <div class="list pt-2">
                <ol class="mb-12">
                    <li>
                        <router-link
                            to="/search"
                            class="collection theme__sidebar__collection">
                            <svg-vue
                                class="w-4 fill-current mr-2"
                                icon="remix/search-line" />
                            <span class="align-middle text-gray-700 theme__sidebar__label"
                                >Search</span
                            >
                        </router-link>
                    </li>
                    <li>
                        <router-link
                            to="/tags"
                            class="collection theme__sidebar__collection">
                            <svg-vue
                                class="w-5 fill-current mr-1"
                                icon="material/label" />
                            <span class="align-middle text-gray-700 theme__sidebar__label"
                                >Tags</span
                            >
                        </router-link>
                    </li>
                    <li>
                        <router-link
                            to="/import"
                            class="collection theme__sidebar__collection">
                            <svg-vue
                                class="w-5 fill-current -ml-0.5 mr-1.5"
                                icon="remix/git-repository-commits-line" />
                            <span class="align-middle text-gray-700 theme__sidebar__label"
                                >Import & Export</span
                            >
                        </router-link>
                    </li>
                    <li>
                        <router-link
                            to="/restore"
                            class="collection theme__sidebar__collection"
                            :class="{
                                'router-link-exact-active': isActiveLink('/restore'),
                            }">
                            <svg-vue
                                icon="zondicons/trash"
                                class="w-4 fill-current align-text-bottom mr-2" />
                            <span class="align-middle text-gray-700 theme__sidebar__label"
                                >Recycle Bin</span
                            >
                        </router-link>
                    </li>
                    <li>
                        <router-link
                            to="/users"
                            class="collection theme__sidebar__collection">
                            <svg-vue
                                class="w-4 fill-current mr-2"
                                icon="remix/group-fill" />
                            <span class="align-middle text-gray-700 theme__sidebar__label"
                                >Users</span
                            >
                        </router-link>
                    </li>
                    <li>
                        <router-link
                            :to="'/users/' + authUser.id"
                            class="collection theme__sidebar__collection">
                            <svg-vue
                                class="w-4 fill-current mr-2"
                                icon="remix/user-settings-fill" />
                            <span class="align-middle text-gray-700 theme__sidebar__label"
                                >My Account</span
                            >
                        </router-link>
                    </li>
                    <li class="collection theme__sidebar__collection">
                        <a @click="logout()">
                            <svg-vue
                                class="w-4 fill-current mr-2"
                                icon="remix/logout-circle-line" />
                            <span class="align-middle text-gray-700 theme__sidebar__label"
                                >Logout</span
                            >
                        </a>
                    </li>
                </ol>
                <router-link
                    to="/"
                    class="collection mb-4 theme__sidebar__collection"
                    :class="{ 'router-link-exact-active': isActiveLink('/') }">
                    <svg-vue
                        icon="remix/folder-unknow-fill"
                        class="w-4 fill-current align-text-bottom mr-2" />
                    <span class="align-middle text-gray-700 theme__sidebar__label"
                        >Uncategorized</span
                    >
                </router-link>
                <span
                    v-if="
                        sharedCollections != null &&
                        Object.entries(sharedCollections).length > 0
                    "
                    class="mb-2 md:px-8 px-4 block text-xs text-gray-700 font-medium uppercase theme__sidebar__subhead">
                    Shared Collections
                    <svg-vue
                        class="w-4 ml-2 fill-current text-blue-600 align-text-bottom"
                        icon="remix/share-line" />
                </span>
                <ol class="mb-4">
                    <CollectionSidebarWrapper v-model="sharedCollections" />
                </ol>

                <span
                    class="mb-2 md:px-8 px-4 block text-xs text-gray-700 font-medium uppercase theme__sidebar__subhead">
                    Collections
                </span>

                <CollectionSidebarWrapper v-model="collections" />
            </div>
            <router-link
                to="/c/create"
                class="block md:mx-8 mx-4 mt-4 text-orange-600 font-medium">
                <svg-vue
                    class="w-4 mr-2 fill-current align-text-bottom"
                    icon="remix/folder-add-fill" />
                <span class="align-middle">Create a new collection</span>
            </router-link>
            <div class="w-full px-4 md:px-6 mb-0 mt-auto pt-12">
                <svg-vue class="w-6 align-text-bottom" icon="logo_64x64" />
                <span class="flex-1 ml-1 text-orange-600 text-xl font-medium"
                    >Benotes</span
                >
            </div>
        </div>
    </transition>
</template>

<script>
import { mapState } from 'vuex'
import axios from 'axios'
import { collectionIconIsInline } from './../api/collection'
import CollectionSidebarWrapper from './CollectionSidebarWrapper.vue'
export default {
    name: 'Sidebar',
    components: {
        CollectionSidebarWrapper,
    },
    computed: {
        ...mapState(['showSidebar']),
        ...mapState('auth', ['authUser']),
        ...mapState('collection', ['sharedCollections']),
        ...mapState(['isMobile']),
        collections: {
            get() {
                return JSON.parse(
                    JSON.stringify(this.$store.state.collection.collections)
                )
            },
            set(value) {
                this.$store.commit('collection/setCollections', value)
            },
        },
    },
    mounted() {
        this.init()
    },
    methods: {
        init() {
            this.$store
                .dispatch('collection/fetchCollections', { nested: true })
                .then(() => {
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
            // also resets vuex
            this.$router.go('/login')
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
    @apply h-full fixed flex flex-col pb-6 pt-20 overflow-y-auto z-40 bg-white;
    font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen,
        Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
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
        .collections {
            margin-left: -0.25rem;
        }
        .collection {
            @apply flex w-full py-1 relative;
            @apply font-medium text-gray-500;
            @apply pl-4;
            border-left: 3px solid;
            border-color: transparent;

            a {
                @apply cursor-pointer;
            }
        }

        @screen sm {
            .collection {
                @apply pl-8;
            }
        }

        .nested .collection {
            @apply pl-4;
        }

        svg.glyphs {
            margin-right: 0.25rem;
        }

        svg.no-glyph {
            margin-left: 0.25rem;
        }
    }

    // outside of .list in order to be able to be overriden
    .router-link-exact-active-parent,
    .collection.router-link-exact-active {
        @apply bg-orange-200 text-gray-700 border-orange-600 font-semibold;
    }
}

.sidebar::-webkit-scrollbar {
    width: 3px;
    height: 3px;
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

.sidebar .collection .fade-enter-active,
.sidebar .collection.fade-leave-active {
    transition: opacity 0.1s;
}
</style>
