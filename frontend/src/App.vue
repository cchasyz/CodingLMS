<script setup>
import short from './router/axios';

const token = localStorage.getItem('token');
const fullname = localStorage.getItem('fullname');

const logout = async()=>{
  try {
    const res = await short.post('/logout',{},{
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    console.log(res.data);
    localStorage.removeItem('token');
    localStorage.removeItem('fullname');
    window.location.href = '/login'
  } catch (error) {
    console.error(error);
    
  }
}
</script>

<template>
  <div>

    <nav v-if="!token" class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container">
          <a class="navbar-brand" href="/">WebTechStudio</a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                  <li class="nav-item">
                      <a class="nav-link" href="/login">Login</a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link" href="/register">Register</a>
                  </li>
              </ul>
          </div>
      </div>
    </nav>

    <nav v-if="token" class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container">
        <a class="navbar-brand" href="/">WebTechStudio</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="#">{{ fullname }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" @click.prevent="logout" href="">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
    
    <RouterView />
  </div>
</template>

<style scoped>

</style>
