import './adminlteJs/jquery'
import './adminlteJs/bootstrap461'
import './adminlteJs/adminlte'
import './adminlteJs/select2'

import axios from 'axios'
window.axios = axios

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
