/**
 * Form Builder - JavaScript
 * Funcionalidades para el constructor visual de formularios
 */

// --- VARIABLES GLOBALES ---
let formId;
let csrfToken;
let questionModal;
let currentQuestionType = null;
let optionCounter = 0;
// Almacena las preguntas disponibles para la lógica condicional
window.conditionalQuestions = [];

// --- CONSTANTES ---
const typesWithOptions = ['radio', 'checkbox', 'select'];
const typesWithConditionals = ['radio']; // Solo 'radio' puede tener condicionales por ahora
const textTypes = ['text', 'textarea', 'email', 'number', 'date'];

// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', function() {
    formId = document.getElementById('form_id')?.value;
    csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    const modalElement = document.getElementById('questionModal');
    if (modalElement) {
        questionModal = new bootstrap.Modal(modalElement);
    }
    
    const questionsList = document.getElementById('questions-list');
    if (questionsList && questionsList.children.length > 0) {
        initDragAndDrop();
    }
});

// --- DRAG AND DROP ---
function initDragAndDrop() {
    const questionsList = document.getElementById('questions-list');
    if (!questionsList) return;
    
    new Sortable(questionsList, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'dragging',
        onEnd: updateQuestionsOrder
    });
}

function updateQuestionsOrder() {
    const orders = {};
    document.querySelectorAll('.question-item').forEach((item, index) => {
        orders[item.dataset.questionId] = index + 1;
    });
    
    fetch(`${BASE_URL}/admin/questions/reorder`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ form_id: formId, orders: orders })
    }).then(res => res.json()).then(data => {
        if (!data.success) console.error('Error al actualizar orden:', data.message);
    }).catch(err => console.error('Error en la petición de reordenar:', err));
}

// --- GESTIÓN DEL MODAL DE PREGUNTA ---

async function openAddQuestionModal(typeName, typeId, typeLabel) {
    document.getElementById('questionForm').reset();
    document.getElementById('question_id').value = '';
    document.getElementById('type_id').value = typeId;
    document.getElementById('options_list').innerHTML = '';
    optionCounter = 0;
    
    currentQuestionType = typeName;
    document.getElementById('questionModalTitle').innerHTML = `Agregar Pregunta: <strong>${typeLabel}</strong>`;
    
    toggleFieldsByType(typeName);
    
    // Cargar preguntas para la lógica condicional
    await loadConditionalQuestions();

    if (typesWithOptions.includes(typeName)) {
        addOption();
        addOption();
    }
    
    questionModal.show();
}

async function editQuestion(questionId) {
    try {
        // Corregido: usar la nueva ruta 'get'
        const response = await fetch(`${BASE_URL}/admin/questions/get?id=${questionId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (!data.success || !data.question) throw new Error(data.message || 'No se encontró la pregunta.');

        const question = data.question;
        
        // Resetear y poblar el formulario
        document.getElementById('questionForm').reset();
        document.getElementById('question_id').value = question.id;
        document.getElementById('type_id').value = question.type_id;
        document.getElementById('question_text').value = question.question_text;
        document.getElementById('required').checked = question.required == 1;
        document.getElementById('placeholder').value = question.placeholder || '';
        document.getElementById('help_text').value = question.help_text || '';
        
        currentQuestionType = question.type_name;
        document.getElementById('questionModalTitle').innerHTML = `Editar Pregunta: <strong>${question.type_label}</strong>`;
        
        // Limpiar opciones anteriores
        document.getElementById('options_list').innerHTML = '';
        optionCounter = 0;

        toggleFieldsByType(question.type_name);

        // Cargar preguntas para condicionales, excluyendo la actual
        await loadConditionalQuestions(questionId);

        // Cargar opciones existentes
        if (question.options && question.options.length > 0) {
            question.options.forEach(opt => addOption(opt));
        }

        questionModal.show();

    } catch (error) {
        console.error('Error al cargar la pregunta para editar:', error);
        alert('No se pudo cargar la pregunta.');
    }
}

// --- NUEVA FUNCIÓN ---
/**
 * Eliminar una pregunta
 */
function deleteQuestion(questionId) {
    if (!confirm('¿Está seguro de que desea eliminar esta pregunta? Esta acción no se puede deshacer.')) {
        return;
    }

    fetch(`${BASE_URL}/admin/questions/delete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ id: questionId, csrf_token: csrfToken })
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + (result.message || 'No se pudo eliminar la pregunta.'));
        }
    })
    .catch(error => {
        console.error('Error al eliminar:', error);
        alert('Ocurrió un error de red al intentar eliminar la pregunta.');
    });
}


function toggleFieldsByType(typeName) {
    const optionsContainer = document.getElementById('options_container');
    // Corregido: de 'placeholder_group' a 'placeholder_container'
    const placeholderContainer = document.getElementById('placeholder_container');

    optionsContainer.style.display = typesWithOptions.includes(typeName) ? 'block' : 'none';
    
    // Asegurarse de que el contenedor del placeholder exista antes de manipularlo
    if (placeholderContainer) {
        placeholderContainer.style.display = textTypes.includes(typeName) ? 'block' : 'none';
    }

    // Actualizar visibilidad de la UI condicional para las opciones ya existentes en el modal
    const conditionalUIs = document.querySelectorAll('.conditional-logic-ui');
    const showConditionals = typesWithConditionals.includes(typeName);
    conditionalUIs.forEach(ui => {
        ui.style.display = showConditionals ? 'block' : 'none';
    });
}

