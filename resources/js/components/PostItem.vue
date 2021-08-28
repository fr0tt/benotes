<template>
    <li class="md:inline-block mx-6 md:mx-4 my-4 relative text-left post" :post-id="post.id">
        <div v-if="post.type === 'link'" class="card">
            <a :href="post.url" target="_blank" rel="noopener" class="w-3/7 md:w-full">
                <div v-if="post.image_path" class="h-cover w-full bg-cover bg-center"
                    v-lazy:background-image="this.post.image_path" 
                    :key="this.post.image_path">
                </div>
                <div v-else class="h-cover w-full flex items-center justify-center" :style="color">
                    <span class="text-white text-2xl font-medium">{{ domain }}</span>
                </div>
            </a>
            <div class="w-4/7 md:w-full">
                <div class="px-6 pt-4 cursor-pointer">
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
                    <img :src="'https://external-content.duckduckgo.com/ip3/' + domain + '.ico'"
                        class="w-4 inline img-vertical-align">
                    <a :href="post.url" :title="post.url" target="_blank" rel="noopener" class="text-blue-600">
                        {{ post.url }}
                    </a>
                </div>
                <svg @click="showContextMenu($event)" v-if="permission > 4" class="more-svg" 
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path d="M10 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0-6a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/>
                </svg>
            </div>
        </div>
        <PostItemText v-else-if="post.type === 'text'" 
            :post="post"
            :showContextMenu="showContextMenu"
            :permission="permission"/> 
        <PostItemPlaceholder v-else/>
        <div v-if="debug" class="absolute bottom-0 w-full">
            <span class="px-1 bg-orange-200">id:{{ post.id }}</span>
            <span class="px-1 bg-orange-200 float-right">o:{{ post.order }}</span>
        </div>
        <ContextMenu :postId="post.id"/>
    </li>
</template>
<script>
import { mapState } from 'vuex'
import ContextMenu from './PostContextMenu.vue'
import PostItemText from './PostItemText.vue'
import PostItemPlaceholder from './PostItemPlaceholder.vue'
export default {
    name: 'PostItem',
    props: ['post', 'permission'],
    components: {
        ContextMenu,
        PostItemText,
        PostItemPlaceholder
    },
    data () {
        return {
            debug: false
        }
    },
    methods: {
        showContextMenu (event) {
            this.$store.dispatch('post/setContextMenu', {
                post: this.post,
                target: event.target,
                positionX: event.pageX
            })
            document.addEventListener('click', this.globalClickEvent)
        },
        globalClickEvent (event) {
            if (this.contextMenu.post !== null) {
                if (this.contextMenu.target !== event.target) {
                    this.$store.dispatch('post/hideContextMenu')
                    document.removeEventListener('click', this.globalClickEvent)
                }
            }
        }
    },
    computed: {
        color () {
            if (this.post.color) {
                return 'background-color: ' + this.post.color
            }
            return 'background-color: ' + '#ffb27f'
        },
        domain () {
            return this.post.base_url.replace(/(https|http):\/\//, '')
        },
        ...mapState('post', [
            'contextMenu'
        ])
    }
}
</script>
<style lang="scss">
    .card {
        @apply flex relative overflow-hidden shadow-lg;
        @apply w-full;
        height: 10.5rem;
        font-family: Inter, Noto Sans, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        transition: background-color 0.3s;
        -webkit-transition: background-color 0.3s;
        -moz-transition: background-color 0.3s;
        .h-cover {
            height: 10.5rem;
        }
        .description {
            height: 3rem;
        }
        .img-vertical-align {
            margin-top: -4px;
        }
        .more-svg {
            @apply w-5 h-5 absolute cursor-pointer;
            right: 0.75rem;
            bottom: 1.25rem;
        }
        .editorContent {
            font-size: 1.1rem;
            line-height: 1.45;
            .unfurling-link {
                span {
                    @apply text-base;
                }
                img {
                    @apply w-8 h-8;
                }
                .ul-link {
                    @apply hidden;
                }
            }
        }
    }
    @screen md {
        .card {
            @apply block;
            width: 20.5rem;
            height: 20.5rem;
        }
    }
    .card::-webkit-scrollbar {
        width: 2px;
        background-color: #F5F5F5;
    }
    .card::-webkit-scrollbar-track {
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
        background-color: rgba(255,255,255,0.8);
    }
    .card::-webkit-scrollbar-thumb {
        @apply bg-orange-500;
        border-radius: 8px;
    }
    .card.active {
        @apply bg-orange-100 rounded border border-orange-300 overflow-auto;
    }
    .ProseMirror {
        outline: none;
    }
</style>