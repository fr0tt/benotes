<template>
    <div class="flex w-full">
        <div class="max-w-xs w-1/6">
            <Sidebar/>
        </div>
        <div class="w-5/6 pr-6">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
import Sidebar from './SidebarComponent.vue'

    export default {
        components: { 
            Sidebar
        },
        computed: {
            authUser () {
                return this.$store.state.auth.authUser
            }
        }
    }
</script>
<style lang="scss">
    button {
        transition: color, background-color 0.2s;
    }
</style>
