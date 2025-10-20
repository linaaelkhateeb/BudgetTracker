// js/signup.js
window.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector('form[action$="auth/signup.php"]');
  if (!form) return; // Only runs on the signup page

  const nameEl = document.getElementById("name");
  const emailEl = document.getElementById("email");
  const passEl = document.getElementById("password");
  const pass2El = document.getElementById("password_confirmation");

  // Show/hide password toggles
  const show1 = document.getElementById("showPass");
  const show2 = document.getElementById("showPass2");
  if (show1 && passEl) {
    show1.addEventListener("change", () => {
      passEl.type = show1.checked ? "text" : "password";
    });
  }
  if (show2 && pass2El) {
    show2.addEventListener("change", () => {
      pass2El.type = show2.checked ? "text" : "password";
    });
  }

  // Inline error helpers (client-side UX only; server still validates)
  function setError(input, msg) {
    clearError(input);
    if (!msg) return;
    const holder = input.closest(".mb-3") || input.parentElement || input;
    let box = holder.querySelector(".client-error");
    if (!box) {
      box = document.createElement("div");
      box.className = "client-error";
      box.style.color = "crimson";
      box.style.fontSize = "0.9rem";
      box.style.marginTop = "6px";
      holder.appendChild(box);
    }
    box.textContent = msg;
    input.setAttribute("aria-invalid", "true");
  }

  function clearError(input) {
    input.removeAttribute("aria-invalid");
    const holder = input.closest(".mb-3") || input.parentElement || input;
    const box = holder.querySelector(".client-error");
    if (box) box.textContent = "";
  }

  function isValidComEmail(v) {
    if (!v) return false;
    return v.toLowerCase().endsWith(".com") && /\S+@\S+\.\S+/.test(v);
  }

  function isValidPassword(v) {
    if (!v || v.length < 8) return false;
    const hasLetter = /[A-Za-z]/.test(v);
    const hasDigit = /\d/.test(v);
    return hasLetter && hasDigit;
  }

  // Live validation
  if (emailEl) {
    emailEl.addEventListener("input", () => {
      if (!emailEl.value) {
        clearError(emailEl);
        return;
      }
      setError(
        emailEl,
        isValidComEmail(emailEl.value) ? "" : "Please enter a valid .com email."
      );
    });
  }
  if (passEl) {
    passEl.addEventListener("input", () => {
      if (!passEl.value) {
        clearError(passEl);
        return;
      }
      setError(
        passEl,
        isValidPassword(passEl.value)
          ? ""
          : "Password must be at least 8 characters and include a letter and a number."
      );
      if (pass2El && pass2El.value) {
        setError(
          pass2El,
          pass2El.value === passEl.value ? "" : "Passwords do not match."
        );
      }
    });
  }
  if (pass2El) {
    pass2El.addEventListener("input", () => {
      if (!pass2El.value) {
        clearError(pass2El);
        return;
      }
      setError(
        pass2El,
        pass2El.value === (passEl ? passEl.value : "")
          ? ""
          : "Passwords do not match."
      );
    });
  }
  if (nameEl) {
    nameEl.addEventListener("input", () => {
      if (!nameEl.value) {
        clearError(nameEl);
        return;
      }
      setError(
        nameEl,
        nameEl.value.trim().length >= 2 ? "" : "Please enter your full name."
      );
    });
  }

  // Final gate
  form.addEventListener("submit", (e) => {
    let ok = true;

    if (nameEl) {
      const v = (nameEl.value || "").trim();
      if (v.length < 2) {
        setError(nameEl, "Please enter your full name.");
        ok = false;
      } else {
        clearError(nameEl);
      }
    }

    if (emailEl) {
      if (!isValidComEmail(emailEl.value)) {
        setError(emailEl, "Please enter a valid .com email.");
        ok = false;
      } else {
        clearError(emailEl);
      }
    }

    if (passEl) {
      if (!isValidPassword(passEl.value)) {
        setError(
          passEl,
          "Password must be at least 8 characters and include a letter and a number."
        );
        ok = false;
      } else {
        clearError(passEl);
      }
    }

    if (passEl && pass2El) {
      if (passEl.value !== pass2El.value) {
        setError(pass2El, "Passwords do not match.");
        ok = false;
      } else {
        clearError(pass2El);
      }
    }

    if (!ok) e.preventDefault();
  });
});
