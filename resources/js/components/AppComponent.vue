<template>
    <div class="flex w-full" @click="globalClickEvent($event)">
        <div class="max-w-xs md:w-56 lg:w-1/6">
            <Sidebar/>
        </div>
        <div class="w-5/6 pr-6">
            <router-view></router-view>
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
                    this.$store.dispatch('post/setContextMenu', {
                        isVisible: false,
                        post: null
                    })  
                }
            },
            globalClickEvent (event) {
                if (this.currentPost) {
                    console.log('i')
                    this.stopEditing(event)
                } else if (this.contextMenu.isVisible) {
                    console.log('a ' + this.contextMenu.isVisible)
                    this.hideContextMenu(event)
                }
            }
        },
        computed: {
            ...mapState([
                'authUser'
            ]),
            ...mapState('post', [
                'currentPost'
            ]),
            ...mapState('post', [
                'contextMenu'
            ])
        }
    }
</script>
<style lang="scss">
    button {
        transition: color, background-color 0.2s;
    }
</style>
