<script setup>
import short from '@/router/axios';
import { ref, onMounted } from 'vue';

const courses = ref([]);
const usercourses = ref([]);

const token = localStorage.getItem('token');
if(!token){
  window.location.href = '/login';
}

const getCourses = async()=>{
  try {
    const res = await short.get('/courses',{
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    console.log(res.data.data);
    courses.value = res.data.data
  } catch (error) {
    console.error(error);
    
  }
}

const getUsersCourses = async()=>{
  try {
    const res = await short.get('/users/progress',{
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    console.log(res.data.data.progress);
    usercourses.value = res.data.data.progress
  } catch (error) {
    console.error(error);
    
  }
}

const filter = async()=>{
  try { 
    await getUsersCourses();
    await getCourses();
    const userCourseNames = usercourses.value.map(uc => uc.course.name);

    courses.value = courses.value.filter(course => {
    return !userCourseNames.includes(course.name);
    });
  } catch (error) {
    console.error(error);
    
  }
}

onMounted(()=>{
  document.title = 'Home';
  filter();
});
</script>

<template>
  <main class="py-5">
    <section class="my-courses">
        <div class="container">
            <h4 class="mb-3">My courses</h4>
            <div class="courses d-flex flex-column gap-3" v-for="usercourse in usercourses" :key="usercourse.id">
                <a :href="'/detailcourse/' + usercourse.course.slug" class="card text-decoration-none bg-body-tertiary">
                    <div class="card-body">
                        <h5 class="mb-2">{{ usercourse.course.name }}</h5>
                        <p class="text-muted mb-0">{{ usercourse.course.description }}</p>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <section class="other-courses mt-4">
        <div class="container">
            <h4 class="mb-3">Other courses</h4>
            <div class="d-flex flex-column gap-3" v-for="course in courses" :key="course.id">
                <a :href="'/detailcourse/' + course.slug" class="card text-decoration-none bg-body-tertiary">
                    <div class="card-body">
                        <h5 class="mb-2">{{ course.name }}</h5>
                        <p class="text-muted mb-0">{{course.description}}</p>
                    </div>
                </a>
            </div>
        </div>
    </section>
</main>

</template>
