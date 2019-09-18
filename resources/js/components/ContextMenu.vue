<template>
    <transition name="fade">
        <ol v-if="contextMenu.isVisible" :style="{ top: contextMenu.top + 20 + 'px', left: contextMenu.left - 5 + 'px' }"
            class="absolute bg-white shadow-lg contextmenu">
            <li v-if="contextMenu.post.type === 'link'" @click="edit()">Edit</li>
            <li @click="del()">Delete</li>
        </ol>
    </transition>
</template>

<script>
import { mapState } from 'vuex'
export default {
    name: 'ContextMenu',
    methods: {
        edit () {
            this.$store.dispatch('post/setCurrentPost', this.contextMenu.post)
            this.hide()
        },
        del () {
            this.$store.dispatch('post/deletePost', this.contextMenu.post.id)
            this.hide()
        },
        hide () {
            this.$store.dispatch('post/setContextMenu', {
                isVisible: false,
                post: null
            })  
        }
    },
    computed: {
        ...mapState('post', [
            'contextMenu'
        ])
    }
}
</script>

<style lang="scss">
	.contextmenu {
        li {
            @apply py-2 px-4 text-gray-800 font-medium cursor-pointer;
            min-width: 6rem;
            transition: background-color 0.1s;
        }
        li:hover {
            @apply bg-gray-200;
        }
    }
    .fade-enter-active, .fade-leave-active {
        transition: opacity .2s;
    }
    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
</style>