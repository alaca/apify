import React, { 
  useState, 
  useContext
} from 'react'

import {
  Button,
  Form,
  Grid,
  Card,
  Divider,
  Container,
  Message
} from 'semantic-ui-react'

import { Link } from 'react-router-dom'

import AppState from '../context'
import API from '../api'

export const Login = (props) => {
  
  // context
  const [ state, dispatch ] = useContext( AppState )
  // state
  const [isLoading, setisLoading] = useState(false);
  const [email, setEmail] = useState('');
  const [emailError, setEmailError] = useState(false);
  const [passwordError, setPasswordError] = useState(false);
  const [password, setPassword] = useState('');
  const [formError, setFormError] = useState([]);

  // Logout user if user is already logged in
  if ( state.isLoggedIn ) {

    dispatch({
      type: 'login',
      payload: false
    })

  }


  const validateEmail = () => {
    const status = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
    setEmailError(!status)
    return status
  }

  const validatePassword = () => {
    const status = (password.length < 6)
    setPasswordError(status)
    return !status
  }


  const loginUser = () => {

    setFormError([])

    const validEmail = validateEmail()
    const validPassword = validatePassword()

    if ( ! validEmail || ! validPassword ) 
      return false;
    
    setisLoading( true );

    API.post('/login', {
      username: email,
      password: password
    }).then( response => {

      sessionStorage.setItem('jwt', response.data.token)

      dispatch({
        type: 'login',
        payload: true
      })

     props.history.push('/home') 

    }).catch(err => {

      setisLoading( false );

      if ( err.response && err.response.data ) {

        if ( Array.isArray(err.response.data.message) ) {
          setFormError(err.response.data.message)
        } else {
          setFormError([err.response.data.message])
        }

      }

      return false;

    })

  }


  const showErrorMessages = () => {

    if ( formError.length > 0 ) {

      let errors = formError.map( (error,i) => 

        <Message negative key={i}>
          {error}
        </Message>
      )
  
      return <Container style={{marginTop: '10px'}}>{errors}</Container>

    }

  }


  return (
    <Grid columns="3" doubling centered container>

      <Grid.Row centered verticalAlign="middle">

        <Grid.Column>
          
          <Card raised>

            <Card.Content>

              <Form loading={isLoading}>

                <Form.Field>
                  <label>Email</label>
                  <Form.Input
                    placeholder="Enter email"
                    error={emailError} 
                    value={email} 
                    onChange={ e => {
                      setEmail( e.target.value )
                      setEmailError(false)
                    }}
                  />
                </Form.Field>

                <Form.Field>
                  <label>Password</label>
                  <Form.Input 
                    type="password" 
                    error={passwordError} 
                    placeholder="Enter password" 
                    value={password} 
                    onChange={ e => {
                      setPassword( e.target.value )
                      setPasswordError(false)
                    }} 
                  />
                </Form.Field>

                <Button color="blue" type="submit" onClick={ loginUser }>Sign in</Button>

                <Divider></Divider>

                <Container textAlign="right">
                  <Link to="/lost-password">Lost password?</Link>
                </Container>

              </Form>

              { showErrorMessages() }

            </Card.Content>

          </Card>

        </Grid.Column>

      </Grid.Row>

    </Grid>

  )


}

export default Login;