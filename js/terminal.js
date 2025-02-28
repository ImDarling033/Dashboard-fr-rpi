/**
 * Script pour le terminal
 */

// Variables globales
const commandHistory = []
let historyIndex = -1
let currentDirectory = "~"

// Initialiser le terminal
document.addEventListener("DOMContentLoaded", () => {
  const terminalInput = document.getElementById("terminalInput")
  const clearTerminalBtn = document.getElementById("clearTerminal")
  const downloadOutputBtn = document.getElementById("downloadOutput")

  if (terminalInput) {
    terminalInput.focus()

    // Gestionnaire d'événements pour les touches
    terminalInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter") {
        e.preventDefault()
        processCommand()
      } else if (e.key === "ArrowUp") {
        e.preventDefault()
        navigateHistory("up")
      } else if (e.key === "ArrowDown") {
        e.preventDefault()
        navigateHistory("down")
      } else if (e.key === "Tab") {
        e.preventDefault()
        autoComplete()
      }
    })
  }

  // Gestionnaire pour le bouton d'effacement
  if (clearTerminalBtn) {
    clearTerminalBtn.addEventListener("click", () => {
      clearTerminal()
    })
  }

  // Gestionnaire pour le bouton de téléchargement
  if (downloadOutputBtn) {
    downloadOutputBtn.addEventListener("click", () => {
      downloadTerminalOutput()
    })
  }

  // Ajouter un clic sur le terminal pour le focus
  const terminal = document.getElementById("terminal")
  if (terminal) {
    terminal.addEventListener("click", () => {
      if (terminalInput) terminalInput.focus()
    })
  }
})

// Déclarations des fonctions manquantes (simulations)
function showSystemInfo() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return
  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = "System Info: (Simulated)"
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showNetworkInfo() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return
  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = "Network Info: (Simulated)"
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showDirectoryListing() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return
  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = "Directory Listing: (Simulated)"
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showCurrentDirectory() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return
  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = "Current Directory: " + currentDirectory
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function changeDirectory(path) {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  if (path === "..") {
    // Simulate going up one level (very basic)
    if (currentDirectory !== "~") {
      currentDirectory = "~" // Simplified
    }
  } else if (path === "~") {
    currentDirectory = "~"
  } else {
    currentDirectory = "~/" + path // Simplified
  }

  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = "Changed directory to: " + currentDirectory
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

// Traiter une commande
function processCommand() {
  const terminalInput = document.getElementById("terminalInput")
  const terminalOutput = document.getElementById("terminalOutput")
  const commandHistoryList = document.getElementById("commandHistory")

  if (!terminalInput || !terminalOutput) return

  const command = terminalInput.value.trim()
  if (!command) return

  // Ajouter à l'historique
  commandHistory.unshift(command)
  historyIndex = -1

  // Limiter l'historique à 20 commandes
  if (commandHistory.length > 20) {
    commandHistory.pop()
  }

  // Mettre à jour l'affichage de l'historique
  if (commandHistoryList) {
    const historyItem = document.createElement("li")
    historyItem.className = "list-group-item"
    historyItem.textContent = command

    // Ajouter un bouton pour réexécuter la commande
    const rerunButton = document.createElement("button")
    rerunButton.className = "btn btn-sm btn-outline-primary float-end"
    rerunButton.innerHTML = '<i class="bi bi-arrow-repeat"></i>'
    rerunButton.title = "Réexécuter cette commande"
    rerunButton.addEventListener("click", () => {
      terminalInput.value = command
      processCommand()
    })

    historyItem.appendChild(rerunButton)

    if (commandHistoryList.firstChild) {
      commandHistoryList.insertBefore(historyItem, commandHistoryList.firstChild)
    } else {
      commandHistoryList.appendChild(historyItem)
    }

    // Limiter l'affichage à 10 commandes
    if (commandHistoryList.children.length > 10) {
      commandHistoryList.removeChild(commandHistoryList.lastChild)
    }
  }

  // Ajouter la commande à l'affichage
  const hostname = document.querySelector(".terminal-line .text-success").textContent
  const commandLine = document.createElement("div")
  commandLine.className = "terminal-line"
  commandLine.innerHTML = `<span class="text-success">${hostname}</span> ${command}`
  terminalOutput.appendChild(commandLine)

  // Traiter les commandes internes
  if (command === "clear") {
    clearTerminal()
    terminalInput.value = ""
    return
  } else if (command === "help") {
    showHelp()
    terminalInput.value = ""
    return
  } else if (command === "sysinfo") {
    showSystemInfo()
    terminalInput.value = ""
    return
  } else if (command === "netinfo") {
    showNetworkInfo()
    terminalInput.value = ""
    return
  } else if (command === "ls" || command === "dir") {
    showDirectoryListing()
    terminalInput.value = ""
    return
  } else if (command === "pwd") {
    showCurrentDirectory()
    terminalInput.value = ""
    return
  } else if (command.startsWith("cd ")) {
    changeDirectory(command.substring(3))
    terminalInput.value = ""
    return
  } else if (command === "date") {
    showDate()
    terminalInput.value = ""
    return
  } else if (command === "whoami") {
    showWhoAmI()
    terminalInput.value = ""
    return
  } else if (command === "hostname") {
    showHostname()
    terminalInput.value = ""
    return
  } else if (command === "uname -a") {
    showUname()
    terminalInput.value = ""
    return
  } else if (command === "df -h") {
    showDiskUsage()
    terminalInput.value = ""
    return
  } else if (command === "free -h") {
    showMemoryUsage()
    terminalInput.value = ""
    return
  } else if (command === "ps aux") {
    showProcesses()
    terminalInput.value = ""
    return
  } else if (command === "ifconfig" || command === "ip addr") {
    showNetworkInterfaces()
    terminalInput.value = ""
    return
  } else if (command === "netstat -tuln") {
    showNetworkConnections()
    terminalInput.value = ""
    return
  } else if (command.startsWith("ping ")) {
    pingHost(command.substring(5))
    terminalInput.value = ""
    return
  } else if (command.startsWith("cat ")) {
    showFileContent(command.substring(4))
    terminalInput.value = ""
    return
  } else if (command === "uptime") {
    showUptime()
    terminalInput.value = ""
    return
  } else if (command === "who") {
    showConnectedUsers()
    terminalInput.value = ""
    return
  }

  // Exécuter la commande via l'API
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
      terminalOutput.appendChild(outputLine)

      // Faire défiler vers le bas
      terminalOutput.scrollTop = terminalOutput.scrollHeight

      // Effacer l'entrée
      terminalInput.value = ""
    })
    .catch((error) => {
      console.error("Erreur lors de l'exécution de la commande:", error)
      const errorLine = document.createElement("div")
      errorLine.className = "terminal-line"
      errorLine.innerHTML = '<span class="text-error">Erreur lors de l\'exécution de la commande</span>'
      terminalOutput.appendChild(errorLine)
      terminalOutput.scrollTop = terminalOutput.scrollHeight
      terminalInput.value = ""
    })
}

