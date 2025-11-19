import Swal from 'sweetalert2'

export const alertSuccess = async (message) => {
    return Swal.fire({
        icon: 'success',
        title: 'success',
        text: message,
    })
}

export const alertError = async (errorObj) => {
    let textMessage = 'Terjadi kesalahan.'
    // console.log(errorObj);

    if (typeof errorObj === 'string') {
        textMessage = errorObj
    } else if (errorObj?.errors) {
        const messages = Object.values(errorObj.errors)
            .flat()
            .map((msg) => `• ${msg}`)
            .join('\n')

        textMessage = messages || errorObj.message || textMessage
    } else if (errorObj?.message) {
        textMessage = errorObj.message
    }

    return Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: textMessage,
        customClass: {
            popup: 'swal2-show-text-left',
        },
    })
}

export const alertWarning = async (message) => {
    return Swal.fire({
        icon: 'warning',
        title: 'warning',
        text: message,
    })
}

export const alretConfirm = async (method = 'a') => {
    let text = ''
    let title = ''
    let confirmButtonText = ''
    let confirmButtonColor = ''
    if (method == 'delete') {
        text = 'Pastikan data yang anda hapus sudah benar !'
        title = 'Peringatan'
        confirmButtonText = 'Hapus'
        confirmButtonColor = '#3085d6'
    } else {
        text = 'Pastikan data yang anda simpan sudah benar'
        title = 'Simpan?'
        confirmButtonText = 'Simpan'
        confirmButtonColor = '#3085d6'
    }
    return Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: confirmButtonColor,
        cancelButtonColor: '#d33',
        confirmButtonText: confirmButtonText,
        cancelButtonText: 'Batal',
    })
}
