/**
 * Form Builder - JavaScript
 * Funcionalidades para el constructor visual de formularios
 */

// Variables globales
let formId;
let csrfToken;
let questionModal;
let currentQuestionType = null;
let optionCounter = 0;

// Tipos que requieren opciones
const typesWithOptions = ['radio', 'checkbox', 'select'];

/**
 * Inicializar builder al cargar la página
 */
document.addEventListener('DOMContentLoaded', function() {
    // Obtener datos de la página
    formId = document.getElementById('form_id')?.value;
    csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    // Inicializar modal
    const modalElement = document.getElementById('questionModal');
    if (modalElement) {
        questionModal = new bootstrap.Modal(modalElement);
    }
    
    // Inicializar drag-and-drop si hay preguntas
    const questionsList = document.getElementById('questions-list');
    if (questionsList && questionsList.children.length > 0) {
        initDragAndDrop();
    }
});

/**
 * Inicializar drag-and-drop con SortableJS
 */
function initDragAndDrop() {
    const questionsList = document.getElementById('questions-list');
    
    if (!questionsList) return;
    
    new Sortable(questionsList, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'dragging',
        onEnd: function(evt) {
            updateQuestionsOrder();
        }
    });
}

/**
 * Actualizar orden de preguntas después del drag
 */
function updateQuestionsOrder() {
    const questions = document.querySelectorAll('.question-item');
    const orders = {};
    
    questions.forEach((item, index) => {
        const questionId = item.dataset.questionId;
        orders[questionId] = index + 1;
    });
    
    // Enviar al servidor
    fetch(BASE_URL + '/admin/questions/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            form_id: formId,
            orders: orders,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Orden actualizado correctamente');
        } else {
            console.error('Error al actualizar orden:', data.message);
        }
    })
    .catch(error => {
        console.error('Error en la petición:', error);
    });
}

/**
 * Abrir modal para agregar pregunta
 */
function openAddQuestionModal(typeName, typeId, typeLabel, typeDescription) {
    // Reset form
    const form = document.getElementById('questionForm');
    if (form) form.reset();
    
    document.getElementById('question_id').value = '';
    document.getElementById('type_id').value = typeId;
    currentQuestionType = typeName;
    
    // Cambiar título (solo label, sin description)
    const titleHTML = `Agregar Pregunta: <strong>${typeLabel}</strong>`;
    document.getElementById('questionModalTitle').innerHTML = titleHTML;
    
    // Mostrar/ocultar campos según tipo
    toggleFieldsByType(typeName);
    
    // Resetear contador de opciones
    optionCounter = 0;
    
    // Mostrar modal
    if (questionModal) {
        questionModal.show();
    }
}

/**
 * Mostrar/ocultar campos según tipo de pregunta
 */
function toggleFieldsByType(typeName) {
    const placeholderContainer = document.getElementById('placeholder_container');
    const optionsContainer = document.getElementById('options_container');
    const optionsList = document.getElementById('options_list');
    
    // Placeholder solo para tipos de texto
    const textTypes = ['text', 'textarea', 'email', 'number'];
    if (placeholderContainer) {
        placeholderContainer.style.display = textTypes.includes(typeName) ? 'block' : 'none';
    }
    
    // Opciones solo para radio, checkbox, select
    if (optionsContainer && optionsList) {
        if (typesWithOptions.includes(typeName)) {
            optionsContainer.style.display = 'block';
            
            // Agregar 2 opciones por defecto si está vacío
            if (optionsList.children.length === 0) {
                addOption();
                addOption();
            }
        } else {
            optionsContainer.style.display = 'none';
            optionsList.innerHTML = '';
        }
    }
}

/**
 * Agregar opción al listado
 */
