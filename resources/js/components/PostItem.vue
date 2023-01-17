<template>
    <div class="md:inline-block mx-6 md:mx-4 my-4 relative text-left post" :post-id="post.id">
        <PostItemLink
            v-if="post.type === 'link'"
            :post="post"
            :show-context-menu="showContextMenu"
            :permission="permission"
            :restore="restore" />
        <PostItemText
            v-else-if="post.type === 'text'"
            :post="post"
            :show-context-menu="showContextMenu"
            :permission="permission"
            :restore="restore" />
        <PostItemPlaceholder v-else />
        <div v-if="debug" class="absolute bottom-0 w-full">
            <span class="px-1 bg-orange-200">id:{{ post.id }}</span>
            <span class="px-1 bg-orange-200 float-right">o:{{ post.order }}</span>
        </div>
        <ContextMenu :post-id="post.id" />
    </div>
</template>
<script>
import { mapState } from 'vuex'
import ContextMenu from './PostContextMenu.vue'
import PostItemText from './PostItemText.vue'
import PostItemLink from './PostItemLink.vue'
import PostItemPlaceholder from './PostItemPlaceholder.vue'
export default {
    name: 'PostItem',
    components: {
        ContextMenu,
        PostItemText,
        PostItemLink,
        PostItemPlaceholder,
    },
    props: ['post', 'permission', 'restore'],
    data() {
        return {
            debug: false,
        }
    },
    methods: {
        showContextMenu(event) {
            this.$store.dispatch('post/setContextMenu', {
                post: this.post,
                target: event.target,
                positionX: event.pageX,
            })
            document.addEventListener('click', this.globalClickEvent)
        },
        globalClickEvent(event) {
            if (this.contextMenu.post !== null) {
                if (this.contextMenu.target !== event.target) {
                    this.$store.dispatch('post/hideContextMenu')
                    document.removeEventListener('click', this.globalClickEvent)
                }
            }
        },
    },
    computed: {
        ...mapState('post', ['contextMenu']),
    },
}
</script>
<style lang="scss">
.card {
    @apply flex relative overflow-hidden shadow-lg;
    @apply w-full;
    height: 10.5rem;
    font-family: Inter, Noto Sans, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen,
        Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    transition: background-color 0.3s;
    -webkit-transition: background-color 0.3s;
    -moz-transition: background-color 0.3s;
    .h-cover {
        height: 10.5rem;
    }
    .description {
        height: 3rem;
    }
    .img-vertical-align {
        margin-top: -4px;
    }
    .more-icon,
    .restore-icon {
        @apply w-5 h-5 absolute cursor-pointer;
        right: 0.75rem;
        bottom: 1rem;
    }
    .restore-icon {
        right: 1rem;
    }
    .editorContent {
        font-size: 1.1rem;
        line-height: 1.45;
        .unfurling-link {
            span {
                @apply text-base;
            }
            img {
                @apply w-8 h-8;
            }
            .ul-link {
                @apply hidden;
            }
        }
    }
}
@screen md {
    .card {
        @apply block;
        width: 20.5rem;
        height: 21.5rem;
    }
}
.card::-webkit-scrollbar {
    width: 2px;
    background-color: #f5f5f5;
}
.card::-webkit-scrollbar-track {
    -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
    background-color: rgba(255, 255, 255, 0.8);
}
.card::-webkit-scrollbar-thumb {
    @apply bg-orange-500;
    border-radius: 8px;
}
.card.active {
    @apply bg-orange-100 rounded border border-orange-300 overflow-auto;
}
.ProseMirror {
    outline: none;
}
</style>
