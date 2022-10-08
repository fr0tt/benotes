<template>
    <div class="w-full outline-none" tabindex="0" autofocus @keyup.ctrl.alt.78="createNewPost()">
        <Appbar />
        <div>
            <Sidebar v-if="!staticAuth" />
            <div
                id="view"
                class="pt-16 view-slide-transition"
                :class="{ 'xl:pl-1/6 lg:pl-64 md:pl-48': showSidebar }">
                <transition name="router-fade" mode="out-in">
                    <router-view />
                </transition>
            </div>
        </div>
        <div class="absolute bottom-0 w-full overflow-hidden">
            <Notification />
            <BottomSheet />
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex'
import Sidebar from './Sidebar.vue'
import Appbar from './Appbar.vue'
import BottomSheet from './BottomSheet.vue'
import Notification from './Notification.vue'

export default {
    components: {
        Sidebar,
        Appbar,
        BottomSheet,
        Notification,
    },
    computed: {
        ...mapState('collection', ['currentCollection']),
        ...mapState('auth', ['staticAuth']),
        ...mapState(['showSidebar']),
    },
    created() {
        this.isMobile()
    },
    methods: {
        createNewPost() {
            this.$router.push({ path: `/c/${this.currentCollection.id}/p/create` })
        },
        isMobile() {
            let media = window.matchMedia('(max-width: 768px)')
            this.$store.commit('isMobile', media.matches)
            media.addEventListener('change', this.isMobile, { once: true })
        },
    },
}
</script>

<style lang="scss">
.view-slide-transition {
    transition: padding 0.3s;
}

@screen xl {
    .xl\:pl-1\/6 {
        padding-left: 16.66%;
    }
}
</style>
