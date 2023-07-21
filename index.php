<?php
// This file generated by {calling} builder.php from source code! Do not change this file, change source code!
  require_once('C:\OSPanel\domains\microservice\objects\Category.php');
  require_once('C:\OSPanel\domains\microservice\objects\Event.php');
  require_once('C:\OSPanel\domains\microservice\objects\Section.php');
  require_once('C:\OSPanel\domains\microservice\modules\TypeSanitizer.php');
  require_once('C:\OSPanel\domains\microservice\server\DB.php');
  require_once('C:\OSPanel\domains\microservice\server\Host.php');
  require_once('C:\OSPanel\domains\microservice\server\Response.php');
  require_once('C:\OSPanel\domains\microservice\server\Task.php');

  use objects\Category;
  use objects\Event;
  use objects\Section;
  use modules\TypeSanitizer;
  use server\DB;
  use server\Host;
  use server\Response;
  use server\Task;
?>

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

    function getTypeByName(string $type)
    {
        $class = "objects\\" . ucfirst($type);
        if (!class_exists(getTypeByName($class))) {
            return null;
        }
        $object = new $class();
        return $object;
    };

    function buildObject(object $object, $instance)
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


    // *
    // Collect received tasks into array 
    $tasks = [];
    foreach($inputObj->tasks AS $taskObject)
    {
      $task = Task::taskFromObject($taskObject, (int)$inputObj->user);
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
      array_push($tasks, $task);
    };


    // Handle the tasks
    foreach ($tasks AS $task)
    {
        if (!isset($task->action)){ continue; }
        $task->type = ucfirst(TypeSanitizer::sanitizeField( $task->type, 'name'));
        $class = "objects\\" . ucfirst($task->type);
        $task->map = $class::getSanitizeMap();
        
        if (is_array($task->where))
        {
          foreach($task->where AS $tv)
          {
            $wh = Task::Where();
            $wh->column   = TypeSanitizer::sanitizeField( $tv->column, 'name');
            $wh->value    = TypeSanitizer::sanitizeField( $tv->value, 'string');
            if (!isset($tv->operator) || $tv->operator == ""){
                    $wh->operator = "=";
                  } else {
                    $wh->operator = TypeSanitizer::sanitizeField( $tv->operator, 'operator');
                  }
                  $tv = $wh;
                };
              };
              
              switch ($task->action) {
                case 1:
              //    echo "PIZDEC";
                  $rowData = DB::getRows($task);
                  if ($rowData == false){
                      break; 
                    };
                  $task->results = $rowData;
                
                break;
        }


    }

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


    $evt = Event::createTableQueryText();
    DB::createTable($evt);
    $evt = Category::createTableQueryText();
    DB::createTable($evt);
    $evt = Section::createTableQueryText();
    DB::createTable($evt);


    $event = new Event("The name of second... ZZZ", 7);
    print_r($event);
    echo DB::writeObject($event);

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