// --- GESTIÓN DE OPCIONES Y CONDICIONALES ---

function addOption(option = { id: '', option_text: '', child_question_id: null }) {
    optionCounter++;
    const optionId = option.id || `new_${optionCounter}`;
    const isConditionalType = typesWithConditionals.includes(currentQuestionType);

    const optionDiv = document.createElement('div');
    optionDiv.className = 'mb-2 option-item';
    optionDiv.setAttribute('data-option-id', optionId);

    optionDiv.innerHTML = `
        <div class="input-group">
            <input type="text" class="form-control option-text" value="${escapeHTML(option.option_text)}" placeholder="Texto de la opción">
            <button class="btn btn-outline-danger" type="button" onclick="removeOption(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        <div class="conditional-logic-ui" style="display: ${isConditionalType ? 'block' : 'none'};">
            <div class="input-group mt-1">
                <span class="input-group-text" title="Lógica Condicional">
                    <i class="bi bi-arrow-return-right"></i>
                </span>
                <select class="form-select form-select-sm conditional-question-select">
                    <option value="">-- Mostrar pregunta (opcional) --</option>
                </select>
            </div>
        </div>
    `;

    document.getElementById('options_list').appendChild(optionDiv);
    
    const newSelect = optionDiv.querySelector('.conditional-question-select');
    populateConditionalSelect(newSelect, window.conditionalQuestions);
    
    // Si la opción que se está agregando tiene una hija, la seleccionamos
    if (option.child_question_id) {
        newSelect.value = option.child_question_id;
    }
}

function removeOption(button) {
    const optionsList = document.getElementById('options_list');
    if (optionsList.children.length > 1) {
        button.closest('.option-item').remove();
    } else {
        alert('Debe haber al menos una opción.');
    }
}

async function loadConditionalQuestions(currentQuestionId = null) {
    try {
        const url = `${BASE_URL}/admin/forms/${formId}/questions-for-linking?exclude=${currentQuestionId || ''}`;
        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) throw new Error('Error fetching questions');
        
        window.conditionalQuestions = await response.json();
    } catch (error) {
        console.error('Could not load conditional questions:', error);
        window.conditionalQuestions = [];
    }
}

function populateConditionalSelect(selectElement, questions) {
    const currentValue = selectElement.value;
    selectElement.innerHTML = '<option value="">-- Mostrar pregunta (opcional) --</option>';
    questions.forEach(q => {
        const text = q.question_text.length > 60 ? q.question_text.substring(0, 60) + '...' : q.question_text;
        selectElement.innerHTML += `<option value="${q.id}">${escapeHTML(text)}</option>`;
    });
    selectElement.value = currentValue;
}

// --- GUARDAR DATOS ---

function saveQuestion() {
    const form = document.getElementById('questionForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const questionId = document.getElementById('question_id').value;
    const data = {
        form_id: formId,
        question_text: document.getElementById('question_text').value,
        type_id: document.getElementById('type_id').value,
        required: document.getElementById('required').checked ? 1 : 0,
        placeholder: document.getElementById('placeholder').value || null,
        help_text: document.getElementById('help_text').value || null,
        csrf_token: csrfToken
    };

    // Recopilar opciones y condicionales
    if (typesWithOptions.includes(currentQuestionType)) {
        data.options = [];
        document.querySelectorAll('#options_list .option-item').forEach((item, index) => {
            const textInput = item.querySelector('.option-text');
            if (textInput.value.trim() !== '') {
                const optionData = {
                    id: item.dataset.optionId.startsWith('new_') ? null : item.dataset.optionId,
                    option_text: textInput.value,
                    order_number: index + 1,
                    child_question_id: null
                };
                // Añadir condicional si existe
                if (typesWithConditionals.includes(currentQuestionType)) {
                    const conditionalSelect = item.querySelector('.conditional-question-select');
                    if (conditionalSelect && conditionalSelect.value) {
                        optionData.child_question_id = conditionalSelect.value;
                    }
                }
                data.options.push(optionData);
            }
        });

        if (data.options.length === 0) {
            alert('Debe agregar al menos una opción con texto.');
            return;
        }
    }

    const url = questionId ? `${BASE_URL}/admin/questions/update` : `${BASE_URL}/admin/questions/store`;
    const method = 'POST';

    // Enviar al servidor
    fetch(url, {
        method: method,
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest' // <-- Cabecera añadida
        },
        body: JSON.stringify(questionId ? { ...data, id: questionId } : data)
    })
    .then(res => {
        if (!res.ok) {
            // Si la respuesta no es JSON, puede ser un error de PHP
            return res.text().then(text => { throw new Error(text || 'Error de servidor') });
        }
        return res.json();
    })
    .then(result => {
        if (result.success) {
            questionModal.hide();
            location.reload(); 
        } else {
            alert('Error: ' + (result.message || 'No se pudo guardar la pregunta.'));
        }
    })
    .catch(error => {
        console.error('Error al guardar:', error);
        alert('Ocurrió un error de red. Por favor, intente nuevamente.');
    });
}

// --- UTILIDADES ---
function escapeHTML(text) {
    if (text === null || text === undefined) return '';
    return text.toString().replace(/[&<>"']/g, m => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[m]));
}