// Naviguer dans l'historique des commandes
function navigateHistory(direction) {
  const terminalInput = document.getElementById("terminalInput")
  if (!terminalInput || commandHistory.length === 0) return

  if (direction === "up") {
    historyIndex = Math.min(historyIndex + 1, commandHistory.length - 1)
  } else if (direction === "down") {
    historyIndex = Math.max(historyIndex - 1, -1)
  }

  if (historyIndex === -1) {
    terminalInput.value = ""
  } else {
    terminalInput.value = commandHistory[historyIndex]
  }
}

// Auto-complétion (simulation simple)
function autoComplete() {
  const terminalInput = document.getElementById("terminalInput")
  if (!terminalInput) return

  const command = terminalInput.value.trim()

  // Liste des commandes disponibles
  const availableCommands = [
    "clear",
    "help",
    "ls",
    "cat",
    "cd",
    "pwd",
    "sysinfo",
    "netinfo",
    "df",
    "du",
    "free",
    "top",
    "ps",
    "uptime",
    "who",
    "w",
    "ifconfig",
    "ip",
    "netstat",
    "ping",
    "hostname",
    "uname",
    "date",
    "whoami",
    "dir",
    "df -h",
    "free -h",
    "ps aux",
    "ip addr",
    "netstat -tuln",
    "uname -a",
  ]

  // Filtrer les commandes qui commencent par la saisie actuelle
  const matches = availableCommands.filter((cmd) => cmd.startsWith(command))

  if (matches.length === 1) {
    // Une seule correspondance, compléter
    terminalInput.value = matches[0] + " "
  } else if (matches.length > 1) {
    // Plusieurs correspondances, afficher les possibilités
    const terminalOutput = document.getElementById("terminalOutput")
    if (!terminalOutput) return

    const possibilitiesLine = document.createElement("div")
    possibilitiesLine.className = "terminal-line"
    possibilitiesLine.textContent = matches.join("  ")
    terminalOutput.appendChild(possibilitiesLine)

    // Ajouter une nouvelle ligne de prompt
    const hostname = document.querySelector(".terminal-line .text-success").textContent
    const promptLine = document.createElement("div")
    promptLine.className = "terminal-line"
    promptLine.innerHTML = `<span class="text-success">${hostname}</span> ${command}`
    terminalOutput.appendChild(promptLine)

    // Faire défiler vers le bas
    terminalOutput.scrollTop = terminalOutput.scrollHeight
  }
}

