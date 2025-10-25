<?php
/**
 * IPI - Innovation Performance Index
 * Controlador: HomeController
 * 
 * Controlador para la página de inicio pública
 */

class HomeController extends Controller {
    
    /**
     * Mostrar página de inicio
     */
    public function index() {
        // Si ya está autenticado, redirigir al dashboard correspondiente
        if ($this->isAuthenticated()) {
            if ($this->hasRole('admin')) {
                $this->redirect('admin/dashboard');
            } else {
                $this->redirect('survey/dashboard');
            }
            return;
        }
        
        // Mostrar vista de inicio sin layout
        $this->view('home/index', [], null);
    }
}