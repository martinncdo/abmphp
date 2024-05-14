let registroEliminado = document.querySelector(".delete-registro");
let registroActualizado = document.querySelector(".edit-registro");
let modifCampo = document.querySelector(".notif-campo");

let alertas = [registroEliminado, registroActualizado, modifCampo];

alertas.forEach(alerta => {
  if (alerta !== null) {
    setTimeout(() => {
      alerta.classList.toggle("disabled")
    }, 1500);
  }
})
