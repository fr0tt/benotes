<template>
    <form @submit.prevent="update()" class="mt-20 lg:mx-20 mx-10">
        <div class="max-w-lg">

            <div class="mb-8">
                <h2 class="text-3xl font-medium text-gray-800">{{ name }}</h2>
                <p class="text-right italic">{{ name }} is registered since {{ created_at }}</p>
            </div>

            <div class="mb-8">
                <label class="label">Name</label>
                <input v-model="name" placeholder="Name" type="text" class="input" required/>
            </div>
            <div class="mb-8">
                <label class="label">Email</label>
                <input v-model="email" placeholder="Email" type="email" class="input" required/>
            </div>
            <div class="mb-8">
                <label class="label">Old Password</label>
                <input v-model="password_old" placeholder="Old Password" type="password" class="input"/>
            </div>
            <div class="mb-8">
                <label class="label">New Password</label>
                <input v-model="password_new" placeholder="New Password" type="password" class="input"/>
            </div>

            <div class="mt-8 float-right">
                <button class="button">
                    <svg-vue class="button-icon" icon="zondicons/checkmark-outline"/>
                    Save
                </button>
            </div>

            <p v-if="error" class="text-red-500 mt-4">{{ error }}</p>

        </div>
    </form>
</template>

<script>
import axios from 'axios'
import { mapState } from 'vuex'
export default {
    name: 'User',
    props: ['id'],
    data () {
        return {
            name: null,
            email: null,
            password_old: null,
            password_new: null,
            error: null
        }
    },
    methods: {
        update () {

            if (this.name === this.authUser.name && this.email === this.authUser.email &&
                this.password_old === null && this.password_new === null) {
                return
            }

            this.error = ''
            const params = {}
            if (this.name !== this.authUser.name) params.name = this.name
            if (this.email !== this.authUser.email) params.email = this.email
            if (this.password_old) params.password_old = this.password_old
            if (this.password_new) params.password_new = this.password_new

            axios.patch('/api/users/' + this.authUser.id, params)
                .then(response => {
                    const user = response.data.data
                    this.$store.dispatch('auth/setAuthUser', user)
                }).catch(error => {
                    this.error = error
                })

        }
    },
    computed: {
        ...mapState('auth', [
            'authUser'
        ]),
        created_at () {
            return this.authUser.created_at.substring(0, 10).replace(/-/g, '/')
        }
    },
    created () {
        axios.get('/api/users/' + this.id)
            .then(response => {
                const user = response.data.data
                this.name = user.name
                this.email = user.email
            }).catch(error => {
                this.error = error
            })
    }
}
</script>

<style lang="scss" scoped>
    .label {
        @apply block uppercase text-gray-600 font-medium text-sm tracking-wide;
    }
    .input {
        @apply w-full text-lg text-gray-800 font-medium bg-gray-300 outline-none py-2 px-4 my-1;
    }
</style>