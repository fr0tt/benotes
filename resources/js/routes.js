import App from './components/App'
import Post from './components/pages/Post'
import Collection from './components/pages/Collection'
import EditCollection from './components/pages/EditCollection'
import Users from './components/pages/Users'
import User from './components/pages/User'
import Login from './components/pages/Login'
import Forgot from './components/pages/Forgot'
import Reset from './components/pages/Reset'

import store from './store'

export default [
    {
        path: '/',
        component: App,
        children: [
            {
                path: '/',
                component: Collection,
                props: {
                    collectionId: 0,
                    permission: 7
                },
                meta: {
                    isHome: true,
                    authUser: true
                },
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
                path: 'c/:collectionId',
                name: 'collection',
                component: Collection,
                props: (route) => ({
                    collectionId: parseInt(route.params.collectionId),
                    permission: 7
                }),
                meta: {
                    authUser: true
                }
            },
            {
                path: 'c/:collectionId/p/create',
                component: Post,
                props: (route) => ({
                    collectionId: parseInt(route.params.collectionId)
                }),
                meta: {
                    authUser: true
                }
            },
            {
                path: 'p/:id',
                name: 'post',
                component: Post,
                props: (route) => ({
                    id: parseInt(route.params.id)
                }),
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
                            collectionId: store.state.auth.staticAuth.collection_id,
                            permission: store.state.auth.staticAuth.permission
                        }
                    } else {
                        return {
                            collectionId: null,
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
    },
    {
        path: '/forgot',
        name: 'Forgot',
        component: Forgot
    },
    {
        path: '/reset',
        name: 'Reset',
        component: Reset,
        props: route => ({
            email: route.query.email,
            token: route.query.token
        })
    }
]
