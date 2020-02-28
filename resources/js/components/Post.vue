<template>
    <div class="w-full">
        <div class="flex md:mx-6 mx-1 md:my-6 mt-2">

            <div class="flex-1 mr-8 editor">

                <input class="block w-full text-3xl font-medium placeholder-orange-500 text-orange-500 outline-none mb-4"
                    v-model="title" placeholder="Title" tabindex="1"/>

                <EditorMenuBar :editor="editor"/>
                <EditorContent :editor="editor" class="editorContent h-full text-lg my-4"/>

            </div>
            <div class="w-40">
                <button @click="save()" class="button">
                    <svg class="button-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"/></svg>
                    Save
                </button>
            </div>

        </div>
    </div>
</template>
<script>
import axios from 'axios'
import { mapState } from 'vuex'
import { Editor, EditorContent } from 'tiptap'
import { HardBreak, Blockquote, Heading, Bold, Italic, Underline, Link, Code, History } from 'tiptap-extensions'
import EditorMenuBar from './EditorMenuBar.vue'
export default {
    props: ['id'],
    components: {
        EditorContent,
        EditorMenuBar
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
                    new History()
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
            if (this.isNewPost) {
                axios.post('/api/posts', {
                    title: this.title,
                    content: content,
                    collection_id: this.currentCollection.id
                })
                    .then(response => {
                        this.$store.dispatch('post/addPost', response.data.data)
                    })
                    .catch(error => {
                        console.log(error)
                    })
            } else {
                this.post.title = this.title
                const matches = content.match(/^<p>(?<content>.(?:(?!<p>)(?!<\/p>).)*)<\/p>$/)
                if (matches !== null) {
                    content = matches[1]
                }
                this.post.content = content
                this.$store.dispatch('post/updatePost', { post: this.post })
                this.$router.push({ path: '/' })
            }
        }
    },
    computed: {
        ...mapState('collection', [
            'currentCollection'
        ])
    },
    created () {
        this.editor.view.props.attributes = { tabindex: '2' }
        if (this.isNewPost) {
            return
        }
        this.$store.dispatch('post/getPost', this.id)
            .then(post => {
                this.post = post
                this.title = post.title
                this.editor.setContent(this.post.content)
            })
            .catch(error => {
                console.log(error)
            })
    },
    beforeDestroy () {
        this.editor.destroy()
    }
}
</script>
<style lang="scss">
    .editor {
        .editorContent {
            font-family: 'Noto Sans', 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif
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
