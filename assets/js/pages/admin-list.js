// Asks for confirmation before a delete form is sent to the server.

(() => {
  const dialog = document.getElementById("delete-dialog");
  const deleteName = document.getElementById("delete-name");
  let pendingForm = null;

  document.addEventListener("submit", (event) => {
    if (!event.target.matches(".delete-form")) return;
    event.preventDefault();
    pendingForm = event.target;
    deleteName.textContent = pendingForm.dataset.name;
    dialog.showModal();
  });

  document.getElementById("delete-cancel").addEventListener("click", () => dialog.close());

  document.getElementById("delete-confirm").addEventListener("click", () => {
    dialog.close();
    pendingForm.submit();
  });
})();
