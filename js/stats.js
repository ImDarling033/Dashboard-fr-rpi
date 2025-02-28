import { Chart } from "@/components/ui/chart"
let systemActivityChart
const maxDataPoints = 20
const initialData = {
  labels: [],
  datasets: [
    {
      label: "CPU",
      data: [],
      borderColor: "rgba(255, 99, 132, 1)",
      backgroundColor: "rgba(255, 99, 132, 0.2)",
      fill: true,
    },
    {
      label: "RAM",
      data: [],
      borderColor: "rgba(54, 162, 235, 1)",
      backgroundColor: "rgba(54, 162, 235, 0.2)",
      fill: true,
    },
    {
      label: "Stockage",
      data: [],
      borderColor: "rgba(255, 206, 86, 1)",
      backgroundColor: "rgba(255, 206, 86, 0.2)",
      fill: true,
    },
    {
      label: "Température",
      data: [],
      borderColor: "rgba(75, 192, 192, 1)",
      backgroundColor: "rgba(75, 192, 192, 0.2)",
      fill: true,
    },
  ],
}

function initSystemActivityChart() {
  const ctx = document.getElementById("systemActivityChart").getContext("2d")
  systemActivityChart = new Chart(ctx, {
    type: "line",
    data: initialData,
    options: {
      responsive: true,
      scales: {
        x: {
          type: "time",
          time: {
            unit: "second",
          },
        },
        y: {
          beginAtZero: true,
          max: 100,
        },
      },
      animation: {
        duration: 0,
      },
    },
  })
}

function updateStats() {
  fetch("api/get-stats.php")
    .then((response) => response.json())
    .then((data) => {
      // Mettre à jour les valeurs affichées
      document.querySelector(".progress-bar[aria-valuenow=\"<?php echo $stats['cpu']; ?>\"]").style.width =
        data.stats.cpu + "%"
      document.querySelector(".progress-bar[aria-valuenow=\"<?php echo $stats['cpu']; ?>\"]").textContent =
        data.stats.cpu + "%"
      document.querySelector(".progress-bar[aria-valuenow=\"<?php echo $stats['memory']; ?>\"]").style.width =
        data.stats.memory + "%"
      document.querySelector(".progress-bar[aria-valuenow=\"<?php echo $stats['memory']; ?>\"]").textContent =
        data.stats.memory + "%"
      document.querySelector(".progress-bar[aria-valuenow=\"<?php echo $stats['disk']; ?>\"]").style.width =
        data.stats.disk + "%"
      document.querySelector(".progress-bar[aria-valuenow=\"<?php echo $stats['disk']; ?>\"]").textContent =
        data.stats.disk + "%"
      document.querySelector(".progress-bar[aria-valuenow=\"<?php echo $stats['temp']; ?>\"]").style.width =
        Math.min(100, data.stats.temp * 2) + "%"
      document.querySelector(".progress-bar[aria-valuenow=\"<?php echo $stats['temp']; ?>\"]").textContent =
        data.stats.temp + "°C"

      // Mettre à jour le graphique
      const timestamp = new Date()
      systemActivityChart.data.labels.push(timestamp)
      systemActivityChart.data.datasets[0].data.push(data.stats.cpu)
      systemActivityChart.data.datasets[1].data.push(data.stats.memory)
      systemActivityChart.data.datasets[2].data.push(data.stats.disk)
      systemActivityChart.data.datasets[3].data.push(data.stats.temp)

      // Limiter le nombre de points de données
      if (systemActivityChart.data.labels.length > maxDataPoints) {
        systemActivityChart.data.labels.shift()
        systemActivityChart.data.datasets.forEach((dataset) => dataset.data.shift())
      }

      systemActivityChart.update()

      // Mettre à jour les autres informations
      document.querySelector('.card-text:contains("Charge:")').textContent = "Charge: " + data.loadAverage
      document.querySelector('.card-text:contains("Uptime:")').textContent = "Uptime: " + data.uptime
      document.querySelector('.badge:contains("' + data.ipv4 + '")').textContent = data.ipv4
      document.querySelector('.badge:contains("' + data.ipv6 + '")').textContent = data.ipv6
      document.querySelector('.badge:contains("' + data.connectedUsers + '")').textContent = data.connectedUsers
    })
    .catch((error) => console.error("Erreur lors de la récupération des statistiques:", error))
}

function executeQuickCommand() {
  const command = document.getElementById("quickCommand").value
  const output = document.getElementById("quickTerminalOutput")

  fetch("api/execute-command.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ command: command }),
  })
    .then((response) => response.json())
    .then((data) => {
      const newLine = document.createElement("div")
      newLine.className = "terminal-line"
      newLine.textContent = `${command}\n${data.output}`
      output.appendChild(newLine)
      output.scrollTop = output.scrollHeight
      document.getElementById("quickCommand").value = ""
    })
    .catch((error) => {
      console.error("Erreur lors de l'exécution de la commande:", error)
      const errorLine = document.createElement("div")
      errorLine.className = "terminal-line text-danger"
      errorLine.textContent = "Erreur lors de l'exécution de la commande"
      output.appendChild(errorLine)
    })
}

// Initialiser le graphique et commencer l'actualisation automatique
document.addEventListener("DOMContentLoaded", () => {
  initSystemActivityChart()
  updateStats()
  setInterval(updateStats, 10000)
})

