<?php
/**
 * IPI - Innovation Performance Index
 * Controlador: Admin/DashboardController
 * 
 * Dashboard del panel de administración
 */

class DashboardController extends Controller {
    
    /**
     * Mostrar dashboard principal
     */
    public function index() {
        // Verificar autenticación y rol admin
        $this->requireAuth('admin');
        
        // Cargar modelos necesarios
        $userModel = $this->model('User');
        $businessModel = $this->model('Business');
        $formModel = $this->model('Form');
        $responseModel = $this->model('Response');
        
        // Obtener estadísticas generales
        $stats = [
            'total_businesses' => $businessModel->count(['status' => 'active']),
            'total_users' => $userModel->count(['status' => 'active']),
            'total_forms' => $formModel->count(['status' => 'active']),
            'total_responses' => $responseModel->count(['status' => 'completed']),
            'pending_responses' => $responseModel->count(['status' => 'in_progress']),
        ];
        
        // Obtener usuarios por rol
        $usersByRole = $userModel->countByRole();
        $stats['admin_users'] = $usersByRole['admin'];
        $stats['encuestado_users'] = $usersByRole['encuestado'];
        
        // Obtener actividad reciente
        $recentActivity = $this->getRecentActivity();
        
        // Obtener empresas recientes
        $recentBusinesses = $businessModel->all(
            ['status' => 'active'], 
            'created_at DESC', 
            5
        );
        
        // Obtener formularios activos
        $activeForms = $formModel->all(
            ['status' => 'active'], 
            'created_at DESC', 
            5
        );
        
        // Obtener respuestas recientes completadas
        $recentResponses = $this->getRecentResponses(5);
        
        // Obtener estadísticas de respuestas por formulario
        $responsesByForm = $this->getResponsesByForm();
        
        // Preparar datos para la vista
        $data = [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentActivity' => $recentActivity,
            'recentBusinesses' => $recentBusinesses,
            'activeForms' => $activeForms,
            'recentResponses' => $recentResponses,
            'responsesByForm' => $responsesByForm
        ];
        
        // Renderizar vista con layout admin
        $this->view('admin/dashboard', $data, 'admin');
    }
    
    /**
     * Obtener actividad reciente del sistema
     * 
     * @return array
     */
    protected function getRecentActivity() {
        $userModel = $this->model('User');
        
        $sql = "
            SELECT 
                'user' as type,
                u.name as description,
                u.created_at as date,
                'Nuevo usuario registrado' as action
            FROM users u
            WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            
            UNION ALL
            
            SELECT 
                'business' as type,
                b.name as description,
                b.created_at as date,
                'Nueva empresa creada' as action
            FROM businesses b
            WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            
            UNION ALL
            
            SELECT 
                'form' as type,
                f.title as description,
                f.created_at as date,
                'Nuevo formulario creado' as action
            FROM forms f
            WHERE f.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            
            UNION ALL
            
            SELECT 
                'response' as type,
                CONCAT(u.name, ' - ', f.title) as description,
                r.submitted_at as date,
                'Respuesta completada' as action
            FROM responses r
            INNER JOIN users u ON r.user_id = u.id
            INNER JOIN forms f ON r.form_id = f.id
            WHERE r.submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND r.status = 'completed'
            
            ORDER BY date DESC
            LIMIT 10
        ";
        
        return $userModel->query($sql);
    }
    
    /**
     * Obtener respuestas recientes
     * 
     * @param int $limit
     * @return array
     */
    protected function getRecentResponses($limit = 10) {
        $responseModel = $this->model('Response');
        
        $sql = "
            SELECT 
                r.id,
                r.status,
                r.submitted_at,
                u.name as user_name,
                u.login as user_login,
                f.title as form_title,
                b.name as business_name
            FROM responses r
            INNER JOIN users u ON r.user_id = u.id
            INNER JOIN forms f ON r.form_id = f.id
            LEFT JOIN businesses b ON u.business_id = b.id
            WHERE r.status = 'completed'
            ORDER BY r.submitted_at DESC
            LIMIT :limit
        ";
        
        return $responseModel->query($sql, ['limit' => $limit]);
    }
    
    /**
     * Obtener estadísticas de respuestas por formulario
     * 
     * @return array
     */
    protected function getResponsesByForm() {
        $formModel = $this->model('Form');
        
        $sql = "
            SELECT 
                f.id,
                f.title,
                COUNT(r.id) as total_responses,
                SUM(CASE WHEN r.status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN r.status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN r.status = 'started' THEN 1 ELSE 0 END) as started
            FROM forms f
            LEFT JOIN responses r ON f.id = r.form_id
            WHERE f.status = 'active'
            GROUP BY f.id, f.title
            ORDER BY total_responses DESC
            LIMIT 5
        ";
        
        return $formModel->query($sql);
    }
    
    /**
     * Obtener datos para gráficos (AJAX)
     */
    public function getChartData() {
        // Verificar que sea una petición AJAX
        if (!$this->isAjax()) {
            $this->json(['error' => 'Petición inválida'], 403);
            return;
        }
        
        // Verificar autenticación
        $this->requireAuth('admin');
        
        $type = $this->input('type', 'responses');
        
        switch ($type) {
            case 'responses':
                $data = $this->getResponsesChartData();
                break;
                
            case 'users':
                $data = $this->getUsersChartData();
                break;
                
            case 'businesses':
                $data = $this->getBusinessesChartData();
                break;
                
            default:
                $data = [];
        }
        
        $this->json(['success' => true, 'data' => $data]);
    }
    
    /**
     * Datos para gráfico de respuestas
     * 
     * @return array
     */
    protected function getResponsesChartData() {
        $responseModel = $this->model('Response');
        
        $sql = "
            SELECT 
                DATE(submitted_at) as date,
                COUNT(*) as count
            FROM responses
            WHERE status = 'completed'
            AND submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(submitted_at)
            ORDER BY date ASC
        ";
        
        return $responseModel->query($sql);
    }
    
    /**
     * Datos para gráfico de usuarios
     * 
     * @return array
     */
    protected function getUsersChartData() {
        $userModel = $this->model('User');
        
        $sql = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as count
            FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ";
        
        return $userModel->query($sql);
    }
    
    /**
     * Datos para gráfico de empresas
     * 
     * @return array
     */
    protected function getBusinessesChartData() {
        $businessModel = $this->model('Business');
        
        $sql = "
            SELECT 
                sector,
                COUNT(*) as count
            FROM businesses
            WHERE status = 'active'
            AND sector IS NOT NULL
            GROUP BY sector
            ORDER BY count DESC
            LIMIT 10
        ";
        
        return $businessModel->query($sql);
    }
    
    /**
     * Exportar estadísticas del dashboard
     */
    public function exportStats() {
        // Verificar autenticación
        $this->requireAuth('admin');
        
        // TODO: Implementar exportación de estadísticas
        $this->setFlash('info', 'Funcionalidad de exportación en desarrollo');
        $this->redirect('admin/dashboard');
    }
}