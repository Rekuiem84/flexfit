// Initialise la galerie produit sur la page
function initProductGallery() {
	// Récupère le conteneur de l'image principale
	const mainImageContainer = document.querySelector("#shop-image-main-cont");
	if (!mainImageContainer) return; // si pas de galerie, on quitte

	// Image principale et miniatures
	const mainImage = mainImageContainer.querySelector(".main-image");
	const detailImages = document
		.querySelector("#shop-image-details-cont")
		.querySelectorAll("img");

	// Fonction utilitaire pour contraindre une valeur entre min et max
	const clamp = (v, min, max) => Math.min(Math.max(v, min), max);

	// Change l'image principale et met à jour la miniature cliquée
	const switchMainImage = (newSrc) => {
		const previousSrc = mainImage.src;
		mainImage.src = newSrc;
		detailImages.forEach((img) => {
			if (img.src === newSrc) {
				img.src = previousSrc;
			}
		});
	};

	// Gestion du zoom au survol
	const handleZoom = (e) => {
		const rect = mainImageContainer.getBoundingClientRect();
		let x = (e.clientX - rect.left) / rect.width;
		let y = (e.clientY - rect.top) / rect.height;
		// On limite le focus du zoom entre 20% et 80%
		x = clamp(x, 0.2, 0.8);
		y = clamp(y, 0.2, 0.8);
		mainImage.style.transform = "scale(2)";
		mainImage.style.transformOrigin = `${x * 100}% ${y * 100}%`;
	};
	// Réinitialise le zoom
	const resetZoom = () => {
		mainImage.style.transform = "scale(1)";
		mainImage.style.transformOrigin = "center";
	};

	// Événements pour le zoom
	mainImageContainer.addEventListener("mousemove", handleZoom);
	mainImageContainer.addEventListener("mouseleave", resetZoom);

	// Événements pour changer d'image au clic sur les miniatures
	detailImages.forEach((img) =>
		img.addEventListener("click", () => switchMainImage(img.src))
	);
}

// S’exécute au chargement de la page ou après navigation Turbo/PJAX
document.addEventListener("DOMContentLoaded", initProductGallery);
document.addEventListener("turbo:load", initProductGallery);
document.addEventListener("pjax:success", initProductGallery);
