import axios from 'axios'

const { homepage } = require('../../package.json')

let headers = {}

let token = sessionStorage.getItem('jwt')

if ( token )
  headers['Authentication'] = 'Authorization ' + token

export default axios.create({
  baseURL: homepage + '/api',
  headers: headers
})