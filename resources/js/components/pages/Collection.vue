<template>
    <div class="min-h-full relative collection">
        <div class="sm:ml-4 -ml-2 px-2">
            <transition name="collection-fade">
                <Draggable
                    v-if="!isLoading"
                    v-model="posts"
                    tag="ol"
                    :disabled="isUpdating"
                    :delay="180"
                    :delay-on-touch-only="true"
                    v-bind="{ animation: 200 }"
                    class="pt-4 pb-16"
                    @start="drag = true"
                    @end="dragged">
                    <transition-group name="grid-fade">
                        <Post
                            v-for="post in posts"
                            :key="post.id"
                            :class="drag ? '' : 'item-transition'"
                            :post="post"
                            :permission="permission" />
                    </transition-group>
                </Draggable>
                <ul v-else>
                    <Post v-for="post in posts" :key="post.id" :post="post" />
                </ul>
            </transition>
        </div>
        <CollectionMenu v-if="collectionMenu.isVisible" />
        <PostLoader :collection-id="collectionId" />
    </div>
</template>
<script>
import axios from 'axios'
import { mapGetters, mapState } from 'vuex'
import Post from '../PostItem.vue'
import CollectionMenu from '../CollectionMenu.vue'
import PostLoader from '../PostLoader.vue'
import Draggable from 'vuedraggable'

