<template>
    <li class="inline-block m-4 relative" :post-id="post.id">
        <div v-if="post.type === 'link' && !isActive()" class="card">
            <a :href="post.url" target="_blank">
                <div v-if="post.image_path" class="h-cover w-full bg-cover bg-center" :style="image"></div>
                <div v-else class="h-cover w-full flex items-center justify-center" :style="color">
                    <span class="text-white text-2xl font-medium">{{ domain }}</span>
                </div>
            </a>
            <div class="px-6 pt-4">
                <div class="font-bold text-xl mb-2 truncate" :title="post.title">
                    {{ post.title }}
                </div>
                <p v-if="post.description !== null" class="text-gray-700 text-base overflow-hidden description">
                    {{ post.description }}
                </p>
                <p v-else class="text-gray-700 text-base italic overflow-hidden description">
                    No description
                </p>
            </div>
            <div class="px-6 py-4 mr-2 truncate">
                <img :src="'https://www.google.com/s2/u/0/favicons?domain=' + domain" class="inline img-vertical-align">
                <a :href="post.url" :title="post.url" target="_blank" class="text-blue-600">{{ post.url }}</a>
            </div>
            <svg @click="showContextMenu($event)" class="three-dots-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0-6a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/></svg>
        </div>
        <div v-else class="card bg-gray-100" :class="{ 'active' : isActive() }">
            <div class="p-6 h-full">
                <textarea :value="post.content" @click="edit($event)" @input="watchEdit($event)" 
                    :readonly="!isActive()" :autofocus="isActive()"
                    class="text-gray-900 text-xl overflow-hidden outline-none h-full w-full">
                </textarea>
            </div>
            <svg @click="showContextMenu($event)" class="three-dots-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0-6a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/></svg>
        </div>
        <ContextMenu :postId="post.id"/>
    </li>
</template>
<script>
import { mapState } from 'vuex'
import ContextMenu from './ContextMenu.vue'
export default {
    name: 'Post',
    props: ['post'],
    components: {
        ContextMenu
    },
    methods: {
        edit (event) {
            if (this.currentPost !== null) {
                if (this.currentPost.id === this.post.id) {
                    return
                }
            }
            this.$store.dispatch('post/setCurrentPost', this.post)
            document.querySelector('#app').addEventListener('click', this.stopEditing, true)
        },
        isActive () {
            if (this.currentPost === null) {
                return false
            }
            return this.currentPost.id === this.post.id
        },
        watchEdit (event) {
            this.$store.dispatch('post/setCurrentPostContent', event.target.value)
        },
        stopEditing (event) {
            const currentPostTarget = document.querySelector(`[post-id="${this.currentPost.id}"] textarea`)
            if (currentPostTarget !== event.target) {
                document.querySelector('#app').removeEventListener('click', this.stopEditing, true)
                this.$store.dispatch('post/updatePost', this.currentPost)
                this.$store.dispatch('post/setCurrentPost', null)
            }
        },
        showContextMenu (event) {
            this.$store.dispatch('post/setContextMenu', {
                post: this.post,
                target: event.target,
                positionX: event.pageX
            })
        }
    },
    computed: {
        image () {
            return 'background-image: url(\'' + this.post.image_path + '\')'
        },
        color () {
            return 'background-color: ' + this.post.color
        },
        domain () {
            return this.post.base_url.replace(/(https|http):\/\//,'')
        },
        ...mapState('post', [
            'currentPost'
        ]),
        ...mapState('post', [
            'contextMenu'
        ])
    }
}
</script>
<style lang="scss" scoped>
    .card {
        @apply rounded relative overflow-hidden shadow-lg;
        width: 22rem;
        height: 22.5rem;
        transition: background-color 0.2s;
        -webkit-transition: background-color 0.2s;
        -moz-transition: background-color 0.2s;
    }
    .h-cover {
        height: 12.5rem;
    }
    .description {
        height: 3rem;
    }
    .img-vertical-align {
        margin-top: -4px;
    }
    .three-dots-svg {
        @apply w-5 h-5 absolute cursor-pointer;
        /* right: 0.5rem;
        top: 0.75rem; */
        right: 0.75rem;
        bottom: 1.25rem;
    }
    .active {
        @apply bg-yellow-300 border border-yellow-400;
    }
    textarea {
        resize: none;
        background: transparent;
    }
</style>