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

    $input = file_get_contents('php://input');
    $inputObj = json_decode($input);

    function GetTypeByName(string $type)
    {
        $class = "objects\\" . ucfirst($type);
        $object = new $class();
        return $object;
    };

    function BuildObject(object $object, $instance)
    {
        foreach($instance AS $key => $value)
        {
          if (isset($object[$key]))
          {
            $instance[$key] = $object[$key];
          } else {
            unset($instance[$key]);
          }
        }
        return $instance;
    };

    // Handle format errors
    if (!isset($inputObj->user))
    {
        $response = new Response();
        $response->status = 1;
        $response->message = "There is no user section detected!";
        print_r($response);
        return;
    };

    if (!isset($inputObj->tasks))
    {
        $response = new Response();
        $response->status = 1;
        $response->message = "There is no task section detected!";
        if (!isset($inputObj->user)){
            $response->user = (int)$inputObj->user;
        }
        print_r($response);
        return;
    };

    if (is_array($inputObj->tasks) && count($inputObj->tasks) == 0)
    {
        $response = new Response();
        $response->status = 1;
        $response->message = "There is no one task detected!";
        if (!isset($inputObj->user)){
            $response->user = (int)$inputObj->user;
        }
        print_r($response);
        return;
    };


    $tasks = [];
    foreach($inputObj->tasks AS $taskObject)
    {
      $task = Task::TaskFromObject($taskObject, (int)$inputObj->user);
      if (is_string($task))
      {
        $response = new Response();
        $response->status = 1;
        $response->message = "The problem with task definition: " . $task ;
        if (!isset($inputObj->user)){
            $response->user = (int)$inputObj->user;
        }
        print_r($response);
        return;
      }
      array_push($tasks, $task->Simplify());
    };


    print_r($tasks);
    
    echo "<br>";
    echo $inputObj->user;

    return;

    
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
    $evt = Category::CreateTableQueryText();
    DB::CreateTable($evt);
    $evt = Section::CreateTableQueryText();
    DB::CreateTable($evt);


    $event = new Event("The name of second... ZZZ", 7);
    print_r($event);
    echo DB::WriteObject($event);

    echo $event->Name();
    echo "<br>";
    echo "<br>";
    print_r(DB::GetRows("event", "user", 7));

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