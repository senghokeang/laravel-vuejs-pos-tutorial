<template>
    <section class="vh-100"
        style="background-image: url('images/bg.jpg');background-position: center; background-repeat: no-repeat; background-size: cover;">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card shadow-2-strong" style="border-radius: 1rem; background: lightsteelblue;">
                        <div class="card-body p-5">
                            <img src="images/favicon.png"
                                style="position: absolute; top: 10px; left: 10px; height: 80px;" />
                            <h1 class="mb-4 text-center fw-bold">Sign In</h1>
                            <div class="form-outline mb-4">
                                <input type="text"
                                    :class="['form-control form-control-lg', { 'is-invalid': errors?.username }]"
                                    placeholder="Username" ref="autofocus" v-model="form.username"
                                    :disabled="processing" v-on:keyup.enter="login" />
                                <span v-if="errors?.username" class="text-danger form-label" style="padding-left: 5px">
                                    {{ errors?.username[0] }}
                                </span>
                            </div>

                            <div class="form-outline mb-4">
                                <input type="password"
                                    :class="['form-control form-control-lg', { 'is-invalid': errors?.password }]"
                                    placeholder="Password" v-model="form.password" :disabled="processing"
                                    v-on:keyup.enter="login" />
                                <span v-if="errors?.password" class="text-danger form-label" style="padding-left: 5px">
                                    {{ errors?.password[0] }}
                                </span>
                            </div>
                            <div class="d-grid mb-2">
                                <button class="btn btn-primary btn-lg" type="button" :disabled="processing"
                                    @click="login">
                                    <span v-show="processing" class="spinner-border spinner-border-sm" role="status"
                                        aria-hidden="true"></span>
                                    Login
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRouter } from 'vue-router'
import { setFocus } from "../../helper";

const processing = ref(false);
const form = reactive({
    username: null,
    password: null

});
const errors = ref(null);
const router = useRouter();
const autofocus = ref(null);
const login = () => {
    processing.value = true;
    axios.get('/sanctum/csrf-cookie')
        .then(() => {
            axios.post('/api/login', form).then((response) => {
                if (response.data.success) {
                    router.push('/');
                } else {
                    errors.value = response.data.errors;
                    setFocus(autofocus);
                }
            }).catch(response => {
                console.log(response);
            }).finally(() => {
                processing.value = false;
            })
        });
}

onMounted(() => {
    document.body.style.display = "block";
    autofocus.value.focus();
});
</script>
