<?php
// this source code stores in /source/index_source.php
    // echo A::Hello();
    // echo C::Hello();
    // echo D::Hello();

    // Allow access from only approved hosts
    if (!Host::Allowed(Host::CHECKTOKEN))
    {
        http_response_code(400); exit;
    }
    
    



    




    echo $_SERVER['REMOTE_ADDR'];
?>