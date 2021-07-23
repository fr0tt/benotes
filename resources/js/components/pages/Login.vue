<template>

<div class="flex justify-center mt-32 h-full">
    <div class="w-full max-w-2xl">

        <form @submit.prevent="authenticate" class="bg-white shadow-md px-12 pt-12 pb-16 mb-4">

            <div class="mb-4">
                <svg-vue class="w-16 block m-auto" icon="logo_64x64"/>
                <span class="block my-2 text-2xl text-orange-600 font-semibold text-center align-middle">
                    Benotes
                </span>
            </div>

            <div class="mb-8">
                <label class="label" for="email">Email</label>
                <input class="input" v-model="email" type="email" name="email"
                    placeholder="Email Address" autofocus required/>
            </div>

            <div class="mb-8">
                <label class="label" for="password">Password</label>
                <input class="input tracking-tighter" v-model="password" type="password" name="password"
                    placeholder="Password" required/>
            </div>

            <div class="mb-12">
                <p v-if="error" class="text-red-500 text-sm italic">{{ error }}</p>
            </div>

            <div class="flex items-center justify-between">
                <button class="button" type="submit">Login</button>
                <router-link to="/forgot" class="inline-block align-baseline 
                    font-semibold text-sm text-orange-600 hover:text-orange-700">
                    Forgot Password?
                </router-link>
            </div>
        </form>

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
                    this.$cookie.set('token', token, { expires: 14, samesite: 'Strict' })
                    axios.defaults.headers.common = { 'Authorization': `Bearer ${token}` }
                    this.$store.dispatch('auth/fetchAuthUser')
                    this.$router.push({ path: '/' })
                })
                .catch(error => {
                    if (error.response.data.length < 200) {
                        this.error = error.response.data
                    }
                })
        }
    }

}
</script>
<style>
    .bg-gray-input {
        background-color: #ececec;
    }
</style>
