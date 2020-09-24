<?php
    class View
    {
        function __construct()
        {
            
        }

        public function renderHtml($name, $data)
        {
            $this->data = $data;
            require 'view/'. $name . '.php';
        }
    }
    
?>