<template>
    <div class="w-full outline-none" @keyup.ctrl.alt.78="createNewPost()" tabindex="0" autofocus>
        <div class="flex">
            <Sidebar class="pt-16 z-40" v-if="!staticAuth"/>
            <div class="flex-1 h-screen overflow-y-scroll pt-16">
                <Appbar/>
                <transition name="router-fade" mode="out-in">
                    <router-view></router-view>
                </transition>
            </div>
        </div>
        <div class="absolute bottom-0 w-full overflow-hidden">
            <Notification/>
            <BottomSheet/>
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
        Notification
    },
    methods: {
        createNewPost () {
            this.$router.push({ path: `/c/${this.currentCollection.id}/p/create` })
        },
        isMobile () {
            let media = window.matchMedia('(max-width: 768px)')
            this.$store.commit('isMobile', media.matches)
            media.addEventListener("change", this.isMobile, { once: true })
        }
    },
    computed: {
        ...mapState('collection', [
            'currentCollection'
        ]),
        ...mapState('auth', [
            'staticAuth'
        ])
    },
    created () {
        this.isMobile()
    }
}
</script>
