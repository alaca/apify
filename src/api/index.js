import axios from 'axios'

export default axios.create({
  baseURL: process.env.REACT_APP_API_URL,
  headers: {
    //Authentication: 'Authorization ' + sessionStorage.getItem('jwt'),
    'Content-Type': 'application/json',
  },
  params: {
    api_key: '12345'
  }
})