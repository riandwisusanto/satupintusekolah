import { useUser } from '../store'

export async function apiRequest(
    endpoint,
    { method = 'GET', params = null, body = null, headers = {} } = {}
) {
    if (!endpoint) throw new Error('endpoint is required')

    const token = useUser().user?.accesstoken || null

    let url = `${import.meta.env.VITE_API_PATH}/api/v1/${endpoint}`
    if (params && Object.keys(params).length) {
        const search = new URLSearchParams(params).toString()
        url += `?${search}`
    }

    const defaultHeaders = { Accept: 'application/json' }
    if (token) defaultHeaders['Authorization'] = `Bearer ${token}`

    const options = {
        method: method.toUpperCase(),
        headers: { ...defaultHeaders, ...headers },
    }

    if (body && method !== 'GET') {
        options.body = typeof body === 'string' ? body : body

        if (body instanceof FormData) {
            delete options.headers['Content-Type']
        } else {
            options.headers['Content-Type'] = 'application/json'
            options.body = JSON.stringify(body)
        }
    }

    let data = null
    let error = null
    let status = 0

    try {
        const res = await fetch(url, options)
        status = res.status
        const isJson = res.headers.get('content-type')?.includes('application/json')
        data = isJson ? await res.json() : await res.text()

        if (status === 403) {
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            window.location.href = '/login'
            return
        }

        if (!res.ok) {
            error = data?.message || data || 'Request failed'
        }
    } catch (err) {
        error = err.message || 'Network error'
    }

    return { ok: !error, status, data, error }
}
