import React from 'react';
import {
  Grid,
} from 'semantic-ui-react'


const AppLayout = ({ children }) => {
  
  return (
    <Grid columns="16" stackable>
      <Grid.Row>
        <Grid.Column width="3">
          livo
        </Grid.Column>
        <Grid.Column>

            Desno

        </Grid.Column>
      </Grid.Row>
    </Grid>
  )
}

export default AppLayout;