export default {
    components: {
        Post,
        CollectionMenu,
        PostLoader,
        Draggable,
    },
    props: {
        collectionId: Number,
        permission: Number,
    },
    data() {
        return {
            drag: false,
        }
    },
    computed: {
        posts: {
            get() {
                return this.$store.state.post.posts
            },
            set(value) {
                this.$store.commit('post/setPosts', value)
            },
        },
        isSupported() {
            if (typeof navigator.clipboard === 'undefined') {
                return false
            }
            if (typeof navigator.clipboard.readText === 'undefined') {
                return false
            }
            if (navigator.clipboard.readText() !== null) {
                return true
            }
            return false
        },
        isRealCollection() {
            return this.$route.params.collectionId > 0
        },
        ...mapState('collection', ['currentCollection']),
        ...mapGetters('post', ['maxOrder']),
        ...mapState('post', ['isLoading']),
        ...mapState('post', ['isUpdating']),
        ...mapState('collection', ['collectionMenu']),
    },
    watch: {
        collectionId() {
            this.init()
            this.$store
                .dispatch('collection/getCurrentCollection', this.collectionId)
                .then((name) => {
                    this.$store.commit('appbar/setTitle', name)
                })
            this.$store.dispatch('appbar/setOptions', [
                {
                    label: 'Paste',
                    longLabel: 'Paste (a link)',
                    callback: this.pasteNewPost,
                    icon: 'paste',
                    condition: this.isSupported,
                },
                {
                    label: 'Edit',
                    longLabel: 'Edit Collection',
                    callback: this.editCollection,
                    icon: 'edit',
                    color: 'gray',
                    condition: this.isRealCollection,
                },
            ])
        },
    },
    created() {
        this.init()

        this.$store.dispatch('appbar/setAppbar', {
            hint: 'Ctrl + Alt + N',
            button: {
                label: 'Create',
                callback: this.create,
                icon: 'add',
            },
            options: [
                {
                    label: 'Paste',
                    longLabel: 'Paste (a link)',
                    callback: this.pasteNewPost,
                    icon: 'paste',
                    condition: this.isSupported,
                },
                {
                    label: 'Edit',
                    longLabel: 'Edit Collection',
                    callback: this.editCollection,
                    icon: 'edit',
                    color: 'gray',
                    condition: this.isRealCollection,
                },
            ],
        })

        if (
            this.currentCollection.name == '' ||
            this.currentCollection.id !== this.collectionId
        ) {
            this.$store
                .dispatch('collection/getCurrentCollection', this.collectionId)
                .then(() => {
                    this.$store.commit('appbar/setTitle', this.currentCollection.name, {
                        root: true,
                    })
                })
        } else {
            this.$store.commit('appbar/setTitle', this.currentCollection.name, {
                root: true,
            })
        }
    },
    methods: {
        init() {
            this.$store.dispatch('post/fetchPosts', { collectionId: this.collectionId })
        },
        dragged(event) {
            this.drag = false
            if (event.oldIndex === event.newIndex) {
                return
            }
            this.$store.commit('post/isUpdating', true)
            let post = this.$store.state.post.posts[event.newIndex]
            const newOrder = post.order + (event.oldIndex - event.newIndex)
            axios
                .patch('/api/posts/' + post.id, {
                    order: newOrder,
                })
                .then(() => {
                    this.$store.dispatch('post/updatePostOrders', {
                        oldIndex: event.oldIndex,
                        newIndex: event.newIndex,
                        newOrder: newOrder,
                    })
                })
                .catch((error) => {
                    // reverse order
                    let changes = new Map()
                    changes.set(
                        event.oldIndex,
                        this.$store.state.post.posts[event.newIndex]
                    )
                    if (event.newIndex > event.oldIndex) {
                        // was moved to the right
                        for (let index = event.oldIndex; index < event.newIndex; index++)
                            changes.set(index + 1, this.$store.state.post.posts[index])
                    } else {
                        // was moved to the left
                        for (let index = event.newIndex; index < event.oldIndex; index++)
                            changes.set(index, this.$store.state.post.posts[index + 1])
                    }
                    console.log(changes)
                    for (const [index, post] of changes) {
                        this.$store.state.post.posts[index] = post
                    }

                    this.$store.commit('post/isUpdating', false)
                    this.$store.dispatch('notification/setNotification', {
                        type: 'error',
                        title: 'Error',
                        description: 'Post could not be moved.',
                    })
                })
        },
        create() {
            this.$router.push(`/c/${this.collectionId}/p/create`)
        },
        pasteNewPost() {
            // may only work with a secure connection
            navigator.clipboard.readText().then((content) => {
                if (content === '' || content === null) {
                    this.$store.dispatch('notification/setNotification', {
                        type: 'error',
                        title: 'Nothing to paste',
                        description: 'Clipboard is empty.',
                    })
                    return
                }
                const id = 'placeholder-' + (this.maxOrder + 1)
                this.$store.dispatch('post/addPost', {
                    id: id,
                    type: 'placeholder',
                    order: this.maxOrder + 1,
                })
                axios
                    .post('/api/posts', {
                        content: content,
                        collection_id: this.collectionId > 0 ? this.collectionId : null,
                        is_uncategorized: this.collectionId === 0,
                    })
                    .then((response) => {
                        this.$store.dispatch('post/setPostById', {
                            id: id,
                            post: response.data.data,
                        })
                    })
                    .catch((error) => {
                        this.$store.commit('post/deletePost', id)
                        let message = 'Post could not be created.'
                        if (!['undefined', 'object'].includes(typeof error))
                            message = toString(error).substring(0, 60)
                        this.$store.dispatch('notification/setNotification', {
                            type: 'error',
                            title: 'Error ' + error.response.status,
                            description: message,
                        })
                    })
            })
        },
        editCollection() {
            this.$router.push({ path: '/c/' + this.collectionId + '/edit' })
        },
        deleteCollection() {
            this.$store.dispatch('collection/deleteCollection', this.collectionId)
            this.$router.push({ path: '/' })
        },
    },
}
</script>
<style lang="scss">
.collection {
    .collection-fade-enter-active,
    .collection-fade-leave {
        transition: opacity 0.6s;
    }
    .collection-fade-enter,
    .collection-fade-leave-to {
        opacity: 0;
    }
    .item-transition {
        transition: transform 0.4s;
    }
    .grid-fade-enter-active {
        transition: all 0.2s;
    }
    .grid-fade-leave-active {
        position: absolute;
    }
    .grid-fade-leave-to {
        opacity: 0;
        transform: translateY(30px);
    }
    .grid-fade-enter {
        opacity: 0;
    }
}
.mb-1\/5 {
    margin-bottom: 0.05rem;
}
</style>
