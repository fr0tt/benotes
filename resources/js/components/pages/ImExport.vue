<template>
    <div class="min-h-full import">
        <div class="sm:ml-8 px-2 md:px-8 pb-12">
            <div class="py-4 md:pt-16 mb-6">
                <h2 class="text-lg mb-6 font-medium text-gray-700">Bookmarks</h2>
                <router-link to="/import/bookmarks" class="import-card">
                    <div class="pr-4">
                        <span class="icon-wrapper">
                            <svg-vue
                                icon="remix/git-repository-commits-line"
                                class="icon" />
                        </span>
                    </div>
                    <div class="flex-1 relative">
                        <span
                            class="absolute right-0 top-0 px-1 text-xs font-medium text-orange-600 border rounded border-orange-600">
                            Beta
                        </span>
                        <span class="title">Import Bookmarks</span>
                        <p class="leading-5 text-gray-800">
                            Import bookmarks from your favorite browser as .html file
                        </p>
                    </div>
                </router-link>
                <router-link to="/export/bookmarks" class="import-card">
                    <div class="pr-4">
                        <span class="icon-wrapper">
                            <svg-vue
                                icon="remix/git-repository-commits-line"
                                class="icon" />
                        </span>
                    </div>
                    <div class="flex-1 relative">
                        <span
                            class="absolute right-0 top-0 px-1 text-xs font-medium text-orange-600 border rounded border-orange-600">
                            Beta
                        </span>
                        <span class="title">Export Bookmarks</span>
                        <p class="leading-5 text-gray-800">
                            Export bookmarks as .zip in order for file you to use them
                            somewhere else
                        </p>
                    </div>
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
    computed: {
        ...mapState('post', ['isLoading']),
    },
    created() {
        this.$store.commit('post/setPosts', [])
        this.$store.dispatch('appbar/setAppbar', {
            title: 'Import & Export',
            options: null,
            button: null,
        })
    },
    methods: {
        goToCreatePost() {
            this.$router.push(`/c/0/p/create`)
        },
    },
}
</script>
<style lang="scss">
.import {
    .import-card {
        @apply flex px-4 py-4 mb-10 max-w-sm;
        box-shadow: 0px 3px 10px -2px rgba(0, 0, 0, 0.2);

        .title {
            @apply block text-lg font-medium mb-1;
        }

        .icon-wrapper {
            @apply inline-block my-1 bg-orange-600 text-white rounded;
        }

        .icon {
            @apply m-1 fill-current;
        }
    }

    .import-card:hover {
        @apply bg-gray-100;
    }
}
</style>