// Effacer le terminal
function clearTerminal() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  // Effacer tout le contenu
  terminalOutput.innerHTML = ""

  // Ajouter une ligne de bienvenue
  const hostname =
    "root@" +
    (document.querySelector(".terminal-title")
      ? document.querySelector(".terminal-title").textContent.split(" - ")[1]
      : "localhost") +
    ":~$"
  const welcomeLine = document.createElement("div")
  welcomeLine.className = "terminal-line"
  welcomeLine.innerHTML = `<span class="text-success">${hostname}</span> <span class="terminal-welcome">Terminal effacé. Tapez 'help' pour voir les commandes disponibles.</span>`
  terminalOutput.appendChild(welcomeLine)
}

// Télécharger le contenu du terminal
function downloadTerminalOutput() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  // Récupérer le contenu du terminal
  let content = ""
  const lines = terminalOutput.querySelectorAll(".terminal-line")
  lines.forEach((line) => {
    content += line.textContent + "\n"
  })

  // Créer un blob et un lien de téléchargement
  const blob = new Blob([content], { type: "text/plain" })
  const url = URL.createObjectURL(blob)

  const a = document.createElement("a")
  a.href = url
  a.download = "terminal_output_" + new Date().toISOString().replace(/[:.]/g, "-") + ".txt"
  document.body.appendChild(a)
  a.click()

  // Nettoyer
  setTimeout(() => {
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
  }, 0)
}

// Afficher l'aide
function showHelp() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  const helpText = `
Commandes disponibles:
  help                  Affiche cette aide
  clear                 Efface le terminal
  ls, dir               Liste les fichiers et dossiers
  cat [fichier]         Affiche le contenu d'un fichier
  cd [dossier]          Change de répertoire
  pwd                   Affiche le répertoire courant
  sysinfo               Affiche les informations système
  netinfo               Affiche les informations réseau
`

  const helpLines = helpText.split("\n")

  helpLines.forEach((line) => {
    const outputLine = document.createElement("div")
    outputLine.className = "terminal-line"
    outputLine.textContent = line
    terminalOutput.appendChild(outputLine)
  })

  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showDate() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  const now = new Date()
  const dateString = now.toLocaleDateString()
  const timeString = now.toLocaleTimeString()

  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = `Date: ${dateString}, Time: ${timeString}`
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showWhoAmI() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = "User: root" // Simulated
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showHostname() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  const hostname = document.querySelector(".terminal-title")
    ? document.querySelector(".terminal-title").textContent.split(" - ")[1]
    : "localhost"

  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = `Hostname: ${hostname}`
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showUname() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = "Linux (Simulated)"
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showDiskUsage() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = "Disk Usage: (Simulated)"
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showMemoryUsage() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = "Memory Usage: (Simulated)"
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showProcesses() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = "Processes: (Simulated)"
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showNetworkInterfaces() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = "Network Interfaces: (Simulated)"
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showNetworkConnections() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = "Network Connections: (Simulated)"
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function pingHost(host) {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = `Pinging ${host}: (Simulated)`
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showFileContent(filename) {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = `Content of ${filename}: (Simulated)`
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showUptime() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = "Uptime: (Simulated)"
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

function showConnectedUsers() {
  const terminalOutput = document.getElementById("terminalOutput")
  if (!terminalOutput) return

  const outputLine = document.createElement("div")
  outputLine.className = "terminal-line"
  outputLine.textContent = "Connected Users: (Simulated)"
  terminalOutput.appendChild(outputLine)
  terminalOutput.scrollTop = terminalOutput.scrollHeight
}

