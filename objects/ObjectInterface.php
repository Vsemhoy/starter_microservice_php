<?php
namespace objects;
    interface ObjectInterface
    {
        public static function createTableQueryText();
        /// Update Id to new generated
        public function FreshId();
        /// Get name of the class type
        public function Name();
    }
?>