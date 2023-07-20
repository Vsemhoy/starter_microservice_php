<?php
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

    private static function sanitizeField($value, string $type)
    {
        switch ($type) {
            case 'string':
                // Apply your string sanitization here if needed
                // Example: $value = htmlspecialchars($value);
                break;
                
            case 'html':
                // Apply your integer sanitization here if needed
                // Example: $value = (int) $value;
                break;

            case 'int':
                // Apply your integer sanitization here if needed
                // Example: $value = (int) $value;
                break;

            case 'float':
                // Apply your integer sanitization here if needed
                // Example: $value = (int) $value;
                break;

            case 'json':
                // Apply your integer sanitization here if needed
                // Example: $value = (int) $value;
                break;

            case 'datetime':
                // Apply your integer sanitization here if needed
                // Example: $value = (int) $value;
                break;

            case 'date':
                // Apply your integer sanitization here if needed
                // Example: $value = (int) $value;
                break;

            // Add more cases for other data types as needed

            default:
                // No specific sanitization for this type
                break;
        }

        return $value;
    }
}