function addOption() {
    optionCounter++;
    const optionsList = document.getElementById('options_list');
    
    if (!optionsList) return;
    
    const optionHtml = `
        <div class="input-group mb-2" id="option_${optionCounter}">
            <input type="text" 
                   class="form-control" 
                   name="options[]" 
                   placeholder="Opción ${optionCounter}"
                   required>
            <button type="button" 
                    class="btn btn-outline-danger" 
                    onclick="removeOption(${optionCounter})"
                    title="Eliminar opción">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    
    optionsList.insertAdjacentHTML('beforeend', optionHtml);
}

/**
 * Eliminar opción
 */
function removeOption(id) {
    const option = document.getElementById('option_' + id);
    if (option) {
        // Verificar que no sea la última opción
        const optionsList = document.getElementById('options_list');
        if (optionsList && optionsList.children.length > 2) {
            option.remove();
        } else {
            alert('Debe haber al menos 2 opciones');
        }
    }
}

/**
 * Guardar pregunta (crear o actualizar)
 */
function saveQuestion() {
    const form = document.getElementById('questionForm');
    const formData = new FormData(form);
    
    // Validar formulario
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Validar opciones si es necesario
    if (typesWithOptions.includes(currentQuestionType)) {
        const options = Array.from(formData.getAll('options[]')).filter(opt => opt.trim() !== '');
        if (options.length < 2) {
            alert('Debe agregar al menos 2 opciones');
            return;
        }
    }
    
    // Preparar datos
    const data = {
        form_id: formId,
        question_text: formData.get('question_text'),
        type_id: formData.get('type_id'),
        required: formData.get('required') ? 1 : 0,
        placeholder: formData.get('placeholder') || null,
        help_text: formData.get('help_text') || null,
        csrf_token: csrfToken
    };
    
    // Agregar opciones si es necesario
    if (typesWithOptions.includes(currentQuestionType)) {
        data.options = Array.from(formData.getAll('options[]')).filter(opt => opt.trim() !== '');
    }
    
    const questionId = formData.get('question_id');
    const url = questionId ? 
        BASE_URL + '/admin/questions/update/' + questionId :
        BASE_URL + '/admin/questions/store';
    
    // Deshabilitar botón mientras se procesa
    const saveBtn = document.querySelector('#questionModal .btn-primary');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Guardando...';
    
    // Enviar al servidor
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            if (questionModal) {
                questionModal.hide();
            }
            
            // Mostrar mensaje de éxito
            showToast('Éxito', data.message || 'Pregunta guardada correctamente', 'success');
            
            // Recargar página después de 500ms
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            // Mostrar error
            alert('Error: ' + (data.message || 'No se pudo guardar la pregunta'));
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar la pregunta. Por favor, intente nuevamente.');
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
}

/**
 * Editar pregunta existente
 */
function editQuestion(questionId) {
    // Obtener datos de la pregunta
    fetch(BASE_URL + '/admin/questions/get?id=' + questionId, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.question) {
            const question = data.question;
            
            // Llenar formulario
            document.getElementById('question_id').value = question.id;
            document.getElementById('question_text').value = question.question_text;
            document.getElementById('type_id').value = question.type_id;
            document.getElementById('required').checked = question.required == 1;
            
            if (question.placeholder) {
                document.getElementById('placeholder').value = question.placeholder;
            }
            
            if (question.help_text) {
                document.getElementById('help_text').value = question.help_text;
            }
            
            currentQuestionType = question.type_name;
            
            // Cambiar título
            document.getElementById('questionModalTitle').innerHTML = 
                'Editar Pregunta: <strong>' + question.type_label + '</strong>';
            
            // Mostrar/ocultar campos
            toggleFieldsByType(question.type_name);
            
            // Cargar opciones si las tiene
            if (question.options && question.options.length > 0) {
                const optionsList = document.getElementById('options_list');
                optionsList.innerHTML = '';
                optionCounter = 0;
                
                question.options.forEach(option => {
                    optionCounter++;
                    const optionHtml = `
                        <div class="input-group mb-2" id="option_${optionCounter}">
                            <input type="text" 
                                   class="form-control" 
                                   name="options[]" 
                                   value="${escapeHtml(option.option_text)}"
                                   required>
                            <button type="button" 
                                    class="btn btn-outline-danger" 
                                    onclick="removeOption(${optionCounter})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                    optionsList.insertAdjacentHTML('beforeend', optionHtml);
                });
            }
            
            // Mostrar modal
            if (questionModal) {
                questionModal.show();
            }
        } else {
            alert('Error al cargar la pregunta');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cargar la pregunta');
    });
}

/**
 * Eliminar pregunta
 */
function deleteQuestion(questionId) {
    if (!confirm('¿Está seguro que desea eliminar esta pregunta?\n\nEsta acción no se puede deshacer.')) {
        return;
    }
    
    fetch(BASE_URL + '/admin/questions/delete/' + questionId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Éxito', data.message || 'Pregunta eliminada correctamente', 'success');
            
            // Recargar después de 500ms
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            alert('Error: ' + (data.message || 'No se pudo eliminar la pregunta'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar la pregunta');
    });
}

/**
 * Publicar formulario
 */
function publishForm() {
    if (!confirm('¿Está seguro que desea publicar este formulario?\n\nEstará disponible para asignar a usuarios.')) {
        return;
    }
    
    fetch(BASE_URL + '/admin/forms/publish', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: formId,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Éxito', data.message || 'Formulario publicado correctamente', 'success');
            
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert('Error: ' + (data.message || 'No se pudo publicar el formulario'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al publicar el formulario');
    });
}

/**
 * Cerrar formulario
 */
function closeForm() {
    if (!confirm('¿Está seguro que desea cerrar este formulario?\n\nNo se podrán registrar más respuestas.')) {
        return;
    }
    
    fetch(BASE_URL + '/admin/forms/close', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            id: formId,
            csrf_token: csrfToken
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Éxito', data.message || 'Formulario cerrado correctamente', 'success');
            
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alert('Error: ' + (data.message || 'No se pudo cerrar el formulario'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cerrar el formulario');
    });
}

/**
 * Mostrar toast de notificación
 */
function showToast(title, message, type = 'info') {
    // Si hay un sistema de toasts implementado, usarlo
    // Por ahora, usar console.log
    console.log(`[${type.toUpperCase()}] ${title}: ${message}`);
}

/**
 * Escapar HTML para prevenir XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}