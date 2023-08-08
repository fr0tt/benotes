import App from './components/App'
import Post from './components/pages/Post'
import Collection from './components/pages/Collection'
import EditCollection from './components/pages/EditCollection'
import Search from './components/pages/Search'
import Restore from './components/pages/Restore'
import Users from './components/pages/Users'
import User from './components/pages/User'
import Tags from './components/pages/Tags'
import EditTag from './components/pages/EditTag'
import Tag from './components/pages/Tag'
import Login from './components/pages/Login'
import Forgot from './components/pages/Forgot'
import Reset from './components/pages/Reset'
import ImExport from './components/pages/ImExport'
import ImportBookmarks from './components/pages/ImportBookmarks'
import ExportBookmarks from './components/pages/ExportBookmarks'

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
                    permission: 7,
                },
                meta: {
                    isHome: true,
                    authUser: true,
                },
            },
            {
                path: 'c/create',
                component: EditCollection,
                props: {
                    isNew: true,
                },
                meta: {
                    authUser: true,
                },
            },
            {
                path: 'c/:id/edit',
                component: EditCollection,
                props: (route) => ({
                    id: route.params.id,
                    isNew: false,
                }),
                meta: {
                    authUser: true,
                },
            },
            {
                path: 'c/:collectionId',
                name: 'collection',
                component: Collection,
                props: (route) => ({
                    collectionId: parseInt(route.params.collectionId),
                    permission: 7,
                }),
                meta: {
                    authUser: true,
                },
            },
            {
                path: 'c/:collectionId/p/create',
                component: Post,
                props: (route) => ({
                    collectionId: parseInt(route.params.collectionId),
                    shareTargetApi: {
                        headline: route.query.title,
                        text: route.query.text,
                        url: route.query.url,
                    },
                }),
                meta: {
                    authUser: true,
                },
            },
            {
                path: 'search',
                name: 'search',
                component: Search,
                meta: {
                    authUser: true,
                },
            },
            {
                path: 'restore',
                name: 'restore',
                component: Restore,
                meta: {
                    authUser: true,
                },
            },
            {
                path: 'import',
                name: 'imexport',
                component: ImExport,
                meta: {
                    authUser: true,
                },
            },
            {
                path: 'import/bookmarks',
                component: ImportBookmarks,
                meta: {
                    authUser: true,
                },
            },
            {
                path: 'export/bookmarks',
                component: ExportBookmarks,
                meta: {
                    authUser: true,
                },
            },
            {
                path: 'p/:id',
                name: 'post',
                component: Post,
                props: (route) => ({
                    id: parseInt(route.params.id),
                }),
                meta: {
                    authUser: true,
                    staticAuth: true,
                },
            },
            {
                path: '/users',
                name: 'users',
                component: Users,
                meta: {
                    authUser: true,
                },
            },
            {
                path: '/users/create',
                component: User,
                props: {
                    isNew: true,
                },
                meta: {
                    authUser: true,
                },
            },
            {
                path: '/users/:id',
                name: 'user',
                component: User,
                props: (route) => ({
                    id: route.params.id,
                    isNew: false,
                }),
                meta: {
                    authUser: true,
                },
            },
            {
                path: '/tags',
                name: 'tags',
                component: Tags,
                meta: {
                    authUser: true,
                },
            },
            {
                path: '/tags/create',
                component: EditTag,
                props: {
                    isNew: true,
                },
                meta: {
                    authUser: true,
                },
            },
            {
                path: '/tags/:id/edit',
                component: EditTag,
                props: (route) => ({
                    id: route.params.id,
                    isNew: false,
                }),
                meta: {
                    authUser: true,
                },
            },
            {
                path: '/tags/:id',
                name: 'tag',
                component: Tag,
                props: (route) => ({
                    id: Number(route.params.id),
                }),
                meta: {
                    authUser: true,
                },
            },
            {
                path: '/s',
                name: 'share',
                component: Collection,
                props: (route) => {
                    if (store.state.auth.staticAuth) {
                        return {
                            collectionId: store.state.auth.staticAuth.collection_id,
                            permission: store.state.auth.staticAuth.permission,
                        }
                    } else {
                        return {
                            collectionId: null,
                            permission: 0,
                        }
                    }
                },
                meta: {
                    staticAuth: true,
                },
            },
        ],
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
    },
    {
        path: '/forgot',
        name: 'Forgot',
        component: Forgot,
    },
    {
        path: '/reset',
        name: 'Reset',
        component: Reset,
        props: (route) => ({
            email: route.query.email,
            token: route.query.token,
        }),
    },
]
