import { Chart } from "@/components/ui/chart"
/**
 * Script pour les statistiques du serveur
 */

// Variables pour les graphiques
let systemActivityChart
const cpuData = []
const memoryData = []
const diskData = []
const labels = []

// Initialiser le graphique d'activité système
function initSystemActivityChart() {
  const ctx = document.getElementById("systemActivityChart")
  if (!ctx) return

  // Initialiser les données
  for (let i = 0; i < 10; i++) {
    labels.push("")
    cpuData.push(0)
    memoryData.push(0)
    diskData.push(0)
  }

  systemActivityChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "CPU",
          data: cpuData,
          borderColor: "#0d6efd",
          backgroundColor: "rgba(13, 110, 253, 0.1)",
          tension: 0.4,
          fill: true,
        },
        {
          label: "RAM",
          data: memoryData,
          borderColor: "#198754",
          backgroundColor: "rgba(25, 135, 84, 0.1)",
          tension: 0.4,
          fill: true,
        },
        {
          label: "Disque",
          data: diskData,
          borderColor: "#0dcaf0",
          backgroundColor: "rgba(13, 202, 240, 0.1)",
          tension: 0.4,
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          title: {
            display: true,
            text: "Utilisation (%)",
          },
        },
      },
      plugins: {
        legend: {
          position: "top",
        },
        tooltip: {
          mode: "index",
          intersect: false,
        },
      },
    },
  })
}

// Déclarer la variable updateSidebarStats (ou importer si nécessaire)
function updateSidebarStats() {
  // Ajoutez ici le code pour mettre à jour les statistiques de la barre latérale
  // ou importez la fonction si elle est définie dans un autre fichier.
  console.log("Fonction updateSidebarStats appelée")
}

// Mettre à jour les statistiques
function updateStats() {
  fetch("api/get-stats.php")
    .then((response) => response.json())
    .then((data) => {
      // Mettre à jour les cartes
      updateStatCards(data)

      // Mettre à jour le graphique
      updateSystemActivityChart(data)

      // Mettre à jour les statistiques de la barre latérale
      updateSidebarStats()
    })
    .catch((error) => console.error("Erreur lors de la récupération des statistiques:", error))
}

// Mettre à jour les cartes de statistiques
function updateStatCards(data) {
  // CPU
  const cpuProgress = document.querySelector('.card-title:contains("CPU") + .progress .progress-bar')
  const cpuText = document.querySelector('.card-title:contains("CPU") + .progress .progress-bar')
  const cpuLoad = document.querySelector('.card-title:contains("CPU") + .progress + .card-text')

  if (cpuProgress) cpuProgress.style.width = data.stats.cpu + "%"
  if (cpuText) cpuText.textContent = data.stats.cpu + "%"
  if (cpuLoad) cpuLoad.textContent = "Charge: " + data.loadAverage

  // RAM
  const ramProgress = document.querySelector('.card-title:contains("RAM") + .progress .progress-bar')
  const ramText = document.querySelector('.card-title:contains("RAM") + .progress .progress-bar')
  const ramUsage = document.querySelector('.card-title:contains("RAM") + .progress + .card-text')

  if (ramProgress) ramProgress.style.width = data.stats.memory + "%"
  if (ramText) ramText.textContent = data.stats.memory + "%"
  if (ramUsage) ramUsage.textContent = data.stats.memory_used + " / " + data.stats.memory_total

  // Stockage
  const diskProgress = document.querySelector('.card-title:contains("Stockage") + .progress .progress-bar')
  const diskText = document.querySelector('.card-title:contains("Stockage") + .progress .progress-bar')
  const diskUsage = document.querySelector('.card-title:contains("Stockage") + .progress + .card-text')

  if (diskProgress) diskProgress.style.width = data.stats.disk + "%"
  if (diskText) diskText.textContent = data.stats.disk + "%"
  if (diskUsage) diskUsage.textContent = data.diskUsage

  // Température
  const tempProgress = document.querySelector('.card-title:contains("Température") + .progress .progress-bar')
  const tempText = document.querySelector('.card-title:contains("Température") + .progress .progress-bar')
  const uptime = document.querySelector('.card-title:contains("Température") + .progress + .card-text')

  if (tempProgress) tempProgress.style.width = Math.min(100, data.stats.temp * 2) + "%"
  if (tempText) tempText.textContent = data.stats.temp + "°C"
  if (uptime) uptime.textContent = "Uptime: " + data.uptime

  // Informations réseau
  const ipv4 = document.querySelector('.list-group-item:contains("IPv4") .badge')
  const ipv6 = document.querySelector('.list-group-item:contains("IPv6") .badge')
  const connectedUsers = document.querySelector('.list-group-item:contains("Utilisateurs connectés") .badge')

  if (ipv4) ipv4.textContent = data.ipv4
  if (ipv6) ipv6.textContent = data.ipv6
  if (connectedUsers) connectedUsers.textContent = data.connectedUsers
}

// Mettre à jour le graphique d'activité système
function updateSystemActivityChart(data) {
  if (!systemActivityChart) return

  // Ajouter les nouvelles données
  const now = new Date()
  const timeString =
    now.getHours() +
    ":" +
    (now.getMinutes() < 10 ? "0" : "") +
    now.getMinutes() +
    ":" +
    (now.getSeconds() < 10 ? "0" : "") +
    now.getSeconds()

  labels.push(timeString)
  cpuData.push(data.stats.cpu)
  memoryData.push(data.stats.memory)
  diskData.push(data.stats.disk)

  // Supprimer les anciennes données
  if (labels.length > 10) {
    labels.shift()
    cpuData.shift()
    memoryData.shift()
    diskData.shift()
  }

  // Mettre à jour le graphique
  systemActivityChart.update()
}

// Déclarer la variable jQuery (ou importer si nécessaire)
if (typeof jQuery === "undefined") {
  console.error("jQuery is not defined. Please include jQuery in your project.")
} else {
  // Fonction pour sélectionner des éléments avec du texte spécifique
  jQuery.expr[":"].contains = (a, i, m) => jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0
}

