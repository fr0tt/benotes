<template>

<div class="flex justify-center mt-32 h-full">
    <div class="w-full max-w-2xl">

        <form @submit.prevent="resetPassword" class="bg-white shadow-md px-12 pt-12 pb-16 mb-4">

            <div class="mb-4">
                <svg-vue class="w-16 block m-auto" icon="logo_64x64"/>
                <span class="block my-2 text-2xl text-orange-600 font-semibold text-center align-middle">
                    Benotes
                </span>
            </div>

            <div class="mb-8">
                <label class="label" for="password">New Password</label>
                <input class="input tracking-tighter" v-model="password" type="password" name="password"
                    placeholder="Password" required/>
            </div>

            <div class="mb-8">
                <label class="label" for="password_confirmation">Confirm Password</label>
                <input class="input tracking-tighter" v-model="password_confirmation" 
                    type="password" name="password_confirmation"
                    placeholder="Password" required/>
            </div>

            <div class="mb-12">
                <p v-if="error" class="text-red-500 text-sm italic">{{ error }}</p>
            </div>

            <div class="flex items-center justify-between">
                <button class="button" type="submit">Reset</button>
            </div>
        </form>

    </div>
</div>

</template>
<script>
import axios from 'axios'
export default {
    props: {
        token: String,
        email: String
    },
    data () {
        return {
            password: '',
            password_confirmation: '',
            error: ''
        }
    },
    methods: {
        resetPassword () {
            if (this.token == '' || this.email == '') {
                this.error = 'Your url needs to contain a token and an email address.'
                return
            }
            axios.post('/api/auth/reset', {
                email: this.email,
                token: this.token,
                password: this.password,
                password_confirmation: this.password_confirmation
            })
                .then(response => {
                    this.$router.push({ path: '/' })
                })
                .catch(error => {
                    if (error.response.data.error) {
                        this.error = error.response.data.error
                    } else if (error.response.data.password) {
                        this.error = error.response.data.password.toString()
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
