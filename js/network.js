import { Chart } from "@/components/ui/chart"
let networkTrafficChart
const maxDataPoints = 20
const initialData = {
  labels: [],
  datasets: [
    {
      label: "Téléchargement",
      data: [],
      borderColor: "rgba(75, 192, 192, 1)",
      backgroundColor: "rgba(75, 192, 192, 0.2)",
      fill: true,
    },
    {
      label: "Téléversement",
      data: [],
      borderColor: "rgba(255, 99, 132, 1)",
      backgroundColor: "rgba(255, 99, 132, 0.2)",
      fill: true,
    },
  ],
}

function initNetworkTrafficChart() {
  const ctx = document.getElementById("networkTrafficChart").getContext("2d")
  networkTrafficChart = new Chart(ctx, {
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
          title: {
            display: true,
            text: "KB/s",
          },
        },
      },
      animation: {
        duration: 0,
      },
    },
  })
}

function updateNetworkInfo() {
    fetch('api/get-network-info.php')
        .then(response => response.json())
        .then(data => {
            // Mettre à jour les informations réseau
            document.getElementById('hostname').textContent = data.hostname;
            document.\

