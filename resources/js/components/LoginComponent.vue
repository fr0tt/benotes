<template>

<div class="flex justify-center mt-32 h-full">
    <div class="w-full max-w-2xl">

        <form @submit.prevent="authenticate" class="bg-white shadow-md rounded px-12 py-16 mb-4">

            <div class="mb-8">
                <label class="label" for="email">Email</label>
                <input type="email" class="input" v-model="email" placeholder="Email Address" autocomplete required>
            </div>

            <div class="mb-8">
                <label class="label" for="password">Password</label>
                <input type="password" class="input" v-model="password" placeholder="Password" required>
            </div>

            <div class="mb-12">
                <p v-if="error" class="text-red-500 text-xs italic">{{ error }}</p>
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white text-lg font-bold py-2 px-6
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
                    this.$store.dispatch('auth/fetchAuthUser')
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
        @apply appearance-none w-full py-3 px-3 text-gray-700 bg-gray-input leading-tight;
    }
    .input:focus {
        @apply outline-none shadow-outline;
    }
    label.label {
        @apply block text-gray-600 font-semibold text-sm uppercase mb-2;
    }
    .bg-gray-input {
        background-color: #ececec;
    }
</style>
