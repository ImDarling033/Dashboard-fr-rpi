/**
 * Script pour la page des paramètres
 */

document.addEventListener("DOMContentLoaded", () => {
  // Charger les paramètres actuels
  loadSettings()

  // Gestionnaire pour le formulaire des paramètres du serveur
  const serverSettingsForm = document.getElementById("serverSettingsForm")
  if (serverSettingsForm) {
    serverSettingsForm.addEventListener("submit", (e) => {
      e.preventDefault()
      saveServerSettings()
    })
  }

  // Gestionnaire pour le formulaire des paramètres d'interface
  const uiSettingsForm = document.getElementById("uiSettingsForm")
  if (uiSettingsForm) {
    uiSettingsForm.addEventListener("submit", (e) => {
      e.preventDefault()
      saveUISettings()
    })
  }

  // Gestionnaire pour le bouton de réinitialisation des paramètres d'interface
  const resetUISettingsBtn = document.getElementById("resetUISettings")
  if (resetUISettingsBtn) {
    resetUISettingsBtn.addEventListener("click", () => {
      resetUISettings()
    })
  }

  // Gestionnaire pour le formulaire de changement de mot de passe
  const securitySettingsForm = document.getElementById("securitySettingsForm")
  if (securitySettingsForm) {
    securitySettingsForm.addEventListener("submit", (e) => {
      e.preventDefault()
      changePassword()
    })
  }

  // Gestionnaires pour les boutons radio de thème
  const themeRadios = document.querySelectorAll('input[name="theme"]')
  themeRadios.forEach((radio) => {
    radio.addEventListener("change", function () {
      if (this.checked) {
        applyTheme(this.value)
      }
    })
  })

  // Gestionnaire pour le sélecteur de couleur principale
  const primaryColorInput = document.getElementById("primaryColor")
  if (primaryColorInput) {
    primaryColorInput.addEventListener("change", function () {
      applyPrimaryColor(this.value)
    })
  }

  // Gestionnaire pour la taille de police
  const fontSizeSelect = document.getElementById("fontSize")
  if (fontSizeSelect) {
    fontSizeSelect.addEventListener("change", function () {
      applyFontSize(this.value)
    })
  }

  // Gestionnaire pour la position de la barre latérale
  const sidebarPositionSelect = document.getElementById("sidebarPosition")
  if (sidebarPositionSelect) {
    sidebarPositionSelect.addEventListener("change", function () {
      applySidebarPosition(this.value)
    })
  }

  // Gestionnaire pour le mode compact
  const compactModeCheckbox = document.getElementById("compactMode")
  if (compactModeCheckbox) {
    compactModeCheckbox.addEventListener("change", function () {
      applyCompactMode(this.checked)
    })
  }
})

// Charger les paramètres actuels
function loadSettings() {
  // Charger les paramètres d'interface depuis localStorage
  const theme = localStorage.getItem("theme") || "light"
  const primaryColor = localStorage.getItem("primaryColor") || "#0d6efd"
  const fontSize = localStorage.getItem("fontSize") || "medium"
  const sidebarPosition = localStorage.getItem("sidebarPosition") || "left"
  const compactMode = localStorage.getItem("compactMode") === "true"

  // Appliquer les paramètres d'interface
  document.getElementById("theme" + theme.charAt(0).toUpperCase() + theme.slice(1)).checked = true
  document.getElementById("primaryColor").value = primaryColor
  document.getElementById("fontSize").value = fontSize
  document.getElementById("sidebarPosition").value = sidebarPosition
  document.getElementById("compactMode").checked = compactMode

  // Appliquer les styles
  applyTheme(theme)
  applyPrimaryColor(primaryColor)
  applyFontSize(fontSize)
  applySidebarPosition(sidebarPosition)
  applyCompactMode(compactMode)
}

