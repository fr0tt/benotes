<template>
    <div class="w-full">
        <div class="md:mx-6 mx-1 md:my-6 mt-2">
            <div class="mx-4 editor">
                <div class="max-w-5xl" @keyup.ctrl.alt.83="keySave">
                    <EditorMenuBar :editor="editor" class="w-full my-4"/>
                    <input class="block w-full text-3xl font-medium placeholder-orange-500 text-orange-500
                        outline-none mb-4" v-model="title" placeholder="Title" tabindex="1" autofocus/>
                    <EditorContent :editor="editor" class="editorContent h-32 text-lg my-4"/>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import axios from 'axios'
import { mapState } from 'vuex'
import { Editor, EditorContent } from 'tiptap'
import { HardBreak, Blockquote, Heading, Bold, Italic,
    Underline, Link, Code, History, Placeholder } from 'tiptap-extensions'
import EditorMenuBar from './EditorMenuBar.vue'
export default {
    props: ['collectionId', 'id'],
    components: {
        EditorContent,
        EditorMenuBar,
    },
    data () {
        return {
            isNewPost: isNaN(this.id),
            post: null,
            title: null,
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
                    collection_id: parseInt(this.collectionId)
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
                this.$store.dispatch('post/updatePost', { post: this.post })
                this.$router.push({ path: '/c/' + this.post.collection_id })
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
        ...mapState('post', [
            'posts'
        ])
    },
    created () {
        this.editor.view.props.attributes = { tabindex: '2' }
        
        if (this.isNewPost) {
            this.$store.dispatch('appbar/setAppbar', {
                title: 'Create Post',
                button: {
                    label: 'Create',
                    callback: this.save,
                    icon: 'zondicons/add-outline'
                } 
            })
        } else {
            this.$store.dispatch('post/getPost', this.id)
                .then(post => {
                    this.post = post
                    this.title = post.title
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
                    icon: 'zondicons/checkmark-outline'
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
</style>
