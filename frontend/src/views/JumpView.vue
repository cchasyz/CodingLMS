<script setup>
import { useRoute, useRouter } from 'vue-router'
import short from '@/router/axios'
import { ref, computed, onMounted } from 'vue'

const route = useRoute()
const router = useRouter()

const course_slug = route.params.slug
const set_id = Number(route.params.setId)

const course = ref({})
const setData = ref({})

// We now store lessons as “lesson groups” with their quizzes.
// Each group will have: lessonId, lessonName, order, quizzes (array)
const lessons = ref([])

// Instead of currentLesson (which was confusing), we track two indices:
const currentLessonIndex = ref(0)
const currentQuizIndex = ref(0)

const loading = ref(true)

// Compute the current lesson (group) based on the index.
const currentLesson = computed(() => {
  return lessons.value[currentLessonIndex.value] || null
})

// Compute the current quiz from the current lesson.
const currentQuiz = computed(() => {
  return currentLesson.value && currentLesson.value.quizzes[currentQuizIndex.value] || null
})

// Compute overall progress as percentage across all quizzes in all lessons.
const totalQuizzes = computed(() => {
  return lessons.value.reduce((acc, lessonGroup) => acc + lessonGroup.quizzes.length, 0)
})
const currentGlobalQuizIndex = computed(() => {
  let count = 0
  for (let i = 0; i < currentLessonIndex.value; i++) {
    count += lessons.value[i]?.quizzes.length || 0
  }
  count += currentQuizIndex.value
  return count
})
const progressPercentage = computed(() => {
  if (totalQuizzes.value === 0) return 0
  return Math.floor((currentGlobalQuizIndex.value / totalQuizzes.value) * 100)
})

const token = localStorage.getItem('token')

const getCourseDetail = async () => {
  try {
    const res = await short.get(`/courses/${course_slug}`, {
      headers: { Authorization: `Bearer ${token}` }
    })
    course.value = res.data.data

    setData.value = course.value.sets.find(item => item.id === set_id)
    if (!setData.value) {
      console.error('Set not found')
      router.push(`/detailcourse/${course_slug}`)
      return
    }

    // Build lesson groups: for each lesson in the set, filter quiz-type contents.
    const lessonGroups = []
    for (const lesson of setData.value.lessons) {
      if (!lesson.contents || !Array.isArray(lesson.contents)) continue

      // Filter for quiz-type contents
      const quizzes = lesson.contents
        .filter(content => content.type === 'quiz')
        .map(content => ({
          ...content,
          // selectedOption for v-model; options remains as is (or [] if missing)
          selectedOption: null,
          options: content.options || []
        }))
      
      // Only include lessons that have at least one quiz.
      if (quizzes.length > 0) {
        lessonGroups.push({
          lessonId: lesson.id,
          lessonName: lesson.name,
          order: lesson.order, // assuming lesson.order exists
          quizzes: quizzes
        })
      }
    }
    
    // Sort lesson groups by their order.
    lessons.value = lessonGroups.sort((a, b) => a.order - b.order)
    
    // Initialize indices.
    currentLessonIndex.value = 0
    currentQuizIndex.value = 0
    console.log("Current quiz:", currentQuiz.value)
    
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

// This API completes a single lesson.
const completeLessonAPI = async (lessonId) => {
  try {
    const res = await short.put(`/lessons/${lessonId}/complete`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    })
    console.log('Lesson complete:', res.data)
  } catch (error) {
    console.error(error)
  }
}

// Called when the user clicks the "Check" button for the current quiz.
const checkAnswer = async () => {
  if (!currentQuiz.value) return
  try {
    // Use the lessonId from the current lesson group and the quiz id from currentQuiz.
    const lessonId = currentLesson.value.lessonId
    const quizId = currentQuiz.value.id
    const selectedOption = currentQuiz.value.selectedOption

    const res = await short.post(`/lessons/${lessonId}/contents/${quizId}/check`, {
      option_id: selectedOption
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    if (res.data.data.is_correct) {
      // If correct, move to the next quiz or, if finished, complete the lesson.
      incrementQuiz()
    } else {
      alert('Your answer is wrong!')
    }
  } catch (error) {
    console.error(error)
  }
}

// Advances the quiz pointer within the current lesson group or completes the lesson when done.
const incrementQuiz = async () => {
  if (!currentLesson.value) return

  // If there are more quizzes in the current lesson, move to the next quiz.
  if (currentQuizIndex.value < currentLesson.value.quizzes.length - 1) {
    currentQuizIndex.value++
  } else {
    // Completed all quizzes for this lesson.
    // Complete the lesson via API.
    await completeLessonAPI(currentLesson.value.lessonId)
    
    // Move to the next lesson group, if one exists.
    if (currentLessonIndex.value < lessons.value.length - 1) {
      currentLessonIndex.value++
      currentQuizIndex.value = 0
    } else {
      // No more lessons left; redirect to course detail page.
      router.push(`/detailcourse/${course_slug}`)
    }
  }
}

onMounted(() => {
  document.title = 'JumpSet'
  getCourseDetail()
})
</script>

<template>
  <main class="py-5">
    <section v-if="!loading">
      <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <!-- Dynamic Set Name -->
          <h4 class="mb-0">{{ setData?.name || '[Set name]' }}</h4>
          <span>{{ progressPercentage }}%</span>
        </div>

        <!-- Dynamic progress bar -->
        <div class="progress mb-5" role="progressbar" 
             :aria-valuenow="progressPercentage" 
             aria-valuemin="0" aria-valuemax="100">
          <div class="progress-bar" :style="{ width: progressPercentage + '%' }"></div>
        </div>

        <div class="mb-4">
          <!-- Display the current lesson's name (optional) -->
          <p class="mb-2" v-if="currentLesson">
            <strong>{{ currentLesson.lessonName }}</strong>
          </p>
          <!-- Dynamic Quiz Content -->
          <p class="mb-4" v-if="currentQuiz?.content">
            {{ currentQuiz.content }}
          </p>

          <div v-if="currentQuiz?.options?.length > 0">
            <!-- Dynamic Quiz Options -->
            <div class="my-2" v-for="(option, index) in currentQuiz.options" :key="option.id">
              <input type="radio" :id="'choice-' + index" name="answer" class="input-choice"
                     v-model="currentQuiz.selectedOption" :value="option.id" />
              <label :for="'choice-' + index" class="card">
                <div class="card-body">
                  {{ option.option_text }}
                </div>
              </label>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <button @click="checkAnswer" class="btn btn-primary w-100 mt-2">
              Check
            </button>
          </div>
        </div>
      </div>
    </section>
    <!-- Optional loading state -->
    <section v-else class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3">Loading...</p>
    </section>
  </main>
</template>
