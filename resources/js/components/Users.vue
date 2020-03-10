<template>
    <div class="mt-16 ml-20 mr-8 max-w-4xl">
        <div class="flex mb-10">
            <div class="flex-1">
                <h1 class="text-3xl font-medium text-gray-800">Users</h1>
            </div>
            <div>
                <button class="button">
                    <svg-vue class="button-icon" icon="zondicons/add-outline"/>
                    Create
                </button>
            </div>
        </div>
        <table class="users table w-full">
            <tr class="table-row">
                <th class="table-cell px-6 py-3 text-left text-gray-800">Name</th>
                <th class="table-cell px-6 py-3 text-left text-gray-800">Email</th>
            </tr>
            <router-link :to="'/users/' + user.id" v-for="user in users" :key="user.id"
                tag="tr" class="table-row cursor-pointer">
                <td class="px-6 py-3 table-cell">{{ user.name }}</td>
                <td class="px-6 py-3 table-cell">{{ user.email }}</td>
            </router-link>
        </table>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'Profile',
    data () {
        return {
            users: null
        }
    },
    methods: {
    },
    computed: {
    },
    created () {
        axios.get('/api/users/')
            .then(response => {
                this.users = response.data.data
            }).catch(error => {
                this.error = error
            })
    }
}
</script>

<style lang="scss">
    table.users {
        font-family: Noto Sans, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        .table-row:nth-child(even) {
            @apply bg-gray-200;
        }
        td {
            transition: background-color 0.2s;
        }
        .table-row:hover td {
            @apply bg-orange-200;
        }
    }
</style>