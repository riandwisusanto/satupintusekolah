/**
 * Currency formatting utilities
 */

/**
 * Format number to Indonesian currency format
 * @param {number|string} value - The value to format
 * @param {Object} options - Formatting options
 * @returns {string} Formatted currency string
 */
export function formatCurrency(value, options = {}) {
    const { currency = 'Rp', showDecimal = false, compact = false, locale = 'id-ID' } = options

    const num = parseFloat(value) || 0

    if (compact && num >= 1000000) {
        return `${currency}. ${(num / 1000000).toFixed(showDecimal ? 1 : 0)} Jt`
    }

    return `${currency}. ${num.toLocaleString(locale, {
        minimumFractionDigits: showDecimal ? 2 : 0,
        maximumFractionDigits: showDecimal ? 2 : 0,
    })}`
}

/**
 * Format number with decimal places
 * @param {number|string} num - The number to format
 * @param {number} decimals - Number of decimal places
 * @returns {string} Formatted number
 */
export function formatNumber(num, decimals = 1) {
    return parseFloat(num).toFixed(decimals)
}

/**
 * Format date to Indonesian format
 * @param {string|Date} date - The date to format
 * @returns {string} Formatted date string
 */
export function formatDate(date) {
    if (!date) return '-'

    const d = new Date(date)
    if (isNaN(d.getTime())) return '-'

    return d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    })
}

export function formatTime (timeString) {
    if (!timeString) return '-'
    return timeString.slice(0, 5)
}

/**
 * Generate random color for charts
 * @param {number} index - Index for consistent color generation
 * @returns {string} Hex color code
 */
export function generateColor(index = 0) {
    const colors = [
        'rgba(54, 162, 235, 0.7)', // Blue
        'rgba(75, 192, 192, 0.7)', // Teal
        'rgba(255, 159, 64, 0.7)', // Orange
        'rgba(46, 204, 113, 0.7)', // Green
        'rgba(155, 89, 182, 0.7)', // Purple
        'rgba(231, 76, 60, 0.7)', // Red
        'rgba(241, 196, 15, 0.7)', // Yellow
        'rgba(52, 73, 94, 0.7)', // Dark Blue
    ]

    return colors[index % colors.length]
}

/**
 * Generate border color for charts
 * @param {string} backgroundColor - Background color
 * @returns {string} Border color
 */
export function generateBorderColor(backgroundColor) {
    return backgroundColor.replace('0.7', '1')
}

/**
 * Debounce function to limit API calls
 * @param {Function} func - Function to debounce
 * @param {number} delay - Delay in milliseconds
 * @returns {Function} Debounced function
 */
export function debounce(func, delay = 300) {
    let timeoutId
    return function (...args) {
        clearTimeout(timeoutId)
        timeoutId = setTimeout(() => func.apply(this, args), delay)
    }
}
