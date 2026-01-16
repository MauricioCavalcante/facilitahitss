function setActionType(type) {
  document.getElementById("action_type").value = type;
}
function validateAndFinalizeReport(event, type) {
  event.preventDefault();

  const start = document.getElementById("period_start").value;
  const end = document.getElementById("period_end").value;

  if (!start || !end) {
    alert("Por favor, preencha as datas de início e fim do período.");
    return false;
  }

  if (end < start) {
    alert("A data final deve ser igual ou posterior à data de início.");
    return false;
  }

  const indicatorInputs = document.querySelectorAll('[name^="inputs["]');
  let allFilled = true;

  indicatorInputs.forEach((input) => {
    if (input.value === "" || input.value === null) {
      allFilled = false;
    }
  });

  if (!allFilled) {
    alert(
      "Por favor, preencha todos os campos dos indicadores antes de finalizar o relatório."
    );
    return false;
  }

  document.getElementById("action_type").value = type;
  event.target.closest("form").submit();
}

// Validação padrão de datas no envio do formulário
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector(".needs-validation");
  const validationDiv = document.getElementById("validation");

  if (!form) return;

  form.addEventListener(
    "submit",
    function (event) {
      let periodStart = document.getElementById("period_start");
      let periodEnd = document.getElementById("period_end");
      let isValid = true;

      let existingAlert = document.getElementById("date-validation-error");
      if (existingAlert) existingAlert.remove();

      periodStart.classList.remove("is-invalid");
      periodEnd.classList.remove("is-invalid");

      if (!periodStart.value.trim()) {
        periodStart.classList.add("is-invalid");
        isValid = false;
      }

      if (!periodEnd.value.trim()) {
        periodEnd.classList.add("is-invalid");
        isValid = false;
      }

      if (
        periodStart.value &&
        periodEnd.value &&
        periodEnd.value < periodStart.value
      ) {
        periodStart.classList.add("is-invalid");
        periodEnd.classList.add("is-invalid");
        isValid = false;

        const errorMessage = document.createElement("div");
        errorMessage.id = "date-validation-error";
        errorMessage.className = "text-danger";
        errorMessage.innerHTML =
          "O Final do período deve ser uma data igual ou posterior à data de início.";
        validationDiv.appendChild(errorMessage);
      }

      if (!isValid) {
        event.preventDefault();
        event.stopPropagation();

        validationDiv.scrollIntoView({
          behavior: "smooth",
          block: "center",
        });
      }
    },
    false
  );
});
