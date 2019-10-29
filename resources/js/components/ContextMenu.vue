<template>
    <transition name="fade">
        <ol v-if="contextMenu.isVisible" :style="{ top: contextMenu.top + 20 + 'px', left: contextMenu.left - 5 + 'px' }"
            class="absolute bg-white shadow-lg contextmenu">
            <li v-if="contextMenu.post.type === 'link'" @click="edit()">
                Edit
                <svg class="context-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M12.3 3.7l4 4L4 20H0v-4L12.3 3.7zm1.4-1.4L16 0l4 4-2.3 2.3-4-4z"/></svg>
            </li>
            <li @click="del()">
                Delete
                <svg class="context-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M6 2l2-2h4l2 2h4v2H2V2h4zM3 6h14l-1 14H4L3 6zm5 2v10h1V8H8zm3 0v10h1V8h-1z"/></svg>
            </li>
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
        .context-icon {
            @apply mb-1 ml-2 w-4 fill-current;
        }
    }
    .fade-enter-active, .fade-leave-active {
        transition: opacity .2s;
    }
    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
</style>