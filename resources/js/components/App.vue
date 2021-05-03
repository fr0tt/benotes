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
        <BottomSheet/>
    </div>
</template>

<script>
import { mapState } from 'vuex'
import Sidebar from './Sidebar.vue'
import Appbar from './Appbar.vue'
import BottomSheet from './BottomSheet.vue'

export default {
    components: {
        Sidebar,
        Appbar,
        BottomSheet
    },
    methods: {
        createNewPost () {
            this.$router.push({ path: `/c/${this.currentCollection.id}/p/create` })
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
        this.$store.dispatch('checkDevice')
    }
}
</script>
