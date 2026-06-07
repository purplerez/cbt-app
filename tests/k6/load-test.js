import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

const BASE_URL = 'http://43.157.213.192';
const EXAM_ID = 2;

const loginErrors = new Rate('login_errors');
const dashboardErrors = new Rate('dashboard_errors');
const startExamErrors = new Rate('start_exam_errors');
const saveAnswerErrors = new Rate('save_answer_errors');
const submitExamErrors = new Rate('submit_exam_errors');

const loginDuration = new Trend('login_duration', true);
const dashboardDuration = new Trend('dashboard_duration', true);
const startExamDuration = new Trend('start_exam_duration', true);
const saveAnswerDuration = new Trend('save_answer_duration', true);
const submitExamDuration = new Trend('submit_exam_duration', true);

export const options = {
  stages: [
    { duration: '5m', target: 300 },
    { duration: '5m', target: 750 },
    { duration: '5m', target: 1500 },
    { duration: '10m', target: 1500 },
    { duration: '5m', target: 0 },
  ],
  thresholds: {
    http_req_duration: ['p(95)<3000', 'p(99)<5000'],
    http_req_failed: ['rate<0.1'],
    login_errors: ['rate<0.05'],
    dashboard_errors: ['rate<0.05'],
    start_exam_errors: ['rate<0.05'],
    save_answer_errors: ['rate<0.05'],
    submit_exam_errors: ['rate<0.05'],
  },
};

function getUserId() {
  return Math.floor(Math.random() * 1500) + 978;
}

export default function () {
  const userId = getUserId();
  const headers = { 'Content-Type': 'application/json' };

  let loginRes = http.post(
    `${BASE_URL}/api/benchmark/login`,
    JSON.stringify({ user_id: userId }),
    { headers, tags: { name: 'POST /benchmark/login' } }
  );

  let loginOk = check(loginRes, {
    'login status 200': (r) => r.status === 200,
    'login has token': (r) => r.json('success') === true,
  });
  loginErrors.add(!loginOk);
  loginDuration.add(loginRes.timings.duration);

  if (!loginOk) {
    sleep(1);
    return;
  }

  sleep(2);

  let dashboardRes = http.post(
    `${BASE_URL}/api/benchmark/dashboard`,
    JSON.stringify({ user_id: userId }),
    { headers, tags: { name: 'POST /benchmark/dashboard' } }
  );

  let dashboardOk = check(dashboardRes, {
    'dashboard status 200': (r) => r.status === 200,
    'dashboard has data': (r) => r.json('success') === true,
  });
  dashboardErrors.add(!dashboardOk);
  dashboardDuration.add(dashboardRes.timings.duration);

  sleep(3);

  let startRes = http.post(
    `${BASE_URL}/api/benchmark/start-exam`,
    JSON.stringify({ user_id: userId, exam_id: EXAM_ID }),
    { headers, tags: { name: 'POST /benchmark/start-exam' } }
  );

  let startOk = check(startRes, {
    'start-exam status 200': (r) => r.status === 200,
    'start-exam has session': (r) => r.json('success') === true,
  });
  startExamErrors.add(!startOk);
  startExamDuration.add(startRes.timings.duration);

  if (!startOk) {
    sleep(2);
    return;
  }

  let sessionToken = startRes.json('session_token');
  let questions = startRes.json('exam') || [];

  for (let i = 0; i < Math.min(5, questions.length); i++) {
    let questionId = questions[i].id;
    let answer = ['A', 'B', 'C', 'D', 'E'][Math.floor(Math.random() * 5)];

    let saveRes = http.post(
      `${BASE_URL}/api/benchmark/save-answer`,
      JSON.stringify({
        session_token: sessionToken,
        question_id: questionId,
        answer: answer,
      }),
      { headers, tags: { name: 'POST /benchmark/save-answer' } }
    );

    let saveOk = check(saveRes, {
      'save-answer status 200': (r) => r.status === 200,
      'save-answer success': (r) => r.json('success') === true,
    });
    saveAnswerErrors.add(!saveOk);
    saveAnswerDuration.add(saveRes.timings.duration);

    sleep(5);
  }

  let submitRes = http.post(
    `${BASE_URL}/api/benchmark/submit-exam`,
    JSON.stringify({
      session_token: sessionToken,
      exam_id: EXAM_ID,
    }),
    { headers, tags: { name: 'POST /benchmark/submit-exam' } }
  );

  let submitOk = check(submitRes, {
    'submit-exam status 200': (r) => r.status === 200,
    'submit-exam success': (r) => r.json('success') === true,
  });
  submitExamErrors.add(!submitOk);
  submitExamDuration.add(submitRes.timings.duration);

  sleep(2);
}
