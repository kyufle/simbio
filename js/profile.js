const PROJECTS_API_URL = 'includes/user_projects.php';

// Función para obtener los proyectos del usuario por email
async function fetchUserProjects(userEmail) {
    try {
        const response = await fetch(
            `${PROJECTS_API_URL}?email=${encodeURIComponent(userEmail)}`
        );

        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }

        return await response.json();
    } catch (error) {
        console.error('Error fetching user projects:', error);
        return [];
    }
}

// Función para mostrar los proyectos en el perfil del usuario
async function displayUserProjects(userEmail) {
    const projectsContainer = document.getElementById('user-projects');
    projectsContainer.innerHTML = '';

    const projects = await fetchUserProjects(userEmail);

    projects.forEach(project => {
        const projectElement = document.createElement('div');
        projectElement.classList.add('project');

        // Enlace que contiene el título
        const projectLink = document.createElement('a');
        projectLink.href = `preview_video.php?video=${encodeURIComponent(project.video)}`;
        projectLink.textContent = project.title; // ✅ solo aquí va el título
        projectLink.classList.add('project-title-link');

        // Imagen de preview
        const projectImage = document.createElement('img');
        projectImage.src = project.image;
        projectImage.alt = project.title;
        projectImage.classList.add('project-preview');

        // Agregar elementos al contenedor
        projectElement.appendChild(projectLink);
        projectElement.appendChild(document.createElement('br')); // Salto de línea entre título e imagen
        projectElement.appendChild(projectImage);

        projectsContainer.appendChild(projectElement);
    });

    console.log(projects);
}


// Suponiendo que ya tienes el email del usuario autenticado
displayUserProjects(userEmail);

/* ========================================
   FUNCIONALIDAD DE ETIQUETAS
   ======================================== */

const tagSearchInput = document.getElementById('tag-search');
const tagSuggestions = document.getElementById('tag-suggestions');
const addTagBtn = document.getElementById('add-tag-btn');
const tagsList = document.getElementById('tags-list');

// Función para obtener las etiquetas actuales del formulario
function getCurrentTags() {
    const hiddenInputs = tagsList.querySelectorAll('input[type="hidden"]');
    return Array.from(hiddenInputs).map(input => input.value);
}

// Función para filtrar etiquetas que coincidan
function filterTags(searchTerm) {
    if (!searchTerm.trim()) {
        return [];
    }
    
    const currentTags = getCurrentTags();
    const lowerSearchTerm = searchTerm.toLowerCase();
    
    return allAvailableTags.filter(tag => {
        // No mostrar etiquetas que ya estén añadidas
        if (currentTags.includes(tag)) return false;
        // Filtrar por coincidencia
        return tag.toLowerCase().includes(lowerSearchTerm);
    }).slice(0, 8); // Máximo 8 sugerencias
}

// Función para mostrar las sugerencias
function showSuggestions(tags) {
    tagSuggestions.innerHTML = '';
    
    if (tags.length === 0) {
        return;
    }
    
    tags.forEach(tag => {
        const suggestionItem = document.createElement('div');
        suggestionItem.classList.add('suggestion-item');
        suggestionItem.textContent = tag;
        suggestionItem.onclick = () => {
            tagSearchInput.value = tag;
            tagSuggestions.innerHTML = '';
        };
        tagSuggestions.appendChild(suggestionItem);
    });
}

// Event listener para búsqueda en tiempo real
tagSearchInput.addEventListener('input', (e) => {
    const searchTerm = e.target.value;
    const suggestions = filterTags(searchTerm);
    showSuggestions(suggestions);
});

// Event listener para el botón de añadir
addTagBtn.addEventListener('click', (e) => {
    e.preventDefault();
    const tagValue = tagSearchInput.value.trim();
    
    if (!tagValue) {
        alert('Por favor, escribe una etiqueta');
        return;
    }
    
    // Verificar que exista en la lista de etiquetas disponibles
    if (!allAvailableTags.includes(tagValue)) {
        alert('Esta etiqueta no existe');
        return;
    }
    
    // Verificar que no esté ya añadida
    const currentTags = getCurrentTags();
    if (currentTags.includes(tagValue)) {
        alert('Esta etiqueta ya está añadida');
        return;
    }
    
    // Crear el elemento de etiqueta
    const tagItem = document.createElement('div');
    tagItem.classList.add('tag-item');
    
    const tagSpan = document.createElement('span');
    tagSpan.textContent = tagValue;
    
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.classList.add('remove-tag-btn');
    removeBtn.setAttribute('data-tag', tagValue);
    removeBtn.textContent = '×';
    removeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        tagItem.remove();
    });
    
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'tags[]';
    hiddenInput.value = tagValue;
    
    tagItem.appendChild(tagSpan);
    tagItem.appendChild(removeBtn);
    tagItem.appendChild(hiddenInput);
    
    tagsList.appendChild(tagItem);
    
    // Limpiar búsqueda
    tagSearchInput.value = '';
    tagSuggestions.innerHTML = '';
});

// Eliminar etiqueta cuando se hace click en el botón X
tagsList.addEventListener('click', (e) => {
    if (e.target.classList.contains('remove-tag-btn')) {
        e.preventDefault();
        e.target.parentElement.remove();
    }
});

// Cerrar sugerencias cuando se hace click fuera
document.addEventListener('click', (e) => {
    if (!e.target.closest('.tag-search-wrapper')) {
        tagSuggestions.innerHTML = '';
    }
});

