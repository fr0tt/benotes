<template>
    <div>
        <div class="sm:ml-4 -ml-2 px-2">
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
    props: {
        collectionId: Number, 
        permission: Number
    },
    components: {
        Post,
        CollectionMenu,
        Draggable
    },
    data () {
        return {
            drag: false,
        }
    },
    methods: {
        init () {
            this.$store.dispatch('post/fetchPosts', this.collectionId)
        },
        dragged (event) {
            axios.patch('/api/posts/' + event.draggedContext.element.id, {
                order: this.maxOrder - event.draggedContext.futureIndex
            })
                .catch(error => {
                    console.log(error)
                })
        },
        create () {
            this.$router.push(`/c/${this.collectionId}/p/create`)
        }
    },
    watch: {
        collectionId () {
            this.init()
            this.$store.dispatch('collection/getCurrentCollection', this.collectionId)
        },
        currentCollection: function (newValue, oldValue) {
            this.$store.commit('appbar/setTitle', newValue.name, { root: true })
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
            if (typeof navigator.clipboard.readText === 'undefined') {
                return false
            }
            if (navigator.clipboard.readText() !== null) {
                return true
            }
            return false
        },
        ...mapState('collection', [
            'currentCollection'
        ]),
        ...mapState('post', [
            'maxOrder'
        ]),
        ...mapState('post', [
            'isLoading'
        ]),
        ...mapState('collection', [
            'collectionMenu'
        ]),
    },
    created () {
        this.init()

        this.$store.dispatch('appbar/setAppbar', {
            allowPaste: true,
            hint: 'Ctrl + Alt + N',
            button: {
                label: 'Create',
                callback: this.create,
                icon: 'zondicons/add-outline'
            }
        })

        if (this.currentCollection.name == '') {
            this.$store.dispatch('collection/getCurrentCollection', this.collectionId).then(() => {
                this.$store.commit('appbar/setTitle', this.currentCollection.name, { root: true })
            })
        } else {
            this.$store.commit('appbar/setTitle', this.currentCollection.name, { root: true })
        }
    },
}
</script>
<style lang="scss">
    .collection-fade-enter-active, .collection-fade-leave-active {
        transition: opacity .6s;
    }
    .collection-fade-enter, .collection-fade-leave-to {
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