// Sauvegarder les paramètres du serveur
function saveServerSettings() {
  const serverName = document.getElementById("serverName").value
  const timeZone = document.getElementById("timeZone").value
  const maxUploadSize = document.getElementById("maxUploadSize").value
  const maxExecutionTime = document.getElementById("maxExecutionTime").value
  const memoryLimit = document.getElementById("memoryLimit").value

  // Créer le formulaire de données
  const formData = new FormData()
  formData.append("action", "save_server_settings")
  formData.append("serverName", serverName)
  formData.append("timeZone", timeZone)
  formData.append("maxUploadSize", maxUploadSize)
  formData.append("maxExecutionTime", maxExecutionTime)
  formData.append("memoryLimit", memoryLimit)

  // Simuler une sauvegarde réussie (dans un environnement réel, vous enverriez ces données à une API)
  setTimeout(() => {
    showNotification("Paramètres du serveur enregistrés avec succès", "success")
  }, 500)
}

// Sauvegarder les paramètres d'interface
function saveUISettings() {
  const theme = document.querySelector('input[name="theme"]:checked').value
  const primaryColor = document.getElementById("primaryColor").value
  const fontSize = document.getElementById("fontSize").value
  const sidebarPosition = document.getElementById("sidebarPosition").value
  const compactMode = document.getElementById("compactMode").checked

  // Sauvegarder dans localStorage
  localStorage.setItem("theme", theme)
  localStorage.setItem("primaryColor", primaryColor)
  localStorage.setItem("fontSize", fontSize)
  localStorage.setItem("sidebarPosition", sidebarPosition)
  localStorage.setItem("compactMode", compactMode.toString())

  // Appliquer les styles
  applyTheme(theme)
  applyPrimaryColor(primaryColor)
  applyFontSize(fontSize)
  applySidebarPosition(sidebarPosition)
  applyCompactMode(compactMode)

  showNotification("Paramètres d'interface appliqués avec succès", "success")
}

// Réinitialiser les paramètres d'interface
function resetUISettings() {
  // Valeurs par défaut
  const defaultTheme = "light"
  const defaultPrimaryColor = "#0d6efd"
  const defaultFontSize = "medium"
  const defaultSidebarPosition = "left"
  const defaultCompactMode = false

  // Mettre à jour les contrôles
  document.getElementById("themeLight").checked = true
  document.getElementById("primaryColor").value = defaultPrimaryColor
  document.getElementById("fontSize").value = defaultFontSize
  document.getElementById("sidebarPosition").value = defaultSidebarPosition
  document.getElementById("compactMode").checked = defaultCompactMode

  // Sauvegarder dans localStorage
  localStorage.setItem("theme", defaultTheme)
  localStorage.setItem("primaryColor", defaultPrimaryColor)
  localStorage.setItem("fontSize", defaultFontSize)
  localStorage.setItem("sidebarPosition", defaultSidebarPosition)
  localStorage.setItem("compactMode", defaultCompactMode.toString())

  // Appliquer les styles
  applyTheme(defaultTheme)
  applyPrimaryColor(defaultPrimaryColor)
  applyFontSize(defaultFontSize)
  applySidebarPosition(defaultSidebarPosition)
  applyCompactMode(defaultCompactMode)

  showNotification("Paramètres d'interface réinitialisés", "info")
}

// Changer le mot de passe
function changePassword() {
  const currentPassword = document.getElementById("currentPassword").value
  const newPassword = document.getElementById("newPassword").value
  const confirmPassword = document.getElementById("confirmPassword").value

  // Vérifier que les mots de passe correspondent
  if (newPassword !== confirmPassword) {
    showNotification("Les mots de passe ne correspondent pas", "danger")
    return
  }

  // Créer le formulaire de données
  const formData = new FormData()
  formData.append("action", "change_password")
  formData.append("currentPassword", currentPassword)
  formData.append("newPassword", newPassword)

  // Simuler un changement réussi (dans un environnement réel, vous enverriez ces données à une API)
  setTimeout(() => {
    showNotification("Mot de passe changé avec succès", "success")
    document.getElementById("currentPassword").value = ""
    document.getElementById("newPassword").value = ""
    document.getElementById("confirmPassword").value = ""
  }, 500)
}

