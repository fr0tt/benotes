<template>
    <transition
        enter-active-class="transition ease-out duration-300" 
		enter-class="transform translate-y-64"
		enter-to-class="transform translate-y-0"
        leave-active-class="transition ease-in duration-300 opacity-0 translate-y-64 transform"
    >
        <div v-if="showBottomSheet" id="bottomSheet" 
            class="absolute bottom-0 w-full bg-white -shadow-md rounded-t-lg">
            <div class="relative px-8 py-4 border-b-2 border-gray-400">
                <button @click="hide" class="absolute">
                    <svg-vue class="icon text-gray-600" icon="remix/arrow-down-s-line"/>
                </button>
                <span class="block text-center font-medium">{{ title }}</span>
            </div>
            <div class="px-8">
                <ol v-for="option in bottomSheet" :key="option.label" class="">
                    <li v-if="option.condition" @click="executeCallback(option.callback($event))" 
                        class="block my-6 font-medium text-gray-700">
                        <svg v-html="icon(option.icon)" class="icon -mt-1 mr-6 text-gray-600"
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"></svg>
                        {{ option.longLabel }}
                    </li>
                </ol>
            </div>
        </div>
    </transition>
</template>
<script>
import { mapState } from 'vuex'
export default {
    computed: {
        ...mapState([
            'showBottomSheet',
        ]),
        ...mapState([
            'bottomSheet',
        ]),
        ...mapState('appbar', [
            'title'
        ])
    },
    methods: {
        hide () {
            this.$store.commit('showBottomSheet', false)
        },
        icon (icon) {
            if (icon === 'add')
                return '<path d="M11 9h4v2h-4v4H9v-4H5V9h4V5h2v4zm-1 11a10 10 0 1 1 0-20 10 10 0 0 1 0 20zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16z"/>'
            else if (icon === 'checkmark')
                return '<path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"/>'
            else if (icon === 'paste')
                return '<path d="M10.5 20H2a2 2 0 0 1-2-2V6c0-1.1.9-2 2-2h1V3l2.03-.4a3 3 0 0 1 5.94 0L13 3v1h1a2 2 0 0 1 2 2v1h-2V6h-1v1H3V6H2v12h5v2h3.5zM8 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2zm2 4h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-8a2 2 0 0 1-2-2v-8c0-1.1.9-2 2-2zm0 2v8h8v-8h-8z"/>'
            else if (icon === 'edit')
                return '<path fill="none" d="M0 0h24v24H0z"/><path d="M15.728 9.686l-1.414-1.414L5 17.586V19h1.414l9.314-9.314zm1.414-1.414l1.414-1.414-1.414-1.414-1.414 1.414 1.414 1.414zM7.242 21H3v-4.243L16.435 3.322a1 1 0 0 1 1.414 0l2.829 2.829a1 1 0 0 1 0 1.414L7.243 21z"/>'
            else if (icon === 'delete')
                return '<path d="M6 2l2-2h4l2 2h4v2H2V2h4zM3 6h14l-1 14H4L3 6zm5 2v10h1V8H8zm3 0v10h1V8h-1z"/>'
        },
        executeCallback (callback) {
            this.callback
            this.hide
        }
    }
}
</script>
<style>
    .icon {
        @apply w-5 align-middle inline-block fill-current;
    }
    .-shadow-md {
        box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1), 0 -2px 4px -1px rgba(0, 0, 0, 0.06);
    }
</style>