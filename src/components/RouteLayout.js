import React from 'react';
import { Route } from 'react-router-dom'
import HomeLayout from './AppLayout';

const RouteLayout = ({ component: Component, ...rest }) => {

  return (
    <Route {...rest} render={matchProps => (
      <HomeLayout>
        <Component {...matchProps} />
      </HomeLayout>
    )} />
  )
};

export default RouteLayout;