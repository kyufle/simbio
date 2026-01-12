// js/discover.js

const PROJECTS_JSON = 'includes/projects.json';
const BUFFER_SIZE = 5;

let allProjects = [];
let buffer = [];
let currentVisible = null;

const container = document.getElementById('discover-container');

// Crear card de proyecto con barra inferior y toggle de detalles
function createProjectCard(project) {
    const card = document.createElement('div');
    card.classList.add('project-card');

    card.innerHTML = `
        <h2>${project.title}</h2>
        <p><strong>Centro:</strong> ${project.center}</p>
        <video width="320" height="180" controls preload="metadata">
            <source src="${project.video}" type="video/mp4">
            Tu navegador no soporta video.
        </video>

        <div class="buttons">
            <button class="nope-btn">Nope</button>
            <button class="like-btn">Like</button>
        </div>

        <div class="bottom-bar">
            <a href="profile.php">Perfil</a>
            <a href="messages.php">Converses</a>
            <button class="toggle-details">Detalles</button>
        </div>

        <div class="details" style="display:none;">
            <p class="description">${project.description}</p>
            <p class="tags"><strong>Etiquetas:</strong> ${project.tags.join(', ')}</p>
        </div>
    `;

    // Toggle de detalles
    const toggleBtn = card.querySelector('.toggle-details');
    const detailsDiv = card.querySelector('.details');

    toggleBtn.addEventListener('click', () => {
        if (detailsDiv.style.display === 'none') {
            detailsDiv.style.display = 'block';
            toggleBtn.textContent = 'Ocultar detalles';
        } else {
            detailsDiv.style.display = 'none';
            toggleBtn.textContent = 'Detalles';
        }
    });

    // Botones Like / Nope
    card.querySelector('.nope-btn').addEventListener('click', () => handleAction());
    card.querySelector('.like-btn').addEventListener('click', () => handleAction());

    return card;
}

// Manejar Like / Nope con animación
function handleAction() {
    if (!currentVisible) return;

    currentVisible.style.transition = 'all 0.5s ease';
    currentVisible.style.opacity = '0';
    currentVisible.style.transform = 'translateX(100%) scale(0.9)';

    setTimeout(() => {
        showNextProject();
    }, 500);
}

// Mostrar siguiente proyecto del buffer
function showNextProject() {
    container.innerHTML = '';

    if (buffer.length === 0) {
        container.innerHTML = '<p>No hay más proyectos</p>';
        return;
    }

    const project = buffer.shift();
    currentVisible = createProjectCard(project);
    container.appendChild(currentVisible);

    // Animación de entrada
    currentVisible.style.opacity = '0';
    currentVisible.style.transform = 'translateX(-20px)';
    setTimeout(() => {
        currentVisible.style.transition = 'all 0.5s ease';
        currentVisible.style.opacity = '1';
        currentVisible.style.transform = 'translateX(0)';
    }, 50);

    // Precargar siguiente proyecto si existe
    if (allProjects.length > 0) {
        const nextProject = allProjects.shift();
        buffer.push(nextProject);
        const videoPreload = document.createElement('video');
        videoPreload.src = nextProject.video;
        videoPreload.preload = 'metadata';
    }
}

// Inicializar Discover
function initDiscover() {
    fetch(PROJECTS_JSON)
        .then(res => res.json())
        .then(data => {
            allProjects = data;
            buffer = allProjects.splice(0, BUFFER_SIZE);
            showNextProject();
        })
        .catch(err => {
            container.innerHTML = '<p>Error cargando proyectos</p>';
            console.error(err);
        });
}

document.addEventListener('DOMContentLoaded', initDiscover);
