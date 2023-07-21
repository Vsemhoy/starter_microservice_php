<?php
namespace modules;

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

    public static function sanitizeField($value, string $type)
    {
        $type = strtolower($type);
        switch ($type) {
            case 'string':
            case 'text':
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                break;
                
            case 'title':
                $cleanedString = str_replace(array('`', '\\'), '', $value);
                return htmlspecialchars($cleanedString, ENT_QUOTES, 'UTF-8');
                break;

            case 'html':
                $allowedTags = '<p><strong><em><ul><li><a><ol><div><table><td><tr><th><thead><tbody><span><br><hr><h3><h4><h5><pre><code>';
                 // Remove unwanted tags like <script> and <style>
                $cleanedHTML = strip_tags($value, $allowedTags);

                // Remove any other HTML tags not present in the allowed tags list
                $cleanedHTML = preg_replace('/<[^>]*>/', '', $cleanedHTML);

                $value = htmlspecialchars($cleanedHTML);
                return $value;
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
                if (!$parsedDate || $parsedDate['error_count'] > 0) {
                    // Handle validation error (e.g., return an error message or log it)
                    $parsedDate = date("Y-m-d");
                }
                return $parsedDate;
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