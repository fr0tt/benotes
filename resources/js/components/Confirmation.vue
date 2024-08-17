<template>
    <transition name="fade">
        <div
            ref="confirmationModal"
            class="fixed top-0 left-0 w-full h-full bg-half-transparent z-50">
            <div
                class="w-5/6 max-w-xl m-auto mt-10 border-gray-400 rounded-lg bg-gray-100 theme__modal">
                <div class="py-8 mx-6">
                    <h2 class="mb-4 mx-1 text-3xl text-orange-600 font-bold">
                        Are you really sure ?
                    </h2>
                    <p class="mb-8 mx-1 gray-600 italic leading-tight">
                        {{ description }}
                    </p>
                    <input v-model="confirmationInput" class="input" />
                    <button class="button block mt-4 ml-auto mr-0" @click="execute">
                        {{ confirmationText }}
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>
<script>
export default {
    props: {
        action: {
            type: Function,
            required: true,
        },
        hide: {
            type: Function,
            required: true,
        },
        description: {
            type: String,
            required: true,
        },
        confirmationText: {
            type: String,
            required: true,
        },
    },
    data() {
        return {
            confirmationInput: '',
        }
    },
    created() {
        document.querySelector('#app').addEventListener('click', this.hideEvent, true)
    },
    methods: {
        execute() {
            if (
                this.confirmationInput.toLowerCase() ===
                this.confirmationText.toLowerCase()
            ) {
                this.action()
                this.hide()
            }
        },
        hideEvent(event) {
            if (this.$refs.confirmationModal === event.target) {
                this.hide()
                document
                    .querySelector('#app')
                    .removeEventListener('click', this.hideEvent, true)
            }
        },
    },
}
</script>
<style lang="scss">
.bg-half-transparent {
    background-color: rgba(0, 0, 0, 0.5);
}
.transition-color-0\.2 {
    transition: color 0.2s;
    -webkit-transition: color 0.2s;
    -moz-transition: color 0.2s;
}
</style>
