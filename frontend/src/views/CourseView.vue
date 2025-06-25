<script setup>
import { onMounted, ref, computed } from 'vue';
import short from '@/router/axios';
import { useRoute } from 'vue-router';

const route = useRoute();
const slug = route.params.slug;
const course = ref({});
const token = localStorage.getItem('token');
const usercourses = ref();
const registered = ref(false);
const marked = ref(false);
const lessonStatuses = ref({});
const progress = ref(0);
const fullname = localStorage.getItem('fullname');

const calculateLessonStatuses = () => {
  marked.value = false;
  const statuses = {};
  let totalLessons = 0;
  let completedCount = 0;

  const completed = (usercourses.value?.[0]?.completed_lessons ?? []).map(l => l.id);
  console.log("Completed lesson IDs:", completed);

  course.value.sets.forEach(set => {
    set.lessons.forEach(lesson => {
      totalLessons++;
      const id = lesson.id;

      if (completed.includes(id)) {
        statuses[id] = 'Completed';
        completedCount++;
      } else if (!marked.value) {
        statuses[id] = 'Current';
        marked.value = true;
      } else {
        statuses[id] = 'Locked';
      }

      console.log(`Lesson ${id}: ${statuses[id]}`);
    });
  });

  lessonStatuses.value = statuses;

  // Calculate progress as a percentage
  if (totalLessons > 0) {
    progress.value = Math.round((completedCount / totalLessons) * 100);
  } else {
    progress.value = 0;
  }

  console.log("Progress:", progress.value + "%");
};

const registerCourse = async()=>{
  try {
    const res = await short.post(`courses/${slug}/register`, {}, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    console.log(res.data);
    window.location.href = '/';
  } catch (error) {
    console.error(error);
    
  }
}

const firstIncompleteSetId = computed(() => {
  if (!course.value.sets) return null;
  // Loop over sets and return the id of the first set with at least one lesson not marked as 'Completed'
  for (const set of course.value.sets) {
    // Use every() to test if all lessons in this set are marked 'Completed'
    const allCompleted = set.lessons.every(lesson => lessonStatuses.value[lesson.id] === 'Completed');
    if (!allCompleted) {
      return set.id;
    }
  }
  return null;
});


const getDetail = async()=>{
  try {
    const res = await short.get(`/courses/${slug}`, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    console.log(res.data.data);
    course.value = res.data.data
    
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
    await getDetail();
    registered.value = usercourses.value.some(item => item.course.id === course.value.id);
   
    if (registered.value) {
    usercourses.value = usercourses.value.filter(item => item.course.id === course.value.id);
    console.log("Filtered usercourses:", JSON.stringify(usercourses.value, null, 2));
    calculateLessonStatuses();
  }
    
  } catch (error) {
    console.error(error);
    
  }
}

onMounted(()=>{
  document.title = 'CourseDetails';
  filter();
})
</script>

<template>
<div>
  <main class="py-5" v-if="registered">
    <section>
        <div class="container">
            <h3 class="mb-3">{{ course.name }}</h3>
            <div class="progress mb-5" role="progressbar" aria-label="Example with label" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" :style="{width: progress + '%' }">{{progress}}%</div>
            </div>

            <div class="mb-4" v-for="set in course.sets" :key="set.id">
                <h4 class="mb-3">{{ set.name }}</h4>
                <div class="row">
                    <div class="col-md-12" v-for="lesson in set.lessons" :key="lesson.id">
                        <a :href="lessonStatuses[lesson.id] !== 'Locked' ? '/' + slug + '/lessondetail/' + lesson.id : '#'" :class="{
                          'card mb-3 text-decoration-none bg-body-tertiary' : lessonStatuses[lesson.id] === 'Completed' || lessonStatuses[lesson.id] === 'Current',
                          'card mb-3 text-decoration-none bg-body-tertiary opacity-50' : lessonStatuses[lesson.id] === 'Locked'
                          }">
                            <div class="card-body d-flex justify-content-between">
                                <div>
                                    <small class="text-muted">Lesson</small>
                                    <h5 class="mt-2">{{ lesson.name }}</h5>
                                </div>
                                <div>
                                  <div
                                    :class="{
                                      'badge border border-success text-success': lessonStatuses[lesson.id] === 'Completed',
                                      'badge border border-primary text-primary': lessonStatuses[lesson.id] === 'Current',
                                      'badge border text-secondary': lessonStatuses[lesson.id] === 'Locked'
                                    }
                                    ">
                                    {{ lessonStatuses[lesson.id] }}
                                  </div>
                                </div>
                            </div>
                        </a>
                      </div>
                    </div>
                    
                    <div class="text-center mb-4" v-if="set.id === firstIncompleteSetId">
                        <p class="mb-2"><b>Too easy?</b></p>
                        <a :href="'/' + course.slug + '/jump/' + set.id" class="btn btn-outline-primary">Jump Here</a>
                    </div>

            </div>

            <div v-if="progress === 100">
              <h4 class="mb-3">Certificate</h4>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-3 text-decoration-none bg-body-tertiary">
                            <div class="card-body text-center d-flex flex-column gap-5 p-4">
                                <h5>Course Certificate</h5>

                                <div class="text-center d-flex flex-column gap-2">
                                    <p class="mb-0 text-muted"><small>This is to certify that</small></p>
                                    <h1 class="mb-0 fw-bold">{{ fullname }}</h1>
                                    <p class="mb-0 text-muted">
                                        <small>has successfully completed the course by demonstrating <br/> theorical and practical understanding to</small>
                                    </p>
                                    <h3 class="fw-normal">{{ course.name }}</h3>
                                </div>

                                <h6 class="mb-0">WebTechStudio</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          </div>
          </section>
</main>
<main class="py-5" v-if="!registered">
    <section>
        <div class="container">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h3 class="mb-0">{{ course.name }}</h3>
                <a @click.prevent="registerCourse" href="" class="btn btn-primary">Register to this course</a>
            </div>

            <p class="mb-5">
                {{ course.description }}
            </p>

            <div class="mb-4">
                <h4 class="mb-3">Outline</h4>
                <div class="row">
                    <div class="col-md-12" v-for="set in course.sets" :key="set.id">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="mb-0">{{ set.order + 1 }}. {{ set.name }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</main>
</div>
</template>