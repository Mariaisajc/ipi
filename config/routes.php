<?php
/**
 * IPI - Innovation Performance Index
 * Configuración de Rutas
 * 
 * Formato: 'ruta' => 'Controlador@metodo'
 */

return [
    // ========================================
    // RUTAS PÚBLICAS (Sin autenticación)
    // ========================================
    '' => 'HomeController@index',
    'login' => 'AuthController@showLogin',
    'auth/login' => 'AuthController@showLogin',
    'auth/do-login' => 'AuthController@login',
    'auth/logout' => 'AuthController@logout',
    
    // ========================================
    // RUTAS DE ERROR
    // ========================================
    'error/404' => 'ErrorController@notFound',
    'error/403' => 'ErrorController@forbidden',
    
    // ========================================
    // PANEL DE ADMINISTRACIÓN
    // ========================================
    
    // Dashboard Admin
    'admin' => 'Admin\DashboardController@index',
    'admin/dashboard' => 'Admin\DashboardController@index',
    
    // Gestión de Empresas
    'admin/businesses' => 'Admin\BusinessController@index',
    'admin/businesses/create' => 'Admin\BusinessController@create',
    'admin/businesses/store' => 'Admin\BusinessController@store',
    'admin/businesses/show' => 'Admin\BusinessController@show', // Asume ID por GET (?id=X)
    'admin/businesses/edit' => 'Admin\BusinessController@edit',   // Asume ID por GET (?id=X)
    'admin/businesses/update' => 'Admin\BusinessController@update',
    'admin/businesses/delete' => 'Admin\BusinessController@delete', // Para peticiones AJAX
    
    // Gestión de Áreas de Negocio
    'admin/business-areas/{business_id}' => 'Admin\BusinessController@areas',
    'admin/business-areas/store' => 'Admin\BusinessController@storeArea',
    'admin/business-areas/delete/{id}' => 'Admin\BusinessController@deleteArea',
    
    // Gestión de Usuarios
    'admin/users' => 'Admin\UserController@index',
    'admin/users/create' => 'Admin\UserController@create',
    'admin/users/store' => 'Admin\UserController@store',
    'admin/users/show' => 'Admin\UserController@show',     // Asume ID por GET (?id=X)
    'admin/users/edit' => 'Admin\UserController@edit',     // Asume ID por GET (?id=X)
    'admin/users/update' => 'Admin\UserController@update',
    'admin/users/delete' => 'Admin\UserController@delete',   // CORREGIDO: Se mantiene una sola ruta para eliminar (AJAX)
    'admin/users/toggle-status' => 'Admin\UserController@toggleStatus',
    
    // Gestión de Formularios
    'admin/forms' => 'Admin\FormController@index',
    'admin/forms/create' => 'Admin\FormController@create',
    'admin/forms/store' => 'Admin\FormController@store',
    'admin/forms/show' => 'Admin\FormController@show',       // Asume ID por GET (?id=X)
    'admin/forms/edit' => 'Admin\FormController@edit',       // Asume ID por GET (?id=X)
    'admin/forms/update' => 'Admin\FormController@update',
    'admin/forms/destroy' => 'Admin\FormController@destroy', // CORREGIDO: Se mantiene 'destroy' como convención
    'admin/forms/duplicate' => 'Admin\FormController@duplicate',
    'admin/forms/change-status' => 'Admin\FormController@changeStatus',
    'admin/forms/publish' => 'Admin\FormController@publish',
    'admin/forms/close' => 'Admin\FormController@close',
    'admin/forms/builder' => 'Admin\FormController@builder', // Asume ID por GET (?id=X)
    'admin/forms/{id}/questions-for-linking' => 'Admin\FormController@getQuestionsForLinking',
    
    
    // Gestión de Preguntas (para AJAX desde el builder)
    'admin/questions/store' => 'Admin\QuestionController@store',
    'admin/questions/update' => 'Admin\QuestionController@update',
    'admin/questions/delete' => 'Admin\QuestionController@delete',
    'admin/questions/reorder' => 'Admin\QuestionController@reorder',
    'admin/questions/get' => 'Admin\QuestionController@get',
    
    // Preguntas Condicionales (Padre-Hijo)
    'admin/question-children/store' => 'Admin\QuestionController@storeChild',
    'admin/question-children/delete/{id}' => 'Admin\QuestionController@deleteChild',
    
    // Asignación de Formularios
    'admin/forms/assign/{id}' => 'Admin\FormController@assign',
    'admin/forms/assign-users' => 'Admin\FormController@assignUsers',
    'admin/forms/unassign/{form_id}/{user_id}' => 'Admin\FormController@unassignUser',
    
    // Reportes y Análisis
    'admin/reports' => 'Admin\ReportController@index',
    'admin/reports/form/{id}' => 'Admin\ReportController@formReport',
    'admin/reports/business/{id}' => 'Admin\ReportController@businessReport',
    'admin/reports/responses/{form_id}' => 'Admin\ReportController@responses',
    'admin/reports/response-detail/{id}' => 'Admin\ReportController@responseDetail',
    
    // Exportaciones
    'admin/export/excel/{form_id}' => 'Admin\ExportController@excel',
    'admin/export/csv/{form_id}' => 'Admin\ExportController@csv',
    'admin/export/history' => 'Admin\ExportController@history',
    'admin/export/download/{id}' => 'Admin\ExportController@download',
    
    // ========================================
    // PANEL DE ENCUESTADOS (SURVEY)
    // ========================================
    
    // Dashboard Encuestado
    'survey' => 'Survey\DashboardController@index',
    'survey/dashboard' => 'Survey\DashboardController@index',
    
    // Responder Formularios
    'survey/form/{id}' => 'Survey\FormResponseController@show',
    'survey/form/start/{id}' => 'Survey\FormResponseController@start',
    'survey/form/save' => 'Survey\FormResponseController@save',
    'survey/form/submit/{response_id}' => 'Survey\FormResponseController@submit',
    'survey/form/success' => 'Survey\FormResponseController@success',
    
    // Historial de Respuestas
    'survey/responses' => 'Survey\FormResponseController@history',
    'survey/response/{id}' => 'Survey\FormResponseController@viewResponse',
    
    // ========================================
    // API (Para JavaScript)
    // ========================================
    'api/questions/get/{form_id}' => 'Admin\QuestionController@getQuestions',
    'api/question-options/get/{question_id}' => 'Admin\QuestionController@getOptions',
    'api/form/save-progress' => 'Survey\FormResponseController@saveProgress',
];