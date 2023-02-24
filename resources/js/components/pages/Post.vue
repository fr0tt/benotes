<template>
    <div class="w-full">
        <div class="md:mx-6 mx-1 md:my-6 mt-2">
            <div class="mx-4 editor">
                <div class="max-w-5xl mt-4" @keyup.ctrl.alt.83="keySave">
                    <input
                        v-model="title"
                        class="block w-full text-3xl font-medium placeholder-orange-600 text-orange-600 bg-transparent outline-none"
                        placeholder="Title"
                        tabindex="1"
                        autofocus />
                    <div class="mt-4 mb-4">
                        <!--
                        <Select
                            v-model="collection"
                            class="inline-block w-80"
                            label="name"
                            :options="optionsCollections"
                            :tabindex="2" />
                        -->
                        <Treeselect
                            v-model="selectedCollectionId"
                            :options="optionsCollections"
                            :close-on-select="true"
                            :clear-on-select="true"
                            :normalizer="
                                (node) => {
                                    return {
                                        id: node.id,
                                        label: node.name,
                                        children:
                                            node.nested?.length > 0
                                                ? node.nested
                                                : node.children,
                                    }
                                }
                            "
                            placeholder=""
                            :tabIndex="2"
                            class="inline-block w-80" />
                    </div>

                    <Select
                        v-model="tags"
                        placeholder="Add tag"
                        class="tags block w-full"
                        taggable
                        multiple
                        :close-on-select="false"
                        label="name"
                        :tabindex="3"
                        :options="optionsTags">
                        <template #selected-option="{ id, name }">
                            <router-link :to="'/tags/' + id">
                                {{ name }}
                            </router-link>
                        </template>
                        <template #no-options="{ search, searching, loading }">
                            No additional tags found
                        </template>
                    </Select>

                    <EditorMenuBar :editor="editor" class="w-full my-4" />
                    <EditorContent :editor="editor" class="editorContent text-lg my-4" />
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
import Treeselect from '@riophae/vue-treeselect'
import '@riophae/vue-treeselect/dist/vue-treeselect.css'

import EditorMenuBar from '../EditorMenuBar.vue'
import { Editor, EditorContent } from '@tiptap/vue-2'
import StarterKit from '@tiptap/starter-kit'
import Typography from '@tiptap/extension-typography'
import Underline from '@tiptap/extension-underline'
import Placeholder from '@tiptap/extension-placeholder'
import Link from '@tiptap/extension-link'
import TaskList from '@tiptap/extension-task-list'
import TaskItem from '@tiptap/extension-task-item'
import UnfurlingLink from '../../UnfurlingLink'

