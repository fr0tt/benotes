<template>
    <div class="post-loader">
        <div
            v-if="showSpinner"
            class="absolute bottom-0 pb-6 left-0 right-0 text-center animate-pulse text-gray-700">
            <svg-vue
                icon="remix/refresh-line"
                class="button-icon w-6 inline-block align-top animate-spin fill-current" />
            <span class="ml-1">loading...</span>
        </div>
    </div>
</template>
<script>
import { mapState, mapGetters } from 'vuex'
export default {
    props: {
        collectionId: Number,
        isArchived: Boolean,
    },
    data() {
        return {
            showSpinner: false,
        }
    },
    computed: {
        ...mapState('post', ['isCompletelyLoaded']),
        ...mapGetters('post', ['lastId']),
    },
    mounted() {
        window.addEventListener('scroll', this.handleScroll)
    },
    beforeDestroy() {
        window.removeEventListener('scroll', this.handleScroll)
    },
    methods: {
        handleScroll() {
            if (this.showSpinner || this.isCompletelyLoaded) {
                return
            }
            if (!this.lastId) {
                return
            }
            const rect = document.querySelector('.post-loader').getBoundingClientRect()
            if (rect.bottom < (window.innerHeight || document.documentElement.clientHeight) + 80) {
                this.showSpinner = true
                let data = {
                    collectionId: this.collectionId,
                }
                if (this.isArchived) {
                    data = {
                        isArchived: true,
                    }
                }
                this.$store.dispatch('post/fetchMorePosts', data).then(() => {
                    this.showSpinner = false
                })
            }
        },
    },
}
</script>
