export default {
    namespaced: true,
    state: {
        currentRoute: null
    },
    mutations: {
        setCurrentRoute (state, route) {
            state.currentRoute = route
        }
    },
    actions: {
        setCurrentRoute (context, route) {
            context.commit('setCurrentRoute', route)
        }
    }
}
