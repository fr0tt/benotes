<template>

<div class="flex justify-center items-center h-full">
    <div class="w-full max-w-xs">

        <form @submit.prevent="authenticate" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                <input type="email" class="input" v-model="email" placeholder="Email Address" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                <input type="password" class="input" v-model="password" placeholder="Password" required>
            </div>

            <div class="mb-6">
                <p v-if="error" class="text-red-500 text-xs italic">{{ error }}</p>
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded
                    focus:outline-none focus:shadow-outline" type="submit">
                    Sign In
                </button>
                <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="#">
                    Forgot Password?
                </a>
            </div>
        </form>

        <p class="text-center text-gray-500 text-xs">
            &copy;2019 Acme Corp. All rights reserved.
        </p>

    </div>
</div>

</template>
<script>
import axios from 'axios'
export default {
    data () {
        return {
            email: '',
            password: '',
            error: ''
        }
    },
    methods: {
        authenticate () {
            axios.post('/api/auth/login', {
                email: this.email,
                password: this.password
            })
                .then(response => {
                    const token = response.data.data.token.access_token
                    this.$cookie.set('token', token)
                    axios.defaults.headers.common = { 'Authorization': `Bearer ${token}` }
                    this.$store.dispatch('fetchAuthUser')
                    this.$router.push({ path: '/' })
                })
                .catch(error => {
                    this.error = error.response.data
                })
        }
    }

}
</script>
<style lang="scss" scoped>
    .input {
        @apply shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight;
    }
    .input:focus {
        @apply outline-none shadow-outline;
    }
</style>
