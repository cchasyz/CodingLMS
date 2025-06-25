<script setup>
import short from '@/router/axios';
import { ref,onMounted } from 'vue';

const username = ref();
const password = ref();
const token = localStorage.getItem('token');
if(token){
  window.location.href = '/';
}

const login = async () => {
  try {
    const res = await short.post('/login', {
      username: username.value,
      password: password.value
    });
    console.log(res.data);
    localStorage.setItem('token', res.data.data.token);
    localStorage.setItem('fullname', res.data.data.full_name);
    window.location.href = '/';
  } catch (error) {
    console.error(error);
    
  }
}
onMounted(()=>{
  document.title = 'login'
})
</script>

<template>
  <main class="py-5">
    <section>
        <div class="container">
            <h3 class="mb-3 text-center">Login</h3>

            <div class="row justify-content-center">
                <div class="col-md-7">
                    <div class="card mb-4">
                        <div class="card-body">
                            <form action="index.html">
                                <div class="form-group mb-2">
                                    <label for="username">Username</label>
                                    <input type="text" name="username" id="username" class="form-control" v-model="username" autofocus/>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="password">Password</label>
                                    <input type="password" name="password" id="password" class="form-control" v-model="password" />
                                </div>
                                <div class="mt-3">
                                    <button @click.prevent="login" class="btn btn-primary w-100">Login</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <p class="text-center">You have no an account yet? <a href="/register">Register</a></p>
                </div>
            </div>

        </div>
    </section>
  </main>
</template>