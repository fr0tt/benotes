<template>
    <div class="w-full">
        <div class="md:mx-6 mx-1 md:my-6 mt-2">
            <div class="mx-4 editor">
                <div class="max-w-5xl mt-4" @keyup.ctrl.alt.83="keySave">
                    <input class="block w-full text-3xl font-medium placeholder-orange-600 text-orange-600
                        outline-none" v-model="title" placeholder="Title" tabindex="1" autofocus/>
                    <div class="mt-4 mb-6">
                        <!--
                            <svg-vue class="w-5 inline-block align-text-bottom mb-0.25 mr-2 text-gray-600 fill-current" icon="remix/folder-3-line"/>
                        -->
                        <Select class="inline-block w-80" v-model="collection" label="name" :options="optionsCollections"
                            :tabindex="2"/>
                    </div>
                    <EditorMenuBar :editor="editor" class="w-full my-4"/>
                    <EditorContent :editor="editor" class="editorContent h-32 text-lg my-4"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import axios from 'axios'
import { mapState } from 'vuex'

import Select from 'vue-select'
import OpenIndicator from './OpenIndicator.vue'
import Deselect from './Deselect.vue'
import 'vue-select/dist/vue-select.css'

import { Editor, EditorContent } from 'tiptap'
import { HardBreak, Blockquote, Heading, Bold, Italic,
    Underline, Link, Code, History, Placeholder } from 'tiptap-extensions'
import EditorMenuBar from './EditorMenuBar.vue'

export default {
    props: {
        collectionId: Number,
        id: Number
    },
    components: {
        Select,
        OpenIndicator, 
        Deselect,
        EditorContent,
        EditorMenuBar,
    },
    data () {
        return {
            isNewPost: isNaN(this.id),
            post: null,
            title: null,
            collection: null,
            optionsCollections: [],
            editor: new Editor({
                editable: true,
                extensions: [
                    new HardBreak(),
                    new Heading({ levels: [1, 2, 3] }),
                    new Bold(),
                    new Underline(),
                    new Italic(),
                    new Blockquote(),
                    new Link(),
                    new Code(),
                    new History(),
                    new Placeholder({
                        emptyEditorClass: 'is-editor-empty',
                        showOnlyWhenEditable: true,
                        showOnlyCurrent: true
                    })
                ]
            })
        }
    },
    methods: {
        save () {
            let content = this.editor.getHTML()
            if (content === '' || this.currentCollection === null) {
                return
            }
            const matches = content.match(/^<p>(?<content>.(?:(?!<p>)(?!<\/p>).)*)<\/p>$/)
            if (matches !== null) {
                content = matches[1]
            }

            if (this.isNewPost) {
                axios.post('/api/posts', {
                    title: this.title,
                    content: content,
                    collection_id: this.collection.id
                })
                    .then(response => {
                        if (this.posts !== null) {
                            this.$store.dispatch('post/addPost', response.data.data)
                        }
                    })
                    .catch(error => {
                        console.log(error.response.data)
                        console.log(error)
                    })
                this.$router.push({ path: '/c/' + this.collectionId })
            } else {
                this.post.title = this.title
                this.post.content = content
                this.post.collection_id = this.collection.id
                this.$store.dispatch('post/updatePost', { post: this.post })
                const route = this.post.collection_id == null ? '/' : '/c/' + this.post.collection_id
                this.$router.push({ path: route })
            }
        },
        keySave (event) {
            event.preventDefault()
            this.save()
        }
    },
    computed: {
        ...mapState('collection', [
            'currentCollection'
        ]),
        ...mapState('collection', [
            'collections'
        ]),
        ...mapState('post', [
            'posts'
        ])
    },
    created () {
        Select.props.components.default = () => ({ OpenIndicator, Deselect })
        this.editor.view.props.attributes = { tabindex: '3' }

        const uncategorized = { name: 'Uncategorized', id: null }
        this.optionsCollections.push(uncategorized)
        this.$store.dispatch('collection/fetchCollections').then(() => {
            this.optionsCollections = this.optionsCollections.concat(this.collections)
        })
        
        if (this.isNewPost) {
            this.$store.dispatch('collection/fetchCollections').then(() => {
                if (this.collectionId === 0 || typeof this.collectionId === 'undefined') {
                    this.collection = uncategorized
                } else {
                    this.collection = this.collections.find(collection => 
                        collection.id === this.collectionId
                    )
                }
            })
            this.$store.dispatch('appbar/setAppbar', {
                title: 'Create Post',
                button: {
                    label: 'Save',
                    callback: this.save,
                    icon: 'checkmark'
                } 
            })
        } else {
            this.$store.dispatch('post/getPost', this.id)
                .then(post => {
                    this.post = post
                    this.title = post.title
                    this.$store.dispatch('collection/fetchCollections').then(() => {
                        if (post.collection_id === null) {
                            this.collection = uncategorized
                        } else {
                            this.collection = this.collections.find(collection => 
                                collection.id === post.collection_id
                            )
                        }
                    })
                    this.editor.setContent(this.post.content)
                })
                .catch(error => {
                    console.log(error)
            })
            this.$store.dispatch('appbar/setAppbar', {
                title: 'Edit Post',
                button: {
                    label: 'Save',
                    callback: this.save,
                    icon: 'checkmark'
                } 
            })
        }
    },
    beforeDestroy () {
        this.editor.destroy()
    }
}
</script>
<style lang="scss">
    .editor {
        .editorContent {
            font-family: Inter, 'Noto Sans', 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
            p.is-editor-empty:first-child::before {
                content: 'Write...';
                color: #aaa;
                float: left;
                pointer-events: none;
                height: 0;
                font-style: italic;
            }
        }
    }
    .w-80 {
        width: 20rem;
    }
    .ProseMirror {
        @apply h-full;
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
    .mb-0\.25 {
        margin-bottom: 0.0675rem;
    }
</style>
