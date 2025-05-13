document.addEventListener("DOMContentLoaded", () => {
	const passwordInput = document.getElementById("inputPassword");
	const eyeIcon = document.querySelector(".input--password i");

	if (passwordInput && eyeIcon) {
		eyeIcon.addEventListener("click", () => {
			const type =
				passwordInput.getAttribute("type") === "password" ? "text" : "password";
			passwordInput.setAttribute("type", type);

			// Toggle eye icon
			eyeIcon.classList.toggle("fa-eye");
			eyeIcon.classList.toggle("fa-eye-slash");
		});
	}
});
