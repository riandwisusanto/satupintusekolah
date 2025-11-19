<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useLocalStorage } from '@vueuse/core'
import { useUser } from '../../../store.js'
import { alertSuccess, alertError } from '../../../lib/alert.js'
import { useLoading } from 'vue-loading-overlay'

const token = useLocalStorage('token', '')
const credential = useUser()
const router = useRouter()
const user = reactive({
    email: '',
    password: '',
})

const $loading = useLoading({
    // options
})

const loginForm = ref(null)
const showPassword = ref(false)
const isFocused = ref(false)
const togglePassword = () => {
    showPassword.value = !showPassword.value
}

async function userLogin({ email, password }) {
    const form = loginForm.value
    if (!form.checkValidity()) {
        form.classList.add('was-validated')
        return false
    }

    form.classList.remove('was-validated')

    const loader = $loading.show({
        // Optional parameters
    })

    const response = await fetch(`${import.meta.env.VITE_API_PATH}/api/v1/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
        },
        body: JSON.stringify({ email, password }),
    })

    const responseBody = await response.json()
    if (response.status === 200) {
        // store to localstorage
        token.value = JSON.stringify(responseBody.data)

        // store to pinia
        credential.setUser({
            accesstoken: responseBody.data.token,
            role: responseBody.data.user.role,
            permissions: responseBody.data.user.permissions,
            user: responseBody.data.user,
        })

        loader.hide()

        await alertSuccess(responseBody.message)

        router.push('/')
    } else {
        loader.hide()

        await alertError(responseBody.message)
    }
}
</script>
<template>
    <section class="vh-100" style="background: linear-gradient(to bottom, #4a90e2, #7ed6df);">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col col-xl-10">
                    <div class="card" style="border-radius: 1rem">
                        <div class="row g-0">
                            <div class="col-md-6 col-lg-5 d-none d-md-block">
                                <img
                                    src="/public/assets/images/pngegg.png"
                                    alt="login form"
                                    class="img-fluid p-4"
                                    style="border-radius: 1rem 0 0 1rem"
                                />
                            </div>
                            <div class="col-md-6 col-lg-7 d-flex align-items-center">
                                <div class="card-body p-4 p-lg-5 text-black">
                                    <form
                                        id="loginForm"
                                        ref="loginForm"
                                        class="needs-validation"
                                        novalidate
                                        @submit.prevent="userLogin(user)"
                                    >
                                        <div class="d-flex align-items-center mb-3 pb-1">
                                            <img
                                                src="/public/assets/images/logo-satupintu.png"
                                                style="width: 50px; margin-right: 20px"
                                                alt="logo"
                                            />
                                            <span
                                                class="h1 fw-bold mb-0"
                                                style="
                                                    font-family: 'Poppins', sans-serif;
                                                    font-size: 230%;
                                                "
                                                >SATU PINTU</span
                                            >
                                        </div>

                                        <hr />

                                        <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px">
                                            Masuk ke akun anda
                                        </h5>

                                        <div data-mdb-input-init class="form-outline mb-4">
                                            <label class="form-label" for="form2Example17"
                                                >Alamat Email</label
                                            >
                                            <input
                                                id="form2Example17"
                                                v-model="user.email"
                                                style="font-size: 16px"
                                                type="email"
                                                class="form-control form-control-lg"
                                                placeholder="Email"
                                                required
                                            />
                                            <div class="invalid-feedback">
                                                Please provide a valid email.
                                            </div>
                                        </div>

                                        <div class="form-outline mb-4 position-relative">
                                            <label class="form-label" for="form2Example27"
                                                >Password</label
                                            >

                                            <div class="position-relative">
                                                <input
                                                    id="form2Example27"
                                                    v-model="user.password"
                                                    :type="showPassword ? 'text' : 'password'"
                                                    class="form-control form-control-lg pe-5"
                                                    placeholder="Password"
                                                    required
                                                    style="font-size: 16px"
                                                    @focus="isFocused = true"
                                                    @blur="isFocused = false"
                                                />
                                                <i
                                                    v-show="isFocused || user.password"
                                                    class="fa-solid"
                                                    :class="
                                                        showPassword
                                                            ? 'fas fa-eye-slash'
                                                            : 'fas fa-eye'
                                                    "
                                                    style="
                                                        position: absolute;
                                                        top: 50%;
                                                        right: 2.8rem;
                                                        transform: translateY(-50%);
                                                        cursor: pointer;
                                                        color: #888;
                                                        z-index: 3;
                                                        height: 1.5rem;
                                                        line-height: 1.5rem;
                                                        display: flex;
                                                        align-items: center;
                                                        justify-content: center;
                                                    "
                                                    @mousedown.prevent="togglePassword"
                                                ></i>
                                            </div>
                                        </div>

                                        <div class="pt-1 mb-4">
                                            <button
                                                type="submit"
                                                class="btn btn-lg w-100 d-flex justify-content-center align-items-center gap-2"
                                                style="
                                                    background: linear-gradient(
                                                        90deg,
                                                        #4a90e2 0%, /* biru sekolah / profesional */
                                                        #7ed6df 100% /* hijau mint lembut */
                                                    );
                                                    color: #fff;
                                                    border: none;
                                                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                                                    transition: all 0.3s ease-in-out;
                                                "
                                                @mouseover="
                                                    (e) =>
                                                        (e.currentTarget.style.boxShadow =
                                                            '0 6px 16px rgba(0, 0, 0, 0.25)')
                                                "
                                                @mouseout="
                                                    (e) =>
                                                        (e.currentTarget.style.boxShadow =
                                                            '0 4px 12px rgba(0, 0, 0, 0.15)')
                                                "
                                            >
                                                <i class="fas fa-sign-in-alt"></i>
                                                Login
                                            </button>
                                        </div>

                                        <!-- <a class="small text-muted" href="#!">Forgot password?</a> -->
                                        <!-- <p class="mb-5 pb-lg-2" style="color: #393f81;">Don't have an account? <RouterLink :to="{name:'Register'}"  class="text-center" style="color: #1974D2;">Register a new membership</RouterLink></p> -->
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped></style>
