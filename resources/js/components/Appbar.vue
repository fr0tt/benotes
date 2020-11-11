<template>
    <div class="absolute top-0 right-0 w-full bg-white z-50">
        <div class="flex sm:px-8 px-4 py-3">
            <div class="w-16 my-auto">
                <button @click="toggleSidebar()" class="align-bottom">
                    <svg-vue class="w-6 cursor-pointer" icon="remix/menu-line"/>
                </button>
            </div>
            <div class="flex-1 mb-0 my-auto text-center">
                <span class="text-gray-800 font-medium text-xl">{{ title }}</span>
                <div class="relative inline-block" v-if="currentCollection.id > 0">
                    <button @click="showContextMenu = !showContextMenu" class="align-text-bottom">
                        <svg-vue class="w-5 mb-1/5" icon="remix/more-2-fill"/>
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
            <div v-if="permission >= 6" class="">
                <button v-if="allowPaste && isSupported" class="button" @click="pasteNewPost()">
                    <svg-vue class="button-icon" icon="zondicons/paste"/>
                    Paste
                </button>
                <button v-if="button.callback" @click="button.callback" class="button ml-2 md:ml-4"
                    tag="button" :title="hint">
                    <!--<svg-vue :icon="button.icon" class="button-icon"/>-->
                    <svg v-html="icon" class="button-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"></svg>
                    {{ button.label }}
                </button>
            </div>
        </div>
        <hr class="block border-t-2 border-orange-600">
    </div>
</template>

<script>
import axios from 'axios'
import { mapState } from 'vuex'
export default {
    name: 'Appbar',
    data () {
        return {
            isOpen: true,
            showContextMenu: false
        }
    },
    methods: {
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
        edit () {
            this.showContextMenu = false
            this.$router.push({ path: '/c/' + this.currentCollection.id + '/edit' })
        },
        del () {
            this.showContextMenu = false
            this.$store.dispatch('collection/deleteCollection', this.currentCollection.id)
            this.$router.push({ path: '/' })
        },
        toggleSidebar () {
            this.$store.dispatch('toggleSidebar')
        }
    },
    computed: {
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
        ...mapState('appbar', [
            'title',
            'allowPaste',
            'hint',
            'button'
        ]),
        ...mapState('collection', [
            'currentCollection'
        ]),
        ...mapState('auth', [
            'permission'
        ]),
        icon () {
            if (this.button.icon === 'zondicons/add-outline')
                return '<path d="M11 9h4v2h-4v4H9v-4H5V9h4V5h2v4zm-1 11a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16z"/>'
            else if (this.button.icon === 'zondicons/checkmark-outline')
                return '<path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"/>'
        }
    }
}
</script>