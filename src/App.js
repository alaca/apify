import React, { useReducer } from 'react'

import {
  BrowserRouter as Router,
  Route,
  Switch,
  Redirect
} from 'react-router-dom'

// Components
import RouteLayout from './components/RouteLayout' 

// Route Components
import Login from './routes/Login'
import Home from './routes/Home'

// Styling
import 'semantic-ui-css/semantic.min.css'
import './style.css'

import AppState from './context'
import AppReducer from './reducers/AppReducer'


const App = () => {

  const [ state, dispatch ] = useReducer( AppReducer, [] )

  return (
    <AppState.Provider value={[state, dispatch]}>
      <Router>
          <Switch>
            <Route exact path="/">
              <Redirect to="/login" />
            </Route>
            <Route path="/login" component={Login} />
            <RouteLayout path="/home" component={Home} />
          </Switch>
      </Router>
    </AppState.Provider>
  )

}

export default App;
