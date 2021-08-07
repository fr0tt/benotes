<template>
    <div class="w-full">
        <div class="md:mx-6 mx-1 md:my-6 mt-2">
            <div class="mx-4 editor">
                <div class="max-w-5xl mt-4" @keyup.ctrl.alt.83="keySave">
                    <input class="block w-full text-3xl font-medium placeholder-orange-600 
                        text-orange-600 bg-transparent outline-none" 
                        v-model="title" placeholder="Title" tabindex="1" autofocus/>
                    <div class="mt-4 mb-6">
                        <Select class="inline-block w-80" v-model="collection"
                            label="name" :options="optionsCollections" :tabindex="2"/>
                    </div>
                    <EditorMenuBar :editor="editor" class="w-full my-4"/>
                    <EditorContent :editor="editor" class="editorContent text-lg my-4"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import axios from 'axios'
import { mapState } from 'vuex'

import Select from 'vue-select'
import OpenIndicator from '../OpenIndicator.vue'
import Deselect from '../Deselect.vue'
import 'vue-select/dist/vue-select.css'

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
import UnfurlingLink from '../../UnfurlingLink'

import EditorMenuBar from '../EditorMenuBar.vue'

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
            autofocus: true,
            injectCSS: false,
            editor: new Editor({
                editable: true,
                extensions: [
                    StarterKit,
                    Document,
                    Typography, // e.g. ->
                    Text,
                    Underline,
                    BulletList,
                    OrderedList,
                    ListItem,
                    Gapcursor,
                    Placeholder,
                    UnfurlingLink
                ],
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
                    collection_id: this.collection.id,
                    is_uncategorized: this.collection.id > 0 ? false : true
                })
                    .then(response => {
                        if (this.posts !== null) {
                            this.$store.dispatch('post/addPost', response.data.data)
                        }
                    })
                    .catch(error => {
                        console.log(error)
                    })
                this.$router.push({ path: '/c/' + this.collectionId })
            } else {
                const originCollectionId = this.post.collection_id
                this.post.title = this.title
                this.post.content = content
                this.post.collection_id = this.collection.id
                this.$store.dispatch('post/updatePost', { post: this.post })
                const route = originCollectionId === null ? '/' : '/c/' + originCollectionId
                this.$router.push({ path: route })
            }
        },
        keySave (event) {
            event.preventDefault()
            this.save()
        },
        delete () {
            this.$store.dispatch('post/deletePost', this.id)
            const route = this.post.collection_id > 0 ? '/c/' + this.post.collection_id : '/'
            this.$router.push(route)
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
        // @todo does this actually work ?
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
                    this.editor.commands.setContent(this.post.content)
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
                },
                options: [{
                    label: 'Delete',
                    longLabel: 'Delete Post',
                    callback: this.delete,
                    icon: 'delete',
                    color: 'red',
                    condition: true
                }] 
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
                content: attr(data-placeholder);
                pointer-events: none;
                height: 0;
                float: left;
                @apply not-italic text-gray-600; //text-orange-600
            }
        }
    }
    .w-80 {
        width: 20rem;
    }
    .ProseMirror {
        @apply h-full;
    }
    .mb-0\.25 {
        margin-bottom: 0.0675rem;
    }
</style>
