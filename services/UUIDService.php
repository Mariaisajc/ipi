<?php
/**
 * IPI - Innovation Performance Index
 * Servicio: UUIDService
 * 
 * Servicio para generar y validar UUIDs (Universally Unique Identifiers)
 * Utilizado principalmente para la tabla responses
 */

class UUIDService {
    
    /**
     * Generar UUID v4
     * 
     * @return string UUID en formato 8-4-4-4-12 (36 caracteres)
     */
    public static function generate() {
        // Generar 16 bytes aleatorios
        $data = random_bytes(16);
        
        // Establecer el byte de versión a 0100 (versión 4)
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        
        // Establecer los bits de variante a 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        // Formatear como UUID
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    /**
     * Validar si un string es un UUID válido
     * 
     * @param string $uuid
     * @return bool
     */
    public static function isValid($uuid) {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        return preg_match($pattern, $uuid) === 1;
    }
    
    /**
     * Generar múltiples UUIDs únicos
     * 
     * @param int $count Cantidad de UUIDs a generar
     * @return array
     */
    public static function generateMultiple($count) {
        $uuids = [];
        
        for ($i = 0; $i < $count; $i++) {
            $uuids[] = self::generate();
        }
        
        return $uuids;
    }
    
    /**
     * Convertir UUID a formato binario (para almacenamiento eficiente)
     * 
     * @param string $uuid
     * @return string
     */
    public static function toBinary($uuid) {
        $uuid = str_replace('-', '', $uuid);
        return pack('H*', $uuid);
    }
    
    /**
     * Convertir UUID binario a formato string
     * 
     * @param string $binary
     * @return string
     */
    public static function fromBinary($binary) {
        $hex = unpack('H*', $binary)[1];
        return substr($hex, 0, 8) . '-' . 
               substr($hex, 8, 4) . '-' . 
               substr($hex, 12, 4) . '-' . 
               substr($hex, 16, 4) . '-' . 
               substr($hex, 20);
    }
    
    /**
     * Generar UUID a partir de un string (determinístico)
     * Útil para generar IDs consistentes basados en datos
     * 
     * @param string $string
     * @return string
     */
    public static function generateFromString($string) {
        $hash = md5($string);
        
        return sprintf(
            '%08s-%04s-4%03s-%04s-%012s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 13, 3),
            dechex(hexdec(substr($hash, 16, 4)) & 0x3fff | 0x8000),
            substr($hash, 20, 12)
        );
    }
}