import React from "react"
import {
  BrowserRouter as Router,
  Route
} from "react-router-dom"


// Routes
import {
  Home,
  Login
} from "./routes"

import 'semantic-ui-css/semantic.min.css'

const App = () => {
  return (
    <Router>
      <Route component={Home} path="/" exact />
      <Route component={Login} path="/login" />
    </Router>
  );
}

export default App;
