<script setup>
import { useRoute } from 'vue-router';
import short from '@/router/axios';
import { ref, onMounted } from 'vue';

const route = useRoute();
const lessonId = route.params.id;
const slug = route.params.slug;
const is_quiz = ref(false);
const course = ref({});
const usercourses = ref();
const lessons = ref({});
const order = ref(0);
const selectedOption = ref(null);
const max = ref();
const token = localStorage.getItem('token');

const getDetail = async()=>{
  try {
    const res = await short.get(`/courses/${slug}`, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    
    course.value = res.data.data
    console.log(course.value.sets);
    
  } catch (error) {
    console.error(error);
    
  }
}

const incrementOrder = async () =>{
    console.log('ran');
    const index = Number(order.value);
    max.value = lessons.value.contents.length - 1;
    if(index < max.value){
        order.value++;
        
    } else {
        alert('finished lesson!');
        completeLesson();
    }
}

const completeLesson = async()=>{
    try {
        const res = await short.put(`/lessons/${lessonId}/complete`,{},{
            headers: {
                Authorization: `Bearer ${token}`
            }
        });
        console.log(res.data);
        window.location.href = `/detailcourse/${slug}`;
    } catch (error) {
        console.error(error);
        
    }
}

const getLesson = () =>{
    try {
        for (let i = 0; i < course.value.sets.length; i++) {
            const set = course.value.sets[i];
            for (let j = 0; j < set.lessons.length; j++) {
                const lesson = set.lessons[j];
                if(lesson.id == lessonId){
                    lessons.value = lesson;
                }

            }
        }
    } catch (error) {
        console.error(error);

    } finally {
        console.log(lessons.value);
    }
} 

const checkAnswer = async(contentId, optionId)=>{
    try {
        const res = await short.post(`/lessons/${lessonId}/contents/${contentId}/check`,{
            option_id: optionId
        },{
            headers: {
                Authorization: `Bearer ${token}`
            }
        });
        console.log(res.data.data.is_correct);
        if(res.data.data.is_correct){
            const index = Number(order.value);
            if(index < max.value){
                order.value++;
            } else {
                alert('finished lesson!');
                completeLesson();
            }
        } else {
            alert('your answer is wrong!')
        }
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
    usercourses.value = res.data.data.progress
  } catch (error) {
    console.error(error);
    
  }
}

const filter = async()=>{
  try {
    await getUsersCourses();
    await getDetail();

    usercourses.value = usercourses.value.filter(item => item.course.id === course.value.id);

    getLesson();
    
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
    <main class="py-5" v-if="!is_quiz">
      <section>
        <div class="container">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-0">{{ lessons.name }}</h4>
            </div>
            <div class="progress mb-5" role="progressbar" aria-label="Example with label" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" :style="{ width: (max ? ((order / max) * 100).toFixed(2) + '%' : '0%') }"></div>
            </div>

            <div v-if="lessons.contents">

                <div v-if="lessons.contents[order].type === 'learn'">
                    <div class="mb-4">
                        <p class="mb-4">
                            {{ lessons.contents[order].content }}
                        </p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <a @click.prevent="incrementOrder" href="#" class="btn btn-primary w-100 mt-2">Continue</a>
                        </div>
                    </div>
                </div>
                    
            <div v-else>

                    <div class="mb-4">
                        <p class="mb-4">
                            {{ lessons.contents[order].content }}
                        </p>

                <div>
                    <div class="my-2" v-for="option in lessons.contents[order].options" :key="option">
                        <input
                            type="radio"
                            :id="'choice-' + option.id"
                            name="answer"
                            class="input-choice"
                            v-model="selectedOption"
                            :value="option.id"
                        />
                        <label :for="'choice-' + option.id" class="card">
                            <div class="card-body">
                            {{ option.option_text }}
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <a @click.prevent="checkAnswer(lessons.contents[order].id, selectedOption)" href="#" class="btn btn-primary w-100 mt-2">Check</a>
                </div>
            </div>
        </div>
    </div>

        </div>
    </section>
  </main>

  <main class="py-5" v-if="is_quiz">
    <section>
        <div class="container">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="mb-0">[Lesson name]</h4>
            </div>
            <div class="progress mb-5" role="progressbar" aria-label="Example with label" aria-valuenow="16" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: 16%"></div>
            </div>

            <div class="mb-4">
                <p class="mb-4">
                    [Lesson content text]
                </p>

                <div>
                    <div class="my-2">
                        <input type="radio" id="choice-1" name="answer" class="input-choice"/>
                        <label for="choice-1" class="card">
                            <div class="card-body">
                                [Option text]
                            </div>
                        </label>
                    </div>
                    <div class="my-2">
                        <input type="radio" id="choice-2" name="answer" class="input-choice"/>
                        <label for="choice-2" class="card">
                            <div class="card-body">
                                [Option text]
                            </div>
                        </label>
                    </div>
                    <div class="my-2">
                        <input type="radio" id="choice-3" name="answer" class="input-choice"/>
                        <label for="choice-3" class="card">
                            <div class="card-body">
                                [Option text]
                            </div>
                        </label>
                    </div>
                    <div class="my-2">
                        <input type="radio" id="choice-4" name="answer" class="input-choice"/>
                        <label for="choice-4" class="card">
                            <div class="card-body">
                                [Option text]
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <a href="detail-course-registered.html" class="btn btn-primary w-100 mt-2">Check</a>
                </div>
            </div>

        </div>
    </section>
  </main>
</div>
</template>