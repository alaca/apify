export default (state, action ) => {

    switch( action.type ) {
        case 'login':
            return  { ...state, isLoggedIn: action.payload }
        default:
            return state
    }
    
}