export default {
    components: {
        Select,
        OpenIndicator,
        Deselect,
        Treeselect,
        EditorContent,
        EditorMenuBar,
    },
    props: {
        collectionId: Number,
        id: Number,
        shareTargetApi: Object,
    },
    data() {
        return {
            isNewPost: isNaN(this.id),
            post: null,
            title: null,
            selectedCollectionId: null,
            optionsCollections: [],
            tags: null,
            optionsTags: [],
            autofocus: true,
            injectCSS: false,
            editor: new Editor({
                editable: true,
                extensions: [
                    StarterKit,
                    Typography, // e.g. for:  ->
                    Underline,
                    Placeholder.configure({
                        placeholder: 'Write something or paste a link',
                    }),
                    Link,
                    TaskList,
                    TaskItem,
                    UnfurlingLink,
                ],
            }),
        }
    },
    computed: {
        ...mapState('collection', ['currentCollection']),
        ...mapState('collection', ['collections']),
        ...mapState('post', ['posts']),
    },
    mounted() {
        if (!this.shareTargetApi) {
            return
        }
        if (this.shareTargetApi.headline) {
            this.title = this.shareTargetApi.headline
        }
        if (this.shareTargetApi.url) {
            this.editor.commands.setContent(this.shareTargetApi.url)
        } else if (this.shareTargetApi.text) {
            this.editor.commands.setContent(this.shareTargetApi.text)
        }
    },
    created() {
        Select.props.components.default = () => ({ OpenIndicator, Deselect })
        // @todo does this actually work ?
        this.editor.view.props.attributes = { tabindex: '3' }

        // id has to be 0 because of Treeselect
        const uncategorized = { name: 'Uncategorized', id: 0, nested: null }
        this.optionsCollections.push(uncategorized)
        this.$store.dispatch('collection/fetchCollections', { nested: true }).then(() => {
            this.optionsCollections = this.optionsCollections.concat(this.collections)
        })

        axios.get('/api/tags').then((response) => {
            this.optionsTags = response.data.data
        })

        if (this.isNewPost) {
            this.selectedCollectionId = this.collectionId
            this.$store.dispatch('appbar/setAppbar', {
                title: 'Create Post',
                button: {
                    label: 'Save',
                    callback: this.save,
                    icon: 'checkmark',
                },
            })
        } else {
            // edit post
            this.$store
                .dispatch('post/getPost', this.id)
                .then((post) => {
                    this.post = post
                    this.title = post.title
                    this.selectedCollectionId = post.collection_id
                    this.tags = post.tags
                    this.editor.commands.setContent(this.post.content)
                })
                .catch((error) => {
                    console.log(error)
                })
            this.$store.dispatch('appbar/setAppbar', {
                title: 'Edit Post',
                button: {
                    label: 'Save',
                    callback: this.save,
                    icon: 'checkmark',
                },
                options: [
                    {
                        label: 'Delete',
                        longLabel: 'Delete Post',
                        callback: this.delete,
                        icon: 'delete',
                        color: 'red',
                        condition: true,
                    },
                ],
            })
        }
    },
    beforeDestroy() {
        this.editor.destroy()
    },
    methods: {
        async save() {
            let content = this.editor.getHTML()
            if (content === '' || this.currentCollection === null) {
                return
            }

            let tags = await this.saveTags()

            const matches = content.match(/^<p>(?<content>.(?:(?!<p>)(?!<\/p>).)*)<\/p>$/)
            if (matches !== null) {
                content = matches[1]
            }

            if (this.isNewPost) {
                axios
                    .post('/api/posts', {
                        title: this.title,
                        content: content,
                        collection_id:
                            this.selectedCollectionId > 0
                                ? this.selectedCollectionId
                                : null,
                        is_uncategorized: this.selectedCollectionId === null || 0,
                        tags: tags === null ? null : tags.map((tag) => tag.id),
                    })
                    .then((response) => {
                        if (this.posts !== null) {
                            this.$store.dispatch('post/addPost', response.data.data)
                        }
                    })
                    .catch(() => {
                        this.$store.dispatch('notification/setNotification', {
                            type: 'error',
                            title: 'Error',
                            description: 'Post could not be created.',
                        })
                    })
                this.$router.push({
                    path:
                        this.selectedCollectionId === null
                            ? '/'
                            : '/c/' + this.selectedCollectionId,
                })
            } else {
                this.post.title = this.title
                this.post.content = content
                this.post.collection_id = this.selectedCollectionId
                this.post.tags = tags
                this.$store.dispatch('post/updatePost', { post: this.post })
                const originCollectionId = this.post.collection_id
                this.$router.push({
                    path: originCollectionId === null ? '/' : '/c/' + originCollectionId,
                })
            }
        },
        keySave(event) {
            event.preventDefault()
            this.save()
        },
        async saveTags() {
            let newTags = []
            let existingTags = []
            if (!this.tags) {
                return null
            }

            this.tags.forEach((tag) => {
                if (typeof tag.id === 'undefined') {
                    newTags.push(tag)
                } else {
                    existingTags.push(tag)
                }
            })
            return this.combineTags(existingTags, newTags)
        },
        combineTags(existingTags, newTags) {
            return new Promise((resolve) => {
                if (newTags.length === 0) {
                    return resolve(existingTags)
                }
                axios
                    .post('/api/tags', {
                        tags: newTags,
                    })
                    .then((response) => {
                        existingTags = existingTags.concat(response.data.data)
                        resolve(existingTags)
                    })
                    .catch(() => {
                        this.$store.dispatch('notification/setNotification', {
                            type: 'error',
                            title: 'Error',
                            description: 'Tag(s) could not be created.',
                        })
                        resolve(existingTags)
                    })
            })
        },
        delete() {
            this.$store.dispatch('post/deletePost', this.id)
            const route =
                this.post.collection_id > 0 ? '/c/' + this.post.collection_id : '/'
            this.$router.push(route)
        },
    },
}
</script>
<style lang="scss">
.editor {
    .editorContent {
        font-family: Inter, 'Noto Sans', 'Open Sans', -apple-system, BlinkMacSystemFont,
            'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
        p.is-editor-empty:first-child::before {
            content: attr(data-placeholder);
            pointer-events: none;
            height: 0;
            float: left;
            @apply not-italic text-gray-600; //text-orange-600
        }
        .is-empty.is-editor-empty {
            height: calc(100vh - 21rem);
        }
    }
    .vs__dropdown-toggle {
        @apply border-gray-300 border-2;
    }
    .vs__dropdown-menu {
        @apply p-0 order-2 border-gray-300 shadow-none;
        .vs__dropdown-option--highlight {
            @apply bg-orange-500;
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
.editor .tags {
    input.vs__search {
        @apply text-gray-500;
    }
    .vs__dropdown-toggle {
        @apply border-t-0 border-l-0 border-r-0 border-b-2 border-gray-300;
        border-radius: 0;
    }
    .vs__dropdown-menu {
        @apply max-w-xs bg-gray-200 border-gray-200 rounded;
        margin-top: -2px;
        .vs__dropdown-option {
            @apply pt-1 pb-1;
        }
    }
    .vs__actions {
        display: none;
    }
    .vs__selected {
        @apply pl-2 pr-1 rounded bg-orange-600 text-white border-none;
    }
    .vs__deselect svg {
        fill: #fff;
    }
    .vs__dropdown-option--selected {
        display: none;
    }
}
</style>
