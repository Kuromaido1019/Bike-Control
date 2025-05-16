<template>
  <div class="dashboard-stats">
    <h2>Estadísticas del Dashboard</h2>
    <div v-if="loading">Cargando estadísticas...</div>
    <div v-else-if="error" class="alert alert-danger">{{ error }}</div>
    <div v-else>
      <div class="row">
        <div class="col-md-6">
          <div class="card mb-4">
            <div class="card-body">
              <h5 class="card-title">Usuarios registrados</h5>
              <p class="card-text">{{ stats.users }}</p>
              <canvas ref="usersChart"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card mb-4">
            <div class="card-body">
              <h5 class="card-title">Bicicletas</h5>
              <p class="card-text">{{ stats.bikes }}</p>
              <canvas ref="bikesChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Chart from 'chart.js/auto';

const stats = ref({ users: 0, bikes: 0, accesses: 0 });
const loading = ref(true);
const error = ref('');
const usersChart = ref(null);
const bikesChart = ref(null);
let usersChartInstance = null;
let bikesChartInstance = null;

onMounted(async () => {
  try {
    const res = await fetch('/api/dashboard-stats');
    if (!res.ok) throw new Error('No se pudo cargar estadísticas');
    const data = await res.json();
    stats.value = data;
  } catch (e) {
    error.value = e.message;
  } finally {
    loading.value = false;
  }

  usersChartInstance = new Chart(usersChart.value, {
    type: 'doughnut',
    data: {
      labels: ['Usuarios'],
      datasets: [{
        data: [stats.value.users],
        backgroundColor: ['#4e73df'],
      }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
  });

  bikesChartInstance = new Chart(bikesChart.value, {
    type: 'doughnut',
    data: {
      labels: ['Bicicletas'],
      datasets: [{
        data: [stats.value.bikes],
        backgroundColor: ['#1cc88a'],
      }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
  });
});

onUnmounted(() => {
  if (usersChartInstance) usersChartInstance.destroy();
  if (bikesChartInstance) bikesChartInstance.destroy();
});
</script>

<style scoped>
.dashboard-stats {
  padding: 1rem;
}
</style>
