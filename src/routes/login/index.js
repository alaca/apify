import React from 'react'
import { 
  Button, 
  Form, 
  Grid, 
  GridColumn, 
  Card, 
  Divider, 
  Container 
} from 'semantic-ui-react'
import { Link } from 'react-router-dom'

class Login extends React.Component {

  render() {
    return (
      <Grid centered columns={6}>
        <GridColumn>

          <Card raised>

            <Card.Content>

              <Form>

                <Form.Field>
                  <label>Email</label>
                  <input placeholder="Enter email" />
                </Form.Field>

                <Form.Field>
                  <label>Password</label>
                  <input type="password" placeholder="Enter password" />
                </Form.Field>
                <Button type="submit">Sign in</Button>
              </Form>

              <Divider></Divider>

              <Container textAlign="right">
                <Link to="/lost-password">Forgot password?</Link>
              </Container>

            </Card.Content>

          </Card>

        </GridColumn>
      </Grid>
    )
  }

}

export { Login };