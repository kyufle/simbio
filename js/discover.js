// js/discover.js - Versión TikTok vertical optimizada con Toasts

const PROJECTS_JSON = 'includes/projects.php';
const BUFFER_SIZE = 5;

let allProjects = [];
let buffer = [];
let currentVisible = null;

const container = document.getElementById('discover-container');

/* ============================================================
   CREAR CARD DE PROYECTO
============================================================ */
function createProjectCard(project) {
    const card = document.createElement('div');
    card.classList.add('project-card');

    card.innerHTML = `
        <header>
            <h2>${project.title}</h2>
            <p><strong>${project.type}:</strong> ${project.entity}</p>
        </header>

        <section class="video-section">
            <video autoplay muted loop playsinline>
                <source src="${project.video+"?v=" + Date.now()}" type="video/mp4">
            </video>
        </section>

        <section class="actions">
            <div class="buttons">
                <button class="nope-btn" aria-label="No m'interessa"></button>
                <button class="like-btn" aria-label="M'interessa"></button>
            </div>

            <nav class="bottom-bar">
                <a href="profile.php">Perfil</a>
                <a href="messages.php">Converses</a>
                <button class="toggle-details">Detalls</button>
            </nav>
        </section>

        <aside class="details hidden">
            <button class="close-details" aria-label="Tancar">&times;</button>
            <h3>Descripció</h3>
            <p class="description">${project.description}</p>
            <h3>Etiquetes</h3>
            <p class="tags">${project.tags.join(', ')}</p>
        </aside>
    `;

    /* ----- Toggle de detalles ----- */
    const openDetailsBtn = card.querySelector('.toggle-details');
    const closeDetailsBtn = card.querySelector('.close-details');
    const detailsDiv = card.querySelector('.details');
    const video = card.querySelector('video');

    openDetailsBtn.addEventListener('click', () => {
        detailsDiv.classList.remove('hidden');
        video.pause();
    });

    closeDetailsBtn.addEventListener('click', () => {
        detailsDiv.classList.add('hidden');
        video.play();
    });

    return card;
}

/* ============================================================
   ANIMACIÓN DE SWIPE
============================================================ */
function animateSwipe(card, direction) {
    if (!card) return;

    if (direction === "like") {
        card.classList.add("swipe-right");
    } else if (direction === "nope") {
        card.classList.add("swipe-left");
    }

    card.addEventListener("animationend", () => {
        card.remove();
        showNextProject();
    }, { once: true });
}

/* ============================================================
   LISTENERS DE LIKE / NOPE
============================================================ */
document.addEventListener("click", (e) => {
    if (!currentVisible) return;

    if (e.target.classList.contains("like-btn")) {
        animateSwipe(currentVisible, "like");
    }

    if (e.target.classList.contains("nope-btn")) {
        animateSwipe(currentVisible, "nope");
    }
});

/* ============================================================
   MOSTRAR SIGUIENTE PROYECTO
============================================================ */
function showNextProject() {
    container.innerHTML = '';

    // ⭐ TOAST: Si no quedan proyectos
    if (buffer.length === 0) {
        container.innerHTML = `
            <div class="empty-message">
                <h2>🎉 Has vist tots els projectes!</h2>
                <p>No hi ha més projectes disponibles en aquest moment.</p>
                <p style="margin-top: 15px; font-size: 0.9rem;">Torna més tard per veure nous projectes.</p>
            </div>
        `;
        
        // ⭐ Mostrar toast informativo
        if (typeof window.mostrarInfo === 'function') {
            window.mostrarInfo(
                'Sense més projectes', 
                'Has vist tots els projectes disponibles!'
            );
        }
        
        return;
    }

    const project = buffer.shift();
    currentVisible = createProjectCard(project);
    container.appendChild(currentVisible);

    /* Animación de entrada */
    currentVisible.style.opacity = '0';
    currentVisible.style.transform = 'scale(0.85) translateY(30px)';

    setTimeout(() => {
        currentVisible.style.transition = 'all 0.45s cubic-bezier(0.34, 1.56, 0.64, 1)';
        currentVisible.style.opacity = '1';
        currentVisible.style.transform = 'scale(1) translateY(0)';
    }, 30);

    /* Precarga del siguiente */
    if (allProjects.length > 0) {
        const nextProject = allProjects.shift();
        buffer.push(nextProject);

        const preload = document.createElement('video');
        preload.src = nextProject.video;
        preload.preload = 'metadata';
    }

    /* Reproducir video */
    const video = currentVisible.querySelector('video');
    video.play().catch(() => {});
}

/* ============================================================
   SWIPE TÁCTIL (MÓVIL)
============================================================ */
let touchStartX = 0;
let touchStartY = 0;

document.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
    touchStartY = e.changedTouches[0].screenY;
});

document.addEventListener('touchend', (e) => {
    if (!currentVisible) return;

    const endX = e.changedTouches[0].screenX;
    const endY = e.changedTouches[0].screenY;

    const diffX = endX - touchStartX;
    const diffY = Math.abs(endY - touchStartY);

    if (Math.abs(diffX) > 80 && Math.abs(diffX) > diffY) {
        if (diffX > 0) {
            animateSwipe(currentVisible, "like");
        } else {
            animateSwipe(currentVisible, "nope");
        }
    }
});

/* ============================================================
   TECLADO (OPCIONAL)
============================================================ */
document.addEventListener('keydown', (e) => {
    if (!currentVisible) return;

    if (e.key === 'ArrowLeft') animateSwipe(currentVisible, "nope");
    if (e.key === 'ArrowRight') animateSwipe(currentVisible, "like");
});

/* ============================================================
   INICIALIZAR DISCOVER
============================================================ */
function initDiscover() {
    fetch(PROJECTS_JSON)
        .then(res => {
            if (!res.ok) throw new Error('Error al carregar projectes');
            return res.json();
        })
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                throw new Error('No hi ha projectes disponibles');
            }
            
            allProjects = data;
            buffer = allProjects.splice(0, BUFFER_SIZE);
            showNextProject();
        })
        .catch(err => {
            container.innerHTML = `
                <div class="error-message">
                    <h2>⚠️ Error al carregar projectes</h2>
                    <p>${err.message}</p>
                    <p style="font-size: 0.85rem; margin-top: 10px;">
                        Verifica que l'arxiu <code>includes/projects.php</code> funcioni correctament.
                    </p>
                </div>
            `;
            
            // ⭐ TOAST: Error al cargar proyectos
            if (typeof window.mostrarError === 'function') {
                window.mostrarError('Error de càrrega', err.message);
            }
            
            console.error('Error en initDiscover:', err);
        });
}

document.addEventListener('DOMContentLoaded', initDiscover);