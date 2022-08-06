<template>
    <div class="card">
        <a :href="post.url" target="_blank" rel="noopener" class="w-3/7 md:w-full">
            <div
                v-if="post.image_path"
                :key="post.image_path"
                v-lazy:background-image="post.image_path"
                class="h-cover w-full bg-cover bg-center" />
            <div v-else class="h-cover w-full flex items-center justify-center" :style="color">
                <span class="text-white text-2xl font-medium">{{ domain }}</span>
            </div>
        </a>
        <div class="w-4/7 md:w-full">
            <div class="px-6 pt-4 cursor-pointer">
                <div class="font-bold text-xl mb-2 truncate" :title="post.title">
                    {{ post.title }}
                </div>
                <p
                    v-if="post.description !== null"
                    class="text-gray-700 text-base overflow-hidden description">
                    {{ post.description }}
                </p>
                <p v-else class="text-gray-700 text-base italic overflow-hidden description">
                    No description
                </p>
            </div>
            <PostItemTags
                :tags="post.tags"
                class="px-6 py-1"
                :class="post.tags && post.tags.length > 0 ? '' : 'py-4'" />
            <div class="px-6 pt-1 mr-2 truncate">
                <img
                    :src="'https://external-content.duckduckgo.com/ip3/' + domain + '.ico'"
                    class="w-4 inline img-vertical-align" />
                <a
                    :href="post.url"
                    :title="post.url"
                    target="_blank"
                    rel="noopener"
                    class="text-blue-600">
                    {{ post.url }}
                </a>
            </div>
            <svg
                v-if="permission > 4"
                class="more-icon"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                @click="showContextMenu($event)">
                <path
                    d="M10 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0-6a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4z" />
            </svg>
            <button
                v-else-if="restore"
                :title="'Restore into ' + collectionName"
                @click="restorePost()">
                <svg-vue class="restore-icon" icon="remix/inbox-unarchive-line" />
            </button>
        </div>
    </div>
</template>

<script>
import { restorePost } from './../api/post.js'
import { getCollectionName } from './../api/collection.js'
import PostItemTags from './PostItemTags.vue'
export default {
    components: {
        PostItemTags,
    },
    props: ['post', 'showContextMenu', 'permission', 'restore'],
    computed: {
        color() {
            if (this.post.color) {
                return 'background-color: ' + this.post.color
            }
            return 'background-color: ' + '#ffb27f'
        },
        domain() {
            return this.post.base_url.replace(/(https|http):\/\//, '')
        },
        collectionName() {
            return getCollectionName(this.post.collection_id)
        },
    },
    methods: {
        restorePost() {
            restorePost(this.post)
        },
    },
}
</script>
