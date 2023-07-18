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
    
    echo uniqid();
    echo "<br>";
    echo uniqid('', true);
    echo "<br>";


    $timestamp = '1299762201428';
    $date = date('Y-m-d H:i:s', substr($timestamp, 0, -3));
    

    $ev = new Event("Hellow", 33);
    print_r($ev);


    echo $_SERVER['REMOTE_ADDR'];

    $evt = Event::CreateTableQueryText();
    DB::CreateTable($evt);


    $event = new Event("The name of second... ZZZ", 7);
    print_r($event);
    echo DB::WriteObject($event);

    echo $event->Name();

    // `page_ID` INT AUTO_INCREMENT NOT NULL,
    // `url` varchar(200) NOT NULL,
    // `title` varchar(200),
    // `content` TEXT,
    // `parent` varchar(10),
    // `privacy` varchar(1),
    // `status` varchar(1),
    // `creation` varchar(30),
    // PRIMARY KEY (`page_ID`)) 
    // CHARACTER SET utf8 COLLATE utf8mb4_general_ci
?>