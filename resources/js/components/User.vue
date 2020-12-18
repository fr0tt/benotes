<template>
    <form v-if="isNew" @submit.prevent="create()" class="mt-16 lg:mx-20 mx-10 pb-8">
        <div class="max-w-lg">

            <div class="mb-8">
                <h1 class="text-3xl font-medium text-gray-800">Create new User</h1>
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
                <label class="label">Password</label>
                <input v-model="password" placeholder="Password" type="password" class="input"/>
            </div>

            <p v-if="error" class="text-red-500 mt-4">{{ error }}</p>

        </div>
    </form>

    <div v-else class="mt-20 lg:mx-20 mx-10 pb-8">
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

            <p v-if="error" class="text-red-500 mt-4">{{ error }}</p>

        </div>
    </div>
</template>

<script>
import axios from 'axios'
import { mapState } from 'vuex'
export default {
    name: 'User',
    props: ['id', 'isNew'],
    data () {
        return {
            name: null,
            email: null,
            password: null,
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

            axios.patch('/api/users/' + this.id, params)
                .then(response => {
                    const user = response.data.data
                    this.$store.dispatch('auth/setAuthUser', user)
                }).catch(error => {
                    this.error = error
                })
        },
        create () {
            axios.post('/api/users', {
                name: this.name,
                email: this.email,
                password: this.password
            })
                .then(response => {
                    this.$router.push({ path: '/users' })
                }).catch(error => {
                    console.log(error)
                    this.error = error
                })
        },
        del () {
            axios.delete('/api/users/' + this.id)
                .then(response => {
                    this.$router.push({ path: '/users' })
                }).catch(error => {
                    console.log(error)
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
        if (this.isNew) {
            this.$store.dispatch('appbar/setAppbar', {
                title: 'Create User',
                button: {
                    label: 'Create',
                    callback: this.create,
                    icon: 'add'
                } 
            })
        } else {
            axios.get('/api/users/' + this.id)
                .then(response => {
                    const user = response.data.data
                    this.name = user.name
                    this.email = user.email
                }).catch(error => {
                    this.error = error
                })
            this.$store.dispatch('appbar/setAppbar', {
                title: 'Edit User',
                button: {
                    label: 'Save',
                    callback: this.update,
                    icon: 'checkmark'
                },
                options: [{
                    label: 'Delete',
                    longLabel: 'Delete User',
                    color: 'red',
                    icon: 'delete',
                    callback: this.del,
                    condition: true
                }] 
            })
        }
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