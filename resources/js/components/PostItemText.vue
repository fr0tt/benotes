<template>
    <div class="card bg-gray-100" :class="{ 'active' : isActive }">
        <router-link v-if="isMobile" :to="'/p/' + post.id" class="block w-full">
            <div class="p-6 h-full">
                <div class="text-gray-900 text-xl outline-none h-full w-full">
                    <p v-if="post.title" class="text-orange-600 text-xl bg-transparent font-semibold">
                        {{ post.title }}
                    </p>
                    <!--<div class="editorContent" v-html="post.content"></div>-->
                    <EditorContent :editor="editor" class="editorContent" />
                </div>
            </div>
        </router-link>
        <div v-else class="p-6 h-full">
            <div @click="edit()" class="text-gray-900 text-xl outline-none h-full w-full"
                :class="{ 'overflow-hidden cursor-pointer' : !isActive }">
                <input v-if="post.title" v-model="localPost.title"
                    class="text-orange-600 text-xl bg-transparent font-semibold"/>
                <EditorContent :editor="editor" class="editorContent" />
            </div>
        </div>
        <svg @click="showContextMenu($event)" v-if="permission > 4" class="more-svg" 
            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path d="M10 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0-6a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/>
        </svg>
    </div>
</template>

<script>
import { mapState } from 'vuex'
import { Editor, EditorContent } from '@tiptap/vue-2'
import StarterKit from '@tiptap/starter-kit'
import Document from '@tiptap/extension-document'
import Text from '@tiptap/extension-text'
import Typography from '@tiptap/extension-typography'
import Underline from '@tiptap/extension-underline'
import BulletList from '@tiptap/extension-bullet-list'
import OrderedList from '@tiptap/extension-ordered-list'
import ListItem from '@tiptap/extension-list-item'
import Gapcursor from '@tiptap/extension-gapcursor'
import Placeholder from '@tiptap/extension-placeholder'
import UnfurlingLink from '../UnfurlingLink'

export default {
    props: ['post', 'showContextMenu', 'permission'],
    components: {
        EditorContent,
    },
    data () {
        return {
            isActive: false,
            localPost: null,
            editor: new Editor({
                editable: false,
                extensions: [
                    StarterKit,
                    Document,
                    Typography,
                    Text,
                    Underline,
                    BulletList,
                    OrderedList,
                    ListItem,
                    Gapcursor,
                    Placeholder,
                    UnfurlingLink
                ],
                content: this.post.content
            })
        }
    },
    methods: {
        edit () {
            if (this.isActive) {
                return
            }
            this.setActive(true)
            document.querySelector('#app').addEventListener('click', this.stopEditing, true)
        },
        setActive (value) {
            this.editor.setEditable(value)
            this.isActive = value
        },
        stopEditing (event) {
            const currentPostTarget = document.querySelector(`[post-id="${this.localPost.id}"] .ProseMirror`)
            if (!currentPostTarget.contains(event.target)) {
                this.setActive(false)
                document.querySelector('#app').removeEventListener('click', this.stopEditing, true)
                const content = this.editor.getHTML()
                const matches = content.match(/^<p>(?<content>.(?:(?!<p>)(?!<\/p>).)*)<\/p>$/)
                if (matches == null) {
                    this.localPost.content = content
                } else {
                    this.localPost.content = matches[1]
                }
                this.$store.dispatch('post/updatePost', { post: this.localPost })
            }
        }
    },
    computed: {
        ...mapState([
            'isMobile'
        ])
    },
    created () {
        this.localPost = Object.assign({}, this.post)
    },
    beforeDestroy () {
        this.editor.destroy()
    }
}
</script>