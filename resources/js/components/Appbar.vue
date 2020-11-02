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
                <button @click="buttonCallback" class="button ml-2 md:ml-4"
                    tag="button" title="Strg + Alt + N">
                    <svg-vue class="button-icon" :icon="buttonIcon"/>
                    {{ buttonLabel }}
                </button>
            </div>
        </div>
        <hr class="block border-t-2 border-orange-600">
    </div>
</template>

<script>
import axios from 'axios'
import { mapState } from 'vuex'
import Sidebar from './Sidebar.vue'
export default {
    name: 'Appbar',
    props: {
        title: String,
        buttonLabel: String, 
        buttonIcon: String,
        buttonCallback: Function,
        allowPaste: {
            type: Boolean,
            default: false
        }
    },
    data () {
        return {
            isOpen: true,
            showContextMenu: false
        }
    },
    components: {
        Sidebar
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
            console.log('sdfsf')
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
        ...mapState('collection', [
            'currentCollection'
        ]),
        ...mapState('auth', [
            'permission'
        ])
    }
}
</script>

<style lang="scss">
    
</style>