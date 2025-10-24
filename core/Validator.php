<?php
/**
 * IPI - Innovation Performance Index
 * Clase: Validator
 * 
 * Sistema de validación de datos de formularios
 */

class Validator {
    protected $errors = [];
    protected $data = [];
    
    /**
     * Validar datos según reglas
     * 
     * @param array $data Datos a validar
     * @param array $rules Reglas de validación
     * @return array|bool Array de errores o true si es válido
     */
    public function validate($data, $rules) {
        $this->errors = [];
        $this->data = $data;
        
        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            
            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $rule);
            }
        }
        
        return empty($this->errors) ? true : $this->errors;
    }
    
    /**
     * Aplicar una regla de validación
     * 
     * @param string $field
     * @param string $rule
     * @return void
     */
    protected function applyRule($field, $rule) {
        // Parsear regla con parámetros (ejemplo: min:3, max:10)
        $ruleParts = explode(':', $rule);
        $ruleName = $ruleParts[0];
        $ruleParams = isset($ruleParts[1]) ? explode(',', $ruleParts[1]) : [];
        
        $value = $this->data[$field] ?? null;
        
        // Aplicar la regla correspondiente
        switch ($ruleName) {
            case 'required':
                $this->validateRequired($field, $value);
                break;
                
            case 'email':
                $this->validateEmail($field, $value);
                break;
                
            case 'min':
                $this->validateMin($field, $value, $ruleParams[0] ?? 0);
                break;
                
            case 'max':
                $this->validateMax($field, $value, $ruleParams[0] ?? 0);
                break;
                
            case 'numeric':
                $this->validateNumeric($field, $value);
                break;
                
            case 'integer':
                $this->validateInteger($field, $value);
                break;
                
            case 'alpha':
                $this->validateAlpha($field, $value);
                break;
                
            case 'alphanumeric':
                $this->validateAlphanumeric($field, $value);
                break;
                
            case 'date':
                $this->validateDate($field, $value);
                break;
                
            case 'url':
                $this->validateUrl($field, $value);
                break;
                
            case 'in':
                $this->validateIn($field, $value, $ruleParams);
                break;
                
            case 'unique':
                $this->validateUnique($field, $value, $ruleParams[0] ?? null, $ruleParams[1] ?? null);
                break;
                
            case 'confirmed':
                $this->validateConfirmed($field, $value);
                break;
                
            case 'same':
                $this->validateSame($field, $value, $ruleParams[0] ?? null);
                break;
        }
    }
    
    /**
     * Validar campo requerido
     */
    protected function validateRequired($field, $value) {
        if (empty($value) && $value !== '0') {
            $this->addError($field, "El campo {$field} es requerido");
        }
    }
    
    /**
     * Validar email
     */
    protected function validateEmail($field, $value) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "El campo {$field} debe ser un email válido");
        }
    }
    
    /**
     * Validar longitud mínima
     */
    protected function validateMin($field, $value, $min) {
        if (!empty($value) && strlen($value) < $min) {
            $this->addError($field, "El campo {$field} debe tener al menos {$min} caracteres");
        }
    }
    
    /**
     * Validar longitud máxima
     */
    protected function validateMax($field, $value, $max) {
        if (!empty($value) && strlen($value) > $max) {
            $this->addError($field, "El campo {$field} no puede tener más de {$max} caracteres");
        }
    }
    
    /**
     * Validar numérico
     */
    protected function validateNumeric($field, $value) {
        if (!empty($value) && !is_numeric($value)) {
            $this->addError($field, "El campo {$field} debe ser numérico");
        }
    }
    
    /**
     * Validar entero
     */
    protected function validateInteger($field, $value) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, "El campo {$field} debe ser un número entero");
        }
    }
    
    /**
     * Validar solo letras
     */
    protected function validateAlpha($field, $value) {
        if (!empty($value) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $value)) {
            $this->addError($field, "El campo {$field} solo puede contener letras");
        }
    }
    
    /**
     * Validar alfanumérico
     */
    protected function validateAlphanumeric($field, $value) {
        if (!empty($value) && !preg_match('/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]+$/', $value)) {
            $this->addError($field, "El campo {$field} solo puede contener letras y números");
        }
    }
    
    /**
     * Validar fecha
     */
    protected function validateDate($field, $value) {
        if (!empty($value)) {
            $date = DateTime::createFromFormat('Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                $this->addError($field, "El campo {$field} debe ser una fecha válida (YYYY-MM-DD)");
            }
        }
    }
    
    /**
     * Validar URL
     */
    protected function validateUrl($field, $value) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "El campo {$field} debe ser una URL válida");
        }
    }
    
    /**
     * Validar valor dentro de una lista
     */
    protected function validateIn($field, $value, $options) {
        if (!empty($value) && !in_array($value, $options)) {
            $this->addError($field, "El campo {$field} debe ser uno de: " . implode(', ', $options));
        }
    }
    
    /**
     * Validar valor único en la base de datos
     */
    protected function validateUnique($field, $value, $table, $exceptId = null) {
        if (empty($value) || empty($table)) {
            return;
        }
        
        require_once CORE_PATH . '/Model.php';
        $model = new Model();
        
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$field} = :value";
        
        if ($exceptId) {
            $sql .= " AND id != :exceptId";
            $result = $model->query($sql, ['value' => $value, 'exceptId' => $exceptId]);
        } else {
            $result = $model->query($sql, ['value' => $value]);
        }
        
        if ($result[0]['count'] > 0) {
            $this->addError($field, "El valor del campo {$field} ya está en uso");
        }
    }
    
    /**
     * Validar confirmación de campo (ejemplo: password_confirmation)
     */
    protected function validateConfirmed($field, $value) {
        $confirmField = $field . '_confirmation';
        $confirmValue = $this->data[$confirmField] ?? null;
        
        if ($value !== $confirmValue) {
            $this->addError($field, "La confirmación del campo {$field} no coincide");
        }
    }
    
    /**
     * Validar que sea igual a otro campo
     */
    protected function validateSame($field, $value, $otherField) {
        $otherValue = $this->data[$otherField] ?? null;
        
        if ($value !== $otherValue) {
            $this->addError($field, "El campo {$field} debe ser igual a {$otherField}");
        }
    }
    
    /**
     * Agregar error de validación
     */
    protected function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    /**
     * Obtener errores
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Obtener el primer error de un campo
     */
    public function getFirstError($field) {
        return isset($this->errors[$field]) ? $this->errors[$field][0] : null;
    }
    
    /**
     * Verificar si hay errores
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
}