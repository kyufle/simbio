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

        const projectLink = document.createElement('a');
        projectLink.href = `preview_video.php?video=${encodeURIComponent(project.video)}`;

        const projectImage = document.createElement('img');
        projectImage.src = project.image;
        projectImage.alt = project.title;
        projectImage.classList.add('project-preview');

        projectLink.appendChild(projectImage);

        const projectTitle = document.createElement('h3');
        projectTitle.textContent = project.title;

        projectElement.appendChild(projectLink);
        projectElement.appendChild(projectTitle);

        projectLink.textContent = project.title;
        projectTitle.appendChild(projectLink);
        projectsContainer.appendChild(projectElement);
    });

    console.log(projects);
}

// Suponiendo que ya tienes el email del usuario autenticado
displayUserProjects(userEmail);
