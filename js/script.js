/**
 * Script principal pour l'administration du serveur
 */

// Fonction pour appliquer le thème
function applyTheme() {
  const theme = localStorage.getItem("theme") || "light"

  if (theme === "dark" || (theme === "auto" && window.matchMedia("(prefers-color-scheme: dark)").matches)) {
    document.body.classList.add("dark-mode")
  } else {
    document.body.classList.remove("dark-mode")
  }
}

// Appliquer le thème au chargement
document.addEventListener("DOMContentLoaded", () => {
  applyTheme()

  // Gestionnaires pour les boutons de thème
  const lightThemeBtn = document.getElementById("lightTheme")
  const darkThemeBtn = document.getElementById("darkTheme")
  const autoThemeBtn = document.getElementById("autoTheme")

  if (lightThemeBtn) {
    lightThemeBtn.addEventListener("click", () => {
      localStorage.setItem("theme", "light")
      applyTheme()
    })
  }

  if (darkThemeBtn) {
    darkThemeBtn.addEventListener("click", () => {
      localStorage.setItem("theme", "dark")
      applyTheme()
    })
  }

  if (autoThemeBtn) {
    autoThemeBtn.addEventListener("click", () => {
      localStorage.setItem("theme", "auto")
      applyTheme()
    })
  }

  // Mettre à jour les statistiques dans la barre latérale
  updateSidebarStats()
})

// Fonction pour mettre à jour les statistiques dans la barre latérale
function updateSidebarStats() {
  fetch("api/get-stats.php")
    .then((response) => response.json())
    .then((data) => {
      const cpuUsage = document.querySelector(".cpu-usage")
      const ramUsage = document.querySelector(".ram-usage")
      const diskUsage = document.querySelector(".disk-usage")
      const temp = document.querySelector(".temp")

      if (cpuUsage) cpuUsage.textContent = data.stats.cpu + "%"
      if (ramUsage) ramUsage.textContent = data.stats.memory + "%"
      if (diskUsage) diskUsage.textContent = data.stats.disk + "%"
      if (temp) temp.textContent = data.stats.temp + "°C"
    })
    .catch((error) => console.error("Erreur lors de la récupération des statistiques:", error))
}

// Mettre à jour les statistiques toutes les 30 secondes
setInterval(updateSidebarStats, 30000)

// Fonction pour exécuter une commande rapide
function executeQuickCommand() {
  const commandInput = document.getElementById("quickCommand")
  const outputDiv = document.getElementById("quickTerminalOutput")

  if (!commandInput || !outputDiv) return

  const command = commandInput.value.trim()
  if (!command) return

  // Ajouter la commande à l'affichage
  const hostname = document.querySelector(".terminal-line .text-success").textContent
  const commandLine = document.createElement("div")
  commandLine.className = "terminal-line"
  commandLine.innerHTML = `<span class="text-success">${hostname}</span> ${command}`
  outputDiv.appendChild(commandLine)

  // Exécuter la commande
  fetch("api/execute-command.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ command: command }),
  })
    .then((response) => response.json())
    .then((data) => {
      // Afficher le résultat
      const outputLine = document.createElement("div")
      outputLine.className = "terminal-line"
      outputLine.textContent = data.output
      outputDiv.appendChild(outputLine)

      // Ajouter une nouvelle ligne de prompt
      const newPromptLine = document.createElement("div")
      newPromptLine.className = "terminal-line"
      newPromptLine.innerHTML = `<span class="text-success">${hostname}</span> `
      outputDiv.appendChild(newPromptLine)

      // Faire défiler vers le bas
      outputDiv.scrollTop = outputDiv.scrollHeight

      // Effacer l'entrée
      commandInput.value = ""
    })
    .catch((error) => {
      console.error("Erreur lors de l'exécution de la commande:", error)
      const errorLine = document.createElement("div")
      errorLine.className = "terminal-line"
      errorLine.innerHTML = '<span class="text-error">Erreur lors de l\'exécution de la commande</span>'
      outputDiv.appendChild(errorLine)
      outputDiv.scrollTop = outputDiv.scrollHeight
    })
}

// Fonction pour afficher une notification
function showNotification(message, type = "info") {
  const notificationContainer = document.createElement("div")
  notificationContainer.className = "position-fixed top-0 end-0 p-3"
  notificationContainer.style.zIndex = "1050"

  const toast = document.createElement("div")
  toast.className = `toast show bg-${type}`
  toast.setAttribute("role", "alert")
  toast.setAttribute("aria-live", "assertive")
  toast.setAttribute("aria-atomic", "true")

  const toastHeader = document.createElement("div")
  toastHeader.className = "toast-header"

  const icon = document.createElement("i")
  icon.className = type === "success" ? "bi bi-check-circle-fill me-2" : "bi bi-info-circle-fill me-2"

  const title = document.createElement("strong")
  title.className = "me-auto"
  title.textContent = type.charAt(0).toUpperCase() + type.slice(1)

  const closeButton = document.createElement("button")
  closeButton.type = "button"
  closeButton.className = "btn-close"
  closeButton.setAttribute("data-bs-dismiss", "toast")
  closeButton.setAttribute("aria-label", "Close")

  const toastBody = document.createElement("div")
  toastBody.className = "toast-body text-white"
  toastBody.textContent = message

  toastHeader.appendChild(icon)
  toastHeader.appendChild(title)
  toastHeader.appendChild(closeButton)

  toast.appendChild(toastHeader)
  toast.appendChild(toastBody)

  notificationContainer.appendChild(toast)
  document.body.appendChild(notificationContainer)

  // Supprimer la notification après 5 secondes
  setTimeout(() => {
    notificationContainer.remove()
  }, 5000)
}

