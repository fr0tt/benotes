<template>
    <div>
        <div class="md:flex sm:px-8 px-2 py-3 shadow-md">
            <div class="flex-1 md:mb-0 mb-2">
                <span class="text-orange-600 font-semibold text-2xl font-sans">{{ currentCollection.name }}</span>
                <div class="relative inline-block" v-if="currentCollection.id > 0">
                    <button @click="showContextMenu = !showContextMenu" class="align-text-bottom">
                        <svg-vue class="w-5 mb-1/5" icon="material/more_vert"/>
                    </button>
                    <transition name="fade">
                        <ol v-if="showContextMenu" class="absolute bg-white shadow-lg contextmenu z-50">
                            <li @click="edit()">
                                Edit
                                <svg class="context-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M12.3 3.7l4 4L4 20H0v-4L12.3 3.7zm1.4-1.4L16 0l4 4-2.3 2.3-4-4z"/></svg>
                            </li>
                            <li @click="del()">
                                Delete
                                <svg class="context-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M6 2l2-2h4l2 2h4v2H2V2h4zM3 6h14l-1 14H4L3 6zm5 2v10h1V8H8zm3 0v10h1V8h-1z"/></svg>
                            </li>
                        </ol>
                    </transition>
                </div>
            </div>
            <div class="">
                <button v-if="isSupported" class="button" @click="pasteNewPost()">
                    <svg-vue class="button-icon" icon="zondicons/paste"/>
                    Paste
                </button>
                <router-link :to="`/c/${this.id}/p/create`" class="button ml-4"
                    tag="button" title="Strg + Alt + N">
                    <svg-vue class="button-icon" icon="zondicons/add-outline"/>
                    Create
                </router-link>
            </div>
        </div>
        <div class="sm:ml-4 -ml-2">
            <transition name="collection-fade">
                <Draggable v-if="!isLoading" tag="ol" v-model="posts" :move="dragged"
                    @start="drag = true" @end="drag = false"
                    v-bind="{ animation: 200 }" class="mt-4 mb-40" >
                    <transition-group name="grid-fade">
                        <Post v-for="post in posts" :class="drag ? null : 'item-transition'"
                            :key="post.order" :post="post" />
                    </transition-group>
                </Draggable>
            </transition>
        </div>
        <CollectionMenu v-if="collectionMenu.isVisible"/>
    </div>
</template>
<script>
import axios from 'axios'
import { mapState } from 'vuex'
import Post from './PostItem.vue'
import CollectionMenu from './CollectionMenu.vue'
import Draggable from 'vuedraggable'

export default {
    props: ['id'],
    components: {
        Post,
        CollectionMenu,
        Draggable
    },
    data () {
        return {
            drag: false,
            showContextMenu: false
        }
    },
    methods: {
        init () {
            this.$store.dispatch('route/setCurrentRoute', this.$route)
            this.$store.dispatch('post/fetchPosts', this.id)
        },
        pasteNewPost () {
            // may only work with a secure connection
            navigator.clipboard.readText().then(content => {
                if (content === '' || content === null) {
                    return
                }
                axios.post('/api/posts', {
                    content: content,
                    collection_id: this.currentCollection.id
                })
                    .then(response => {
                        this.$store.dispatch('post/addPost', response.data.data)
                    })
                    .catch(error => {
                        console.log(error)
                    })
            })
        },
        dragged (event) {
            axios.patch('/api/posts/' + event.draggedContext.element.id, {
                order: this.maxOrder - event.draggedContext.futureIndex
            })
                .catch(error => {
                    console.log(error)
                })
        },
        edit () {
            this.showContextMenu = false
            this.$router.push({ path: '/c/' + this.currentCollection.id + '/edit' })
        },
        del () {
            this.showContextMenu = false
            this.$store.dispatch('collection/deleteCollection', this.currentCollection.id)
            this.$router.push({ path: '/' })
        }
    },
    watch: {
        id () {
            this.init()
            this.$store.dispatch('collection/getCurrentCollection')
        }
    },
    computed: {
        posts: {
            get () {
                return this.$store.state.post.posts
            },
            set (value) {
                this.$store.commit('post/setPosts', value)
            }
        },
        isSupported () {
            if (typeof navigator.clipboard === 'undefined') {
                return false
            }
            if (navigator.clipboard.readText() !== null) {
                return true
            }
            return false
        },
        ...mapState('post', [
            'maxOrder'
        ]),
        ...mapState('post', [
            'isLoading'
        ]),
        ...mapState('collection', [
            'collectionMenu'
        ]),
        ...mapState('collection', [
            'currentCollection'
        ])
    },
    created () {
        this.init()
    }
}
</script>
<style lang="scss">
    .collection-fade-enter-active, .collection-fade-leave-active {
        transition: opacity .6s;
    }
    .collection-fade-enter, .collection-fade-leave-to{
        opacity: 0;
    }
    .item-transition {
        transition: all 0.6s;
    }
    .grid-fade-enter-active {
        transition: all 0.2s;
    }
    .grid-fade-leave-active {
        position: absolute;
    }
    .grid-fade-leave-to {
        opacity: 0;
        transform: translateX(30px);
    }
    .grid-fade-enter {
        opacity: 0;
    }
    .mb-1\/5 {
        margin-bottom: 0.05rem;
    }
</style>