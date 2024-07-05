let alertas = document.querySelectorAll(".alert");

alertas.forEach(alerta => {
  if (alerta !== null) {
    setTimeout(() => {
      alerta.classList.toggle("disabled")
    }, 1500);
  }
})
