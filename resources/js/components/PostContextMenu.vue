<template>
    <transition name="fade">
        <ol v-if="show" class="absolute bg-white shadow-lg contextmenu post-contextmenu" :style="position">
            <li @click="transfer()">
                Transfer
                <svg class="context-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 1l10 6-10 6L0 7l10-6zm6.67 10L20 13l-10 6-10-6 3.33-2L10 15l6.67-4z"/></svg>
            </li>
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
</template>

<script>
import { mapState } from 'vuex'
export default {
    name: 'ContextMenu',
    props: ['postId'],
    methods: {
        edit () {
            this.$router.push({
                path: '/p/' + this.contextMenu.post.id,
                query: { token: this.$route.query.token }
            })
            this.hide()
        },
        del () {
            this.$store.dispatch('post/deletePost', this.contextMenu.post.id)
            this.hide()
        },
        transfer () {
            this.$store.dispatch('collection/setCollectionMenu', {
                isVisible: true,
                post: this.contextMenu.post
            })
            this.hide()
        },
        hide () {
            this.$store.dispatch('post/hideContextMenu')
        }
    },
    computed: {
        ...mapState('post', [
            'contextMenu'
        ]),
        show () {
            return this.contextMenu.post !== null && this.contextMenu.post.id === this.postId
        },
        position () {
            if ((this.contextMenu.positionX + 150) > screen.availWidth) {
                return {
                    right: 0.5 + 'rem'
                }
            }
            return null
        }
    }
}
</script>

<style lang="scss">
	.contextmenu {
        li {
            @apply py-2 px-4 text-gray-800 font-medium cursor-pointer;
            min-width: 7rem;
            transition: background-color 0.1s;
        }
        li:hover {
            @apply bg-gray-200;
        }
        .context-icon {
            @apply mb-1 ml-2 w-4 fill-current inline-block;
        }
    }
    .post-contextmenu {
        margin-top: -0.5rem;
        right: -5.5rem;
        z-index: 100;
    }
    .fade-enter-active, .fade-leave-active {
        transition: opacity .3s;
    }
    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
</style>