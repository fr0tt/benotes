<template>
    <div class="px-4 md:px-16 py-4 md:pt-16 max-w-4xl">
        <div class="flex mb-10">
            <div class="flex-1">
                <h1 class="text-3xl font-medium text-gray-800">Tags</h1>
            </div>
        </div>
        <div class="relative">
            <ol class="list w-full">
                <li
                    v-for="tag in tags"
                    :key="tag.id"
                    class="row flex px-6 py-3 theme__tags__list_item">
                    <router-link :to="'/tags/' + tag.id" class="flex-1 cursor-pointer">
                        {{ tag.name }}
                    </router-link>
                    <svg
                        class="w-5 h-5 cursor-pointer fill-current text-gray-700"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        @click="showContextMenu($event, tag.id)">
                        <path
                            d="M10 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0-6a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 12a2 2 0 1 1 0-4 2 2 0 0 1 0 4z" />
                    </svg>
                </li>
            </ol>
            <!-- TagContextMenu -->
            <transition name="fade">
                <ul
                    v-if="show"
                    class="absolute bg-white shadow-lg top-0 contextmenu tag-contextmenu"
                    :style="position">
                    <router-link tag="li" :to="'/tags/' + id + '/edit'">
                        Edit
                        <svg
                            class="context-icon"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20">
                            <path
                                d="M12.3 3.7l4 4L4 20H0v-4L12.3 3.7zm1.4-1.4L16 0l4 4-2.3 2.3-4-4z" />
                        </svg>
                    </router-link>
                    <li @click="deleteTag">
                        Delete
                        <svg
                            class="context-icon"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20">
                            <path
                                d="M6 2l2-2h4l2 2h4v2H2V2h4zM3 6h14l-1 14H4L3 6zm5 2v10h1V8H8zm3 0v10h1V8h-1z" />
                        </svg>
                    </li>
                </ul>
            </transition>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    data() {
        return {
            tags: null,
            show: false,
            id: -1,
            position: null,
            target: null,
        }
    },
    created() {
        axios
            .get('/api/tags/')
            .then((response) => {
                this.tags = response.data.data
            })
            .catch((error) => {
                this.error = error
            })
        this.$store.dispatch('appbar/setAppbar', {
            title: 'Tags',
            button: {
                label: 'Create',
                callback: this.create,
                icon: 'add',
            },
        })
    },
    methods: {
        create() {
            this.$router.push('/tags/create')
        },
        deleteTag() {
            axios
                .delete('/api/tags/' + this.id)
                .then(() => {
                    const tagIndex = this.tags.findIndex((tag) => {
                        return tag.id === this.id
                    })
                    this.tags.splice(tagIndex, 1)
                })
                .catch(() => {
                    this.$store.dispatch('notification/setNotification', {
                        type: 'error',
                        title: 'Error',
                        description: 'Tag could not be deleted.',
                    })
                })
            this.show = false
            document
                .querySelector('#app')
                .removeEventListener('click', this.hideContextMenu)
        },
        showContextMenu(event, id) {
            this.show = true
            this.id = id
            this.target = event.target
            this.position = {
                top: event.layerY + 15 + 'px',
            }
            document.querySelector('#app').addEventListener('click', this.hideContextMenu)
        },
        hideContextMenu(event) {
            if (this.target === event.target) {
                return
            }
            this.show = false
            document
                .querySelector('#app')
                .removeEventListener('click', this.hideContextMenu)
        },
    },
}
</script>

<style lang="scss">
.list {
    font-family: Inter, Noto Sans, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
        Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    .row:nth-child(even) {
        @apply bg-gray-200;
    }
    li {
        transition: background-color 0.2s;
    }
    .row:hover td {
        @apply bg-orange-200;
    }
}
.tag-contextmenu {
    right: -4rem;
}
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
</style>
