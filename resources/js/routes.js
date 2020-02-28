import App from './components/AppComponent'
import Post from './components/Post'
import Collection from './components/CollectionComponent'
import CreateCollection from './components/CreateCollectionComponent'
import Profile from './components/ProfileComponent'
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
                path: '/users/me',
                name: 'profile',
                component: Profile,
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
