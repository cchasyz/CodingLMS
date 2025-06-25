<script setup>
import short from '@/router/axios';
import { ref,onMounted } from 'vue';

const full_name = ref();
const username = ref();
const password = ref();
const token = localStorage.getItem('token');
if(token){
  window.location.href = '/';
}

const register = async () => {
  try {
    const res = await short.post('/register', {
      full_name: full_name.value,
      username: username.value,
      password: password.value
    });
    console.log(res.data);
    localStorage.setItem('token', res.data.token.token);
    localStorage.setItem('fullname', full_name.value);
    window.location.href = '/';
  } catch (error) {
    console.error(error);
    
  }
}

onMounted(()=>{
  document.title = 'register'
})
</script>

<template>
  <main class="py-5">
    <section>
        <div class="container">
            <h3 class="mb-3 text-center">Register</h3>

            <div class="row justify-content-center">
                <div class="col-md-7">
                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="index.html">
                                <div class="form-group mb-2">
                                    <label for="full_name">Full Name</label>
                                    <input type="text" name="full_name" id="full_name" class="form-control" v-model="full_name" autofocus/>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="username">Username</label>
                                    <input type="text" name="username" id="username" class="form-control" v-model="username" />
                                </div>
                                <div class="form-group mb-2">
                                    <label for="password">Password</label>
                                    <input type="password" name="password" id="password" class="form-control" v-model="password" />
                                </div>
                                <div class="mt-3">
                                    <button @click.prevent="register" class="btn btn-primary w-100">Register</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <p class="text-center">You have an account? <a href="/login">Login</a></p>
                </div>
            </div>

        </div>
    </section>
</main>
</template>