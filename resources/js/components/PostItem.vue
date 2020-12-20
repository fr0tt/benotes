<template>
    <li class="md:inline-block mx-6 md:mx-4 my-4 relative text-left post" :post-id="post.id">
        <div v-if="post.type === 'link' && !isActive()" class="card">
            <a :href="post.url" target="_blank" class="w-3/7 md:w-full">
                <div v-if="post.image_path" class="h-cover w-full bg-cover bg-center" :style="image"></div>
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
                    <a :href="post.url" :title="post.url" target="_blank" class="text-blue-600">{{ post.url }}</a>
                </div>
                <svg @click="showContextMenu($event)" v-if="permission > 4" class="more-svg" 
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path d="M10 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0-6a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/>
                </svg>
            </div>
        </div>
        <div v-else class="card bg-gray-100" :class="{ 'active' : isActive() }">
            <router-link v-if="isMobile" :to="'/p/' + post.id" class="block w-full">
                <div class="p-6 h-full">
                    <div class="text-gray-900 text-xl outline-none h-full w-full">
                        <p v-if="post.title" class="text-orange-600 text-xl bg-transparent font-semibold">
                            {{ post.title }}
                        </p>
                        <div class="editorContent" v-html="post.content"></div>
                    </div>
                </div>
            </router-link>
            <div v-else class="p-6 h-full">
                <div @click="edit($event)" class="text-gray-900 text-xl outline-none h-full w-full"
                    :class="{ 'overflow-hidden cursor-pointer' : !isActive() }">
                    <input v-if="post.title" :value="post.title" @input="updateTitle"
                        class="text-orange-600 text-xl bg-transparent font-semibold"/>
                    <EditorContent :editor="editor" class="editorContent" />
                </div>
            </div>
            <svg @click="showContextMenu($event)" v-if="permission > 4" class="more-svg" 
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path d="M10 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0-6a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/>
            </svg>
        </div>
        <ContextMenu :postId="post.id"/>
    </li>
</template>
<script>
import { mapState } from 'vuex'
import { Editor, EditorContent } from 'tiptap'
import { HardBreak, Heading, Bold, Code, Italic, Link } from 'tiptap-extensions'
import ContextMenu from './PostContextMenu.vue'
export default {
    name: 'PostItem',
    props: ['post', 'permission'],
    components: {
        ContextMenu,
        EditorContent
    },
    data () {
        return {
            editor: new Editor({
                editable: false,
                extensions: [
                    new HardBreak(),
                    new Heading({ levels: [1, 2, 3] }),
                    new Link(),
                    new Bold(),
                    new Code(),
                    new Italic()
                ],
                content: this.post.content,
                onUpdate: ({ getHTML }) => {
                    this.$store.dispatch('post/setCurrentPostContent', getHTML())
                }
            })
        }
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
        stopEditing (event) {
            const currentPostTarget = document.querySelector(`[post-id="${this.currentPost.id}"] .ProseMirror`)
            if (currentPostTarget !== event.target.parentElement) {
                this.editor.setOptions({
                    editable: false
                })
                document.querySelector('#app').removeEventListener('click', this.stopEditing, true)
                this.$store.dispatch('post/updatePost', { post: this.currentPost })
                this.$store.dispatch('post/setCurrentPost', null)
            }
        },
        updateTitle (event) {
            this.$store.dispatch('post/setCurrentPostTitle', event.target.value)
        },
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
        image () {
            return 'background-image: url(\'' + this.post.image_path + '\')'
        },
        color () {
            return 'background-color: ' + this.post.color
        },
        domain () {
            return this.post.base_url.replace(/(https|http):\/\//, '')
        },
        ...mapState('post', [
            'currentPost'
        ]),
        ...mapState('post', [
            'contextMenu'
        ]),
        ...mapState([
            'isMobile'
        ])
    },
    watch: {
        currentPost (value) {
            if (value === null)
                return
            if (value.id !== this.post.id)
                return
            this.editor.setOptions({
                editable: true
            })
        } 
    },
    beforeDestroy () {
        this.editor.destroy()
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
            // line-height: 1.45;
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
    .post .ProseMirror {
        h1 {
            @apply text-3xl font-bold
        }
        h2 {
            @apply text-2xl font-medium
        }
        h3 {
            @apply text-xl font-medium
        }
    }
</style>