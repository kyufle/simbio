const PROJECTS_API_URL = 'includes/projects.php';

// Aqui vamos a programar la funcionalidad de obtener los proyectos relacionados con el usuario (es decir, el proyecto debe estar relacionado con el user_id del usuario)
// Los proyectos se mostrarán debajo de toda la información del perfil del usuario como se indica en el archivo "profile.php".
// Solo se mostrará el titulo del video (en la cual el titulo sera un enlace para una vista previa del video) y la imagen destacada del proyecto.
// La información del proyecto se obtiene de la tabla "project" en la base de datos "simbio".
// La relación entre el usuario y el proyecto se establece a través del campo "user_id" en ambas tablas "user" y "project".

// Función para obtener los proyectos relacionados con el usuario
async function fetchUserProjects(userId) {
    try {
        const response = await fetch(PROJECTS_API_URL);
        const projects = await response.json();
        // Filtrar los proyectos que pertenecen al usuario actual
        return projects.filter(project => project.user_id === userId);
    } catch (error) {
        console.error('Error fetching user projects:', error);
        return [];
    }
}

// Función para mostrar los proyectos en el perfil del usuario
async function displayUserProjects(userId) {
    const projectsContainer = document.getElementById('user-projects');
    const projects = await fetchUserProjects(userId);

    projects.forEach(project => {
        const projectElement = document.createElement('div');
        projectElement.classList.add('project');

        const projectTitle = document.createElement('h3');
        const projectLink = document.createElement('a');
        projectLink.href = `preview_video.html?video_path=${encodeURIComponent(project.video_path)}`;
        projectLink.textContent = project.title;
        projectTitle.appendChild(projectLink);

        const projectImage = document.createElement('img');
        projectImage.src = project.image_path;
        projectImage.alt = project.title;

        projectElement.appendChild(projectTitle);
        projectElement.appendChild(projectImage);
        projectsContainer.appendChild(projectElement);
    });
}

/*
// Suponiendo que tenemos el userId del usuario actual disponible
const currentUserId = 1; // Reemplazar con el ID real del usuario
displayUserProjects(currentUserId); */