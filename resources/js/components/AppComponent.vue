<template>
    <div class="flex w-full" @click="globalClickEvent($event)">
        <Sidebar/>
        <div class="flex-1 h-screen overflow-y-scroll">
            <transition name="router-fade" mode="out-in">
            <router-view></router-view>
            </transition>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex'
import Sidebar from './SidebarComponent.vue'

    export default {
        components: { 
            Sidebar
        },
        methods: {
            stopEditing (event) {
                if (this.currentPost.target !== event.target) {
                    this.$store.dispatch('post/updatePost', this.currentPost)
                    this.$store.dispatch('post/setCurrentPost', null)
                }
            },
            hideContextMenu (event) {
                if (this.contextMenu.target !== event.target) {
                    this.$store.dispatch('post/hideContextMenu')  
                }
            },
            globalClickEvent (event) {
                if (this.currentPost) {
                    this.stopEditing(event)
                } else if (this.contextMenu.post !== null) {
                    this.hideContextMenu(event)
                }
            }
        },
        computed: {
            ...mapState('post', [
                'currentPost'
            ]),
            ...mapState('post', [
                'contextMenu'
            ])
        },
        created () {
            this.$store.dispatch('checkDevice')
        }
    }
</script>
<style lang="scss">
    button {
        transition: color, background-color 0.2s;
    }
    .router-fade-enter-active, .router-fade-leave-active {
        transition: opacity .2s ease;
    }
    .router-fade-enter, .router-fade-leave-to {
        opacity: 0;
    }
</style>
