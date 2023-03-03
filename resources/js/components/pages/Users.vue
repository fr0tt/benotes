<template>
    <div class="px-4 md:px-16 py-4 md:pt-16 max-w-4xl">
        <div class="flex mb-10">
            <div class="flex-1">
                <h1 class="text-3xl font-medium text-gray-800">Users</h1>
            </div>
        </div>
        <table class="users table w-full">
            <tr class="table-row theme__users__list_item">
                <th class="table-cell px-6 pt-3 pb-6 text-left text-gray-800">Name</th>
                <th class="table-cell px-6 pt-3 pb-6 text-left text-gray-800">Email</th>
                <th></th>
            </tr>
            <router-link
                v-for="user in users"
                :key="user.id"
                :to="'/users/' + user.id"
                tag="tr"
                class="table-row cursor-pointer theme__users__list_item">
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td class="text-right">
                    <span
                        v-if="user.permission == 255"
                        class="px-2 py-1 bg-gray-800 text-white border border-white"
                        >Owner</span
                    >
                </td>
            </router-link>
        </table>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    data() {
        return {
            users: null,
        }
    },
    created() {
        axios
            .get('/api/users/')
            .then((response) => {
                this.users = response.data.data
            })
            .catch((error) => {
                this.error = error
            })
        this.$store.dispatch('appbar/setAppbar', {
            title: 'Users',
            button: {
                label: 'Create',
                callback: this.create,
                icon: 'add',
            },
        })
    },
    methods: {
        create() {
            this.$router.push('/users/create')
        },
    },
}
</script>

<style lang="scss">
.users {
    font-family: Inter, Noto Sans, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
        Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    .table-row:nth-child(even) {
        @apply bg-gray-200;
    }
    td {
        @apply px-6 py-3 table-cell;
        transition: background-color 0.2s;
    }
    .table-row:hover td {
        @apply bg-orange-200;
    }
}
</style>