// Appliquer le thème
function applyTheme(theme) {
  if (theme === "dark" || (theme === "auto" && window.matchMedia("(prefers-color-scheme: dark)").matches)) {
    document.body.classList.add("dark-mode")
  } else {
    document.body.classList.remove("dark-mode")
  }
}

// Appliquer la couleur principale
function applyPrimaryColor(color) {
  document.documentElement.style.setProperty("--primary-color", color)

  // Créer une feuille de style pour remplacer les classes Bootstrap
  let style = document.getElementById("custom-primary-color")
  if (!style) {
    style = document.createElement("style")
    style.id = "custom-primary-color"
    document.head.appendChild(style)
  }

  style.textContent = `
        .btn-primary {
            background-color: ${color};
            border-color: ${color};
        }
        .btn-primary:hover {
            background-color: ${adjustColor(color, -20)};
            border-color: ${adjustColor(color, -20)};
        }
        .btn-outline-primary {
            color: ${color};
            border-color: ${color};
        }
        .btn-outline-primary:hover {
            background-color: ${color};
            border-color: ${color};
        }
        .text-primary {
            color: ${color} !important;
        }
        .bg-primary {
            background-color: ${color} !important;
        }
        .border-primary {
            border-color: ${color} !important;
        }
        .badge.bg-primary {
            background-color: ${color} !important;
        }
        .nav-link.active {
            color: ${color} !important;
        }
        .page-link {
            color: ${color};
        }
        .page-item.active .page-link {
            background-color: ${color};
            border-color: ${color};
        }
    `
}

// Ajuster une couleur (éclaircir ou assombrir)
function adjustColor(color, amount) {
  return (
    "#" +
    color
      .replace(/^#/, "")
      .replace(/../g, (color) =>
        ("0" + Math.min(255, Math.max(0, Number.parseInt(color, 16) + amount)).toString(16)).substr(-2),
      )
  )
}

// Appliquer la taille de police
function applyFontSize(size) {
  let rootFontSize

  switch (size) {
    case "small":
      rootFontSize = "14px"
      break
    case "medium":
      rootFontSize = "16px"
      break
    case "large":
      rootFontSize = "18px"
      break
    default:
      rootFontSize = "16px"
  }

  document.documentElement.style.fontSize = rootFontSize
}

// Appliquer la position de la barre latérale
function applySidebarPosition(position) {
  const sidebar = document.getElementById("sidebarMenu")
  const main = document.querySelector("main")

  if (!sidebar || !main) return

  if (position === "right") {
    sidebar.classList.add("order-last")
    main.classList.add("order-first")
  } else {
    sidebar.classList.remove("order-last")
    main.classList.remove("order-first")
  }
}

// Appliquer le mode compact
function applyCompactMode(enabled) {
  if (enabled) {
    document.body.classList.add("compact-mode")
  } else {
    document.body.classList.remove("compact-mode")
  }
}

// Afficher une notification
function showNotification(message, type = "info") {
  const notificationContainer = document.createElement("div")
  notificationContainer.className = "position-fixed top-0 end-0 p-3"
  notificationContainer.style.zIndex = "1050"

  const toast = document.createElement("div")
  toast.className = `toast show bg-${type} text-white`
  toast.setAttribute("role", "alert")
  toast.setAttribute("aria-live", "assertive")
  toast.setAttribute("aria-atomic", "true")

  const toastHeader = document.createElement("div")
  toastHeader.className = "toast-header bg-" + type + " text-white"

  const icon = document.createElement("i")
  icon.className = type === "success" ? "bi bi-check-circle-fill me-2" : "bi bi-info-circle-fill me-2"

  const title = document.createElement("strong")
  title.className = "me-auto"
  title.textContent = type.charAt(0).toUpperCase() + type.slice(1)

  const closeButton = document.createElement("button")
  closeButton.type = "button"
  closeButton.className = "btn-close btn-close-white"
  closeButton.setAttribute("data-bs-dismiss", "toast")
  closeButton.setAttribute("aria-label", "Close")

  const toastBody = document.createElement("div")
  toastBody.className = "toast-body"
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

