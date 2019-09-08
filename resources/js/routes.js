import App from './components/AppComponent'
import Collection from './components/CollectionComponent'
import CreateCollection from './components/CreateCollectionComponent'
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
            }
        ]
    },
    {
        path: '/login',
        name: 'Login',
        component: Login
    }
]
