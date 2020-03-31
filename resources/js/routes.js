import App from './components/App'
import Post from './components/Post'
import Collection from './components/Collection'
import EditCollection from './components/EditCollection'
import Users from './components/Users'
import User from './components/User'
import Login from './components/Login'

import store from './store'

export default [
    {
        path: '/',
        component: App,
        children: [
            {
                path: '',
                component: Collection,
                props: {
                    id: 0,
                    permission: 7
                },
                meta: {
                    authUser: true
                }
            },
            {
                path: 'c/create',
                component: EditCollection,
                props: {
                    isNew: true
                },
                meta: {
                    authUser: true
                }
            },
            {
                path: 'c/:id/edit',
                component: EditCollection,
                props: (route) => ({
                    id: route.params.id,
                    isNew: false
                }),
                meta: {
                    authUser: true
                }
            },
            {
                path: 'c/:id',
                name: 'collection',
                component: Collection,
                props: (route) => ({
                    id: route.params.id,
                    permission: 7
                }),
                meta: {
                    authUser: true
                }
            },
            {
                path: 'c/:collectionId/p/create',
                component: Post,
                props: true,
                meta: {
                    authUser: true
                }
            },
            {
                path: 'p/:id',
                name: 'post',
                component: Post,
                props: true,
                meta: {
                    authUser: true,
                    staticAuth: true
                }
            },
            {
                path: '/users',
                name: 'users',
                component: Users,
                meta: {
                    authUser: true
                }
            },
            {
                path: '/users/create',
                component: User,
                props: {
                    isNew: true
                },
                meta: {
                    authUser: true
                }
            },
            {
                path: '/users/:id',
                name: 'user',
                component: User,
                props: (route) => ({
                    id: route.params.id,
                    isNew: false
                }),
                meta: {
                    authUser: true
                }
            },
            {
                path: '/s',
                name: 'share',
                component: Collection,
                props: (route) => {
                    if (store.state.auth.staticAuth) {
                        return {
                            id: store.state.auth.staticAuth.collection_id,
                            permission: store.state.auth.staticAuth.permission
                        }
                    } else {
                        return {
                            id: null,
                            permission: 0
                        }
                    }
                },
                meta: {
                    staticAuth: true
                }
            }
        ]
    },
    {
        path: '/login',
        name: 'Login',
        component: Login
    }
]
