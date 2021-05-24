<template>
    <div class="absolute top-0 right-0 w-full bg-white z-50">
        <div class="flex sm:px-8 px-4 py-3">
            <div class="w-16 my-auto">
                <button @click="toggleSidebar()" v-if="authUser" class="align-bottom">
                    <svg-vue class="w-6 cursor-pointer" icon="remix/menu-line"/>
                </button>
            </div>
            <div class="flex-1 mb-0 my-auto text-center">
                <span class="text-gray-800 font-medium text-xl">{{ title }}</span>
            </div>
            <div v-if="permission >= 6">
                <button v-if="button.callback" @click="button.callback" 
                    class="button -mr-1" :title="hint">
                    <svg v-html="icon(button.icon)" class="button-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"></svg>
                    {{ button.label }}
                </button>
                <div v-if="options" class="inline-block">
                    <button v-if="isMobile" class="button gray ml-2" id="appbar-options-button"
                        @click="toggleBottomSheet" title="Options">
                        <svg-vue icon="remix/settings-3-line" class="button-icon remix"/>
                        Options
                    </button>
                    <button v-else
                        v-for="option in filteredOptions" :key="option.label" :title="option.longLabel"
                        @click="option.callback" class="button ml-2" :class="option.color">
                        <svg v-html="icon(option.icon)" class="button-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"></svg>
                        {{ option.label }}
                    </button>
                </div>
            </div>
        </div>
        <hr class="block border-t-2 border-orange-600">
    </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
    name: 'Appbar',
    methods: {
        toggleSidebar () {
            this.$store.dispatch('toggleSidebar')
        },
        toggleBottomSheet () {
            if (this.showBottomSheet) {
                this.$store.commit('showBottomSheet', false)
                document.querySelector('#app').removeEventListener('click', this.closeBottomSheet, true)
            } else {
                this.$store.commit('showBottomSheet', true)
                this.$store.commit('setBottomSheet', this.options)
                document.querySelector('#app').addEventListener('click', this.closeBottomSheet, true)
            }
        },
        closeBottomSheet (event) {
            if (document.querySelector('#bottomSheet').contains(event.target) ||
                document.querySelector('#appbar-options-button').contains(event.target)) {
                return
            }
            this.$store.commit('showBottomSheet', false)
            document.querySelector('#app').removeEventListener('click', this.closeBottomSheet, true)
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
        }
   },
    computed: {
        ...mapState('appbar', [
            'title',
            'hint',
            'button',
            'options'
        ]),
        ...mapState('auth', [
            'authUser'
        ]),
        ...mapState('auth', [
            'permission'
        ]),
        ...mapState([
            'isMobile'
        ]),
        ...mapState([
            'showBottomSheet'
        ]),
        filteredOptions () {
            return this.options.filter(option => {
                return option.condition
            })
        }
    }
}
</script>