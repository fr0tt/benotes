<template>
    <div>
        <div class="flex pl-8 pr-8 py-3 shadow-md">
            <div class="flex-1">
                <span class="text-orange-600 font-semibold text-2xl">{{ currentCollection.name }}</span>
            </div>
            <div class="">
                <button class="button mx-2" @click="pasteNewPost()">
                    <svg-vue class="button-icon" icon="zondicons/paste"/>
                    Paste
                </button>
                <router-link to="/p/create" class="button mx-2" tag="button">
                    <svg-vue class="button-icon" icon="zondicons/add-outline"/>
                    Create
                </router-link>
            </div>
        </div>
        <div class="ml-4">
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
import CollectionMenu from './CollectionMenuComponent.vue'
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
            drag: false
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
</style>