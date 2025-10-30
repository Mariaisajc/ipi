<?php
/**
 * Modelo: QuestionType
 * Gestiona los tipos de preguntas disponibles
 */

class QuestionType extends Model {
    protected $table = 'question_types';
    
    /**
     * Obtener todos los tipos de preguntas
     */
    public function getAll($db_connection = null) {
        $sql = "SELECT * FROM {$this->table} ORDER BY id ASC";
        return $this->query($sql, [], $db_connection);
    }

    /**
     * Obtener un tipo por su ID
     */
    public function getById($id, $db_connection = null) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $result = $this->query($sql, [$id], $db_connection);
        return $result ? $result[0] : null;
    }
    
    /**
     * Obtener tipo por nombre
     */
    public function getByName($name, $db_connection = null) {
        $sql = "SELECT * FROM {$this->table} WHERE name = ?";
        $result = $this->query($sql, [$name], $db_connection);
        return $result ? $result[0] : null;
    }
    
    /**
     * Verificar si un tipo requiere opciones
     */
    public function requiresOptions($typeId) {
        $type = $this->getById($typeId);
        if (!$type) {
            return false;
        }
        
        // Tipos que requieren opciones
        $typesWithOptions = ['radio', 'checkbox', 'select'];
        return in_array($type['name'], $typesWithOptions);
    }
    
    /**
     * Obtener tipos que requieren opciones
     */
    public function getTypesWithOptions() {
        $sql = "SELECT * FROM {$this->table} 
                WHERE name IN ('radio', 'checkbox', 'select') 
                ORDER BY id ASC";
        
        return $this->query($sql);
    }
    
    /**
     * Obtener tipos de texto
     */
    public function getTextTypes() {
        $sql = "SELECT * FROM {$this->table} 
                WHERE name IN ('text', 'textarea', 'email') 
                ORDER BY id ASC";
        
        return $this->query($sql);
    }
    
    /**
     * Obtener configuración de renderizado por tipo
     */
    public function getRenderConfig($typeId) {
        $type = $this->getById($typeId);
        if (!$type) {
            return null;
        }
        
        $config = [
            'name' => $type['name'],
            'requires_options' => $this->requiresOptions($typeId),
            'input_type' => $this->getInputType($type['name']),
            'supports_placeholder' => $this->supportsPlaceholder($type['name']),
            'supports_help_text' => true
        ];
        
        return $config;
    }
    
    /**
     * Obtener tipo de input HTML según el tipo de pregunta
     */
    private function getInputType($typeName) {
        $mapping = [
            'text' => 'text',
            'textarea' => 'textarea',
            'number' => 'number',
            'email' => 'email',
            'date' => 'date',
            'radio' => 'radio',
            'checkbox' => 'checkbox',
            'select' => 'select',
            'scale' => 'range'
        ];
        
        return $mapping[$typeName] ?? 'text';
    }
    
    /**
     * Verificar si el tipo soporta placeholder
     */
    private function supportsPlaceholder($typeName) {
        $typesWithPlaceholder = ['text', 'textarea', 'number', 'email', 'date'];
        return in_array($typeName, $typesWithPlaceholder);
    }
    
    /**
     * Obtener ícono de Bootstrap para el tipo
     */
    public function getIcon($typeId) {
        $type = $this->getById($typeId);
        if (!$type) {
            return 'bi-question-circle';
        }
        
        $icons = [
            'text' => 'bi-textarea-t',
            'textarea' => 'bi-textarea',
            'number' => 'bi-hash',
            'email' => 'bi-envelope',
            'date' => 'bi-calendar',
            'radio' => 'bi-circle',
            'checkbox' => 'bi-check-square',
            'select' => 'bi-list',
            'scale' => 'bi-bar-chart'
        ];
        
        return $icons[$type['name']] ?? 'bi-question-circle';
    }
}