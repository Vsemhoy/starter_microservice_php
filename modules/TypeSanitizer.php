<?php
namespace modules;
use DateTime;

class TypeSanitizer
{
    public static function sanitizeObject(ObjectInterface $object): ObjectInterface
    {
        $class = get_class($object);
        if (property_exists($class, 'sanitize_map')) {
            $sanitizeMap = $class::$sanitize_map;

            foreach ($sanitizeMap as $field => $type) {
                if (property_exists($object, $field)) {
                    $value = $object->{$field};
                    $object->{$field} = self::sanitizeField($value, $type);
                }
            }
        }

        return $object;
    }

    public static function rebuildAndSanitizeObjectFromStd($object, $rawObj)
    {
        $class = get_class($object);
        if (property_exists($class, 'sanitize_map')) {
            $sanitizeMap = $class::$sanitize_map;
            $format = 0;
            foreach ($sanitizeMap as $field => $type) {
                if (property_exists($rawObj, $field)) {
                    $value = $rawObj->{$field};
                    if ($field == "content"){
                        if ($format == 0){
                            $type = 'string';
                        } else if ($format == 1) {
                            $type = 'html';
                        }
                    }
                    $result = self::sanitizeField($value, $type);
                    if ($field == "format"){
                        $format = $result;
                    }
                    
                    $object->{$field} = $result;
                }
            }
        }

        return $object;
    }

    public static function sanitizeField($value, string $type)
    {
        $type = strtolower($type);
        switch ($type) {
            case 'string':
            case 'text':
                return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
                break;
            
            case 'nstring':
                if ($value == null) {
                    return null;
                };
                return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
                break;
                
            case 'title':
                $cleanedString = str_replace(array('`', '\\'), '', $value);
                return trim(htmlspecialchars($cleanedString, ENT_QUOTES, 'UTF-8'));
                break;

            case 'html':
                $allowedTags = '<p><strong><em><ul><li><a><ol><div><table><td><tr><th><thead><tbody><span><br><hr><h3><h4><h5><pre><code>';
                 // Remove unwanted tags like <script> and <style>
                $cleanedHTML = strip_tags($value, $allowedTags);

                // Remove any other HTML tags not present in the allowed tags list
                $cleanedHTML = preg_replace('/<[^>]*>/', '', $cleanedHTML);

                $value = htmlspecialchars($cleanedHTML);
                return trim($value);
                break;

            case 'int':
                if (is_numeric($value)){
                    return (int)$value;
                }
                $sanitizedValue = preg_replace('/[^-0-9]/', '', $value);
                // Type casting to integer
                $sanitizedInt = (int)$sanitizedValue;
                // Range validation (optional)
                // $minValue = 0;
                // $maxValue = 100;
                // $sanitizedInt = max($minValue, min($maxValue, $sanitizedInt));
                return $sanitizedInt;
                break;

            case 'float':
                if (is_float($value)) {
                    return $value;
                };
                if (is_numeric($value)) {
                    return (float)$value;
                };
                $sanitizedValue = preg_replace('/[^-0-9.]/', '', $value);
                // Type casting to float
                $sanitizedFloat = (float)$sanitizedValue;
                break;

            case 'json':
                $sanitizedData = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                return $sanitizedData;
                break;

            case 'datetime':
                $timestampValue = new DateTime($value);
                return $timestampValue->format('Y-m-d H:i:s');
                break;

            case 'date':
                $parsedDate = date_parse($value);
                $datestring = "";
                if (!$parsedDate || $parsedDate['error_count'] > 0) {
                    // Handle validation error (e.g., return an error message or log it)
                    return date("Y-m-d");
                }
                $datestring = sprintf("%04d-%02d-%02d", $parsedDate['year'], $parsedDate['month'], $parsedDate['day']);
                break;

            case 'name':
                return $sanitizedString = preg_replace('/[^a-zA-Z0-9_]/', '', $value);
                break;

            case 'operator':
                return $sanitizedString = preg_replace('/[^a-zA-Z=><]/', '', $value);
                break;

            // Add more cases for other data types as needed

            default:
                // No specific sanitization for this type
                break;
        }

        return $value;
    }
}