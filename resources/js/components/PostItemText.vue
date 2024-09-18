<template>
    <div class="card theme__post_item bg-gray-100" :class="{ active: isActive }">
        <router-link :to="'/p/' + post.id" class="block w-full">
            <div class="p-6 h-full">
                <div class="text-gray-900 text-xl outline-none h-full w-full">
                    <p
                        v-if="post.title"
                        class="text-orange-600 text-xl bg-transparent font-semibold">
                        {{ post.title }}
                    </p>
                    <PostItemTags :tags="post.tags" />
                    <EditorContent :editor="editor" class="editorContent" />
                </div>
            </div>
        </router-link>
        <!-- div v-else class="p-6 h-full">
            <div
                class="text-gray-900 text-xl overflow-x-clip outline-none h-full w-full"
                :class="{ 'overflow-hidden cursor-pointer': !isActive }"
                @click="edit()">
                <input
                    v-if="post.title"
                    v-model="localPost.title"
                    class="text-orange-600 text-xl bg-transparent font-semibold" />
                <EditorContent :editor="editor" class="editorContent" />
            </div>
            <PostItemTags v-if="!isActive" :tags="post.tags" class="item-text" />
        </div -->
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
            @click="restorePost()"
            class="restore-icon">
            <svg-vue icon="remix/inbox-unarchive-line" />
        </button>

        <div v-if="post.isUpdating" class="absolute bottom-0 left-0 mb-5 ml-5 bg-white">
            <svg-vue
                icon="remix/refresh-line"
                class="button-icon remix animate-spin fill-current text-gray-900" />
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex'
import { getCollectionName } from './../api/collection.js'
import { restorePost } from './../api/post.js'
import { Editor, EditorContent } from '@tiptap/vue-2'
import StarterKit from '@tiptap/starter-kit'
import Typography from '@tiptap/extension-typography'
import Underline from '@tiptap/extension-underline'
import Link from '@tiptap/extension-link'
import TaskList from '@tiptap/extension-task-list'
import TaskItem from '@tiptap/extension-task-item'
import Image from '@tiptap/extension-image'
import { Color } from '@tiptap/extension-color'
import TextStyle from '@tiptap/extension-text-style'
import UnfurlingLink from '../UnfurlingLink'
import PostItemTags from './PostItemTags.vue'

export default {
    components: {
        EditorContent,
        PostItemTags,
    },
    props: ['post', 'showContextMenu', 'permission', 'restore'],
    data() {
        return {
            isActive: false,
            localPost: null,
            editor: new Editor({
                editable: false,
                extensions: [
                    StarterKit,
                    Typography,
                    Underline,
                    Link,
                    TaskList,
                    TaskItem.configure({ nested: true }),
                    Image,
                    UnfurlingLink,
                    TextStyle,
                    Color.configure({types: ['textStyle'],}),
                ],
                content: this.post.content,
            }),
        }
    },
    computed: {
        collectionName() {
            return getCollectionName(this.post.collection_id)
        },
        ...mapState(['isMobile']),
    },
    created() {
        this.localPost = Object.assign({}, this.post)
    },
    beforeDestroy() {
        this.editor.destroy()
    },
    methods: {
        edit() {
            if (this.isActive) {
                return
            }
            this.setActive(true)
            document
                .querySelector('#app')
                .addEventListener('click', this.stopEditing, true)
        },
        setActive(value) {
            this.editor.setEditable(value)
            this.isActive = value
        },
        stopEditing(event) {
            const currentPostTarget = document.querySelector(
                `[post-id="${this.localPost.id}"] .ProseMirror`
            )
            if (!currentPostTarget.contains(event.target)) {
                this.setActive(false)
                document
                    .querySelector('#app')
                    .removeEventListener('click', this.stopEditing, true)
                const content = this.editor.getHTML()
                const matches = content.match(
                    /^<p>(?<content>.(?:(?!<p>)(?!<\/p>).)*)<\/p>$/
                )
                if (matches == null) {
                    this.localPost.content = content
                } else {
                    this.localPost.content = matches[1]
                }
                this.post.isUpdating = true
                this.$store.dispatch('post/updatePost', { post: this.localPost })
            }
        },
        restorePost() {
            restorePost(this.post)
        },
    },
}
</script>
