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
        globalClickEvent (event) {
            if (this.contextMenu.post !== null) {
                if (this.contextMenu.target !== event.target) {
                    this.$store.dispatch('post/hideContextMenu')
                }
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
    .py-1\.5 {
        padding-top: 0.375rem;
        padding-bottom: 0.375rem;
    }
    button {
        transition: color, background-color 0.2s;
    }
    button:focus {
        outline: none;
    }
    .button {
        @apply border-2 border-orange-600 text-orange-600 bg-white rounded-lg
            outline-none leading-tight text-xl font-semibold px-4 py-1;
        .button-icon {
            @apply mr-1 fill-current align-middle inline-block;
            width: 1.1rem;
            margin-top: -0.3rem;
        }
    }
    .button:hover {
        @apply bg-orange-500 text-white;
    }
    .router-fade-enter-active, .router-fade-leave-active {
        transition: opacity .2s ease;
    }
    .router-fade-enter, .router-fade-leave-to {
        opacity: 0;
    }
    .open-sans {
        font-family: -apple-system, BlinkMacSystemFont, 'Open Sans', 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
    }
</style>
