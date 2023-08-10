<template>
    <transition enter-active-class="transition ease-out duration-300" enter-class="transform translate-y-64"
        enter-to-class="transform translate-y-0"
        leave-active-class="transition ease-in duration-300 opacity-0 translate-y-64 transform">
        <div v-if="isVisible" class="relative ml-auto mr-8 mb-6 max-w-sm bg-white -shadow-md border-1.5"
            :class="color('border', type)">
            <div class=" relative px-6 py-4 flex">
                <div>
                    <svg class="icon mr-6" :class="color('text', type) + ' ' + verticalAdjustment(type)"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" v-html="icon(type)"></svg>
                </div>
                <div class="flex-1">
                    <h6 class="block font-medium mb-2">
                        {{ title }}
                    </h6>
                    <p class="block text-gray-700">
                        {{ description }}
                    </p>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
import { mapState } from 'vuex'
export default {
    computed: {
        ...mapState('notification', ['isVisible', 'type', 'title', 'description']),
    },
    methods: {
        hide() {
            this.$store.commit('notification/showNotification', false)
        },
        icon(icon) {
            switch (icon) {
                case 'error':
                    return '<path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-7v2h2v-2h-2zm0-8v6h2V7h-2z"/>'
                case 'success':
                    return '<path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"/>'
                case 'deletion':
                    return '<path d="M6 2l2-2h4l2 2h4v2H2V2h4zM3 6h14l-1 14H4L3 6zm5 2v10h1V8H8zm3 0v10h1V8h-1z"/>'
            }
        },
        verticalAdjustment(icon) {
            switch (icon) {
                case 'error':
                case 'success':
                    return '-mt-1'
                default:
                    return ''
            }
        },
        color(classType, notificationType) {
            // requires tailwind's safelist
            switch (notificationType) {
                case 'success':
                    return classType + '-green-600'
                default:
                    return classType + '-red-600'
            }
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
