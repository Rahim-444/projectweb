document.addEventListener("DOMContentLoaded", function() {
  // Afficher/masquer les messages d'alerte après un délai
  const alerts = document.querySelectorAll(".alert");
  if (alerts.length > 0) {
    setTimeout(function() {
      alerts.forEach(function(alert) {
        alert.style.opacity = "0";
        setTimeout(function() {
          alert.style.display = "none";
        }, 500);
      });
    }, 5000);
  }

  // Gérer les formulaires de quantité dans le panier
  const quantityForms = document.querySelectorAll(".quantite-form");
  quantityForms.forEach(function(form) {
    const input = form.querySelector('input[name="quantite"]');
    const originalValue = input.value;

    input.addEventListener("change", function() {
      // Soumettre automatiquement le formulaire si la valeur a changé
      if (this.value !== originalValue) {
        form.submit();
      }
    });
  });

  // Animation de défilement fluide pour les ancres
  document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
    anchor.addEventListener("click", function(e) {
      // Uniquement si l'ancre pointe vers un élément existant
      const targetId = this.getAttribute("href");
      if (targetId === "#") return;

      const targetElement = document.querySelector(targetId);
      if (targetElement) {
        e.preventDefault();
        window.scrollTo({
          top: targetElement.offsetTop - 100,
          behavior: "smooth",
        });
      }
    });
  });

  // Validation des formulaires
  const forms = document.querySelectorAll("form");
  forms.forEach(function(form) {
    form.addEventListener("submit", function(event) {
      let isValid = true;

      // Vérifier les champs requis
      const requiredInputs = form.querySelectorAll("[required]");
      requiredInputs.forEach(function(input) {
        if (!input.value.trim()) {
          isValid = false;
          input.classList.add("error");

          // Créer un message d'erreur s'il n'existe pas déjà
          let errorMessage = input.nextElementSibling;
          if (
            !errorMessage ||
            !errorMessage.classList.contains("error-message")
          ) {
            errorMessage = document.createElement("p");
            errorMessage.classList.add("error-message");
            errorMessage.style.color = "#e74c3c";
            errorMessage.style.fontSize = "0.8rem";
            errorMessage.style.marginTop = "5px";
            input.parentNode.insertBefore(errorMessage, input.nextSibling);
          }

          errorMessage.textContent = "Ce champ est obligatoire.";
        } else {
          input.classList.remove("error");

          // Supprimer le message d'erreur s'il existe
          const errorMessage = input.nextElementSibling;
          if (
            errorMessage &&
            errorMessage.classList.contains("error-message")
          ) {
            errorMessage.remove();
          }
        }
      });

      // Vérifier la validation des emails
      const emailInputs = form.querySelectorAll('input[type="email"]');
      emailInputs.forEach(function(input) {
        if (input.value.trim() && !isValidEmail(input.value)) {
          isValid = false;
          input.classList.add("error");

          // Créer un message d'erreur s'il n'existe pas déjà
          let errorMessage = input.nextElementSibling;
          if (
            !errorMessage ||
            !errorMessage.classList.contains("error-message")
          ) {
            errorMessage = document.createElement("p");
            errorMessage.classList.add("error-message");
            errorMessage.style.color = "#e74c3c";
            errorMessage.style.fontSize = "0.8rem";
            errorMessage.style.marginTop = "5px";
            input.parentNode.insertBefore(errorMessage, input.nextSibling);
          }

          errorMessage.textContent =
            "Veuillez entrer une adresse email valide.";
        }
      });

      // Vérifier la correspondance des mots de passe
      const passwordInputs = form.querySelectorAll('input[type="password"]');
      if (
        passwordInputs.length === 2 &&
        passwordInputs[0].value !== passwordInputs[1].value
      ) {
        isValid = false;
        passwordInputs.forEach(function(input) {
          input.classList.add("error");

          let errorMessage = input.nextElementSibling;
          if (
            !errorMessage ||
            !errorMessage.classList.contains("error-message")
          ) {
            errorMessage = document.createElement("p");
            errorMessage.classList.add("error-message");
            errorMessage.style.color = "#e74c3c";
            errorMessage.style.fontSize = "0.8rem";
            errorMessage.style.marginTop = "5px";
            input.parentNode.insertBefore(errorMessage, input.nextSibling);
          }

          errorMessage.textContent = "Les mots de passe ne correspondent pas.";
        });
      }

      if (!isValid) {
        event.preventDefault();
      }
    });
  });

  // Prévisualisation des images
  const imageInput = document.getElementById("image");
  if (imageInput) {
    imageInput.addEventListener("change", function() {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          // Créer ou mettre à jour l'aperçu
          let preview = document.getElementById("image-preview");
          if (!preview) {
            preview = document.createElement("div");
            preview.id = "image-preview";
            preview.style.marginTop = "10px";
            imageInput.parentNode.appendChild(preview);
          }

          preview.innerHTML = `
            <p>Aperçu :</p>
            <img src="${e.target.result}" alt="Aperçu" style="max-width: 200px; max-height: 200px; margin-top: 5px;">
          `;
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // Menu d'administration
  const menuItems = document.querySelectorAll(".admin-menu li a");
  const sections = document.querySelectorAll(".admin-section");

  // Vérifier si un hash est présent dans l'URL
  const hash = window.location.hash;
  if (hash) {
    menuItems.forEach(function(mi) {
      mi.parentElement.classList.remove("active");
      if (mi.getAttribute("href") === hash) {
        mi.parentElement.classList.add("active");
      }
    });

    sections.forEach(function(section) {
      section.classList.remove("active");
    });

    const targetId = hash.substring(1);
    const targetSection = document.getElementById(targetId);
    if (targetSection) {
      targetSection.classList.add("active");
    }
  }

  menuItems.forEach(function(item) {
    item.addEventListener("click", function(e) {
      e.preventDefault();

      menuItems.forEach(function(mi) {
        mi.parentElement.classList.remove("active");
      });

      this.parentElement.classList.add("active");

      sections.forEach(function(section) {
        section.classList.remove("active");
      });

      const targetId = this.getAttribute("href").substring(1);
      document.getElementById(targetId).classList.add("active");
    });
  });
});

// Fonction pour valider les emails
function isValidEmail(email) {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return regex.test(email);
}
