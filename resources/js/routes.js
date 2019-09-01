import Example from './components/ExampleComponent'
import Login from './components/LoginComponent'

export default [
    {
        path: '/',
        name: 'example',
        component: Example,
        meta: {
            requiresAuth: true
        }
    },
    {
        path: '/login',
        name: 'Login',
        component: Login
    }
]
