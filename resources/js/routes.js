import App from './components/AppComponent'
import Post from './components/Post'
import Collection from './components/CollectionComponent'
import CreateCollection from './components/CreateCollectionComponent'
import Users from './components/Users'
import User from './components/User'
import Login from './components/LoginComponent'

export default [
    {
        path: '/',
        component: App,
        meta: {
            requiresAuth: true
        },
        children: [
            {
                path: '',
                component: Collection,
                props: true,
                meta: {
                    requiresAuth: true
                }
            },
            {
                path: 'c/create',
                component: CreateCollection,
                meta: {
                    requiresAuth: true
                }
            },
            {
                path: 'c/:id',
                name: 'collection',
                component: Collection,
                props: true,
                meta: {
                    requiresAuth: true
                }
            },
            {
                path: 'p/create',
                component: Post,
                meta: {
                    requiresAuth: true
                }
            },
            {
                path: 'p/:id',
                name: 'post',
                component: Post,
                props: true,
                meta: {
                    requiresAuth: true
                }
            },
            {
                path: '/users',
                name: 'users',
                component: Users,
                meta: {
                    requiresAuth: true
                }
            },
            {
                path: '/users/:id',
                name: 'user',
                component: User,
                props: true,
                meta: {
                    requiresAuth: true
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
