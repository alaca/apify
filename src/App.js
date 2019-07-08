import React, { useReducer } from 'react'

import {
  BrowserRouter as Router,
  Route,
  Switch,
  Redirect
} from 'react-router-dom'

// Components
import RouteLayout from './components/RouteLayout' 

// Routes
import Login from './routes/Login'
import Home from './routes/Home'

import 'semantic-ui-css/semantic.min.css'
import './style.css'
import AppReducer from './reducers/AppReducer';

import AppState from './context/AppState'


const App = () => {

  const [ state, dispatch ] = useReducer( AppReducer, [] )

  return (
    <AppState.Provider value={[state, dispatch]}>
      <Router basename="/apify/build">
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
